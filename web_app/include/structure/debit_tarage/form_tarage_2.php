<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/


$row = 0;
$nb_data = 0;

echo "<div id='onglet_contenu'>\n";

	
	echo "<div id='boite1' class='first' >\n";
		
		echo "<span  style='font-size:18px;font-weight:bold;background-color: #ffe697;'>".htmlaccent('Chargement de données')."</span><hr>";
				
		echo "<div id='boite_small' style='width:340px;'>\n";
			
			echo "<h2>".htmlaccent('Fichier de données (.csv)')."</h2>\n";
				
			echo "<input type='file' class='file' id='file_data_import' name='file_data_import' style='float:left;'	 onchange=\"chemin_file_split();\">";	
			echo "<input type='text' id='file_info' class='text_info'  onFocus='this.blur()' style='float:left;width:90%;'>";
						
		echo "</div>\n";
		
		
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Format des données')."</h2>\n";
				
			$sql_eq = "SELECT * FROM ".TABLE_EQUIPEMENT." WHERE type_eq=3 ORDER BY designation DESC";
			$eq_query = tep_db_query($sql_link,$sql_eq);
			
			echo "<select name='select_eq' id='select_eq' >";
				while($eq = tep_db_fetch_array($eq_query))
				{		
					echo "<option value='".$eq['id']."' >".htmlaccent($eq['designation'])."</option>";
					//$w++;
				}				

			echo "</select>";

		echo "</div>\n";
		
		//echo "<hr>";
		
		echo "<div id='boite_small'>\n";
		
			echo "<table>";
			
				echo "<tr>";
					echo "<td>";
						echo "<input type='checkbox' name='no_firstline' id='no_firstline' >";
					echo "</td>";
					echo "<td>".htmlaccent('Ne pas prendre en compte la première ligne')."</td>";
				echo "</tr>";
				
			echo "</table>";
		
		echo "</div>";	
		
		echo "<div id='boite_small' style='float:right;'>\n";
		
			echo "<table style='float:right;width:150px;border: 2px solid #000;background-color:#FBFBFB;padding:10px 0;text-align:center;'>";
			
				echo "<tr>";
					echo "<td>";
						$lien_suppr = "debits_tarage_data.php?st=".$select_station;
						echo "<a onClick=\"document.getElementById('block_new_debit').style.display='block';\">";
							echo "<span style='color:#000;margin:0;font-size:12px;font-weight:bold;'>".htmlaccent('Saisie Manuelle')."</span>";
						echo "</a>";
					echo "</td>";
				echo "</tr>";
				
			echo "</table>";
		
		echo "</div>";		
		
		
	echo "<hr>\n";
	echo "</div>\n";
	
	
	echo "<div style='width:98%;border-bottom:1px solid #eef2f6;margin: 10px 15px;'></div>\n";	
		
		
	echo "<table id='table_tri' cellspacing='0' style='width:98%;margin-top:25px;margin-left:15px;'>";
	
		echo "<thead>";
			echo "<tr>";
				
				echo "<th style='width:130px;'>".htmlaccent('Date')."</th>";
				echo "<th style='text-align:center;'>".htmlaccent('Hauteur <br> moyenne <br> (en cm)')."</th>";								
				echo "<th style='text-align:center;'>".htmlaccent('Debit <br> (en m<sup>3</sup>/s)')."</th>";
				echo "<th style='text-align:center;'>".htmlaccent('Vitesse <br> moyenne <br> (en m/s)')."</th>";
				echo "<th style='text-align:center;'>".htmlaccent('Vitesse <br> surface <br> (en m/s)')."</th>";
				echo "<th style='text-align:center;'>".htmlaccent('Section <br> (en m<sup>2</sup>)')."</th>";
				echo "<th style='text-align:center;'>".htmlaccent('Profondeur <br> moyenne <br> (en m/s)')."</th>";
				echo "<th style='text-align:center;'>".htmlaccent('Source <br> des données')."</th>";
				echo "<th style='text-align:center;'>".htmlaccent('Agents')."</th>";
				echo "<th style='text-align:center;'>";
					$lien_suppr = "debits_tarage_data.php?st=".$select_station;
					echo "<input type='submit' class='b_del' name='b_del' value='' title='".htmlaccent('Supprimer')."' />";
					//echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:16px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"confirm_suppr('".$lien_suppr."','toutes les données de débit de la station ','".$station['nom_station']."');	\">";
				echo "</th>";
				
			echo "</tr>";	 
	
		echo "</thead>";
	
	
		//ligne vide dans le tableau		
		echo "<tr>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td>";
				echo "<a href='#' onclick='check_all(".$first_id_data_load.",".$nb_data_load.");'>".htmlaccent('Tous')."</a> / <a href='#' onclick='check_none(".$first_id_data_load.",".$nb_data_load.");'>".htmlaccent('Aucun')."</a>";
			echo "</td>";
			//echo "<td>&nbsp;</td>";
		echo "</tr>";
	
	
		if(isset($jaugeage_array))
		{
			
			if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
			else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
			
			
			for($j=0;$j<sizeof($jaugeage_array);$j++)
			{			
				echo "<tr ".$row_l." >";
					
					echo "<td>".$jaugeage_array[$j]['date_jaugeage'].' '.$jaugeage_array[$j]['heure_debut_jaugeage']."</td>";
					//echo "<td><p style='width:90%;'>".$jaugeage_array[$j]['date_jaugeage'].' '.$jaugeage_array[$j]['heure_debut_jaugeage']."</td>";
					echo "<td style='text-align:center;'>".$jaugeage_array[$j]['hauteur_mean']."</td>";
					echo "<td style='text-align:center;'>".$jaugeage_array[$j]['debit']."</td>";
					echo "<td style='text-align:center;'>".$jaugeage_array[$j]['v_mean']."</td>";
					echo "<td style='text-align:center;'>".$jaugeage_array[$j]['v_surf']."</td>";
					echo "<td style='text-align:center;'>".$jaugeage_array[$j]['section']."</td>";
					echo "<td style='text-align:center;'>".$jaugeage_array[$j]['prof_mean']."</td>";
					echo "<td style='text-align:center;'>".$jaugeage_array[$j]['source_data']."</td>";
					echo "<td style='text-align:center;'>".$jaugeage_array[$j]['agents']."</td>";
					
					// checked
					echo "<td style='width:80px;text-align:center;'>";
						echo "<input type='checkbox' name='check_del_".$jaugeage_array[$j]['id_jaugeage']."' id='check_del_".$jaugeage_array[$j]['id_jaugeage']."'>";
					echo "</td>\n";
					
					// supprimer
					/*
					echo "<td class='t_cont_s' style='text-align:right;'>";
						$lien_suppr = "debits_tarage_data.php?st=".$select_station."&del=".$jaugeage_array[$j]['id_jaugeage'];
						echo "<img src='".DIR_WS_IMG_ICO."delete.png' style='width:16px;cursor:pointer;' title='".htmlaccent('Supprimer')."' onClick=\"confirm_suppr('".$lien_suppr."','la mesure du ','".$jaugeage_array[$j]['date_jaugeage']."');\">";
					echo "</td>\n";
					*/
				echo "</tr>";
				
				//$nb_data++;
			}
			
			
		}
		
	echo "</table>";
	
	
	
	
	
					
echo "<hr>\n";
echo "</div>\n";
?>
