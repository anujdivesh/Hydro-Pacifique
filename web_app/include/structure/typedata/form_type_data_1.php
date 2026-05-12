<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Gestion des Types de données Formulaire (CI, CIE, QI, QIE, ...)
----------------------------------------
*/

$row = 0;

$periode_transf[1] = 'DAY';
$periode_transf[2] = 'MONTH';
$periode_transf[3] = 'YEAR';


echo "<div id='onglet_contenu' style='height:75vh;'>\n";

	echo "<div id='boite1' class='first'>\n";

		echo "<div class='table-container' style='float:left;height:70vh;'>";

			echo "<table id='table_tri' cellspacing='0' style=''>";
			
				echo "<thead>";
					echo "<tr class='header-row' style='background-color: #eef3f8;'>";
						echo "<th>".htmlaccent('Acronyme')."</th>";				
						echo "<th>".htmlaccent('Intitulé')."</th>";
						echo "<th>".htmlaccent('Type de données')."</th>";			
						echo "<th>".htmlaccent('Axe')."</th>";		
						echo "<th>".htmlaccent('Unité')."</th>";					
						echo "<th>".htmlaccent('Transf. Période')."</th>";
						echo "<th>".htmlaccent('Transf. Chronique')."</th>";
						echo "<th>&nbsp;</td>";
					echo "</tr>";
				echo "</thead>";	


				// Nouvelle Entrée
				echo "<tr><td colspan='8' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter un type de Chronique')."</td></tr>\n";

				echo "<tr>";
				
					echo "<td><input type='text' class='input_texte_60' style='border:2px solid #609966;' name='chron_init_0' ></td>";
					echo "<td><input type='text' class='input_texte_300' style='border:2px solid #609966;' name='chron_nom_0' ></td>";
					
					echo "<td>";
						
						echo "<select name='chron_select_type_mesure_0' id='chron_select_type_mesure_0' style='width:150px;border:2px solid #609966;' >";
										
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

					echo "<td>";
						
						echo "<select name='chron_select_axe_0' id='chron_select_axe_0' style='width:150px;border:2px solid #609966;' >";
										
							$selected = '';		
							if(isset($data_type_axe_array))
							{
								foreach($data_type_axe_array as $key => $value)
								{																			
									echo "<option value='".$key."' ".$selected." >".$value['axe']."</option>";
								}
							}
							
						echo "</select>";
						
					echo "</td>";	
					
					echo "<td><input type='text' class='input_texte_60' id='chron_chron_unite_0' name='chron_chron_unite_0' style='border:2px solid #609966;' ></td>";

					// Transformation période possible (DAY, MONTH, YEAR)
					echo "<td>";
						
						echo "<select name='chron_select_to_periode_0' id='chron_select_to_periode_0' style='width:100px;border:2px solid #609966;' onchange=''>";
										
							echo "<option value='0'>-</option>";
							
							$selected = '';		
							for($p=1;$p<=3;$p++)
							{
								echo "<option value='".$p."' ".$selected." >by ".$periode_transf[$p]."</option>";
							}
							
						echo "</select>";
						
					echo "</td>";


					// Identification de la Chronique par transformation 
					echo "<td>";
					
						echo "<select name='chron_select_chron_periode_0' id='chron_select_chron_periode_0' style='width:200px;border:2px solid #609966;' onchange=''>";
										
							echo "<option value='0'>-</option>";
						
							$selected = '';		
							foreach($chronique_array as $key => $value)
							{
								echo "<option value='".$key."' ".$selected." >".$value['init']." - ".$value['nom_chron']."</option>";
							}
							
						echo "</select>";
						
					echo "</td>";
					
					echo "<td>&nbsp;</td>";
					
				echo "<tr>";

				//ligne vide dans le tableau
				echo "<tr><td colspan='8' class='lignevide'>&nbsp;</td></tr>";

				
				// Affichage dans le formulaire

				foreach ($chronique_array as $id => $data) 
				{
					if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
					else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
		
					echo "<tr ".$row_l." id='row_chron_".$id."'>";
							
						// Acronyme Chronique
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='chron_init_".$id."' value='".$data['init']."'>\n";
						echo "</td>";
						
						// Nom Chronique
						echo "<td>";
							echo "<input type='text' class='input_texte_300' name='chron_nom_".$id."' value='".$data['nom_chron']."'>\n";
						echo "</td>";
						
						// Type de données liée Hydro, Plu, Piezo
						echo "<td>";
						
							echo "<select name='chron_select_type_".$id."' id='chron_select_type_".$id."' style='width:150px;' onchange=''>";
											
								echo "<option value='0'>-</option>";
								
								$selected = '';		
								if(isset($eq_type_array))
								{
									foreach($eq_type_array as $key => $value)
									{
										if($data['id_eq_type'] == $key){$selected="selected";}	
										else{$selected = '';}											
										echo "<option value='".$key."' ".$selected." >".$value."</option>";
									}
								}
								
							echo "</select>";
							
						echo "</td>";

						// Axe lié
						echo "<td>";
						
							echo "<select name='chron_select_axe_".$id."' id='chron_select_axe_".$id."' style='width:150px;' onchange=''>";
											
								echo "<option value='0'>-</option>";
							
								$selected = '';		
								if(isset($data_type_axe_array))
								{
									foreach($data_type_axe_array as $key => $value)
									{
										if($data['axe_id'] == $key){$selected="selected";}	
										else{$selected = '';}											
										echo "<option value='".$key."' ".$selected." >".$value['axe']."</option>";
									}
								}

							echo "</select>";
							
						echo "</td>";

						// Unite
						echo "<td>";
							echo "<input type='text' class='input_texte_60' name='chron_chron_unite_".$id."' value='".$data['unite']."'>\n";
						echo "</td>"; 


						// Transformation période possible (DAY, MONTH, YEAR)
						echo "<td>";
						
							echo "<select name='chron_select_to_periode_".$id."' id='chron_select_to_periode_".$id."' style='width:100px;' onchange=''>";
											
								echo "<option value='0'>-</option>";
							
								$selected = '';		
								for($p=1;$p<=3;$p++)
								{
									if($data['to_periode'] == $p){$selected="selected";}	
									else{$selected = '';}											
									echo "<option value='".$p."' ".$selected." >by ".$periode_transf[$p]."</option>";
								}
								
							echo "</select>";
							
						echo "</td>";


						// Identification de la Chronique par transformation 
						echo "<td>";
						
							echo "<select name='chron_select_chron_periode_".$id."' id='chron_select_chron_periode_".$id."' style='width:200px;' onchange=''>";
											
								echo "<option value='0'>-</option>";
							
								$selected = '';		
								foreach($chronique_array as $key => $value)
								{
									if($data['id_chon_periode'] == $key){$selected="selected";}	
									else{$selected = '';}											
									echo "<option value='".$key."' ".$selected." >".$value['init']." - ".$value['nom_chron']."</option>";
								}
								
							echo "</select>";
							
						echo "</td>";
						
						// Supprimer
						echo "<td class='t_icon'>";
							echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:20px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"delete_typedata('".$id."');\">";
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

	// Fonction de lancement de la procédure AJAX permettant de supprimer une saisie de Chronique
	function delete_typedata(id_typedata)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_typedata: id_typedata
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/geographie/process_deltypedata.php", true);
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
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					document.getElementById('row_chron_'+id_typedata).style.display='none';
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
