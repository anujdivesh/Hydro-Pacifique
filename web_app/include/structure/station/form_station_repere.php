<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Dans fiche station : Onglet repère pour station piézométrique
*/

$row = 0;

$tab_code_repere = array('','C','T','D','S','A');

// Requête sur Repere sur le puits de la station 
$sql_repere = "SELECT DISTINCT id, nature_repere, code_repere, z_repere, precision_repere, date_debut_valid, date_fin_valid,
								nature_repere_1, z_repere_g1, nature_repere_2, z_repere_g2, obs 
				FROM ".TABLE_STATION_PIEZO_REPERE."
                WHERE id_station = ".$id_station."
				ORDER BY date_debut_valid DESC";
$repere_query = tep_db_query($sql_link,$sql_repere);



echo "<div id='onglet_contenu' style='overflow-y: auto;height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<table id='table_tri' cellspacing='0' >";
		
            echo "<tr >";

                echo "<th colspan='2' style='font-size:14px;'>".htmlaccent('Validité')."</th>";
                echo "<th colspan='4' style='font-size:14px;'>".htmlaccent('Repère')."</th>";                
                echo "<th colspan='4' style='font-size:14px;'>".htmlaccent('Relevé par géomètre')."</th>";
                echo "<th colspan='1' style='font-size:14px;'>&nbsp;</td>";

            echo "</tr>";

			 echo "<tr ><td colspan='12' style='height:10px;'></td></tr>";
        
            echo "<tr>";
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Date début')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Date fin')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Nature')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Code')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Z [m]')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Précision')."</th>";
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Repère 1')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Z [m]')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Repère 2')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Z [m]')."</th>";	
				echo "<th style='color:#34495E;font-size:13px;border:0;'>".htmlaccent('Observation')."</th>";
				echo "<th style='width:40px;border:0;'>&nbsp;</th>";
			echo "</tr>";
			
			while($repere_tab = tep_db_fetch_array($repere_query))
			{				
                $id = $repere_tab['id'];
				$date_debut_valid = dateus_fr($repere_tab['date_debut_valid']);
				if($date_debut_valid == '00-00-0000'){$date_debut_valid = '';}
                $date_fin_valid = dateus_fr($repere_tab['date_fin_valid']);
				if($date_fin_valid == '00-00-0000'){$date_fin_valid = '';}
                
                $nature_repere = htmlaccent(html_entity_decode($repere_tab['nature_repere'] ?? $default_string));

                $code_repere = htmlaccent(html_entity_decode($repere_tab['code_repere'] ?? $default_string));
                $code_repere_options = '';
				foreach($tab_code_repere as $option_code_repere)
				{
					$selected = '';
					if($code_repere==$option_code_repere){$selected = 'selected';}

					$code_repere_options .= "<option value='".$option_code_repere."' ".$selected.">".$option_code_repere."</option>";
				}
				
				$z_repere = $repere_tab['z_repere'];
				if($z_repere == '0'){$z_repere = '';}     

                $precision_repere = htmlaccent(html_entity_decode($repere_tab['precision_repere'] ?? $default_string));

                $nature_repere_1 = htmlaccent(html_entity_decode($repere_tab['nature_repere_1'] ?? $default_string));

                $z_repere_g1 = $repere_tab['z_repere_g1']; 				
				if($z_repere_g1 == '0'){$z_repere_g1 = '';}  

                $nature_repere_2 = htmlaccent(html_entity_decode($repere_tab['nature_repere_2'] ?? $default_string));

                $z_repere_g2 = $repere_tab['z_repere_g2'];
				if($z_repere_g2 == '0'){$z_repere_g2 = '';}   


				$obs = htmlaccent(html_entity_decode($repere_tab['obs'] ?? $default_string));
			
				if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
				else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
	
				echo "<tr ".$row_l." id='row_".$id."'>";
						
					echo "<td>";

                        echo "<input class='input_texte' style='width:65px;' name='date_debut_valid_".$id."' id='date_debut_valid_".$id."' value='".$date_debut_valid."' type='text'  onclick=\"javascript:displayCalendar(document.forms[0].date_debut_valid_".$id.",'dd-mm-yyyy',this);\" >"; 
                                           
					echo "</td>";

                    echo "<td>";

                        echo "<input class='input_texte' style='width:65px;' name='date_fin_valid_".$id."' id='date_fin_valid_".$id."' value='".$date_fin_valid."' type='text'  onclick=\"javascript:displayCalendar(document.forms[0].date_fin_valid_".$id.",'dd-mm-yyyy',this);\" >";
                    
					echo "</td>";

					echo "<td>";
						echo "<input class='input_texte' style='width:150px;' name='nature_repere_".$id."' value='".$nature_repere."'>\n";
					echo "</td>";
					
					echo "<td>";
						echo "<select name='code_repere_".$id."'  id='code_repere_".$id."' style='width:50px;'>";
							echo $code_repere_options;
						echo "</select>";
					echo "</td>";
					
					echo "<td>";
						echo "<input class='input_texte' style='width:50px;' name='z_repere_".$id."' value='".$z_repere."'>\n";
					echo "</td>";
					
					echo "<td>";
						echo "<input class='input_texte' style='width:110px;'  name='precision_repere_".$id."' value='".$precision_repere."'>\n";
					echo "</td>";

                    echo "<td>";
						echo "<input class='input_texte' style='width:130px;' name='nature_repere_1_".$id."' value='".$nature_repere_1."'>\n";
					echo "</td>";

                    echo "<td>";
						echo "<input class='input_texte' style='width:50px;' name='z_repere_g1_".$id."' value='".$z_repere_g1."'>\n";
					echo "</td>";

                    echo "<td>";
						echo "<input class='input_texte' style='width:130px;' name='nature_repere_2_".$id."' value='".$nature_repere_2."'>\n";
					echo "</td>";

                    echo "<td>";
						echo "<input class='input_texte' style='width:50px;' name='z_repere_g2_".$id."' value='".$z_repere_g2."'>\n";
					echo "</td>";
					
					echo "<td>";
						echo "<input class='input_texte' style='width:250px;' name='obs_".$id."' value='".$obs."'>\n";
					echo "</td>";
					
					// supprimer
					echo "<td class='t_icon'>";
						echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:20px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"delete_repere('".$id."');\">";
					echo "</td>\n";
				
				echo "</tr>";
			}
			
			
			// NEW DATA TYPE
			$code_repere_options = '';
			foreach($tab_code_repere as $option_code_repere)
			{
				$code_repere_options .= "<option value='".$option_code_repere."' >".$option_code_repere."</option>";
			}
			
			echo "<tr><td colspan='13' style='color:#ABB2B9;font-size:14px;'>".htmlaccent('Ajouter un repère')."</td></tr>\n";
							
			echo "<tr>";
			
				echo "<td>";

					echo "<input class='input_texte' style='width:65px;' name='date_debut_valid_0' id='date_debut_valid_0' value='' type='text'  onclick=\"javascript:displayCalendar(document.forms[0].date_debut_valid_0,'dd-mm-yyyy',this);\" >"; 
										
				echo "</td>";

				echo "<td>";

					echo "<input class='input_texte' style='width:65px;' name='date_fin_valid_0' id='date_fin_valid_0' value='' type='text'  onclick=\"javascript:displayCalendar(document.forms[0].date_fin_valid_0,'dd-mm-yyyy',this);\" >";
				
				echo "</td>";

				echo "<td>";
					echo "<input class='input_texte' style='width:150px;' name='nature_repere_0' value=''>\n";
				echo "</td>";
				
				echo "<td>";
					echo "<select name='code_repere_0'  id='code_repere_0' style='width:50px;'>";
						echo $code_repere_options;
					echo "</select>";
				echo "</td>";
				
				echo "<td>";
					echo "<input class='input_texte' style='width:50px;' name='z_repere_0' value=''>\n";
				echo "</td>";
				
				echo "<td>";
					echo "<input class='input_texte' style='width:110px;'  name='precision_repere_0' value=''>\n";
				echo "</td>";

				echo "<td>";
					echo "<input class='input_texte' style='width:130px;' name='nature_repere_1_0' value=''>\n";
				echo "</td>";

				echo "<td>";
					echo "<input class='input_texte' style='width:50px;' name='z_repere_g1_0' value=''>\n";
				echo "</td>";

				echo "<td>";
					echo "<input class='input_texte' style='width:130px;' name='nature_repere_2_0' value=''>\n";
				echo "</td>";

				echo "<td>";
					echo "<input class='input_texte' style='width:50px;' name='z_repere_g2_0' value=''>\n";
				echo "</td>";
				
				echo "<td>";
					echo "<input class='input_texte' style='width:250px;' name='obs_0' value=''>\n";
				echo "</td>";

				echo "<td>&nbsp;</td>";
				
			echo "<tr>";
			
		
		echo "</table>";

	echo "<hr>\n";
	echo "</div>\n";
	
	
echo "<hr>\n";
echo "</div>\n";
?>


<script>

	// Fonction de lancement de la procédure AJAX permettant de supprimer une saisie de caractéristique
	function delete_repere(id_repere)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_repere: id_repere
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/station/process_delrepere.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				del_repere = jsonResponse['del_repere'];
				message_info = jsonResponse['message_info'];

				if(del_repere)
				{
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					document.getElementById('row_'+id_repere).style.display='none';
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