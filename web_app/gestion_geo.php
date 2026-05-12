<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Définition des zones géographiques (Régions hydrologiques / Tournée)
*/

require('include/application_top.php');

$message_info = '';
$message_suprr_geo = '';


// Récupération des données existantes
// Requête sur TABLE_TERRITOIRE
$sql_territoire = "SELECT DISTINCT nom_territoire, init_territoire, theme_region, region_default FROM ".TABLE_TERRITOIRE." WHERE id_territoire=".$territoire_id." ORDER BY LOWER(nom_territoire) ASC";
$territoire_query = tep_db_query($sql_link,$sql_territoire);
while ($territoire = tep_db_fetch_array($territoire_query))
{				
	$nom_territoire = htmlaccent(html_entity_decode($territoire['nom_territoire'] ?? $default_string));
	$init_territoire = htmlaccent(html_entity_decode($territoire['init_territoire'] ?? $default_string));
	$theme_region = htmlaccent(html_entity_decode($territoire['theme_region'] ?? $default_string));
	$region_default = $territoire['region_default'];
} 

require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

	echo "<div id='contenu_info' style='display:none;'></div>";

	require(DIR_WS_STRUCTURE . 'block_wait.php'); // Block d'attente pendant les interrogations au serveur

	require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
	include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

	echo "<div id='contour_general'>";
		
		echo "<div id='contenu_centre'>";
			
			echo "<div id='contenu_box2'>";
			
				//FORMULAIRE
				echo "<form id='formDataGeo'>";

					echo "<input type='hidden' value='".$id_user."' name='id_user_agent'>";
					echo "<input type='hidden' value='".$territoire_id."' name='territoire_id'>";

					echo "<h1>";
						
						echo "<span>".htmlaccent('Saisie des informations géographiques')."</span>";

						// Bouton validation formulaire - Affichage en haut à gauche
						echo "<input class='button' name='save_dataGeo' id='save_dataGeo' style='float:right;' value='Enregistrer' onclick='saveDataGeo(event);' />";

					echo "</h1>";
			
					echo "<div id='onglet'>";
						echo "<ul id='menu_onglet'>";
						
							echo "<li onClick=\"ChangeOnglet_2(1, 6, 'onglet-', 'contenu-');\" id='onglet-1' class='actif' style='width:150px;'>".htmlaccent('Régions - '.$theme_region)."</li>\n";
							echo "<li onClick=\"ChangeOnglet_2(2, 6, 'onglet-', 'contenu-');\" id='onglet-2' class='' style='width:80px;'>".htmlaccent('Communes')."</li>\n";
													
							echo "<li onClick=\"ChangeOnglet_2(3, 6, 'onglet-', 'contenu-');\" id='onglet-3' class='' >".htmlaccent('Régions hydrologiques')."</li>\n";
							echo "<li onClick=\"ChangeOnglet_2(4, 6, 'onglet-', 'contenu-');\" id='onglet-4' class='' style='width:80px;'>".htmlaccent('Rivières')."</li>\n";
							echo "<li onClick=\"ChangeOnglet_2(5, 6, 'onglet-', 'contenu-');\" id='onglet-5' class='' style='width:100px;'>".htmlaccent('Aquifères')."</li>\n";

							echo "<li onClick=\"ChangeOnglet_2(6, 6, 'onglet-', 'contenu-');\" id='onglet-6' class='' style='width:80px;'>".htmlaccent('Tournées')."</li>\n";
											
						echo "</ul>";

						echo "<div id='contenu-1' class='contenu'>";
						
							require(DIR_WS_GEO . 'form_geo_regiongeo.php');
						
						echo "</div>";

						echo "<div id='contenu-2' class='contenu' style='display:none;'>";
						
							require(DIR_WS_GEO . 'form_geo_commune.php');
						
						echo "</div>";
						

						echo "<div id='contenu-3' class='contenu' style='display:none;'>";
						
							require(DIR_WS_GEO . 'form_geo_regionhydro.php');
						
						echo "</div>";
						
						echo "<div id='contenu-4' class='contenu' style='display:none;'>";
						
							require(DIR_WS_GEO . 'form_geo_riviere.php');
						
						echo "</div>";

						echo "<div id='contenu-5' class='contenu' style='display:none;'>";
						
							require(DIR_WS_GEO . 'form_geo_aquifere.php');
					
						echo "</div>";

						echo "<div id='contenu-6' class='contenu' style='display:none;'>";
						
							require(DIR_WS_GEO . 'form_geo_tournee.php');
					
						echo "</div>";
						
					echo "</div>";
					
				echo "</form>\n";
		
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

<script>

	// Initialisation des variables
	var boxWait = document.getElementById('box_wait'); // Attente lors des opérations sur les RA, occupe l'ensemble de la page
	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info


	function saveDataGeo(event)
	{
		boxWait.style.display = 'block';

		//event.preventDefault(); // Empêche la soumission par défaut du formulaire

		var form = document.getElementById('formDataGeo'); // Récupérer l'élément du formulaire		
		var formData = new FormData(form); // Créer un objet FormData à partir du formulaire

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/geographie/process_datageo_save.php", true);

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				erreur = jsonResponse['erreur'];	
				msg_info = jsonResponse['msg_info'];

				contenuInfo.innerHTML = msg_info;	

				if(erreur){contenuInfo.style.border = '4px solid #930000';}
				else{contenuInfo.style.border = '4px solid #09886d';}

				contenuInfo.style.display = 'block';
				boxWait.style.display = 'none';
            }

			affiche_geo_region_data();
			affiche_geo_commune_data();
			affiche_geo_regionhydro_data();
			affiche_geo_riviere_data();
			affiche_geo_aquifere_data();
			affiche_geo_tournee_data();
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(formData);
		
	}

</script>