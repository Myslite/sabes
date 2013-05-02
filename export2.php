<?php
//derivated booklet function from MPDF author
//it splitts all pages in many booklet (20 to 20)
// for example, first booklet look like this : 20,1,2,19,18,3,4,17,16,5,6,15,14,7,8,13,12,9,10,11
//and the second, like this : 40,21,22,39,38,23,24,37,36,25,26,35,34,27,28,33,32,29,30,31
//in order to produce a book easy to bind, without glue or any expensive device.
//for more information see: www.michelguerin.fr/index.php?post/sabes
function GetBookletPages($np, $backcover=false) {
	$lastpage = $np;
	//finds the closest number from $np which is divisible by 4  (because of the booklet)
	$np = 4*ceil($np/4);
	//finds the last complete booklet from 20 pages
	$np2 = 20*floor($np/20);
	$pp = array();
	for ($i=1, $j=1, $k=1; $i<=$np/2; $i++, $j++, $k++) {
		//defines all even and odd pages in the same order presented above
		if ( $i<=$np2/2){ 
			$p1 = ((20*ceil($i/10))-$j)+1;
			$p2 = (10*floor($i/10.0001))+$i;
		}
		//if there's no enough pages to achieve a booklet from 20 pages, it uses the rest to produce a lesser one
		else {
			$p1 = $np-$k+1;
			$p2 = $np2+$k;
		}
		if ($backcover){
			if ($i == 1) { $p1 = $lastpage; }
			else if ($p1 >= $lastpage) { $p1 = 0; }
		}
		if ($i % 2 == 1) {
			$pp[] = array( $p1,  $p2 ); 
		}
		else { 
			$pp[] = array( $p2, $p1 ); 
		}
		if ($j == 10) {$j = 0;}
		if ($k == 10) {$k = 0;}
	}
	return $pp;
}



$mpdf=new mPDF('','A4-L','','',0,0,0,0,0,0); 
$mpdf->SetImportUse();	
$ow = $mpdf->h;
$oh = $mpdf->w;
$pw = $mpdf->w / 2;
$ph = $mpdf->h;

$mpdf->SetDisplayMode('fullpage');

$pagecount = $mpdf->SetSourceFile('tmp/'.$id.'/tempFile_'.$currentFileName.'.pdf');
$pp = GetBookletPages($pagecount);

//displays all pages from $pp array in the good order and the good size (from A4 to A5)
foreach($pp AS $v) {
	$mpdf->AddPage(); 
	if ($v[0]>0 && $v[0]<=$pagecount) {
		$tplIdx = $mpdf->ImportPage($v[0], 0,0,$ow,$oh);
		$mpdf->UseTemplate($tplIdx, 0, 0, $pw, $ph);
	}
	if ($v[1]>0 && $v[1]<=$pagecount) {
		$tplIdx = $mpdf->ImportPage($v[1], 0,0,$ow,$oh);
		$mpdf->UseTemplate($tplIdx, $pw, 0, $pw, $ph);
	}
}

$mpdf->Output($outputPath.$fileName.".pdf","F");
//checks if file was created
$booklet_MPDF = $outputPath.$fileName.".pdf";
if (file_exists($booklet_MPDF)) { 
	//adds filename in the data base
	//~ chmod($booklet_MPDF, 0777);
	$bdd_pdf = fopen('bdd_pdf.txt', "a");
	fwrite($bdd_pdf, $fileName.".pdf");
	fclose($bdd_pdf);
	echo "<br> PDF_booklet : OK";
	//checks if the user want duplex or non-duplex file
	if($_SESSION["bouton"] == 'non-duplex'){
		include('export3.php');
	}
	elseif($_SESSION["bouton"] == 'duplex'){
		//prints a download button
		echo '<FORM ACTION="books/pdf/' . $fileName . '.pdf" target="_blank">';
		echo '<INPUT TYPE="SUBMIT" VALUE="Télécharger" />';
		echo '</FORM>';
		include ('lib/erase.php');
	}
} else { 
	echo "<br>ERROR : Le fichier PDF n\'a pas pu être transformé en livret."; 
	include ('lib/erase.php');
} 
?>