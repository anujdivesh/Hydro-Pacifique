<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Gestion des régions hydrologiques
----------------------------------------
*/


echo "<div id='onglet_contenu' style='height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<div id='tab_data_georegionhydro' class='table-container' style='float:left;height:70vh;'>";
		echo "</div>\n";

	echo "<hr>\n";
	echo "</div>\n";
	
echo "<hr>\n";
echo "</div>\n";
?>


<script>

	var tabDataGeoRegionHydroe = document.getElementById('tab_data_georegionhydro'); // contenant des données collectées par AJAX		

	// Fonction de lancement de la procédure AJAX permettant d'afficher la liste des données
	function affiche_geo_regionhydro_data()
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							territoireId: <?php echo $territoire_id;?>
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/geographie/process_tab_regionhydro.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				tab_geo_regionhydro = jsonResponse['tab_geo_regionhydro'];
				message_info = jsonResponse['message_info'];

				if(tab_geo_regionhydro)
				{
					htmlcode = jsonResponse['htmlcode'];				
					tabDataGeoRegionHydroe.innerHTML = htmlcode; // Ajoute la ligne dans le tableau des fichiers importables
				}
				else
				{
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
				}
			}
		};

		// Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
		xhr.send(JSON.stringify(dataToSend));
	}

	affiche_geo_regionhydro_data(); // Lancement de la fonction permettant d'aller chercher les données de types de chroniques

	// Fonction de lancement de la procédure AJAX permettant de supprimer une saisie de caractéristique
	function delete_regionhydro(id_regionhydro)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_regionhydro: id_regionhydro
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/geographie/process_delregionhydro.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				del_regionhydro = jsonResponse['del_regionhydro'];
				message_info = jsonResponse['message_info'];

				if(del_regionhydro)
				{
					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					affiche_geo_regionhydro_data();
					affiche_geo_riviere_data();
				}
				else
				{
					contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
				}

				contenuInfo.innerHTML = message_info;							
				contenuInfo.style.display = 'block';

			}
		};

		// Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
		xhr.send(JSON.stringify(dataToSend));
	}

	
</script>