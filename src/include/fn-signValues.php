<?php

/* Usage: array|bool : signValues(difference (signed float), valueSet (array))

    This function tries to determine a combination of negative and positive values from valueSet to yield difference.
    There are 3 possible outcomes from this function: 
        1. A single combination is found
        2. No combination is found
        3. More than 1 combination is found
    If a single combination is found, it is the only set of positive and negative values that can be used to yield difference.

    If no combination is found, it means the difference cannot be the yield of the values in the valueSet, regardless of positive 
    and negative combinations used (all possibilities are exhausted). In other words: the numbers passed to the function are bogus.

    If more than 1 combination is found, it means the difference == 0. When a valueSet yields a difference of 0 it means the values 
    in the valueSet can have their positive or negative signs inversed, and the valueSet will still yield a difference of 0. In these 
    instances, it is not mathematically possible to determine which combination of signs should be applied to the valueSet to 
    yield the difference. More information is needed (a human/AI needs to determine where the signs should be assigned).

    Parameters:
        difference (signed float) - a signed (+/-) float value
        valueSet (array) - an array holding a set of unsigned values (integers or floats). 

        Note: this function does not do any data sanitization. You should ensure all values are sanitized before passing to the 
        function. Do not pass "currency" values into here ($12,345.67) with or without currency symbols as the comma character will 
        cause problems.

    Return Value: 
        The return value is either: 
            boolean false - will occur is difference cannot be yielded by the valueSet. Meaning: your numbers are bogus or corrupted.
            array - if one or more sign combinations can be applied to the valueSet to yield difference, the valueSet(s) will be 
            returned in a multi-dimensional array, eg: 

                Array
                (
                    [0] => Array
                        (
                            [0] => 3,609.04
                            [1] => -950.00
                            [n] => ...
                        )
                    [n] => Array
                        (
                            [n] => ...
                        )
                )
*/

function signValues ($difference, $valueSet)
{
    //print_r($valueSet);
    $a_return = array();
    // create counter value that is sizeof(valueSet) + 1 in decimal (all sign possibilities in a matrix + 0)
    $i_maxPoss = (2 ** sizeof($valueSet) - 1);
    $i_currentPoss = $i_maxPoss;
    for ($i_currentPoss; $i_currentPoss >= 0; $i_currentPoss--)
    {
        // create LHS, zero-padded binary version of i_currentPoss
        $bi_currentPoss = decbin($i_currentPoss);
        $bi_currentPoss = str_pad($bi_currentPoss, strlen(decbin($i_maxPoss)), "0", STR_PAD_LEFT);
        $i_currentDiff = 0;
        $a_currentValueSet = array();

        // iterate over each digit in bi_currentPoss
        for ($i = 0; $i < strlen($bi_currentPoss); $i++)
        {
            $i_sign = substr($bi_currentPoss,$i,1); // pop the required digit off the bi_currentPoss string ($i selects which digit to get)
            if ($i_sign)
            { $i_sign = "-"; $i_currentDiff = $i_currentDiff - $valueSet[$i];} // subtract the signed valueSet value from f_currentDiff
            else
            { $i_sign = "+"; $i_currentDiff = $i_currentDiff + $valueSet[$i];} // add the signed valueSet value to f_currentDiff
            
            array_push($a_currentValueSet, $i_sign.trim($valueSet[$i])); // push signed value into a_currentValueSet (in case it is a winning combo) 
        }
        //echo "[$bi_currentPoss]: $f_currentDiff\n";
        //$f_currentDiff = round($f_currentDiff,2); // round f_currentDiff to 2 decimal places because of the BS float point problem
        // compare f_currentDiff to difference
        if ($i_currentDiff == $difference)
        {
            // we found a possible winning sign combination - save it in the return array
            //echo "we found a winning combination\n";
            array_push($a_return, $a_currentValueSet);
        }
        unset($i_currentDiff, $a_currentValueSet); // unset all the temporary values
    }
    unset($i_currentPoss, $i_maxPoss);
    if (sizeof($a_return) < 1) {$a_return = false;}
    return $a_return;
}
?>