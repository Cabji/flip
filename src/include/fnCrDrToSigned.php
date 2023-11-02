<?php
// this file is a function that will convert a Cr/Dr currency value to a signed value.
// input: string, in format: nnn,nnn.nn Cr (or Dr) which represents a positive (Cr) value or a negative (Dr) value.

function fnCrDrToSigned($input)
{
    $result = false;
    if ($input)
    {
        if (strpos($input,"Cr")) {$lookFor = "Cr"; $swapWith = "+";} elseif (strpos($input,"Dr")) {$lookFor = "Dr"; $swapWith = "-";}
        if (!$lookFor || !$swapWith) {echo "fnCrDrToSigned: couldn't find Cr or Dr in input string\n";}
        else
        {
            $result = preg_replace("/([\d,]+\.\d{2})\s+($lookFor)/is", "$swapWith$1",$input);
        }
    }
    return $result;
}


?>