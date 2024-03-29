<?php
// this file is a flip template custom processing code file. it contains PHP code to handle the $s_trxResults string

// check that we have all the data we need to do our work
// $f is the working string we use our regexes on to process the data

if (!isset($f)) {echo "\n\t  • \e[91mFail\e[39m: flip input string \$f is not set";}
else 
{
    if (!isset($aa_template[$tName]["customRegexes"]) || gettype($aa_template[$tName]["customRegexes"]) != "array") 
    {
        // handle error
        echo "\n\t  • \e[91mFail\e[39m: customRegexes for this template are not available. check template file is fully configured in src/templates/";
    }
    else
    {
        // dev-note: enter custom code to process the $f data here. the aim is to get it into a format that will let us use the 
        // customRegexs on whichever parts of the data we need to operate on to make the standard format for transactions
        // (see docs for more info about the standard format)

        include "$relativePath/include/fnCrDrToSigned.php";
        echo "\n\t  • Exploding result string";
        // explode the string on datebreak token
        $a_temp1 = explode("#DATEBREAK#\n",$f);
        $c = 0;

        // loop the results and explode each entry on SEP token into second array
        foreach ($a_temp1 as $data)
        {
            $a_temp2[$c] = explode("#SEP#", $data);
            $c++;
        }

        // loop the resulting array and build the standard format array - a_temp2 is an array of values: [n] => [0] = nn Mon yyy [1] = trx data string [2] = closing balance
        echo "\n\t  • Building the standard format data array";
        foreach ($a_temp2 as $key => $data)
        {
            if ($data[0] && $data[1] && $data[2])
            {
            // convert date to unix timestamp
                $a_result[$key]["date"] = strtotime(trim($data[0]));

                // place closing balance value
                // dev-note: we strip the , and . chars from currency values here because we need to perform math on the values
                $closingBalance = str_replace(array(",", "."), "", trim(fnCrDrToSigned($data[2])));
                $a_result[$key]["closingBalance"] = $closingBalance;

                // the regex catches foreign currency exchanges and "cash out" at supermarket checkouts, both cause problems in parsing correctly
                $a_temp3 = preg_split("/(\b(?<!(Frgn Amt: )|(Cash Out ))[\d,]+\.\d{2}(?!%))/is",$data[1],-1,PREG_SPLIT_DELIM_CAPTURE);
                // $a_temp3 format is like this: 
                //  array [0] = trx description A   [1] = -12.34
                //        [2] = trx description B   [3] = +987.65
                // this is the array we loop through in the for loop below.

                $c = 0;
                for ($i = 0; $i < sizeof($a_temp3); $i += 2)
                {
                    // $a_temp3[i] = current trx description (string)
                    // $a_temp3[i+1] = current trx value (string: +/-1,234.56)
                    // sanitize the trx value - strip any . or , from the trx value
                    if (isset($a_temp3[$i+1])) {$a_temp3[$i+1] = str_replace(array(",","."),"",$a_temp3[$i+1]);}
                    
                    if (trim($a_temp3[$i]) != "" && isset($a_temp3[$i+1])) 
                    {
                        $a_temp4[$c] = trim($a_temp3[$i]).trim(",".$a_temp3[$i+1]);
                    }
                    $c++;
                }
                // assign the updated data to $a_result array
                if ($key >= 1) {$a_result[$key]["difference"] = $a_result[$key]["closingBalance"] - $a_result[$key - 1]["closingBalance"];}
                if (isset($a_temp4)) {$a_result[$key]["transactions"] = $a_temp4;}
                unset($c, $a_temp3, $a_temp4);
            }
            else {$d .= "nabSpendings.customCode.php - entry $key had missing data. Data was: \n\t[0]: $data[0]\n\t[1]: $data[1]\n\t[2]: $data[2]\n";}
        }
        unset($i, $a_temp1, $a_temp2);

//        print_r($a_result);
        // now we have 'standard format' for the trx data, but in this template we need to determine the signs (+/-) for the trx values
        include "$relativePath/include/fn-signValues.php";

        $a_validateDates = array();
        $a_validateTRXs = array();
        $a_validateSigns = array();
        echo "\n\t  • Calculating +/- signs for TRX values (this could take a few minutes depending on the data)\n\t\t";
        // dev-note: &$trxEntry is passed by reference so we can update it in the foreach loop
        $signedValueSet = null;
        foreach ($a_result as $i => &$trxEntry)
        {
            if (($i+1) % 10 === 1) {echo "\n\t\t";}
            echo " [$i]";
            if ($trxEntry["date"] == "") {array_push($a_validateDates, $i); echo "D";}
            if (!array_key_exists("transactions", $trxEntry)) {$d .= "$tName.customCode.php (".__LINE__."): a_result[$i] has no transactions key\n";}
            else
            {
                // grab the trx values from the trxEntry["transactions"] array and store in $signedValueSet array. array_values() reindexes the array to ensure we start from index [0]
                $signedValueSet[$i] = array_map(function($value) {$parts = explode(",", $value); return intval(end($parts));}, array_values($trxEntry["transactions"]));

                // temporarily hold the result because it can return false if the input valueSet is flawed
                $result = signValues($trxEntry["difference"], $signedValueSet[$i]);

                // check for a false result and handle it
                if ($result == false) 
                {
                    // signValues returned false so something is wrong with this signedValueSet and needs human inspection
                    array_push($a_validateTRXs, $i);
                    echo "V";
                    // copy the UNSIGNED valueSet (the input) to temp
                    $temp = $signedValueSet[$i];
                    // wipe out the UNSIGNED value set (because we need it to be a fresh multi-dim array for the output)
                    unset($signedValueSet[$i]);
                    // assign the temp (unsigned value set, to [0] in the same date location as it was in the signedValueSet)
                    $signedValueSet[$i][0] = $temp;
                    unset($temp);
                }
                else {$signedValueSet[$i] = $result;}

                // update the trxEntry[transactions] with 1 signed value set in signedValueSet
                foreach ($trxEntry["transactions"] as $j => &$trxStr)
                {
                    // update the trxEntry[transactions] values with signed values only if the signedValueSet[i] holds 1 entry only. more than 1 entry means a human has to validate the signs
                    if (sizeof($signedValueSet[$i]) == 1)
                    {
                        // update (by &reference) the trxStr value in the current date's transactions strings to have signed values on the end
                        $trxStr = substr_replace($trxStr, $signedValueSet[$i][0][$j], strrpos($trxStr, ',') + 1);
                    }
                }
                // mark entries with more than 1 signed signedValueSet for human validation
                if (sizeof($signedValueSet[$i]) > 1) {array_push($a_validateSigns, $i); echo "S";}
            }
        }

        // check if any valueSets need human verification
        if (isset($a_validateDates) || isset($a_validateTRXs) || isset($a_validateSigns)) 
        {
            $i_totalVerify = sizeof($a_validateDates) + sizeof ($a_validateTRXs) + sizeof ($a_validateSigns);
            if ($i_totalVerify >= 1) 
            {

                echo "\n\t  • TRX value set verification";
                echo "\n\t      There are ".$i_totalVerify." problems that require human verification.";

                include "$relativePath/include/fn-verifyUserInput.php";
                if (sizeof($a_validateDates) >= 1)
                {
                    echo "\n\t\t\e[33mDate Validation - these entries have a problem with the Date value.\e[39m";
                    include "$relativePath/include/fn-validateDate.php";
                    // loop the validate date array
                    foreach ($a_validateDates as $i => $index)
                    {
                        echo "\n\t\t  • Entry $index";
                        echo "\n\t\t    Content near these transactions: \n".print_r($a_result[$index],true)."\n";
                        $date = false;
                        $verified = false;
                        while (($date === false || !validateDate($date, $aa_template[$tName]["dateFormat"]) || $verified == false))
                        {
                            $date = readline("\n\t\t    Enter date for these transactions [".$aa_template[$tName]["dateFormat-Output"]."]: ");
                            // verify user input call to custom func
                            if ($aa_template["$tName"]["verifyUserInput"]) {$verified = verifyUserInput($date);}
                            else {$verified = true;}
                        }
                        unset($verified);
                        // apply validated date to the record in $a_result as unixtime stamp
                        $a_result[$index]["date"] = strtotime($date);
                    }
                }

                // validate signed valuesets that computer is unsure about and dubious trx values in general
                if (sizeof($a_validateSigns) >= 1)
                {
                    echo "\n\t\t\e[33mTRX Signs Validation - these entries have a problem with the TRX signs (+/-).\e[39m";

                    // loop the validate TRX array
                    foreach ($a_validateSigns as $i => $index)
                    {
                        echo "\n\t\t  • Entry $index";
                        echo "\n\t\t    This transaction group could not have the TRX Value signs determined automatically.";
                        echo "\n\t\t    Please review the transactions and enter the sign values manually.";
                        echo "\n\t\t    Transaction information: \n".print_r($a_result[$index],true)."\n";
                        $signs = false;
                        $verified = false;
                        while ($signs === false || strlen($signs) != count($a_result[$index]["transactions"]) || $verified == false)
                        {
                            $signs = readline("\n\t\t    Enter value signs for these transactions (+/-) in order from top to bottom of the list above: ");
                            
                            // verify user input call to custom func
                            if ($aa_template["$tName"]["verifyUserInput"]) {$verified = verifyUserInput($signs);}
                            else {$verified = true;}

                            // check if the user input is valid or not. set input to false if it is invalid (making this while loop go again)
                            if (preg_match("/[^+-]/", $signs) || strlen($signs) != count($a_result[$index]["transactions"])) 
                            {
                                $signs = false;
                            }
                        }

                        // update the trx values in the $a_result array with user validated signs
                        echo "\n\t\t    Updating transaction values...";
                        // aig
                        for ($i = 0; $i < count($a_result[$index]["transactions"]); $i++) 
                        {
                            // find the position of the last comma in the array element
                            $pos = strrpos($a_result[$index]["transactions"][$i], ",");
                            // insert the character from $str before the digits
                            $a_result[$index]["transactions"][$i] = substr_replace($a_result[$index]["transactions"][$i], $signs[$i], $pos + 1, 0);
                            echo "\n\t\t      • ".$a_result[$index]["transactions"][$i]." ==> ".$aa_Output["CyanOk"];
                        }
                        unset($signs, $verified);
                    }
                }

                // validate faulty valuesets that computer could not generate signed valueSet for
                if (sizeof($a_validateTRXs) >= 1)
                {
                    echo "\n\t\t\e[33mTRX Values Validation - these entries have a problem with the TRX values. Signs couldn't be calculated.\e[39m";
                    echo "\n\t\t    This transaction group could not have the TRX Value signs determined automatically because something is wrong with the TRX values.";
                    echo "\n\t\t    Please review the transactions, note their details down and correct them in the final output data.";
                    // loop the validate TRX array
                    foreach ($a_validateTRXs as $i => $index)
                    {
                        echo "\n\t\t  • Entry $index";
                        echo "\n\t\t    Transaction information: \n".print_r($a_result[$index],true)."\n";
                    }
                }
            }
        }

//      debug stuff

        $d .= print_r($a_result, true);

//        print_r($a_validateTRXs);
//        print_r($signedValueSet);
//        print_r($a_result);
    }
}
?>