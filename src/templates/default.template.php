<?php

$tName = "default";

// user-note: use a comment at tte start of your regexes. It is better for later on and the script will output what it's doing when it uses the regex
// To do a comment do this:
// "/(?# Regex comment here)(the rest of your regex)/is"
//   |                     |
//   +----------+----------+
//              |
//   this is the regex comment
// 
// if you use $ character in your regex string, you NEED TO prepend it with TWO backslash chars, not one, like: \\$
// otherwise PHP won't execute the regex correctly.

$aa_template[$tName]["regexes"] = array("/(?# Initial Regex to extract TRX Data)(.*?)(Date Particulars Debits Credits Balance\s?)(.*?)(Carried forward|Identifying)/is" => "$3", 
                                        "/(?# Remove excessive ... dots from trx data)(\.\.\.*)/is" => "",
                                        "/(?# Group by Date and find Closing Balances)(\d{1,2} (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4})(.*?)(((Cr|Dr)\n([\d,]+\.\d{2}))|(( Brought forward )([\d,]+\.\d{2}) (Cr|Dr)))/is" => "$1#SEP#$3$9#SEP#$7$10 $6$11#DATEBREAK#\n",
                                        "/(?# Clean up excessive whitespace in all data)((?<!#DATEBREAK#)\s+)/is" => " ");

$aa_template[$tName]["dateFormat"] = "Y-m-d";
$aa_template[$tName]["dateFormat-Output"] = "YYYY-MM-DD";
$aa_template[$tName]["verifyUserInput"] = false;

// customRegexes are used in the template.customCode.php file after initial data processing
// if custom processing is not required, you dont need to worry about setting anything below here. Use -ncp option when running flip.php
$aa_template[$tName]["customRegexes"] = array("/(?# First regex in custom code)/is" => "");

?>