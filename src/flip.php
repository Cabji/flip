<?php
// re-write of the bank statement PDF analyzer convert_uudecode

/* data acquisition - what data do we need?
    1. input data (the copy and pasted PDF text usually stored in a text file) - passed as first argument to the
    2. the bank, or "template" name
*/

// deal with cmd line options and arguments first

$arguments = array();
$o = null;
$options = getopt("h:t:");
$aa_settings = array();

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
            $o .= "Options\n  -h\tShow help\n  -t\tTemplate to use for processing";
            $o .= "\n\nEnd of help\n";
            die ($o);
            break;
        case 't':
            if ($value == "") {$value = "default";}
            $aa_settings["template"] = $value;
            break;
    }
}

if (!isset($aa_settings["template"])) {$aa_settings["template"] = "default";}

foreach ($arguments as $argument) {
    // get the first argument as the input file
    $aa_settings["inputFile"] = $argument;
    break;
}

// now we should have our settings in $aa_settings. set some default values

$aa_settings["optionsOutputTypes"]          = "SQLite3, CSV";
$aa_settings["optionsCSVSeperator"]         = ",";
$aa_settings["optionsPDFtoTextCmd"]         = "pdftotext -q -raw #INPUT# #OUTPUT#";
$aa_settings["defaultOutputType"]           = "CSV";
$aa_settings["defaultOutputFile"]           = "output/defaultOutput.file";
$aa_settings["defaultCSVFields"]            = "bank,account,trxDate,trxDescription,trxValue";
$aa_settings["defaultSqlite3TableName"]     = "trxdata";
$aa_settings["defaultSqlite3TableFields"]   = "userid,bank,account,trxDate,trxDescription,trxValue";

$aa_Output["RedErrorTitle"]     = "\e[31m[Error]\e[39m\n\t";
$aa_Output["BlueStartTitle"]    = "\e[34m[Start]\e[39m\n\t";
$aa_Output["CyanOk"]           = "\e[36mOk\e[39m";
$aa_Output["RedFail"]           = "\e[91mFail\e[39m";

// check we were given an input file, die if we weren't.
if (!isset($aa_settings["inputFile"])) {die("No input file was supplied. Use: flip.php -h for help\n");}

// assuming we have input file argument if we get to here
$tName = $aa_settings["template"];

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

// set temp filename
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
// if we get here, we should have out input content in $f

echo "\n\t  • Apply regexes to text data";
// template associative array that holds regexes for processing text - this is what you need to alter to configure a new template
$aa_template[$tName]["regexes"] = array("/(?# Initial Regex to extract TRX Data)(.*?)(Date Particulars Debits Credits Balance\s?)((\d{1,2} (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4}\sBrought forward\s((\d{0,3})(\,{0,1})*(\d{0,3})\.(\d{1,2}) (Cr|Dr)))|Brought forward)(.*?)(Carried forward|Identifying.*)/is" => "$12", 
                                        "/(?# Remove excessive ... dots from trx data)\.\.\.*/is" => "",
                                        "/(?# Group by Date and find Closing Balances)(\d{1,2} (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4})(.*?)((Cr|Dr)\s(\d{0,3})(\,{0,1})*(\d{0,3})(\.)(\d{1,2}))/" => "Date: $1\n$3\nDate Closing Balance $6$7$8$9$10 $5#DATEBREAK#");

$c = 0;
foreach ($aa_template[$tName]["regexes"] as $regExp => $subExp) 
{
    $c++;
    $f = preg_replace($regExp,$subExp,$f);
    // check if the regExp starts with a comment
    if (preg_match('/^\/\(\?\#/', $regExp))
    {
        // pull the regex comment from the regex
        if (preg_match('/^\/\(\?\#(.*?)\)/', $regExp, $matches)) {$regExpName = trim($matches[1]);}
    } else {
        $regExpName = "Regex $c";
    }
    echo "\n\t    • $regExpName";
}

echo "\n";
file_put_contents("temp.txt", $f);
?>