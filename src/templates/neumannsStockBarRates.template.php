<?php

$tName = "neumannsGalvanisingRates";

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

$aa_template[$tName]["regexes"] = array("/(?# Extracting Stock Bar Headers Headers)(.*Bar Code Cut Length Price Per Length Price Per Bundle)\n/is" => "Bar Code,Cut Length,Price Per Length,Price Per Bundle\n", 
                                        "/(?# Extracting Stock Bar Rates)(\b\w+\b) (\d+) \\$(\d{0,3}),?(\d{0,3})(\.\d{2})( \\$(\d{0,3}),?(\d{0,3})(\.\d{2})\n|\n)/is" => "$1,$2,$3$5,$7$8$9\n", 
                                        "/(?# Removing unwanted data)(Notes.*)/is" => "");

$aa_template[$tName]["dateFormat"] = "Y-m-d";
$aa_template[$tName]["dateFormat-Output"] = "YYYY-MM-DD";
$aa_template[$tName]["verifyUserInput"] = false;

// customRegexes are used in the template.customCode.php file after initial data processing
// if custom processing is not required, you dont need to worry about setting anything below here. Use -ncp option when running flip.php
$aa_template[$tName]["customRegexes"] = array("/(?# First regex in custom code)/is" => "");

?>
