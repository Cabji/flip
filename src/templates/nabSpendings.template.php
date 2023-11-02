<?php

$tName = "nabSpendings";

// user-note: use a comment at tte start of your regexes. It is better for later on and the script will output what it's doing when it uses the regex
// To do a comment do this:
// "/(?# Regex comment here)(the rest of your regex)/is"
//   |                     |
//   +----------+----------+
//              |
//   this is the regex comment

$aa_template[$tName]["regexes"] = array("/(?# Initial Regex to extract TRX Data)(.*?)(Date Particulars Debits Credits Balance\s?)((\d{1,2} (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4}\sBrought forward\s((\d{0,3})(\,{0,1})*(\d{0,3})\.(\d{1,2}) (Cr|Dr)))|Brought forward)(.*?)(Carried forward|Identifying.*)/is" => "$12", 
                                                "/(?# Remove excessive ... dots and all newline chars from trx data)(\.\.\.*|\n)/is" => "",
                                                "/(?# Clean up excessive whitespace in all data)(\s+)/is" => " ",
                                                "/(?# Group by Date and find Closing Balances)(\d{1,2} (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4})(.*?)((Cr|Dr)(\d{0,3})(\,{0,1})*(\d{0,3})(\.)(\d{1,2}))/is" => "$1#SEP#$3#SEP#$6$7$8$9$10 $5#DATEBREAK#");

// customRegexes are used in the template.customCode.php file after initial data processing
$aa_template[$tName]["customRegexes"] = array("/(?# First regex in custom code)/is" => "");

?>