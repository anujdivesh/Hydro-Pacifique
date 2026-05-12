<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Gestion des axes pouvant être affiché sur un graphique et de l'unité correspondante
----------------------------------------
*/

$row = 0;

echo "<div id='onglet_contenu' style='height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<div id='tab_axe' class='table-container' style='float:left;height:70vh;'>";
		echo "</div>\n";

	echo "<hr>\n";
	echo "</div>\n";
	
	
echo "<hr>\n";
echo "</div>\n";

?>

<script>

	var tabAxe = document.getElementById('tab_axe'); // contenant des données collectées par AJAX		

	// Fonction de lancement de la procédure AJAX permettant d'afficher la liste des données
	function affiche_axe()
	{
		// Créer un objet JavaScript contenant les données à envoyer
		//var dataToSend = {};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/typedata/process_tab_axe.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				tab_axedata = jsonResponse['tab_axedata'];
				message_info = jsonResponse['message_info'];

				if(tab_axedata)
				{
					htmlcode = jsonResponse['htmlcode'];				
					tabAxe.innerHTML = htmlcode; // Ajoute la ligne dans le tableau des fichiers importables
					console.log(htmlcode);
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
		xhr.send(JSON.stringify());
	}

	affiche_axe(); // Lancement de la fonction permettant d'aller chercher les données de types de chroniques


	// Fonction de lancement de la procédure AJAX permettant de supprimer une saisie de Chronique
	function delete_axe(id_axe)
	{
		idTypeDataSelect = document.getElementById('chron_filter').value; // ça vient de l'onglet des chroniques

		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
                            id_axe: id_axe
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/typedata/process_delaxe.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				del_axe = jsonResponse['del_axe'];
				message_info = jsonResponse['message_info'];

				if(del_axe)
				{
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					affiche_typedata(idTypeDataSelect);
					affiche_axe();
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

	
</script>
