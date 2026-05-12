<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Page de confguration pour gérer les Equipements pour les jaugeages
*/

require('include/application_top.php');

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
				echo "<form id='formEqJGE'>";

					echo "<input type='hidden' value='".$id_user."' name='id_user_agent'>";
					echo "<input type='hidden' value='".$territoire_id."' name='territoire_id'>";
				
					echo "<h1>";
						
						echo "<span>".htmlaccent('Configuration des Appareils de Jaugeage')."</span>";	

						// Bouton validation formulaire - Affichage en haut à gauche
						echo "<input type='submit' class='button' name='save_eqJGE' id='save_eqJGE' style='float:right;' value='Enregistrer' onclick='saveEqJGE(event);' />";

					echo "</h1>";
					
					echo "<div id='onglet'>";
						echo "<ul id='menu_onglet'>";
						
							echo "<li onClick=\"javascript:ChangeOnglet_2(1, 3, 'onglet-', 'contenu-');setActiveTab(1, 3);\" id='onglet-1' class='actif'>".htmlaccent('Hélices')."</li>\n";
							echo "<li onClick=\"javascript:ChangeOnglet_2(2, 3, 'onglet-', 'contenu-');setActiveTab(2, 3);\" id='onglet-2' class=''>".htmlaccent('Moulinets')."</li>\n";
							echo "<li onClick=\"javascript:ChangeOnglet_2(3, 3, 'onglet-', 'contenu-');setActiveTab(3, 3);\" id='onglet-3' class=''>".htmlaccent('Saumons')."</li>\n";
											
						echo "</ul>";
						
						echo "<div id='contenu-1' class='contenu'>";
						
							require(DIR_WS_EQJGE . 'form_eq_jge_helices.php');
					
						echo "</div>";

						echo "<div id='contenu-2' class='contenu' style='display:none;'>";
						
							require(DIR_WS_EQJGE . 'form_eq_jge_moulinets.php');
							
						echo "</div>";

						echo "<div id='contenu-3' class='contenu' style='display:none;'>";
						
							require(DIR_WS_EQJGE . 'form_eq_jge_saumons.php');
					
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
	var boxWait = document.getElementById('box_wait'); // Attente lors des opérations, occupe l'ensemble de la page
	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info

	// Crée un objet URLSearchParams avec les paramètres de l'URL actuelle
	const urlParams = new URLSearchParams(window.location.search);
	// Récupère la valeur du paramètre 'save'
	const saveParam = urlParams.get('save');
	
	if (saveParam === 'true') 
	{
		msg_info_save = "<span style='font-size:16px;'>Les données pour les Equipements de Jaugeage ont bien été enregistrées</span>";
		contenuInfo.innerHTML = msg_info_save;							
		contenuInfo.style.display = 'block';

		contenuInfo.style.border = '4px solid #09886d'; // bordure en vert
	}

	

	function saveEqJGE(event)
	{
		boxWait.style.display = 'block';

		event.preventDefault(); // Empêche la soumission par défaut du formulaire

		var form = document.getElementById('formEqJGE'); // Récupérer l'élément du formulaire		
		var formData = new FormData(form); // Créer un objet FormData à partir du formulaire

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/eq_jge/process_dataeqjge_save.php", true);

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				erreur = jsonResponse['erreur'];	
				msg_info = jsonResponse['msg_info'];

				if(!erreur)
				{
					let cleanUrl = window.location.origin + window.location.pathname; // Supprime tous les paramètres de l'URL actuelle
					cleanUrl += '?save=true'; // Ajouter le paramètre 'save=true' à l'URL nettoyée
					window.location.href = cleanUrl; // Recharge la page avec la nouvelle URL
				}
				else
				{
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
				}

				boxWait.style.display = 'none';
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(formData);

		
	}


	// Fonction pour activer un onglet spécifique et sauvegarder dans localStorage
	function setActiveTab(tabIndex, totalTabs) 
	{
		localStorage.setItem('activeTab', tabIndex);

		for (let i = 1; i <= totalTabs; i++) {
			document.getElementById('onglet-' + i).className = (i === tabIndex) ? 'actif' : '';
			document.getElementById('contenu-' + i).style.display = (i === tabIndex) ? 'block' : 'none';
		}
	}

	// Après le rechargement de la page, activez l'onglet correspondant à localStorage
	window.onload = function() 
	{
		let activeTab = localStorage.getItem('activeTab');
		
		if (!activeTab){activeTab = 1;} // Si aucune valeur n'est trouvée dans localStorage, on met activeTab à 1
		
		setActiveTab(parseInt(activeTab), 3);
	};


</script>