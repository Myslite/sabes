<?php
//derivated from booklet function
// it splits the source pdf into two pdf files in order to achieve a manual duplex

//splits all odd pages to a pdf
function SplitPDF($np2) {
	$pp1 = array();
	for ($m=1; $m<=$np2; $m++) {
		if ($m % 2 == 1) { 
			$pp1[] = array( $m ); 
		}
	}
	return $pp1;
}

$mpdf1=new mPDF('','A4-L','','',0,0,0,0,0,0);
$mpdf1->SetImportUse();	
$mpdf1->SetDisplayMode('fullpage');
$pagecount1 = $mpdf1->SetSourceFile($outputPath.$fileName.".pdf");
$pp1 = SplitPDF($pagecount1);
foreach($pp1 AS $v1) {
	$mpdf1->AddPage(); 
	if ($v1[0]>0 && $v1[0]<=$pagecount1) {
		$tplIdx1 = $mpdf1->ImportPage($v1[0]);
		$mpdf1->UseTemplate($tplIdx1);
	}
}
$mpdf1->Output($outputPath.$fileName."_odd.pdf","F");


//splits all even pages to a pdf
function SplitPDF2($np3) {
	$lastpage = $np3;
	$pp2 = array();
	for ($n=1; $n<=$np3; $n++) {
		if ($n % 2 == 0) { 
			$pp2[] = array( $n ); 
		}
	}
	return $pp2;
}

$mpdf2=new mPDF('','A4-L','','',0,0,0,0,0,0);
$mpdf2->SetImportUse();	
$mpdf2->SetDisplayMode('fullpage');
$pagecount2 = $mpdf2->SetSourceFile($outputPath.$fileName.".pdf");
$pp2 = SplitPDF2($pagecount2);
foreach($pp2 AS $v2) {
	$mpdf2->AddPage(); 
	if ($v2[0]>0 && $v2[0]<=$pagecount2) {
		$tplIdx2 = $mpdf2->ImportPage($v2[0]);
		$mpdf2->UseTemplate($tplIdx2);
	}
}

$mpdf2->Output($outputPath.$fileName."_even.pdf","F");

$recto_MPDF = $outputPath.$fileName."_odd.pdf";
$verso_MPDF = $outputPath.$fileName."_even.pdf";
//checks if both files exist
if (file_exists($recto_MPDF) && file_exists($verso_MPDF)) { 
	//adds them to the data base
	$bdd_pdf = fopen('bdd_pdf.txt', "a");
	fwrite($bdd_pdf, $fileName."_non_duplex.pdf");
	fclose($bdd_pdf);
	//prints a download button
	echo '<br><br>Recto :';
	echo '<FORM ACTION="books/pdf/' . $fileName . '_odd.pdf" target="_blank">';
	echo '<INPUT TYPE="SUBMIT" VALUE="Télécharger"/>';
	echo '</FORM>';
	echo 'Verso :';
	echo '<FORM ACTION="books/pdf/' . $fileName . '_even.pdf" target="_blank">';
	echo '<INPUT TYPE="SUBMIT" VALUE="Télécharger"/>';
	echo '</FORM>';
	include ('lib/erase.php');
} else { 
	echo "<br>ERROR : Le fichier PDF n\'a pas été correctement exporté en deux fichiers .";
	include ('lib/erase.php');
}
?>