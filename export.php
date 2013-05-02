<?php
// include lib from MPDF project (http://www.mpdf1.com/mpdf/index.php)
// and also PHP Simple HTML DOM Parser (http://simplehtmldom.sourceforge.net/)
include('lib/mpdf/mpdf.php');
include('lib/simple_html_dom.php');
include_once('lib/Encoding.php');

//checks where the extracting file is
$dir = 'books/html/' .$id. '/';
$scan = scandir($dir);
if (is_dir($dir . $scan[2])) {
	$scan2 = scandir($dir . $scan[2]);
	if (is_dir($dir . $scan[2] . '/' . $scan2[2])) {
		$currentFileName= $scan2[2] . '.htm';
		$url = $dir .  $scan[2] . '/' . $scan2[2] . '/' . $currentFileName;
	}
	else {
		$currentFileName= $scan[2] . '.htm';
		$url = $dir . $scan[2] . '/' . $currentFileName;
	}
}
else {
	$currentFileName= $scan[2];
	$url = $dir . $currentFileName;
}


//changes filename if user's url not from gutenberg project
if($_SESSION["source"] == 'autre'){
	$fileName = $currentFileName ;
}

$outputPath = "books/pdf/";

$currentFileNamelength = strlen($currentFileName);
$urlimg = substr($url, 0, -$currentFileNamelength);

//checks if url exist
if(is_file($url)){
	//~ $html = new simple_html_dom();
	//fixs encoding problems (there's still some problemes)
	$stringhtml=file_get_contents($url);
	$meta = new simple_html_dom();
	$meta->load($stringhtml);
	//searches meta charset
	$el=$meta->find('meta[http-equiv=Content-Type]',0);
	$fullvalue = $el->content;
	preg_match('/charset=(.+)/', $fullvalue, $matches);
	$meta->clear(); 
	unset($meta);
	if($matches[1] !== null) {
	//if there's a charset in convert it to UTF8
		$stringhtml2 = iconv($matches[1], 'UTF-8//TRANSLIT', $stringhtml);
	}
	else{
		$stringhtml2 = $stringhtml;
	}
	//fixs encoding problems --end
	
	$html = new simple_html_dom();
	$html->load($stringhtml2);
	
	$preLicence = $html->find('pre', -1);
	$notes="";

	$prebeginning = $html->find('pre',0);
	$prebeginning->outertext = $prebeginning->outertext .'<pagebreak />';
	//erases all footnotes from the html
	foreach($html->find('.footnote') as $footNote) {
		$notes = $notes.$footNote->innertext . '<br>';
		$footNote->outertext = '';
	}
	//if they exist, puts all footnotes at the end of the book
	if(!$html->find('.footnote') == ""){
		$preLicence->outertext = '<pagebreak /><h1>NOTES</h1>' . $notes . '<pagebreak />' . $preLicence->outertext;
	}
	else {
		$preLicence->outertext = '<pagebreak />' . $preLicence->outertext;
	}

	//changes image's url in all the html file
	foreach ($html->find('img') as $img) {
		$img->src = $urlimg . $img->src;
	}

	$html2 = $html;

	//sets special margin to bind correctly the book
	$mpdf=new mPDF('c','A4','','',40,16,16,16,9,9);
	$mpdf->SetDefaultBodyCSS('background', '#ffffff');
	$mpdf->ignore_invalid_utf8 = true;
	$mpdf->mirrorMargins = true;
	$mpdf->SetDisplayMode('fullpage','two');
	$mpdf->AddPage();
	//adds page number
	$footer = array (
		'odd' => array (
		'L' => array ('content' => ''),
		'C' => array (
		'content' => '- {PAGENO} -',
		'font-size' => 8.5,
		'font-style' => 'B',
		),
		'R' => array ('content' => ''),
		'line' => 0,
		),
		'even' => array (
		'L' => array ('content' => ''),
		'C' => array (
		'content' => '- {PAGENO} -',
		'font-size' => 8.5,
		'font-style' => 'B',
		),
		'R' => array ('content' => ''),
		'line' => 0,
		),
	);
	$mpdf->SetFooter($footer);
	$stylesheet = file_get_contents('impression.css');
	$mpdf->WriteHTML($stylesheet,1);
	$mpdf->WriteHTML($html2);
	//finds how many pages the document has
	$num_pg = $mpdf->docPageNumTotal(); 
	//finds the closest number from $num_pg which is divisible by 4  (because of the booklet)
	$num1 = 4*ceil($num_pg/4);
	$num2 = $num1 - $num_pg;
	$qrcode= "<div style='width: 150mm; position:absolute; left: 50%; margin-left: -58mm; bottom: 15mm;'><img src='qrcode.svg' width='100%' /></div>";
	//adds a colophon with a link to the project at the very last page of the book
	if ($num2 == 0) {
		$qrcode1 = $qrcode;
	}
	elseif ($num2 == 1) {
		$qrcode1= "<pagebreak />" . $qrcode;
	}
	elseif ($num2 == 2) {
		$qrcode1= "<pagebreak /><pagebreak />" . $qrcode;
	}
	elseif ($num2 == 3) {
		$qrcode1= "<pagebreak /><pagebreak /><pagebreak />" . $qrcode;
	}
	
	$mpdf->WriteHTML($qrcode1);
	$mpdf->Output('tmp/' .$id. '/tempFile_'.$currentFileName.'.pdf','F');
	$tmp_MPDF = 'tmp/'.$id.'/tempFile_'.$currentFileName.'.pdf';
	//checks if file was created
	if (file_exists($tmp_MPDF)) { 
		echo '<br> PDF_source : OK';
		include('export2.php');
	} else { 
		echo "<br>ERROR : Le fichier n\'a pas pu être exporté au format PDF.";
		include ('lib/erase.php');
	} 
}
else{
	echo "<br>ERROR : L\'url n\'as pas été chargé correctement, veuillez recommencer";
	include ('lib/erase.php');
}
?>