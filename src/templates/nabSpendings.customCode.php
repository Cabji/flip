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
        // explode the string on datebreak token
        $a_temp1 = explode("#DATEBREAK#",$f);
        $c = 0;

        // loop the results and explode each entry on SEP token into second array
        foreach ($a_temp1 as $data)
        {
            $a_temp2[$c] = explode("#SEP#", $data);
            $c++;
        }
        unset($c, $a_temp1);

        $a_result = null;
        // loop the resulting array and build the standard format array - a_temp2 is an array of values: [n] => [0] = nn Mon yyy [1] = trx data string [2] = closing balance
        foreach ($a_temp2 as $key => $data)
        {
            if ($data[0] && $data[1] && $data[2])
            {
                // convert date to unix timestamp
                $a_result[$key]["date"] = strtotime(trim($data[0]));
                $a_result[$key]["closingBalance"] = trim(fnCrDrToSigned($data[2]));
                $a_temp3 = preg_split("/([\d,]+\.\d{2})/is",$data[1],-1,PREG_SPLIT_DELIM_CAPTURE);
                $c = 0;
                for ($i = 0; $i <= sizeof($a_temp3); $i += 2)
                {
                    if (trim($a_temp3[$i]) != "") {$a_temp4[$c] = trim($a_temp3[$i]).trim(",".$a_temp3[$i+1]);}
                    $c++;
                }
                $a_result[$key]["transactions"] = $a_temp4;
                unset($c, $a_temp3, $a_temp4);
                
            }
            else {$d .= "nabSpendings.customCode.php - entry $key had missing data. Data was: \n\t[0]: $data[0]\n\t[1]: $data[1]\n\t[2]: $data[2]\n";}
        }
        unset($a_temp2);
        print_r($a_result);

        // now run the custom regexes as required
        $c = 0;
        foreach ($aa_template[$tName]["customRegexes"] as $regExp => $subExp) 
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
            echo "\n\t  • $regExpName";
        }
        unset($c);
    }
}
?>