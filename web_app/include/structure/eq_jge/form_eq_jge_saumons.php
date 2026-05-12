<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Onglet Saumon
*/

$row = 0;

// Requête sur TYPE DE MESURE
$sql_saumon = "SELECT DISTINCT id, num, titre, poids, distance_axe, t_air, r_dist, fabricant, obs FROM ".TABLE_SAUMON."
				ORDER BY num ASC";
$saumon_query = tep_db_query($sql_link,$sql_saumon);


echo "<div id='onglet_contenu' style='height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<div class='table-container' style='float:left;height:70vh;'>";

			echo "<table id='table_tri' cellspacing='0' >";
			
				echo "<thead>";
					echo "<tr class='header-row' style='background-color: #eef3f8;'>";
						echo "<th>".htmlaccent('Num')."</td>";	
						echo "<th>".htmlaccent('Titre')."</td>";	
						echo "<th>".htmlaccent('Poids')."</td>";	
						echo "<th>".htmlaccent('Dist. Axe')."</td>";	
						echo "<th>".htmlaccent('Tair')."</td>";	
						echo "<th>".htmlaccent('Rdist')."</td>";	
						echo "<th>".htmlaccent('Fabricant')."</td>";
						echo "<th>".htmlaccent('Observation')."</td>";
						echo "<th style='text-align:center;' >&nbsp;</td>";
					echo "</tr>";
				echo "</thead>";	
				

				// NEW DATA TYPE

				echo "<tr><td colspan='9' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter - Saumon')."</td></tr>\n";
								
				echo "<tr>";
				
					echo "<td><input type='text' class='input_texte_small' name='saumon_num_0' ></td>";
					echo "<td><input type='text' class='input_texte_200' name='saumon_titre_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' name='saumon_poids_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' name='saumon_dist_axe_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' name='saumon_t_air_0' ></td>";
					echo "<td><input type='text' class='input_texte_60' name='saumon_r_dist_0' ></td>";
					echo "<td><input type='text' class='input_texte_200' name='saumon_fabricant_0' ></td>";
					echo "<td><input type='text' class='input_texte_300' name='saumon_obs_0' ></td>";					
					echo "<td>&nbsp;</td>";
					
				echo "<tr>";

				//ligne vide dans le tableau		
							
				echo "<tr>";
					echo "<td colspan='9' style='height:10px;'>&nbsp;</td>";
				echo "</tr>";
				
				while($saumon_tab = tep_db_fetch_array($saumon_query))
				{
					$saumon_id = $saumon_tab['id'];
					$saumon_num = htmlaccent(html_entity_decode($saumon_tab['num']));
					$saumon_titre = htmlaccent(html_entity_decode($saumon_tab['titre']));
					$saumon_poids = htmlaccent(html_entity_decode($saumon_tab['poids']));
					$saumon_dist_axe = htmlaccent(html_entity_decode($saumon_tab['distance_axe']));
					$saumon_t_air = htmlaccent(html_entity_decode($saumon_tab['t_air']));
					$saumon_r_dist = htmlaccent(html_entity_decode($saumon_tab['r_dist']));
					$saumon_fabricant = htmlaccent(html_entity_decode($saumon_tab['fabricant']));
					$saumon_obs = htmlaccent(html_entity_decode($saumon_tab['obs']));
				
					if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
					else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
		
					echo "<tr ".$row_l." id='row_eqs_".$saumon_id."' >";
							
						echo "<td>";
							echo "<input type='text' class='input_texte_small' name='saumon_num_".$saumon_id."' value='".$saumon_num."'>\n";
						echo "</td>";

						echo "<td>";
							echo "<input type='text' class='input_texte_200' name='saumon_titre_".$saumon_id."' value='".$saumon_titre."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='saumon_poids_".$saumon_id."' value='".$saumon_poids."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='saumon_dist_axe_".$saumon_id."' value='".$saumon_dist_axe."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='saumon_t_air_".$saumon_id."' value='".$saumon_t_air."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='saumon_r_dist_".$saumon_id."' value='".$saumon_r_dist."'>\n";
						echo "</td>";

						echo "<td>";
							echo "<input type='text' class='input_texte_200' name='saumon_fabricant_".$saumon_id."' value='".$saumon_fabricant."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_300' name='saumon_obs_".$saumon_id."' value='".$saumon_obs."'>\n";
						echo "</td>";
						
						// supprimer
						echo "<td class='t_icon'>";
							echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:20px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"delete_eqsaumon('".$saumon_id."');\">";
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
	function delete_eqsaumon(id_saumon)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_saumon: id_saumon
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/eq_jge/process_delsaumon.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				del_saumon = jsonResponse['del_saumon'];
				message_info = jsonResponse['message_info'];

				if(del_saumon)
				{
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					document.getElementById('row_eqs_'+id_saumon).style.display='none';
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