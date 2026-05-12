<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Page d'entrée à la partie admnin pour gérer les utilisateurs de la plateforme + Quelques options très générales
*/

require('include/application_top.php');

$message_suprr_tech = '';
$row = 0;

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";
	
	echo "<div id='contenu_centre'>";
	
		echo "<div id='contenu_box2' >\n";
	
			echo "<h1><span>".htmlaccent('Paramètres de l\'application')."</span></h1>"; 
		
			echo "<div id='box_result' style='padding: 20px;'>\n";
		
				//echo "<div id='contenu_box2' style='background-image: url(".DIR_WS_IMG_ICO."lock_back.png);background-position:center;background-repeat:no-repeat;'>";
				
				echo "<div id='contenu_box2'>";
				
					if($config==1)
					{	
						echo "<div id='gestion_list'>\n";
							
							echo "<img src='".DIR_WS_IMG_ICO."users.png' >";
							echo "<p>".htmlaccent('Utilisateurs')."</p>"; 
							
							echo "<ul>";
							
								echo "<li><a href='list_users.php'>".htmlaccent('Modifications et droits')."</a></li>";
								echo "<li><a href='modif_user.php'>".htmlaccent('Nouvel utilisateur')."</a></li>";
							
							echo "</hr>";
							echo "</ul>";
						
						echo "<hr>";
						echo "</div>";
						
						echo "<div id='gestion_list'>\n";
							
							echo "<img src='".DIR_WS_IMG_ICO."param.png' >";
							echo "<p>".htmlaccent('Configuration')."</p>"; 
						
							echo "<ul>";
							
								//echo "<li><a href='gestion_pdt.php'>".htmlaccent('Pas de temps (exportation)')."</a></li>";
								echo "<li><a href='gestion_type.php'>".htmlaccent('Type de mesure (Pluie/Débit)')."</a></li>";
								//echo "<li><a href='gestion_type_data.php'>".htmlaccent('Type de données (CI, CIE, QI, QIE, ...)')."</a></li>";								
								//echo "<li><a href='gestion_quality_data.php'>".htmlaccent('Code qualité des données')."</a></li>";
								//echo "<li><a href='gestion_method_debit.php'>".htmlaccent('Méthodes de mesure du débit')."</a></li>";
							
							echo "</hr>";
							echo "</ul>";
						
						echo "<hr>";
						echo "</div>";
					}
				
				echo "<hr>";
				echo "</div>";
				
			echo "</div>";	
		
		echo "<hr>";
		echo "</div>";
		
		
	echo "<hr>";
	echo "</div>";
	
	
echo "<hr>";
echo "</div>";
	require('include/application_bottom.php'); 
echo "</body>";

echo "</html>";

?>	
