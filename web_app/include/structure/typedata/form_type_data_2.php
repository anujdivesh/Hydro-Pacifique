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

		echo "<div class='table-container' style='float:left;height:70vh;'>";

			echo "<table id='table_tri' cellspacing='0' style=''>";
			
				echo "<thead>";
					echo "<tr class='header-row' style='background-color: #eef3f8;'>";
						echo "<th>".htmlaccent('Nom de l\'axe')."</th>";				
						echo "<th>".htmlaccent('Unité')."</th>";
						echo "<th>&nbsp;</td>";
					echo "</tr>";
				echo "</thead>";				
					
				// Nouvelle Entrée

				echo "<tr><td colspan='3' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter un axe')."</td></tr>\n";

				echo "<tr>";
				
					echo "<td><input type='text' class='input_texte' name='axe_0' style='border:2px solid #609966;'></td>";
					echo "<td><input type='text' class='input_texte_small' name='unite_0' style='border:2px solid #609966;'></td>";
					
					echo "<td>&nbsp;</td>";
					
				echo "<tr>";

				echo "<tr><td colspan='3' class='lignevide'>&nbsp;</td></tr>";
				
				foreach ($data_type_axe_array as $id => $data)
				{
					$axe = $data['axe'];
					$unite = htmlaccent(html_entity_decode($data['unite'] ?? $default_string));
					
					if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
					else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
		
					echo "<tr ".$row_l." >";
							
						// Nom Axe
						echo "<td class='t_cont_l' style='width:160px;'>";
							echo "<input type='text' class='input_texte' name='axe_".$id."' value='".$axe."'>\n";
						echo "</td>";
						
						// Unite
						echo "<td class='t_cont_s'>";
							echo "<input type='text' class='input_texte_small' style='' name='unite_".$id."' value='".$unite."'>\n";
						echo "</td>";
						
						// Lien pour suppression
						echo "<td class='t_cont_s' style='text-align:center;'>";
							$lien_suppr = "gestion_type_data.php?id_a=".$id;
							echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:20px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"confirm_suppr('".$lien_suppr."','l\'Axe','".$axe."');\">";
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
