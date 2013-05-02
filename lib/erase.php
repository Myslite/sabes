<?php
/******************************************************************************/
/*                                                                            */
/*                       __        ____                                       */
/*                 ___  / /  ___  / __/__  __ _____________ ___               */
/*                / _ \/ _ \/ _ \_\ \/ _ \/ // / __/ __/ -_|_-<               */
/*               / .__/_//_/ .__/___/\___/\_,_/_/  \__/\__/___/               */
/*              /_/       /_/                                                 */
/*                                                                            */
/*                                                                            */
/******************************************************************************/
/*                                                                            */
/* Titre          : Effacer un repertoire et ses sous repertoires             */
/*                                                                            */
/* URL            : http://www.phpsources.org/scripts513-PHP.htm              */
/* Auteur         : evanxg852000                                              */
/* Date édition   : 08 Mai 2009                                               */
/* Website auteur : http://evansofts.com                                      */
/*                                                                            */
/******************************************************************************/

//-Evance soumaoro(Evan-XG)-//
//-http://evansofts.com-//
//-efface un repertoire et ses sous repertoires-//
//-============================================-//

//checks if the directories exists and erases them.
if (is_dir('books/html/' . $id)){RepEfface('books/html/' . $id);}
if (is_dir('tmp/' . $id)){RepEfface('tmp/' . $id);}
function RepEfface($dir)
{
    $handle = opendir($dir);
    while($elem = readdir($handle)) //ce while vide tous les repertoires et sous rep
    {
		if(is_dir($dir.'/'.$elem) && substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') //si c'est un repertoire
        {
			RepEfface($dir.'/'.$elem);
		}
		else
		{
			if(substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.')
			{
				unlink($dir.'/'.$elem);
			}
		}
			
	}
	
	$handle = opendir($dir);
	while($elem = readdir($handle)) //ce while efface tous les dossiers
	{
		if(is_dir($dir.'/'.$elem) && substr($elem, -2, 2) !== '..' && substr($elem, -1, 1) !== '.') //si c'est un repertoire
        {
			RepEfface($dir.'/'.$elem);
			rmdir($dir.'/'.$elem);
		}	
	
	}
	rmdir($dir); //ce rmdir efface le repertoire principale
}
session_unset (); 
session_destroy ();
exit;
?>