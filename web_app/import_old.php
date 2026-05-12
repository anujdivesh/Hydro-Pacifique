<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
*/

require('include/application_top.php');

$message_info = '';
$row = 0;

$champ_dateinit_init=0;
$champ_datefirst_first=0;
$champ_dateend_end=0;

$today_day_import = date('d'); 
$today_month_import = date('m'); 
$today_year_import = date('Y'); 

$region_modif = $region_default;
$first_station=1;



if(isset($_POST['button_stats'])){require(DIR_WS_FORMULAIRE . 'import_data.php');}


if(isset($_GET['imp']) && tep_not_null($_GET['imp']))
{
	$first_station=post_secure($sql_link,$_GET['imp']);
	$region_modif_query = tep_db_query($sql_link,"SELECT DISTINCT r.id, r.nom_region FROM ".TABLE_REGION. " r, ".TABLE_STATION." s WHERE r.id=s.id_region AND s.id=".$first_station);
	$region_modif_tab = tep_db_fetch_array($region_modif_query);
	$region_modif = $region_modif_tab['id'];
}


require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

echo "<div id='contour_general'>";

	echo "<div id='contenu_centre'>";
		
		echo "<div id='contenu_box2'>";
		
			echo "<h1><span>".htmlaccent('Importation des données de mesure')."</span></h1>"; 	
				
			
			echo "<div id='onglet_contenu' class='first'>\n";
			
				echo "<div id='box_result' style='padding:10px 0;'>\n";
				
					echo "<form name='importation' action='import.php' method='post' enctype='multipart/form-data'  onSubmit='return verif_erase_data();'>";
					echo "<input type='hidden' name='id_user_f' id='id_user_f' value='".$id_user."'>";
						
						echo "<table id='stats_select' cellspacing='0' >";
					
						echo "<tr>";
							
							echo "<td class='bold'>".htmlaccent('Emplacement')."</td>";
							
							// Ile
							echo "<td>";
								
								$sql_regions = "SELECT DISTINCT r.id, r.nom_region FROM ".TABLE_REGION." r, ".TABLE_STATION." s WHERE r.id=s.id_region";
								$regions_query = tep_db_query($sql_link,$sql_regions);
																
								echo "<select name='select_region' id='select_region' onchange=\"document.getElementById('button_stats').style.display = 'none';import_select_region_ajax();\">";
								
								//echo "<select name='select_region' id='select_region' onchange='import_select_region_ajax();'>";
									//echo "<option value='-1'>".htmlaccent('Choisir une île')."</option>";
									
									while($regions = tep_db_fetch_array($regions_query))
									{		
										$selected = '';					
										if($regions['id'] == $region_modif){$selected = 'selected';}
										//else{if($regions['id'] == $region_default){$selected = 'selected';}}
										
										echo "<option value='".$regions['id']."' ".$selected.">".htmlaccent($regions['nom_region'])."</option>";
									}
									
								echo "</select>";
								echo "<p>".htmlaccent('Ile / Province')."</p>";
							echo "</td>";
							
							//Station
							echo "<td id='station'>";
								
								$w = 0;
								//$first_station=1;
								//$region_default = 39;
								$sql_stations = "SELECT * FROM ".TABLE_STATION." WHERE id_region=".$region_modif." ORDER BY nom_station";
								$stations_query = tep_db_query($sql_link,$sql_stations);
								
								//echo "<select name='select_station' id='select_station' onchange='import_select_station_ajax();'>";
								echo "<select name='select_station' id='select_station' onchange=\"document.getElementById('button_stats').style.display = 'none';import_select_station_ajax();\">";
									while($stations = tep_db_fetch_array($stations_query))
									{		
										$selected = '';					
										if($w==0){$first_station=$stations['id'];}
										if(isset($_GET['imp']) && $_GET['imp']==$stations['id']){$first_station=$stations['id'];$selected = 'selected';}
										echo $first_station;
										echo "<option value='".$stations['id']."' ".$selected.">".htmlaccent($stations['nom_station'])."</option>";
										$w++;
									}					
								echo "</select>";
								echo "<p>".htmlaccent('Station de mesure')."</p>";
							echo "</td>";
							
							
							//equipement / matériel
							echo "<td id='materiel'>";
								
								$sql_equipements = "SELECT * FROM ".TABLE_STATION_TO_EQUIPEMENT." ste, ".TABLE_EQUIPEMENT." e WHERE ste.id_station=".$first_station." AND ste.id_eq=e.id  ORDER BY designation";
								$equipements_query = tep_db_query($sql_link,$sql_equipements);
								
								echo "<select name='select_materiel' id='select_materiel'  onchange='import_select_date_ini_ajax();'>";
									$cdf=0;
									$ext='';
									while($equipements = tep_db_fetch_array($equipements_query))
									{		
										$champ_dateinit = $equipements['champ_datefirst'];
										$champ_dateend = $equipements['champ_dateend'];
										if($cdf==0 || (isset($_GET['eq']) && $_GET['eq']==$equipements['id'])){$champ_dateinit_init = $champ_dateinit;$champ_dateend_end = $champ_dateend;$ext=$equipements['ext_file'];}
										$cdf++;
											
										$selected = '';	
										if(isset($_GET['eq']) && $_GET['eq']==$equipements['id']){$selected = 'selected';$ext=$equipements['ext_file'];}				
										echo "<option value='".$equipements['id']."' ".$selected.">".htmlaccent($equipements['designation'])."</option>";
										
									}					
								echo "</select>";
								echo "<p>".htmlaccent('Appareil de mesure')."</p>";
							echo "</td>";
							
						echo "</tr>";
						
						
						echo "<tr>";
							
							echo "<td class='bold'>".htmlaccent('Date d\'importation')."</td>";
							
							// date importation
							echo "<td colspan='2'>";
								
								//echo "<div style='height:21px;'><input class='input_date' name='date_mesure' id='date_mesure' value='".$today."' type='text' onFocus='this.blur()' onclick=\"javascript:displayCalendar(document.forms[0].date_mesure,'dd-mm-yyyy',this);\" ></div>";	
								//echo "<p style='width:100%;'>".htmlaccent('Date de l\'importation')."</p>";	
								
								echo "<div style='float:left;'>";
									echo "<select name='day_import' id='day_import' style='width:50px;'>";
										for($a=1;$a<=31;$a++)
										{
											$selected='';if($a==$today_day_import){$selected='selected';}
											$aa=$a;if($a<10){$aa='0'.$a;}
											echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										}
									echo "</select>";
	
									echo "<p>".htmlaccent('Jour')."</p>";
								echo "</div>";
								
								
								echo "<div style='float:left;'>";
									echo select_mois('month_import',$today_month_import,1);
									echo "<p>".htmlaccent('Mois')."</p>";
								echo "</div>";
								
								echo "<div style='float:left;'>\n";	
									echo "<select name='year_import' id='year_import' style='width:60px;'>";
										
										$year_first = $today_year_import -30;
										$year_end = $today_year_import +30;
										
										for($a=$year_first;$a<=$year_end;$a++)
										{
											$selected='';if($a==$today_year_import){$selected='selected';}
											echo "<option value='".$a."' ".$selected." >".$a."</option>";
										}
									echo "</select>";
									echo "<p>".htmlaccent('Année')."</p>";	
								echo "</div>\n";
							
							
							
							
							echo "</td>";
							
							echo "<td>&nbsp;</td>";
							
						echo "</tr>";
						
						
						
						
						echo "<tr>";
							
							echo "<td class='bold'>";
								echo htmlaccent('Fichier de données');
								echo "<br><input type='text' id='extension' class='text_info' value='".$ext."' onFocus='this.blur()' >";
							echo "</td>";
							
							// Fichier d'import
							echo "<td colspan='3' >";
								
								echo "<input type='file' class='file' id='file_data_import' name='file_data_import' style='float:left;'	 onchange=\"chemin_file_split();\">";	
								echo "<input type='text' id='file_info' class='text_info'  onFocus='this.blur()' style='float:left;width:45%;'>";
								echo "<hr>";
							
							echo "</td>";
							
						
						echo "</tr>";
						
						/* 
						//Remplacement de données 
						echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>";
						
						echo "<tr>";
							
							echo "<td class='bold'>".htmlaccent('Certaines données ont déjà été importées')."</td>";
							
							// Fichier d'import
							echo "<td colspan='3' >";
								
								echo "<input type='checkbox' name='replace_data' id='replace_data' >";
							
							echo "</td>";
							
						
						echo "</tr>";
						*/
						
					echo "</table>";
					
					echo "<hr><br><br>";
					
					$display="style='display:none;'";
					if($champ_dateinit_init == 1){$display="style='display:block;'";}
					
					echo "<div id='date_ini'  ".$display.">";
					
						echo "<table id='stats_select' cellspacing='0' >";	
								
								
						  echo "<tr id='champ_dateinit'>";
						  
							  echo "<td class='bold'><span  style='background-color: #ffe697;'>".htmlaccent('Date d\'initialisation')."</span></td>";
							  
							  // date importation
							  echo "<td colspan='2' >";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='day_init' id='day_init' style='width:50px;'>";
										  for($a=1;$a<=31;$a++)
										  {
											  $selected='';if($a==$today_day_import){$selected='selected';}
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
	  
									  echo "<p>".htmlaccent('Jour')."</p>";
								  echo "</div>";
								  
								  
								  echo "<div style='float:left;'>";
									  echo select_mois('month_init',$today_month_import,1);
									  echo "<p>".htmlaccent('Mois')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>\n";	
									  echo "<select name='year_init' id='year_init' style='width:60px;'>";
										  
										  $year_first = $today_year_import -70;
										  $year_end = $today_year_import +10;
										  
										  for($a=$year_first;$a<=$year_end;$a++)
										  {
											  $selected='';if($a==$today_year_import){$selected='selected';}
											  echo "<option value='".$a."' ".$selected." >".$a."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Année')."</p>";	
								  echo "</div>\n";
								  
							  
							  echo "</td>";
						  
						  
							  echo "<td>";
						  
								  echo "<div style='float:left;'>";
									  echo "<select name='heure_init' id='heure_init' style='width:50px;'>";
										  for($a=0;$a<=23;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$h_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Heures')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='min_init' id='min_init' style='width:50px;'>";
										  for($a=0;$a<=59;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$min_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Minutes')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='sec_init' id='sec_init' style='width:50px;'>";
										  for($a=0;$a<=59;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$sec_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Secondes')."</p>";
								  echo "</div>";
						  
						  
							  echo "</td>";
						  
						  
						  echo "</tr>";
						  
					  
					  echo "</table>";
							
					echo "</div>\n";	
					
					
					// date de fin d'importation des données
					
					$display="style='display:none;'";
					if($champ_dateend_end == 1){$display="style='display:block;'";}
					
					echo "<div id='date_end' ".$display.">";
					
						
						echo "<table id='stats_select' cellspacing='0' >";	
								
								
						  echo "<tr id='champ_datefirst'>";
						  
							  echo "<td class='bold'><span  style='background-color: #ffe697;'>".htmlaccent('Début de période')."</span></td>";
							  
							  // date importation
							  echo "<td colspan='2' >";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='day_first' id='day_first' style='width:50px;'>";
										  for($a=1;$a<=31;$a++)
										  {
											  $selected='';if($a==1){$selected='selected';}
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
	  
									  echo "<p>".htmlaccent('Jour')."</p>";
								  echo "</div>";
								  
								  
								  echo "<div style='float:left;'>";
									  echo select_mois('month_first',1,1);
									  echo "<p>".htmlaccent('Mois')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>\n";	
									  echo "<select name='year_first_export' id='year_first_export' style='width:60px;'>";
										  
										  $year_first = $today_year_import -70;
										  $year_end = $today_year_import +10;
										  
										  for($a=$year_first;$a<=$year_end;$a++)
										  {
											  $selected='';if($a==$year_first){$selected='selected';}
											  echo "<option value='".$a."' ".$selected." >".$a."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Année')."</p>";	
								  echo "</div>\n";
								  
							  
							  echo "</td>";
						  
						  
							  echo "<td>";
						  
								  echo "<div style='float:left;'>";
									  echo "<select name='heure_first' id='heure_first' style='width:50px;'>";
										  for($a=0;$a<=23;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$h_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Heures')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='min_first' id='min_first' style='width:50px;'>";
										  for($a=0;$a<=59;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$min_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Minutes')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='sec_first' id='sec_first' style='width:50px;'>";
										  for($a=0;$a<=59;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$sec_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Secondes')."</p>";
								  echo "</div>";
						  
						  
							  echo "</td>";
						  
						  
						  echo "</tr>";
						  
					  
					  echo "</table>";
						
						
						
						
						echo "<table id='stats_select' cellspacing='0' >";	
								
								
						  echo "<tr id='champ_dateend'>";
						  
							  echo "<td class='bold'><span  style='background-color: #ffe697;'>".htmlaccent('Fin de période')."</span></td>";
							  
							  // date importation
							  echo "<td colspan='2' >";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='day_end' id='day_end' style='width:50px;'>";
										  for($a=1;$a<=31;$a++)
										  {
											  $selected='';if($a==$today_day_import){$selected='selected';}
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
	  
									  echo "<p>".htmlaccent('Jour')."</p>";
								  echo "</div>";
								  
								  
								  echo "<div style='float:left;'>";
									  echo select_mois('month_end',$today_month_import,1);
									  echo "<p>".htmlaccent('Mois')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>\n";	
									  echo "<select name='year_end_export' id='year_end_export' style='width:60px;'>";
										  
										  $year_first = $today_year_import -50;
										  $year_end = $today_year_import +10;
										  
										  for($a=$year_first;$a<=$year_end;$a++)
										  {
											  $selected='';if($a==$today_year_import){$selected='selected';}
											  echo "<option value='".$a."' ".$selected." >".$a."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Année')."</p>";	
								  echo "</div>\n";
								  
							  
							  echo "</td>";
						  
						  
							  echo "<td>";
						  
								  echo "<div style='float:left;'>";
									  echo "<select name='heure_end' id='heure_end' style='width:50px;'>";
										  for($a=0;$a<=23;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$h_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Heures')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='min_end' id='min_end' style='width:50px;'>";
										  for($a=0;$a<=59;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$min_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Minutes')."</p>";
								  echo "</div>";
								  
								  echo "<div style='float:left;'>";
									  echo "<select name='sec_end' id='sec_end' style='width:50px;'>";
										  for($a=0;$a<=59;$a++)
										  {
											  $aa=$a;if($a<10){$aa='0'.$a;}
											  //$selected='';if($aa==$sec_first){$selected='selected';}
											  echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										  }
									  echo "</select>";
									  echo "<p>".htmlaccent('Secondes')."</p>";
								  echo "</div>";
						  
						  
							  echo "</td>";
						  
						  
						  echo "</tr>";
						  
					  
					  echo "</table>";
							
					echo "</div>\n";	
					
					
					
			echo "<hr>\n";
			echo "</div>\n";	
					
			echo "<hr>";
			
			//messages
			if(tep_not_null($message_info)){echo "<div id='contenu_info' style='text-align:left;margin-left:20px;'>".$message_info."</div><hr>";}
				
				
			//Boutton	
							
			echo "<div id='boite1' style='margin-top:20px;margin-left:0px;'>\n";
			
				echo "<input type='submit' class='button' name='button_stats' id='button_stats' value=\"Importer\" />";
				
			echo "<hr>\n";
			echo "</div>\n";
		
			echo "</form >\n";
				
				
				
			
			echo "<hr>\n";
			echo "</div>\n";
			
			echo "<hr>";
					
			echo "<div id='box_graph' class='lien' >\n";
					echo "<div class='cadre_lien'><a href='import_list.php'><img src='".DIR_WS_IMG_ICO."list.png' >".htmlaccent('Listing des importations réalisées')."</a></div>";
			echo "</div>";
			
			
		echo "<hr>\n";
		echo "</div>\n";
	
	
	echo "<hr>";
	echo "</div>";
	
	//require(DIR_WS_BOX . 'box_news.php');
	
	echo "<hr>";
echo "</div>";
	require('include/application_bottom.php'); 
echo "</body>";

echo "</html>";

?>

