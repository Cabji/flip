<?php
// re-write of the bank statement PDF analyzer convert_uudecode

/* data acquisition - what data do we need?
    1. input data (the copy and pasted PDF text usually stored in a text file) - passed as first argument to the
    2. the bank, or "template" name
*/

$settings["bank"] = "nab-spendings";
$settings["outputOptions"] = "SQLite3, CSV";
$settings["outputDefault"] = "CSV";
$settings["outputFile"] = "output/defaultOutput.file";
$settings["sqlite3TableName"] = "trxdata";
$settings["sqlite3TableFields"] = "userid,bank,account,trxDate,trxDescription,trxValue";

// comment
?>