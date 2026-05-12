<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Onglet code Qualité
*/

$row = 0;

// Requête sur TYPE DE MESURE
$sql_moulinet = "SELECT DISTINCT id, num, fabricant, obs FROM ".TABLE_MOULINET."
														ORDER BY num ASC";
$moulinet_query = tep_db_query($sql_link,$sql_moulinet);


echo "<div id='onglet_contenu' style='height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<div class='table-container' style='float:left;height:70vh;'>";

			echo "<table id='table_tri' cellspacing='0' >";
			
				echo "<thead>";
					echo "<tr class='header-row' style='background-color: #eef3f8;'>";
						echo "<th>".htmlaccent('Numéro')."</th>";				
						echo "<th>".htmlaccent('Fabricant')."</th>";
						echo "<th>".htmlaccent('Observation')."</th>";
						echo "<th style='text-align:center;' >&nbsp;</th>";
					echo "</tr>";
				echo "</thead>";

				// Nouvelle Entrée

				echo "<tr><td colspan='4' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter - Moulinet')."</td></tr>\n";

				echo "<tr>";
				
					echo "<td><input type='text' class='input_texte_200' style='border:2px solid #609966;' name='moul_num_0' ></td>";
					echo "<td><input type='text' class='input_texte_200' style='border:2px solid #609966;' name='moul_fabricant_0' ></td>";
					echo "<td><input type='text' class='input_texte_300' style='border:2px solid #609966;' name='moul_obs_0' ></td>";
					echo "<td>&nbsp;</td>";
					
				echo "<tr>";

				//ligne vide dans le tableau								
				echo "<tr>";
					echo "<td colspan='4' style='height:10px;'>&nbsp;</td>";
				echo "</tr>";
				


				
				while($moulinet_tab = tep_db_fetch_array($moulinet_query))
				{
					$id_moulinet = $moulinet_tab['id'];
					$num_moulinet = htmlaccent(html_entity_decode($moulinet_tab['num'] ?? $default_string));
					$fabricant_moulinet = htmlaccent(html_entity_decode($moulinet_tab['fabricant'] ?? $default_string));
					$obs_moulinet = htmlaccent(html_entity_decode($moulinet_tab['obs'] ?? $default_string));
				
					if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
					else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
		
					echo "<tr ".$row_l."  id='row_eqm_".$id_moulinet."' >";
							
						echo "<td>";
							echo "<input type='text' class='input_texte_200' name='moul_num_".$id_moulinet."' value='".$num_moulinet."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_200' name='moul_fabricant_".$id_moulinet."' value='".$fabricant_moulinet."'>\n";
						echo "</td>";
						
						echo "<td>";
							echo "<input type='text' class='input_texte_300' name='moul_obs_".$id_moulinet."' value='".$obs_moulinet."'>\n";
						echo "</td>";
						
						// supprimer
						echo "<td class='t_icon'>";
							echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:20px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"delete_eqmoulinet('".$id_moulinet."');\">";
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
	function delete_eqmoulinet(id_moulinet)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_moulinet: id_moulinet
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/eq_jge/process_delmoulinet.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				del_moulinet = jsonResponse['del_moulinet'];
				message_info = jsonResponse['message_info'];

				if(del_moulinet)
				{
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					document.getElementById('row_eqm_'+id_moulinet).style.display='none';
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
