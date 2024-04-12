<?php
// re-write of the bank statement PDF analyzer convert_uudecode

/* data acquisition - what data do we need?
    1. input data (the copy and pasted PDF text usually stored in a text file) - passed as first argument to the
    2. the bank, or "template" name
*/

// set some default values
$aa_settings = array();
$aa_settings["optionsOutputTypes"]          = "SQLite3, CSV";
$aa_settings["optionsCSVSeperator"]         = ",";
$aa_settings["optionsPDFtoTextCmd"]         = "pdftotext -q -raw #INPUT# #OUTPUT#";
$aa_settings["defaultOutputType"]           = "CSV";
$aa_settings["defaultOutputFile"]           = "output/defaultOutput.file";
$aa_settings["defaultCSVFields"]            = "bank,account,trxDate,trxDescription,trxValue";
$aa_settings["defaultCSVDateFormat"]        = "d/m/Y";
$aa_settings["defaultSqlite3TableName"]     = "trxdata";
$aa_settings["defaultSqlite3TableFields"]   = "userid,bank,account,trxDate,trxDescription,trxValue";
$aa_settings["ncp"]                         = false;
$aa_settings["of"]                          = false;
$aa_settings["tttc"]                        = false;

$aa_Output["RedErrorTitle"]     = "\e[31m[Error]\e[39m\n\t";
$aa_Output["BlueStartTitle"]    = "\e[34m[Start]\e[39m\n\t";
$aa_Output["CyanOk"]            = "\e[36mOk\e[39m";
$aa_Output["OrangeNotice"]      = "\e[93mNotice\e[39m";
$aa_Output["RedFail"]           = "\e[91mFail\e[39m";

// deal with cmd line options and arguments

$arguments = array();
$d = null;
$o = null;
$options = getopt("h:t:");
$a_result = null;
// get the relative path of the working dir to the flip.php dir
$relativePath = ".".substr(__DIR__, strlen(realpath($_SERVER['DOCUMENT_ROOT'])))."/";

// aig
foreach ($argv as $key => $arg) {
    if ($key == 0) {
        continue;
    }
    if (strpos($arg, '-') !== 0) {
        if (isset($argv[$key - 1]) && strpos($argv[$key - 1], '-') === 0) {
            continue;
        }
        $arguments[] = $arg;
    } else {
        $options[substr($arg, 1)] = isset($argv[$key + 1]) && strpos($argv[$key + 1], '-') !== 0 ? $argv[$key + 1] : null;
    }
}

// handle cmd line option switches and values
foreach ($options as $key => $value) {
    switch ($key) {
        case 'h':
            $o  = "flip help\n\n";
            $o .= "Usage\n   php flip.php [-o optionValue ...] <inputfile>\n\n";
            $o .= "Options\n";
            $o .= "  -h\tShow help\n";
            $o .= "  -ncp\tNo custom processing (don't do custom processing)\n";
            $o .= "  -t\tTemplate to use for processing\n";
            $o .= "  -tttc\tText: to Title Case\n";
            $o .= "\nEnd of help\n";
            die ($o);
            break;
        case 't':
            if ($value == "") {$value = "default";}
            $aa_settings["template"] = $value;
            break;
        case 'tttc':
            $aa_settings["tttc"] = true;
            break;
        case 'ncp':
            $aa_settings["ncp"] = true;
            break;
        case 'of':
            if ($value == "") {$value = false;}
            $aa_settings["of"] = $value;
            break;
    }
}

// deal with getting the template settings
if (!isset($aa_settings["template"])) {$aa_settings["template"] = "default";}
$tName = $aa_settings["template"];
include($relativePath."templates/".$tName.".template.php");
if (!isset($aa_template[$tName]["regexes"])) {die("Template file does not exist at: ".$relativePath."templates/".$tName.".template.php\n");}

// deal with getting the inputFile argument
foreach ($arguments as $argument) {
    // get the first argument as the input file
    $aa_settings["inputFile"] = $argument;
    break;
}

// check we were given an input file, die if we weren't.
if (!isset($aa_settings["inputFile"])) {die("No input file was supplied. Use: flip.php -h for help\n");}

// assuming we have input file argument if we get to here
/********************************* Stage 1: Read input file contents *******************************************************/
echo "flip - ".$aa_settings["inputFile"]." using template: $tName\n";

// read from PDF to txt using pdftotext cmd
$search = array("#INPUT#", "#OUTPUT#");
$replace = array($aa_settings["inputFile"], "temp/".pathinfo($aa_settings["inputFile"], PATHINFO_FILENAME).".txt");
$cmd = str_replace($search, $replace, $aa_settings["optionsPDFtoTextCmd"]);
$termCode = null;
$output = null;

echo $aa_Output["BlueStartTitle"]."\n\tConvert input PDF file to text\n\t  • $cmd ===> ";
exec($cmd, $output, $termCode);
if ($termCode === 0) {echo $aa_Output["CyanOk"]."\n";} else {echo $aa_Output["RedFail"]."\n\nCheck file path, existence and permissions.\n"; die();}

// set temp output filename
$tF = "temp/".pathinfo($aa_settings["inputFile"], PATHINFO_FILENAME).".txt";

echo "\n\tParse text data using template's regexes\n\t  • Temp file exists: $tF ===> ";
// read file contents into a string
if (!file_exists($tF)) { die($aa_Output["RedFail"]."\n\nText input file does not exist. Check pdftotext conversion worked, path/filename/permissions and try again.\n"); }
else {echo $aa_Output["CyanOk"];}

echo "\n\t  • Read Temp File Contents ===> ";
$f = file_get_contents($tF);
if (!isset($f)) {die($aa_Output["RedFail"]."\n\nFailed to read input file contents.\n");}
else {echo $aa_Output["CyanOk"];
}

// if we get here, we should have our input content in $f
echo "\n\t  • Apply regexes to text data";

// $aa_template[$tName]["regexes"]
// template associative array that holds regexes for processing text - is found in src/templates/default.template or your template config file

$c = 0;
foreach ($aa_template[$tName]["regexes"] as $regExp => $subExp) 
{
    $c++;
    $f = preg_replace($regExp,$subExp,$f);
    // output the regex name to stdout - check if the regExp starts with a comment
    if (preg_match('/^\/\(\?\#/', $regExp))
    {
        // pull the regex comment from the regex
        if (preg_match('/^\/\(\?\#(.*?)\)/', $regExp, $matches)) {$regExpName = trim($matches[1]);}
    } else {
        $regExpName = "Regex $c";
    }
    echo "\n\t    • $regExpName";
}
unset($c);

// convert input data strings to Title Case if required
if ($aa_settings["tttc"] == true)
{
    echo "\n\t-tttc: Converting all words to Title Case ===> ";
    $f = ucwords(strtolower($f)," \t\r\n\f\v,");
    echo $aa_Output["CyanOk"];
}

// output to user defined output file else output to temp.txt
if ($aa_settings["of"])
{
    file_put_contents($aa_settings["of"],$f);
}
else
{
    file_put_contents("temp.txt",$f);
}

if ($aa_settings["ncp"] == false)
{
    // now we need to use custom processing to convert the string with trx data into a standard format object.
    if (file_exists($relativePath."templates/$tName.customCode.php")) 
    {
        echo "\n\tRunning $tName custom processing code"; 
        include $relativePath."templates/$tName.customCode.php";
    }
    else {echo "\n\t".$aa_Output["OrangeNotice"]." custom processing code for template $tName was not found - $relativePath.templates/$tName.customCode.php does not exist\n\t - This is ok, custom processing is not always required. Use option -ncp to prevent custom processing entirely.\n\t   Look in temp.txt for processed data.";}
}
else
{
    echo "\n\t-ncp: No custom processing - Your processed data will be in temp.txt";
}

// now we should have standard format in $a_result that we can output
$aa_settings["optionsOutputTypes"]          = "SQLite3, CSV";
$aa_settings["defaultCSVFields"]            = "bank,account,trxDate,trxDescription,trxValue";

$dateFormat = $aa_settings["defaultCSVDateFormat"];
$sep = $aa_settings["optionsCSVSeperator"];

if (isset($a_result) && count($a_result) >= 1)
{
    include_once "$relativePath/include/fn-verifyUserInput.php";
    $bank = false;
    $outputType = false;
    $s_o = false;
    $verified = false;
    echo "\n\tOutput Processed Data\n"; 
    // ask user for output options

    // get output file name
    while (!$outf || !$verified)
    {
        $outf = readline("  Output filename (Default: ".$aa_settings["defaultOutputFile"]."): ");
        // verify user input call to custom func
        if ($outf == "") {$outf = $aa_settings["defaultOutputFile"];}
        if ($aa_template["$tName"]["verifyUserInput"]) {$verified = verifyUserInput($outf);}
        else {$verified = true;}
    }
    $verified = false;

    while ($outputType != "sqlite3" && $outputType != "csv")
    {
        $outputType = strtolower(readline("\n\t  Select output type (".$aa_settings["optionsOutputTypes"].") Default: [".$aa_settings["defaultOutputType"]."]: "));
        if ($outputType == "") {$outputType = strtolower($aa_settings["defaultOutputType"]);}
    }
    if ($outputType == "csv")
    {
        $s_o .= $aa_settings["defaultCSVFields"]."\n";
        foreach ($a_result as $i => $dateGroupData)
        {
            // skip the first entry as it's just opening balance
            if ($i != 0)
            {
                foreach ($dateGroupData["transactions"] as $j => $trxStr)
                {
                    // explode the trxStr into trx description and trx value - note: 0 is trxVal and 1 is trxDesc because strrev()
                    $a_trxVal = explode(",",strrev($trxStr),2);
                    $a_trxVal[0] = strrev($a_trxVal[0]);
                    $a_trxVal[1] = strrev($a_trxVal[1]);
                    $dotPos = (strlen($a_trxVal[0]) - 2); // Calculate the position for the period
                    $a_trxVal[0] = substr_replace($a_trxVal[0], '.', $dotPos, 0);
                    $s_o .= "$bank$sep$tName$sep".date($dateFormat,$dateGroupData["date"])."$sep".$a_trxVal[1]."$sep".$a_trxVal[0]."\n";
                }
            }
        }
    }
    if (file_put_contents($outf, $s_o)) {echo "Data wrote to output file successfully.\n";}
    else {echo "Write data to file FAILED.\n";}
}

// debug output
//$d .= print_r($a_result, true);

echo "\n";
file_put_contents($relativePath."../temp/debug.txt", $d);

// save tempate settings to template file
$saveFile = $relativePath."templates/$tName.template";
file_put_contents($saveFile, serialize($aa_template[$tName]["regexes"]));

?>