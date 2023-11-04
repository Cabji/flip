<?php

function verifyUserInput($s_input)
{
    $input = readline(" Confirm: $s_input [Y/n]? ");
    if ($input == "y" || $input == "Y" || $input == "yes" || $input = "") {return true;}
    else {return false;}
}

?>