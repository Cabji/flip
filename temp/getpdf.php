<?php

require_once 'PdfParser/vendor/autoload.php';

//use Smalot\PdfParser\Parser;

$parser = new Parser();
$pdf = $parser->parseFile('example.pdf');
$text = $pdf->getText();

echo $text;

?>