<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Gestion des Codes Qualités
----------------------------------------
*/

$row = 0;

// Requête sur TYPE DE MESURE (Hydrométrie, Pluviométrie, Piézométrie, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type FROM ".TABLE_EQ_TYPE." WHERE active_eq_type=1 ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);
while ($eq_type = tep_db_fetch_array($eq_type_query))
{				
	$eq_type_array[$eq_type['id_eq_type']] = htmlaccent(html_entity_decode($eq_type['nom_eq_type'] ?? $default_string));
}

$qualitydata_array = array();

// Requête sur Code Qualité
$sql_quality = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data, info_qualite_data, id_eq_type 
				FROM ".TABLE_DATA_QUALITE." 
				ORDER BY init_qualite_data ASC";
$quality_query = tep_db_query($sql_link,$sql_quality);
while($quality_tab = tep_db_fetch_array($quality_query))
{
	$id_data_qualite = $quality_tab['id_data_qualite'];

	$qualitydata_array[$id_data_qualite]['init_qualite_data'] = htmlaccent(html_entity_decode($quality_tab['init_qualite_data'] ?? $default_string));
	$qualitydata_array[$id_data_qualite]['nom_qualite_data'] = htmlaccent(html_entity_decode($quality_tab['nom_qualite_data'] ?? $default_string));
	$qualitydata_array[$id_data_qualite]['info_qualite_data'] = htmlaccent(html_entity_decode($quality_tab['info_qualite_data'] ?? $default_string));
	$qualitydata_array[$id_data_qualite]['id_eq_type'] = $quality_tab['id_eq_type'];
}				

echo "<div id='onglet_contenu' style='height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<div class='table-container' style='float:left;height:70vh;'>";

			echo "<table id='table_tri' cellspacing='0' style=''>";
			
				echo "<thead>";
					echo "<tr class='header-row' style='background-color: #eef3f8;'>";
						echo "<th>".htmlaccent('Intitulé')."</th>";				
						echo "<th>".htmlaccent('Nom long')."</th>";
						echo "<th>".htmlaccent('Description')."</th>";
						echo "<th>".htmlaccent('Type de données')."</th>";
						echo "<th>&nbsp;</td>";
					echo "</tr>";	
				echo "</thead>";			
					
				// Nouvelle Entrée

				echo "<tr><td colspan='2' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter - Code Qalité')."</td></tr>\n";

				echo "<tr>";
				
					echo "<td><input type='text' class='input_texte_small' style='border:2px solid #609966;' name='quality_init_0' ></td>";
					echo "<td><input type='text' class='input_texte_200' style='border:2px solid #609966;' name='quality_nom_0' ></td>";
					echo "<td><input type='text' class='input_texte_450' style='border:2px solid #609966;' name='quality_info_0' ></td>";

					// Type de données liée Hydro, Plu, Piezo
					echo "<td>";
						
						echo "<select name='quality_select_type_0' id='quality_select_type_0' style='width:120px;' onchange=''>";
										
							echo "<option value='0'>Tous les types</option>";
							
							$selected = '';		
							if(isset($eq_type_array))
							{
								foreach($eq_type_array as $key => $value)
								{
									echo "<option value='".$key."' ".$selected." >".$value."</option>";
								}
							}
							
						echo "</select>";
						
					echo "</td>";
					
					echo "<td>&nbsp;</td>";
					
				echo "<tr>";

				echo "<tr><td colspan='2' class='lignevide'>&nbsp;</td></tr>";
				
				// Mise en forme des lignes pour affichage des régions hydrologiques
				foreach ($qualitydata_array as $id_quality => $quality_tab)
				{				
					if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
					else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
		
					echo "<tr ".$row_l."  id='row_qd_".$id_quality."' >";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_small' name='quality_init_".$id_quality."' value='".$qualitydata_array[$id_quality]['init_qualite_data']."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_200' name='quality_nom_".$id_quality."' value='".$qualitydata_array[$id_quality]['nom_qualite_data']."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_450' name='quality_info_".$id_quality."' value='".$qualitydata_array[$id_quality]['info_qualite_data']."'>\n";
						echo "</td>";

						// Type de données liée Hydro, Plu, Piezo
						echo "<td>";
						
							echo "<select name='quality_select_type_".$id_quality."' id='quality_select_type_".$id_quality."' style='width:120px;' onchange=''>";
											
								echo "<option value='0'>Tous les types</option>";
								
								$selected = '';		
								if(isset($eq_type_array))
								{
									foreach($eq_type_array as $key => $value)
									{
										if($qualitydata_array[$id_quality]['id_eq_type'] == $key){$selected="selected";}	
										else{$selected = '';}											
										echo "<option value='".$key."' ".$selected." >".$value."</option>";
									}
								}
								
							echo "</select>";
							
						echo "</td>";
						
						// supprimer
						echo "<td class='t_icon'>";
							echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:20px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"delete_qualitydata('".$id_quality."');\">";
						echo "</td>\n";
					
					echo "</tr>";					
				}
				
			echo "</table>";

		echo "</div>\n";

	echo "<hr>\n";
	echo "</div>\n";
	
	
echo "<hr>\n";
echo "</div>\n";

?>

<script>

	// Fonction de lancement de la procédure AJAX permettant de supprimer une saisie de caractéristique
	function delete_qualitydata(id_qualitydata)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_qualitydata: id_qualitydata
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/qualitydata/process_delqualitydata.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				del_qualitydata = jsonResponse['del_qualitydata'];
				message_info = jsonResponse['message_info'];

				if(del_qualitydata)
				{
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					document.getElementById('row_qd_'+id_qualitydata).style.display='none';
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