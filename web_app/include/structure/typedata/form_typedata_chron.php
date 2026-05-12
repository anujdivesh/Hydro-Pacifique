<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Gestion des Types de données Formulaire (CI, CIE, QI, QIE, ...)
----------------------------------------
*/

// Requête sur TYPE DE MESURE (Hydrométrie, Pluviométrie, Piézométrie, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type FROM ".TABLE_EQ_TYPE." WHERE active_eq_type=1 ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);
while ($eq_type = tep_db_fetch_array($eq_type_query))
{				
	$eq_type_array[$eq_type['id_eq_type']] = $eq_type['nom_eq_type'];
} 


echo "<div id='onglet_contenu' style='height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<div id='' style='float:left;margin-bottom:5px;'>";

			echo "<p style='float:left;margin-right:20px;padding-top:5px;color:#000;font-size:14px;font-weight:bold;'>";
				echo 'Sélectionner : ';
			echo "</p>";

			echo "<select name='chron_filter' id='chron_filter' style='float:left;width:150px;' onchange='selectTypeData()'>";
								
				echo "<option value='0'>-</option>";
				
				$selected = '';		
				if(isset($eq_type_array))
				{
					foreach($eq_type_array as $key => $value)
					{																			
						echo "<option value='".$key."' ".$selected." >".$value."</option>";
					}
				}
			
			echo "</select>";

		echo "</div>\n";

		echo "<div id='tab_datatypechron' class='table-container' style='float:left;height:70vh;'>";
		echo "</div>\n";

	echo "<hr>\n";
	echo "</div>\n";
	
	
echo "<hr>\n";
echo "</div>\n";

?>

<script>

	var tabDatatypechron = document.getElementById('tab_datatypechron'); // contenant des données collectées par AJAX		

	// Fonction de lancement de la procédure AJAX permettant d'afficher la liste des données
	function affiche_typedata(idTypeData)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							idTypeData: idTypeData,
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/typedata/process_tab_typedata.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				tab_typedata = jsonResponse['tab_typedata'];
				message_info = jsonResponse['message_info'];

				if(tab_typedata)
				{
					htmlcode = jsonResponse['htmlcode'];				
					tabDatatypechron.innerHTML = htmlcode; // Ajoute la ligne dans le tableau des fichiers importables
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

	affiche_typedata(0); // Lancement de la fonction permettant d'aller chercher les données de types de chroniques



	// Fonction de lancement de la procédure AJAX permettant de supprimer une saisie de Chronique
	function delete_typedata(id_typedata)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_typedata: id_typedata
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/typedata/process_deltypedata.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				del_typedata = jsonResponse['del_typedata'];
				message_info = jsonResponse['message_info'];

				if(del_typedata)
				{
					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					affiche_typedata(id_typedata);
					affiche_axe();
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

	function selectTypeData()
	{
		idTypeDataSelect = document.getElementById('chron_filter').value;
		affiche_typedata(idTypeDataSelect);
	}
	
</script>
