<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Pour gérer le type de Chronique
*/

require('include/application_top.php');

$message_info = '';
$message_suprr_chron = '';


// Enregistrement et suppression des données

// Suppression des données
if(isset($_GET['id_td']) && tep_not_null($_GET['id_td'])){require(DIR_WS_SUPPRIMER . 'suppr_type_data.php');}
if(isset($_GET['id_a']) && tep_not_null($_GET['id_a'])){require(DIR_WS_SUPPRIMER . 'suppr_type_axe.php');}

// Appel du fichier pour enregistrement des données modifiées
if(isset($_POST['button_save'])){require(DIR_WS_FORMULAIRE . 'ctrl_type_data.php');}




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
				echo "<form id='formTypeData'>";

					echo "<input type='hidden' value='".$id_user."' name='id_user_agent'>";
					echo "<input type='hidden' value='".$territoire_id."' name='territoire_id'>";
				
					echo "<h1>";
						
						echo "<span>".htmlaccent('Configuration des Chroniques (CI, CIE, QI, QIE, ...) et des Axes pour les graphiques')."</span>";	

						// Bouton validation formulaire - Affichage en haut à gauche
						echo "<input class='button' name='save_typedata' id='save_typedata' style='float:right;' value='Enregistrer' onclick='saveTypedata(event);' />";
						
					echo "</h1>";

					echo "<div id='onglet'>";
						echo "<ul id='menu_onglet'>";
						
							echo "<li onClick=\"javascript:ChangeOnglet_2(1, 2, 'onglet-', 'contenu-');\" id='onglet-1' class='actif'>".htmlaccent('Chroniques')."</li>\n";
							echo "<li onClick=\"javascript:ChangeOnglet_2(2, 2, 'onglet-', 'contenu-');\" id='onglet-2' class=''>".htmlaccent('Axes')."</li>\n";
											
						echo "</ul>";
						
						echo "<div id='contenu-1' class='contenu'>";
						
							require(DIR_WS_TYPEDATA . 'form_typedata_chron.php');
						
						echo "</div>";

						echo "<div id='contenu-2' class='contenu' style='display:none;'>";
						
							require(DIR_WS_TYPEDATA . 'form_typedata_axe.php');
					
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
	
	function saveTypedata(event)
	{
		idTypeDataSelect = document.getElementById('chron_filter').value; // ça vient de l'onglet des chroniques

		boxWait.style.display = 'block';

		var form = document.getElementById('formTypeData'); // Récupérer l'élément du formulaire		
		var formData = new FormData(form); // Créer un objet FormData à partir du formulaire

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/typedata/process_typedata_save.php", true);

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

			affiche_typedata(idTypeDataSelect);
			affiche_axe();
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(formData);

		
	}






</script>
