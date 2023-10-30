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
$settings = array();

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
            break;
        case 't':
            if ($value == "") {$value = "default";}
            $settings["template"] = $value;
            break;
    }
}

// output any message from above block and reset output var
if ($o) {echo $o; $o = null;}

foreach ($arguments as $argument) {
    // get the first argument as the input file
    $settings["inputFile"] = $argument;
    break;
}

foreach ($settings as $key => $value)
{
    echo "$key:\t$value\n";    
}



/*
$template = 
$settings["bank"] = "nab-spendings";
$settings["outputOptions"] = "SQLite3, CSV";
$settings["outputDefault"] = "CSV";
$settings["outputFile"] = "output/defaultOutput.file";
$settings["sqlite3TableName"] = "trxdata";
$settings["sqlite3TableFields"] = "userid,bank,account,trxDate,trxDescription,trxValue";

// check if user gave us a filename in the first argument, read its contents if they did

/********************************* Stage 1: Read input file contents *******************************************************/

/*
// read file contents into a string
$f = file_get_contents($argv[1]);
if (strlen($f) == 0) { die("\e[31m[Error]\e[39m\n\tInput file had nothing in it. Check file/filename and try again.\n"); }

*/

?>