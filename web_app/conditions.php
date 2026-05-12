<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

require('include/application_top.php');

//tep_redirect('operation.php');


require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";	
	
	echo "<div id='contenu_centre'>";

		echo "<h1><span>".htmlaccent('Conditions d\'utilisation')."</span></h1>";
		
		
		echo "<p class='bloc'>";
		
			$handle = fopen('convention_fr.txt','r'); 
		
			if($handle)
			{
				while(!feof($handle))
				{
					$ligne = fgets($handle);
					//echo htmlaccent(post_secure($sql_link,$ligne));
					echo "<br>";
				}
			}
		
		
		echo "</p>";
		
		
		
		echo "<div id='cadre_index_bas'>";	
	
			//echo "<img src='".DIR_WS_IMG."powered_by.png'>";
			
		echo "</div>";
	
	
	echo "<hr>";
	echo "</div>";
	
	
echo "<hr>";
echo "</div>";

	require('include/application_bottom.php'); 

echo "</body>";

echo "</html>";

?>	
