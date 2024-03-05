<?php

$tName = "brimsMaterials";

// user-note: use a comment at tte start of your regexes. It is better for later on and the script will output what it's doing when it uses the regex
// To do a comment do this:
// "/(?# Regex comment here)(the rest of your regex)/is"
//   |                     |
//   +----------+----------+
//              |
//   this is the regex comment

$aa_template[$tName]["regexes"] = array("/(?# Initial Regex to extract Grid Data)(.*\d{4} \d{3} \d{3}\s)(.*?)(Subtotal.*)/is" => "$2", 
                                        "/(?# Convert Grid Data to CSV Values)(.*?) (.*?)(\s1.00 EACH\s)((\d{0,3}),*(\d{0,3}),*(\d{0,3})(.\d{2}))(.*?\n)/is" => "Materials,Brims Tweed Heads,$1,$2,$5$6$7$8\n"
                                       );

$aa_template[$tName]["dateFormat"] = "Y-m-d";
$aa_template[$tName]["dateFormat-Output"] = "YYYY-MM-DD";
$aa_template[$tName]["verifyUserInput"] = false;

// customRegexes are used in the template.customCode.php file after initial data processing
// if custom processing is not required, you dont need to worry about setting anything below here. Use -ncp option when running flip.php
$aa_template[$tName]["customRegexes"] = array("/(?# First regex in custom code)/is" => "");

?>