# flip
Convert content from PDF bank statements into usable data formats.

This project aims to use specifically configured regexs mostly to strip desired data from a bunch of text.

The "bunch of text", in my use case, will be copy and pasted from PDF bank statement files. This software will aim to convert the pasted text into usable data formats. (CSV, SQLite3, others?)

It is written in PHP and will run on the command line.

Caveat #1: An orange is not an orange - if you hilight text in a PDF document and press Ctrl+A to select all the text, then copy and paste it, what you get after you paste it will differ depending on what software you use to select and copy the PDF. Since the input data can change depending on the software used to view the PDF, the regex needed to analyze the input will likely change.
