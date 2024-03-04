# flip
Convert content from PDF bank statements into usable data formats.

This project aims to use specifically configured regexs mostly to strip desired data from a bunch of text.

The "bunch of text", in my use case, will be copy and pasted from PDF bank statement files. This software will aim to convert the pasted text into usable data formats. (CSV, SQLite3, others?)

It is written in PHP and will run on the command line.

## TLDR
 1. A template will need to be configured to suit your PDFs
 2. A template basically is: 
    a. regexes and their substitution values held in $aa_template[$tName]["regexes"] = array("1" => "sub1" ... );
    b. a custom code file in src/templates/ to ensure the trx data is in the 'standard format'
 3. Use the power of regex to maximise efficiency and minmise confusion/need for additional/custom coding. Spend more time learning to use regex. Go to regex101.com and play around in the editors there using sample data, hire a freelancer to write them for you (it's probably worth it if you need stuff quickly), or give AI bots a shot at it.

## LONG WINDED VERSION

flip is designed to be configured to work with whatever PDF you feed into it. This configuration is called making a "template".

A template should be thought of as, "the set of rules and code we use to process a PDF of one format type". 
A "PDF of one format type" is, for example, "a National Australia Bank Credit Card account statement". Which would differ from, "a National Australia Bank Everyday Savings Account statement", and from a "Bank of Queensland Savings Account statement". 

Just because a PDF comes from the same organization (or bank) doesn't mean it will use the same "set of rules to process it". Almost every different type of bank account is going to need its own set of rules for processing, which means they will need their own template.

### Configuring a Template

#### YOU MUST KNOW HOW TO USE REGULAR EXPRESSIONS 

If you don't know what regexes are, you won't be able to use this tool. 

To alter the current template, or create a new one, you should look for these lines in src/flip.php: 

// template associative array that holds regexes for processing text - this is what you need to alter to configure a new template
$aa_template[$tName]["regexes"] = array( ... );

Inside the array( ... ) area is where you will add/configure regexes that will be ran on the input PDF text to extract the data you're after.

THE MOST IMPORTANT REGEX IS THE FIRST ONE. While you can use multiple regexes to achieve what you want, if you know what you're doing with regexes you should be able to remove almost all garbage data using the first regex. Additional processing of the data can be done using additional regexes. A foreach loop is used to loop sequentially through the regexes configured here. They are executed one after the other. 

After months of creating and rewriting this code, I realized that employing the power of regex is far superior to writing more and more code to try to process the data. 

**Just use regex to the fullest extent possible.**

#### YOU SHOULD START THE REGEXES WITH A REGEX COMMENT 

If you didn't know, regexes can have comments in them, just like source code does. 

The script will look for a comment at the beginning of each regex and extract the name of the regex for output during processing (so you can see what's happening/happened). It's also extremely handy to come back to a regex months or years later and be able to read some plain language description of what the regex does, instead of looking at it and breaking into a cold sweat as you tear up feeling a state of panic setting in. A regex comment is made like this: 

    /(?# This is the comment)(... the rest of the regex ...)(etc...)/

flip will look for: /(?# and if it finds it, everything after it up to the first occurence of ) is used as the regex's name. If you don't use a descriptive comment at the start, that's ok - flip will call the configured regexes "Regex 1", "Regex 2" etc. in the output. Now go back and re-read that bit I said about coming back months later to a regex. You'll reap what you sow.

#### THE REGEX IS A SUBSTITUTION REGEX

Note that the $aa_template[$tName]["regexes"]  object is an _associative array_. This is because each entry is used in a preg_replace() function. If you don't know what that is, think of when you use "Search and Replace" in a text editor. That's what is happening here. flip will search the input data using the regex and replace what it finds using the value associated with the regex. This association is done using the following format when you alter the $aa_template regex values: 

   array("regex1" => "replace", "regex2" => "replace2", ... "regex10" => "replace10");

#### SUBSTITUTION CALLBACK/MATCH VALUES

Each "group" in the regex is assigned a match number and can be referenced using a $ and the number, eg: $1, $2, $3 etc. 

I suggest you head over to regex101.com and insert some test data and practice building regexes to see how this works. In the pane on the left side, scroll down until you see a section named "Function" and click "Substitution" This adds a result box below the current regex/data you're working with and if you type $1,$2,$3 etc in the text intput box below "SUBSTITUTION" you might see what happens (f your regex is configured correctly). 

I may do a video about this later. [Insert Video link here]

#### Where To Make The Template File

A template file should be created in src/templates/<templateName>.template.php

where <templateName> is the name of the template.

INSERT INFO ABOUT HOW TO MAKE AND LAY OUT A TEMPLATE FILE

flip will generate a file in src/templates/<templateName>.template. This file should be left alone and not altered. To alter the template's regex settings, you should alter the src/templates/<templateName>.template.php file.

### Template Custom Processing Code

Each template must have regexes associated to it, but regexes might not be enough to get the data to 'standard format'. Custom code can be used on a per-template basis to achieve further data processing. The custom php code for a template should be placed in: 

src/templates/<templateName>.customCode.php

Custom processing code *is not mandatory*. Whether you need custom processing code will depend on the format of the input data, what you can achieve with the initial regex substitutions, how you need your output data grouped/arranged etc. I found custom processing was required when I had to use math or a function to make decisions about the input data to reduce the manual processing time overall. If you are doing simple text-processing with flip, custom processing is probably not needed.

You can use the -ncp option (No Custom Processing) on the command line to disable custom processing entirely. Processed data will be output to text.txt and flip will notify you about this when it is executed.