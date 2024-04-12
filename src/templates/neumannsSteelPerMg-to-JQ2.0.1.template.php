<?php

$tName = "neumannsSteelPerMg-to-JQ2.0.1";

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

$aa_template[$tName]["regexes"] = array("/(?# Extracting Steel per Mg Rates)(?s:.*?)((?#CnB)(^\b[\w\.]+\b) \\$(\d{0,3}),*(\d{0,3})(\.\d{2}) \\$(\d{0,3}),*(\d{0,3})(\.\d{2}) \\$(\d{0,3}),*(\d{0,3})(\.\d{2})( \\$(\d{0,3}),*(\d{0,3})(\.\d{2}))*)/im" => "Materials,Neumanns,$2,,Steel - $2 - Stock (per Mg),$3$4$5,,,tonnes\nMaterials,Neumanns,$2,,Steel - $2 - C&B (per Mg),$6$7$8,,,tonnes\nMaterials,Neumanns,$2,,Steel - $2 - Complex Shape (per Mg),$9$10$11,,,tonnes\nMaterials,Neumanns,$2,,Steel - $2 - Fabrication (per Mg),$13$14$15,,,tonnes\n", 
                                        "/(?# Removing any unwanted data)\n1\n.*/is" => "",
                                        "/(?# Insert field headers at top)^/is" => "Category,Supplier,SupplierSKU,SupplierDesc,Item,Cost per Unit,Formula Not Reqd,Material Coverage Formula,Unit\n");

$aa_template[$tName]["dateFormat"] = "Y-m-d";
$aa_template[$tName]["dateFormat-Output"] = "YYYY-MM-DD";
$aa_template[$tName]["verifyUserInput"] = false;

// customRegexes are used in the template.customCode.php file after initial data processing
// if custom processing is not required, you dont need to worry about setting anything below here. Use -ncp option when running flip.php
$aa_template[$tName]["customRegexes"] = array("/(?# First regex in custom code)/is" => "");

?>
