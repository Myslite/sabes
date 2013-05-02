<?php

/************************************************************************************************
* Software: SABES is an Automated Book Editing Script                                           *
* Version:  1.00                                                                                *
* Date:     23/04/2013                                                                          *
* Author:   Michel Guérin                                                                       *
* License:  Licence Art Libre (see http://artlibre.org/licence/lal)                             *
*                                                                                               *
* You may use and modify this software under the condition of the free art license. *
************************************************************************************************/

session_start ();
ini_set('memory_limit', '128M');
ini_set("default_charset", 'utf-8');
ini_set('display_errors', 'On');

echo '<html>';
echo '<head>';
echo '<title>Michel Guérin</title>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
echo '<link rel="stylesheet" type="text/css" href="style.css">';
echo '</head>';
echo '<body class="dc-post">';
echo '<div id="page">';
echo '<div id="wrapper">';
echo '<div id="main">';
echo '<div id="content">';
echo '<form action="" method="post">';
echo '<img style="float:left; margin-right:10px; margin-top:10px;" height="120px" src="http://michelguerin.fr/public/sabes.svg" />';
echo '<br><a>URL :</a>';
echo '<input type="text" name="book" size="45px"/><br>';
echo '<a>PRINTER TYPE :</a>';
echo '<input type="radio" name="bouton" value="duplex"/> Duplex';
echo '<input type="radio" name="bouton" value="non-duplex"/> Non-Duplex<br>';
echo '<a>SOURCE :</a>';
echo '<input type="radio" name="source" value="gutenberg" checked="checked"/> Projet Gutenberg';
echo '<input type="radio" name="source" value="autre"/> Other<br>';
echo '<br><input type="submit" value="Envoyer" />';
echo '</form>';
//Check if all buttons are selected/full
if(isset($_POST["book"], $_POST["bouton"], $_POST["source"])){
	if($_POST["book"] && $_POST["bouton"] && $_POST["source"]){
		$_SESSION["source"] = $_POST["source"];
		$_SESSION["bouton"] = $_POST["bouton"];
		$_SESSION["book"] = $_POST["book"];
		//add session id in a variable in order to use it as path and file name
		$id = session_id();
		//create user path
		if (!is_dir('books/html/' . $id)){
			mkdir("books/html/" . $id); 
			chmod("books/html/" . $id, 0777);
		}
		if (!is_dir('tmp/' . $id)){
			mkdir("tmp/" . $id); 
			chmod("tmp/" . $id, 0777);
		}
		//check if the user choose a link from the gutenberg project
		if($_SESSION["source"] == 'gutenberg'){
			$bdd_pdf_Name = file_get_contents('bdd_pdf.txt');
			//format user's URL to text for secure reasons
			$UserUrl =trim(htmlspecialchars($_SESSION["book"]));
			//catch the book's number from URL
			$ExplodeUrl = explode("/", trim($UserUrl, "/"));
			//transform it to use it for the gutenberg mirror
			$BookNumber = end($ExplodeUrl);
			$UrlMiddlePart1 = str_split($BookNumber);
			$UrlMiddlePart2 = substr(implode("/", $UrlMiddlePart1), 0, -2);
			$FileUrl = 'http://www.gutenberg.lib.md.us/' . $UrlMiddlePart2 . '/' . $BookNumber . '/' . $BookNumber . '-h.zip';
			$fileName = $BookNumber. '-h';
			//check if the URL is valide
			$UrlValide = @get_headers($FileUrl);
			if (strpos($UrlValide[0],'404') === false){
				//read the data base before to run the script
				if(strpos($bdd_pdf_Name, $fileName) !== FALSE){
					//if file was already generated, it show the result
					echo '<br>Le fichier pdf a déjà  été généré :';
					if($_SESSION["bouton"] == 'non-duplex'){
						// in two file for non-duplex printers
						//check if file exist
						if(strpos($bdd_pdf_Name, $fileName . "_non_duplex") !== FALSE){
							//print a download button
							echo '<br>Recto :';
							echo '<FORM ACTION="books/pdf/' . $fileName . '_odd.pdf" target="_blank">';
							echo '<INPUT TYPE="SUBMIT" VALUE="Télécharger"/>';
							echo '</FORM>';
							echo 'Verso :';
							echo '<FORM ACTION="books/pdf/' . $fileName . '_even.pdf" target="_blank">';
							echo '<INPUT TYPE="SUBMIT" VALUE="Télécharger"/>';
							echo '</FORM>';
							//erase all user's session's files & paths
							include ('lib/erase.php');
						}
						else {
							//else generate it form duplex source pdf
							include ('lib/mpdf/mpdf.php');
							$outputPath = "books/pdf/";
							include ('export3.php');
						}
					}
					elseif($_SESSION["bouton"] == 'duplex'){
						//in one file for duplex printers
						echo '<FORM ACTION="books/pdf/' . $fileName . '.pdf" target="_blank">';
						echo '<INPUT TYPE="SUBMIT" VALUE="Télécharger"/>';
						echo '</FORM>';
						include ('lib/erase.php');
					}
				}
				else{
					//if the file is not in the data base, the script is running
					echo '<br>Veuillez patienter, le fichier va être généré.';
					$newfile = 'tmp/' . $id . 'tmp_file.zip';
					//copy the zip file from gutenberg into the local server
					if (!copy($FileUrl, $newfile)) {
						echo "failed to copy $file...\n";
						include ('lib/erase.php');
					} 
					$zip = new ZipArchive();
					//extract the archive
					if ($zip->open($newfile) ===TRUE) {
						$zip->extractTo('books/html/' .$id. '/');
						$zip->close();
						if(is_dir('books/html/' .$id. '/' . $fileName)){
							echo '<br>ZIP : OK';
						} else {
							echo '<br>ZIP : ERROR';
							echo '<br>Le fichier doit être trop lourd';
							include ('lib/erase.php');
						}
					}
					else{
						exit("cannot open" . $FileUrl . "\n");
						include ('lib/erase.php');
					}
					unlink($newfile);

					include('export.php');

				}
			}
			else{
				//if the url is not valide, the script propose an other way to make it
				echo "Le lien saisi n\'est pas valide. Vérfiez sur le projet Gutenberg que le fichier html existe et qu\'il est présent dans l\'archive.";
				echo "<br> La fin de l\'url du fichier html provenant du site Gutenberg, doit être composé de chiffre puis suivi de \'-h.htm\', comme ceci: 17541-h.htm";
				echo "<br><br> Si la fin de l\'url est comme ceci : kmlus10h.htm ; vous devez utiliser la méthode alternative en modifiant l\'adresse comme cela :";
				echo "<br><br><code>http://www.gutenberg.org/dirs/etext01/kmlus10h.htm</code> &nbsp;&nbsp;&nbsp;devient&nbsp;&nbsp;&nbsp; <code>http://www.gutenberg.lib.md.us/etext01/kmlus10h.zip</code>";
				echo "<br><br>Entrez l\'adresse pointant directement sur un fichier zip (contenant un fichier html �  convertir) et selectionnez \'Other\' dans l\'option \'SOURCE\'.";
				echo "<br>_____________________________________________<br><br>The url is not valide. Check on the Gutenberg Project if the html file exists and if it's in the archive.";
				echo "<br> The end from html file from Gutenberg Project website has to be composed of numbers following by '-h.htm'. Like this : 17541-h.htm";
				echo "<br><br> If the end of the url is like this : kmlus10h.htm ; you have to use the alternative method. Modify the adress like that :";
				echo "<br><br><code>http://www.gutenberg.org/dirs/etext01/kmlus10h.htm</code> &nbsp;&nbsp;&nbsp;devient&nbsp;&nbsp;&nbsp; <code>http://www.gutenberg.lib.md.us/etext01/kmlus10h.zip</code>";
				echo "<br><br>Enter an address which point to an archive (and contain a valid html file) and select 'Other' in option 'SOURCE'.";
				include ('lib/erase.php');
			}
		}
		//check if the user choose a link from an other source
		if($_SESSION["source"] == 'autre'){
			$UserUrl =trim(htmlspecialchars($_SESSION["book"]));
			//checks if the url is pointing to an archive
			if(substr($UserUrl, -4) == ".zip"){
				//checks if the url is beginning by "http://"and adds it if not (function copy needs it)
				if(substr($UserUrl, 0, 7) !== "http://"){
					$UserUrl = "http://" . $UserUrl;
				}
				$UserValide = @get_headers($UserUrl);
				if (strpos($UserValide[0],'404') === false){
					echo '<br>Veuillez patienter, le fichier va être généré.';
					$newfile = 'tmp/' . $id . 'tmp_file.zip';
					if (!copy($UserUrl, $newfile)) {
						echo "failed to copy $UserUrl...\n";
						include ('lib/erase.php');
					} 
					$zip = new ZipArchive();
					if ($zip->open($newfile) ===TRUE) {
						$zip->extractTo('books/html/' .$id. '/');
						$zip->close();
						echo '<br>ZIP : OK';
					}
					else{
						exit("cannot open" . $UserUrl . "\n");
						include ('lib/erase.php');
					}
					unlink($newfile);
					include('export.php');
				}else{
				echo "Le lien saisi n'est pas valide.";
				}
			} else {
			echo "Le lien saisie ne pointe par vers un fichier zip";
			}
		}
	}
	else {
		echo "Vous n'avez pas remplie tout les champs";
	}
}
else {
	echo "Veuillez remplir tout les champs";
}
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</body>';
echo '</html>';
?>