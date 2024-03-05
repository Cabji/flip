20240305: 
 - added new template (brimsHardware)
 - added -ncp option: no custom processing
 - added -tttc option: Text: to Title Case
 - updated README
 - added .gitignore to my dev folder to ignore all the temporary files so the git repo only has the needed files

20231102:
 - added customRegexes and customCode template file for additional processing of input data to achieve "standard format"
 - finished initial processing of PDF file to text format and extracting desired data only via template configuration

20231031:
 - update readme with some documentation
 - added working output messages and formatting

20231030: 
 - tweaked regex to suit new direct input method
 - added reading input file directly from PDF (requires pdftotext util: sudo apt-get install xpdf)
 - initial regex to pull trx data from input written
 - initial reading of inputFile and checks for failure
 - handle command line options and arguments
 - project start
