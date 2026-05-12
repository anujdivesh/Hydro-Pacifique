<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Onglet Hélice
*/

$row = 0;

// Requête sur TYPE DE MESURE
$sql_helice = "SELECT DISTINCT id, num, diametre, pas, l1, a1, b1, l2, a2, b2, a3, b3, fabricant, obs FROM ".TABLE_HELICE."
									ORDER BY num ASC";
$helice_query = tep_db_query($sql_link,$sql_helice);


echo "<div id='onglet_contenu' style='height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<div class='table-container' style='float:left;height:70vh;'>";

			echo "<table id='table_tri' cellspacing='0' >";
			
				echo "<thead>";
					echo "<tr class='header-row' style='background-color: #eef3f8;'>";
						echo "<th>".htmlaccent('Numéro')."</th>";	
						echo "<th>".htmlaccent('Diamètre')."</th>";	
						echo "<th>".htmlaccent('Pas')."</th>";	
						echo "<th>".htmlaccent('l1')."</th>";	
						echo "<th>".htmlaccent('a1')."</th>";	
						echo "<th>".htmlaccent('b1')."</th>";
						echo "<th>".htmlaccent('l2')."</th>";	
						echo "<th>".htmlaccent('a2')."</th>";	
						echo "<th>".htmlaccent('b2')."</th>";	
						echo "<th>".htmlaccent('a3')."</th>";	
						echo "<th>".htmlaccent('b3')."</th>";				
						echo "<th>".htmlaccent('Fabricant')."</th>";
						echo "<th>".htmlaccent('Observation')."</th>";
						echo "<th style='text-align:center;' >&nbsp;</th>";
					echo "</tr>";
				echo "</thead>";		
				
				// NEW DATA TYPE
				
				echo "<tr><td colspan='14' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter - Hélice')."</td></tr>\n";
								
				echo "<tr>";
				
					echo "<td><input type='text' class='input_texte_small' style='border:2px solid #609966;' name='helice_num_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_diam_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_pas_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_l1_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_a1_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_b1_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_l2_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_a2_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_b2_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_a3_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='helice_b3_0' ></td>";
					echo "<td><input type='text' class='input_texte_200' style='border:2px solid #609966;' name='helice_fabricant_0' ></td>";
					echo "<td><input type='text' class='input_texte_300' style='border:2px solid #609966;' name='helice_obs_0' ></td>";
					
					echo "<td>&nbsp;</td>";
					
				echo "<tr>";

				//ligne vide dans le tableau	
				echo "<tr>";
					echo "<td colspan='14' style='height:10px;'>&nbsp;</td>";
				echo "</tr>";
				
				while($helice_tab = tep_db_fetch_array($helice_query))
				{
					$helice_id = $helice_tab['id'];
					$helice_num = htmlaccent(html_entity_decode($helice_tab['num'] ?? $default_string));
					$helice_diam = html_entity_decode($helice_tab['diametre'] ?? $default_string);
					$helice_pas = html_entity_decode($helice_tab['pas'] ?? $default_string);
					$helice_l1 = html_entity_decode($helice_tab['l1'] ?? $default_string);
					$helice_a1 = html_entity_decode($helice_tab['a1'] ?? $default_string);
					$helice_b1 = html_entity_decode($helice_tab['b1'] ?? $default_string);
					$helice_l2 = html_entity_decode($helice_tab['l2'] ?? $default_string);
					$helice_a2 = html_entity_decode($helice_tab['a2'] ?? $default_string);
					$helice_b2 = html_entity_decode($helice_tab['b2'] ?? $default_string);
					$helice_a3 = html_entity_decode($helice_tab['a3'] ?? $default_string);
					$helice_b3 = html_entity_decode($helice_tab['b3'] ?? $default_string);				
					$helice_fabricant = htmlaccent(html_entity_decode($helice_tab['fabricant'] ?? $default_string));
					$helice_obs = htmlaccent(html_entity_decode($helice_tab['obs'] ?? $default_string));
				
					if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
					else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
		
					echo "<tr ".$row_l."  id='row_eqh_".$helice_id."' >";
							
						echo "<td>";
							echo "<input type='text' class='input_texte_small' name='helice_num_".$helice_id."' value='".$helice_num."'>\n";
						echo "</td>";

						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_diam_".$helice_id."' value='".$helice_diam."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_pas_".$helice_id."' value='".$helice_pas."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_l1_".$helice_id."' value='".$helice_l1."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_a1_".$helice_id."' value='".$helice_a1."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_b1_".$helice_id."' value='".$helice_b1."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_l2_".$helice_id."' value='".$helice_l2."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_a2_".$helice_id."' value='".$helice_a2."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_b2_".$helice_id."' value='".$helice_b2."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_a3_".$helice_id."' value='".$helice_a3."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='helice_b3_".$helice_id."' value='".$helice_b3."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_200' name='helice_fabricant_".$helice_id."' value='".$helice_fabricant."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_300' name='helice_obs_".$helice_id."' value='".$helice_obs."'>\n";
						echo "</td>";
						
						// supprimer
						echo "<td class='t_icon'>";
							echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:20px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"delete_eqhelice('".$helice_id."');\">";
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
	function delete_eqhelice(id_helice)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_helice: id_helice
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/eq_jge/process_delhelice.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				del_helice = jsonResponse['del_helice'];
				message_info = jsonResponse['message_info'];

				if(del_helice)
				{
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					document.getElementById('row_eqh_'+id_helice).style.display='none';
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
