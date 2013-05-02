SABES is an Automated Book Editing Script
==============

Description
--------------

SABES is a script which allowed people to transform ebooks from gutenberg's project into real books.
It uses two PHP libraries (included): 
* MPDF (http://www.mpdf1.com/mpdf/)
* PHP Simple HTML DOM Parser (http://simplehtmldom.sourceforge.net/)

The script works as follows :

* It retrieves the file from Gutenberg Project's mirror site.
* It changes some elements in order to unify the html file thanks to the PHP Simple HTML DOM Parser library.
* It edits a PDF file reorganizing the elements and dividing the whole document into several booklets in order to be printable thanks to the MPDF library. 

There're still few encoding problems.

Usage
--------------

* Choose a book on the Gutenberg Project by copying the url of the book you wish to print : here or there
	For example : http://www.gutenberg.org/ebooks/17541 

* Paste the link into the text box and select the relevant options:

	Printer Type : Duplex = automatic duplex | Non-duplex = manual duplex
	Source : Leave it on Project Gutenberg if the link is from the project's website.
	The script allows you to edit other books not from the Gutenberg Project. You have to indicate a link pointing to a ZIP archive containing an html file and, if needed, an 'images' path.

* Print your book and fold the booklets (5 sheets each) . All that remains is for you to clip on a cover.

Testing
--------------

Go on : http://michelguerin.fr/index.php?post/sabes