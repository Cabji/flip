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

foreach ($arguments as $argument) {
    // get the first argument as the input file
    $aa_settings["inputFile"] = $argument;
    break;
}

// now we should have our settings in $aa_settings. set some default values

$aa_settings["optionsOutputTypes"]          = "SQLite3, CSV";
$aa_settings["optionsCSVSeperator"]         = ",";
$aa_settings["defaultOutputType"]           = "CSV";
$aa_settings["defaultOutputFile"]           = "output/defaultOutput.file";
$aa_settings["defaultCSVFields"]            = "bank,account,trxDate,trxDescription,trxValue";
$aa_settings["defaultSqlite3TableName"]     = "trxdata";
$aa_settings["defaultSqlite3TableFields"]   = "userid,bank,account,trxDate,trxDescription,trxValue";

$aa_Output["RedError"]      = "\e[31m[Error]\e[39m\n\t";

// check we were given an input file, die if we weren't.
if (!isset($aa_settings["inputFile"])) {die("No input file was supplied. Use: flip.php -h for help\n");}

// assuming we have input file argument if we get to here
$iF = $aa_settings["inputFile"];
$tName = $aa_settings["template"];

/********************************* Stage 1: Read input file contents *******************************************************/
echo "flip - $iF using template: $tName\n";

// read file contents into a string
if (!file_exists($iF)) { die($aa_Output["RedError"]."Input file does not exist. Check path/filename and try again.\n"); }
$f = file_get_contents($iF);
if (!isset($f)) {die($aa_Output["RedError"]."Failed to read input file contents.\n");}
// if we get here, we should have out input content in $f

// main regex to find transactions initially is here
$aa_template[$tName]["mainRegex"] = array("/(.*?)(Date Particulars Debits Credits Balance (\d{1,2} (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4})?(Brought forward ((\d{0,3})(\,{0,1})*(\d{0,3})\.(\d{1,2}) (Cr|Dr))|Brought forward)(.*?)(Carried forward|Identifying.*))/is" => "$12");

foreach ($aa_template[$tName]["mainRegex"] as $regExp => $subExp) {$s_trxResults = preg_replace($regExp,$subExp,$f);}

echo $s_trxResults."\n\n";

?>