<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
$nb_data = 1;

echo "<div id='onglet_contenu'>\n";

	//Station
	echo "<div id='boite1' class='first'>\n";
				
		echo "<div id='boite_small'>\n";		
		
			echo "<h2>".htmlaccent('Ile / Région')."</h2>\n";
				
			if($modif)
			{
				$sql_regions = "SELECT DISTINCT i.id, i.nom_region FROM ".TABLE_REGION. " i, ".TABLE_STATION." s WHERE i.id=s.region_station AND s.id=".$select_station;
				$regions_query = tep_db_query($sql_link,$sql_regions);
				$regions = tep_db_fetch_array($regions_query);
				
				echo "<input type='hidden' id='select_region' name='select_region' value='".$regions['id']."'>";
				echo "<p style='font-size:16px;'>".htmlaccent($regions['nom_region'])."</p>";
			}
			else
			{
			
				echo "<select name='select_region' id='select_region' onchange='import_select_region_ajax();'>";
					//echo "<option value='-1'>".htmlaccent('Choisir une île')."</option>";
					
					$sql_regions = "SELECT DISTINCT i.id, i.nom_region FROM ".TABLE_REGION. " i, ".TABLE_STATION." s, ".TABLE_STATION_TO_EQUIPEMENT." ste, ".TABLE_EQUIPEMENT." e  WHERE i.id=s.region_station AND s.id=ste.id_station AND ste.id_eq=e.id AND e.type_eq=2";
					$regions_query = tep_db_query($sql_link,$sql_regions);
					$region_default = 39;
					
					while($regions = tep_db_fetch_array($regions_query))
					{		
						$selected = '';					
						if($regions['id'] == $region_default){$selected = 'selected';}
						echo "<option value='".$regions['id']."' ".$selected.">".htmlaccent($regions['nom_region'])."</option>";
					}
					
				echo "</select>";
			}
			
		echo "</div>\n";				
		
					
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Station de mesure')."</h2>\n";
			
			if($modif)
			{
				$sql_stations = "SELECT * FROM ".TABLE_STATION." WHERE id=".$select_station;
				$stations_query = tep_db_query($sql_link,$sql_stations);
				$stations = tep_db_fetch_array($stations_query);
				
				echo "<input type='hidden' id='select_station' name='select_station' value='".$select_station."'>";
				echo "<p style='font-size:16px;'>".htmlaccent($stations['nom_station'])."</p>";
			}
			else
			{	
				$sql_stations = "SELECT s.id as id_station, s.nom_station FROM ".TABLE_STATION." s, ".TABLE_STATION_TO_EQUIPEMENT." ste, ".TABLE_EQUIPEMENT." e WHERE s.region_station=".$region_default." AND s.id=ste.id_station AND ste.id_eq=e.id AND e.type_eq=2 AND NOT EXISTS (SELECT * FROM ".TABLE_DATA_TARAGE." t WHERE s.id=t.id_station) GROUP BY nom_station ORDER BY nom_station";
				$stations_query = tep_db_query($sql_link,$sql_stations);
				
				
				echo "<select name='select_station' id='select_station' >";
				
					while($stations = tep_db_fetch_array($stations_query))
					{		
						echo "<option value='".$stations['id_station']."' >".htmlaccent($stations['nom_station'])."</option>";
						//$w++;
					}				
	
				echo "</select>";
				
			}

		echo "</div>\n";	
		
					
		echo "<div id='boite_small' style='float:right;'>\n";
			
			echo "<p style='font-size:16px;color: #d14836;'>".htmlaccent('- Attention l\'élaboration des équations de la courbe de tarage doit faire l\'objet d\'une étude hydrologique spécifique -')."</p>";

		echo "</div>\n";			
				
	
	echo "<hr>\n";
	echo "</div>\n";
	
	
	echo "<div style='width:100%;border-bottom:1px solid #eef2f6;'></div>\n";
		
	//---------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------
	
	echo "<div id='boite1' class='first'>\n";
				
		
		echo "<table id='table_tri' cellspacing='0' style='width:98%;margin-top:15px;margin-left:0;' >";
		
				echo "<thead>";
					echo "<tr>";
						
						echo "<th>".htmlaccent('Equations <br> Format PHP')."</th>";
						echo "<th>".htmlaccent('Equations <br> Format SQL')."</th>";								
						echo "<th>".htmlaccent('Hauteur d\'eau <br> Min (cm)')."</th>";
						echo "<th>".htmlaccent('Hauteur d\'eau <br> Max (cm)')."</th>";
						echo "<th>".htmlaccent('Début de la période de validité')."</th>";
						echo "<th>".htmlaccent('Fin de la période de validité')."</th>";
						echo "<th>&nbsp;</th>";
						
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
					
				echo "</tr>";
				
				//print_r($tarage_array);
				if(isset($tarage_array))
				{						
					for($j=1;$j<=sizeof($tarage_array);$j++)
					{		
						echo "<tr>";
							echo "<td style='width:20%;'>".tep_draw_input_field('equation_'.$j,$tarage_array[$j-1]['equation'],'style=\'width:95%;margin-bottom:50px;\'')."</td>";
							echo "<td style='width:20%;'>".tep_draw_input_field('equation_sql_'.$j,$tarage_array[$j-1]['equation_sql'],'style=\'width:95%;margin-bottom:50px;\'')."</td>";
							echo "<td style='width:8%;'>".tep_draw_input_field('hauteur_min_eq_'.$j,$tarage_array[$j-1]['hauteur_min_eq'],'style=\'width:60px;margin-bottom:50px;\'')."</td>";
							echo "<td style='width:8%;'>".tep_draw_input_field('hauteur_max_eq_'.$j,$tarage_array[$j-1]['hauteur_max_eq'],'style=\'width:60px;margin-bottom:50px;\'')."</td>";
							
							// DEBUT DE LA PERIODE DE VALIDITE
							echo "<td style='width:20%;height:20px;'>";
								
								// JOUR
								echo "<div style='float:left;'>";
								
									echo "<select name='day_first_eq_".$j."' id='day_first_eq_".$j."' style='width:50px;'>";
										for($a=1;$a<=31;$a++)
										{
											//$selected='';if($a==$today_day_import){$selected='selected';}
											$selected='';if($a==$tarage_array[$j-1]['day_first_eq']){$selected='selected';}
											//if($selected=='')if($a==$today_day_import){$selected='selected';}
											$aa=$a;if($a<10){$aa='0'.$a;}
											echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										}
									echo "</select>";
				
									echo "<p>".htmlaccent('Jour')."</p>";
								echo "</div>";
								
								// MOIS
								echo "<div style='float:left;'>";
									echo select_mois('month_first_eq_'.$j,$tarage_array[$j-1]['month_first_eq'],1);
									echo "<p>".htmlaccent('Mois')."</p>";
								echo "</div>";
								
								
								// ANNEE
								echo "<div style='float:left;'>\n";	
									echo "<select name='year_first_eq_".$j."' id='year_first_eq_".$j."' style='width:60px;'>";
										
										$year_first = $today_year_import - 30;
										$year_end = $today_year_import + 30;
										
										for($a=$year_first;$a<=$year_end;$a++)
										{
											$selected='';if($a==$tarage_array[$j-1]['year_first_eq']){$selected='selected';}
											echo "<option value='".$a."' ".$selected." >".$a."</option>";
										}
									echo "</select>";
									echo "<p>".htmlaccent('Année')."</p>";	
								echo "</div>\n";
								
							echo "</td>";					
							
							
							
							// FIN DE LA PERIODE DE VALIDITE
							echo "<td style='width:20%;height:20px;'>";
								
								// JOUR
								echo "<div style='float:left;'>";
								
									echo "<select name='day_end_eq_".$j."' id='day_end_eq_".$j."' style='width:50px;'>";
										for($a=1;$a<=31;$a++)
										{
											//$selected='';if($a==$today_day_import){$selected='selected';}
											$selected='';if($a==$tarage_array[$j-1]['day_end_eq']){$selected='selected';}
											if($selected=='')if($a==$today_day_import){$selected='selected';}
											$aa=$a;if($a<10){$aa='0'.$a;}
											echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
										}
									echo "</select>";
				
									echo "<p>".htmlaccent('Jour')."</p>";
								echo "</div>";
								
								// MOIS
								echo "<div style='float:left;'>";
									echo select_mois('month_end_eq_'.$j,$tarage_array[$j-1]['month_end_eq'],1);
									echo "<p>".htmlaccent('Mois')."</p>";
								echo "</div>";
								
								
								// ANNEE
								echo "<div style='float:left;'>\n";	
									echo "<select name='year_end_eq_".$j."' id='year_end_eq_".$j."' style='width:60px;'>";
										
										$year_first = $tarage_array[$j-1]['year_first_eq'] - 30;
										$year_end = $tarage_array[$j-1]['year_end_eq'] + 30;
										
										for($a=$year_first;$a<=$year_end;$a++)
										{
											$selected='';if($a==$tarage_array[$j-1]['year_end_eq']){$selected='selected';}
											echo "<option value='".$a."' ".$selected." >".$a."</option>";
										}
									echo "</select>";
									echo "<p>".htmlaccent('Année')."</p>";	
								echo "</div>\n";
								
							echo "</td>";	
							
							// CROSS PLUS
							if($j==sizeof($tarage_array))
							{
								echo "<td style='width:1%;'>";
								
									echo "<img src='".DIR_WS_IMG_ICO."plus_cross.png' id='img_plus_".$nb_data."' style='width:15px;margin-bottom:50px;cursor:pointer;' title='Plus de lignes' onClick=\"document.getElementById('ligne_visible_".($nb_data+1)."').style.display = 'block';document.getElementById('img_plus_".$nb_data."').style.display = 'none';\">";
								
								echo "</td>";
							}
							else
							{echo "<td style='width:1%;'>&nbsp;</td>";}
														
						echo "</tr>";
						
						$nb_data++;
					}
					
					echo "</table>";
				  }
				  else
				  {			
						// LIGNE VIDE
						echo "<tr>";
								echo "<td style='width:20%;'>".tep_draw_input_field('equation_'.$nb_data,'','style=\'width:95%;margin-bottom:50px;\'')."</td>";
								echo "<td style='width:20%;'>".tep_draw_input_field('equation_sql_'.$nb_data,'','style=\'width:92%;margin-bottom:50px;\'')."</td>";
								echo "<td style='width:8%;'>".tep_draw_input_field('hauteur_min_eq_'.$nb_data,'','style=\'width:60px;margin-bottom:50px;\'')."</td>";
								echo "<td style='width:8%;'>".tep_draw_input_field('hauteur_max_eq_'.$nb_data,'','style=\'width:60px;margin-bottom:50px;\'')."</td>";
								
								// DEBUT DE LA PERIODE DE VALIDITE
								echo "<td style='width:20%;height:20px;'>";
									
									// JOUR
									echo "<div style='float:left;'>";
									
										echo "<select name='day_first_eq_".$nb_data."' id='day_first_eq_".$nb_data."' style='width:50px;'>";
											for($a=1;$a<=31;$a++)
											{
												$selected='';if($a==1){$selected='selected';}
												$aa=$a;if($a<10){$aa='0'.$a;}
												echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
											}
										echo "</select>";
					
										echo "<p>".htmlaccent('Jour')."</p>";
									echo "</div>";
									
									// MOIS
									echo "<div style='float:left;'>";
										echo select_mois('month_first_eq_'.$nb_data,1,1);
										echo "<p>".htmlaccent('Mois')."</p>";
									echo "</div>";
									
									
									// ANNEE
									echo "<div style='float:left;'>\n";	
										echo "<select name='year_first_eq_".$nb_data."' id='year_first_eq_".$nb_data."' style='width:60px;'>";
											
										
											/*
											$year_first = $today_year_import - 30;
											$year_end = $today_year_import + 30;
											*/
											
											for($a=$year_first_all;$a<=$year_end_all;$a++)
											{
												$selected='';if($a==$year_first_all){$selected='selected';}
												echo "<option value='".$a."' ".$selected." >".$a."</option>";
											}
										echo "</select>";
										echo "<p>".htmlaccent('Année')."</p>";	
									echo "</div>\n";
									
									
								echo "</td>";					
								
								
								
								// FIN DE LA PERIODE DE VALIDITE
								echo "<td style='width:20%;height:20px;'>";
									
									// JOUR
									echo "<div style='float:left;'>";
									
										echo "<select name='day_end_eq_".$nb_data."' id='day_end_eq_".$nb_data."' style='width:50px;'>";
											for($a=1;$a<=31;$a++)
											{
												if($selected=='')if($a==$today_day_import){$selected='selected';}
												$aa=$a;if($a<10){$aa='0'.$a;}
												echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
											}
										echo "</select>";
					
										echo "<p>".htmlaccent('Jour')."</p>";
									echo "</div>";
									
									// MOIS
									echo "<div style='float:left;'>";
										echo select_mois('month_end_eq_'.$nb_data,12,1);
										echo "<p>".htmlaccent('Mois')."</p>";
									echo "</div>";
									
									
									// ANNEE
									echo "<div style='float:left;'>\n";	
										echo "<select name='year_end_eq_".$nb_data."' id='year_end_eq_".$nb_data."' style='width:60px;'>";
																					
											/*
											$year_first = $today_year_import - 30;
											$year_end = $today_year_import + 30;
											*/
										
											for($a=$year_first_all;$a<=$year_end_all;$a++)
											{
												$selected='';if($a==$year_end_all){$selected='selected';}
												echo "<option value='".$a."' ".$selected." >".$a."</option>";
											}
										echo "</select>";
										echo "<p>".htmlaccent('Année')."</p>";	
									echo "</div>\n";
									
								echo "</td>";	
								
								
								echo "<td style='width:1%;'>";
								
									echo "<img src='".DIR_WS_IMG_ICO."plus_cross.png' id='img_plus_".$nb_data."' style='width:15px;margin-bottom:50px;cursor:pointer;' title='Plus de lignes' onClick=\"document.getElementById('ligne_visible_".($nb_data+1)."').style.display = 'block';document.getElementById('img_plus_".$nb_data."').style.display = 'none';\">";
								
								echo "</td>";
								
							echo "</tr>";
							
						
						echo "</table>";
						
						$nb_data++;
					}
					
					// LIGNE VIDE
					
					$nb_data_temp = $nb_data;	
					for($i=$nb_data_temp;$i<($nb_data_temp+11);$i++)
					{	
						  echo "<div id='ligne_visible_".$i."' style='display:none;'>";
			  
							  echo "<table id='table_tri'  cellspacing='0' style='width:98%;margin-top:15px;margin-left:0;'>";
							
							
								echo "<tr>";
								
										echo "<td style='width:20%;'>".tep_draw_input_field('equation_'.$i,'','style=\'width:95%;margin-bottom:50px;\'')."</td>";
										echo "<td style='width:20%;'>".tep_draw_input_field('equation_sql_'.$i,'','style=\'width:95%;margin-bottom:50px;\'')."</td>";
										echo "<td style='width:8%;'>".tep_draw_input_field('hauteur_min_eq_'.$i,'','style=\'width:60px;margin-bottom:50px;\'')."</td>";
										echo "<td style='width:8%;'>".tep_draw_input_field('hauteur_max_eq_'.$i,'','style=\'width:60px;margin-bottom:50px;\'')."</td>";
										
										// DEBUT DE LA PERIODE DE VALIDITE
										echo "<td style='width:20%;height:20px;'>";
											
											// JOUR
											echo "<div style='float:left;'>";
											
												echo "<select name='day_first_eq_".$i."' id='day_first_eq_".$i."' style='width:50px;'>";
													for($a=1;$a<=31;$a++)
													{
														$selected='';if($a==1){$selected='selected';}
														$aa=$a;if($a<10){$aa='0'.$a;}
														echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
													}
												echo "</select>";
							
												echo "<p>".htmlaccent('Jour')."</p>";
											echo "</div>";
											
											// MOIS
											echo "<div style='float:left;'>";
												echo select_mois('month_first_eq_'.$i,1,1);
												echo "<p>".htmlaccent('Mois')."</p>";
											echo "</div>";
											
											
											// ANNEE
											echo "<div style='float:left;'>\n";	
												echo "<select name='year_first_eq_".$i."' id='year_first_eq_".$i."' style='width:60px;'>";
													
													/*
													$year_first = $today_year_import - 30;
													$year_end = $today_year_import + 30;
													*/
												
													for($a=$year_first_all;$a<=$year_end_all;$a++)
													{
														$selected='';if($a==$year_first_all){$selected='selected';}
														echo "<option value='".$a."' ".$selected." >".$a."</option>";
													}
												echo "</select>";
												echo "<p>".htmlaccent('Année')."</p>";	
											echo "</div>\n";
											
											
										echo "</td>";					
										
										
										
										// FIN DE LA PERIODE DE VALIDITE
										echo "<td style='width:20%;height:20px;'>";
											
											// JOUR
											echo "<div style='float:left;'>";
											
												echo "<select name='day_end_eq_".$i."' id='day_end_eq_".$i."' style='width:50px;'>";
													for($a=1;$a<=31;$a++)
													{
														if($selected=='')if($a==1){$selected='selected';}
														$aa=$a;if($a<10){$aa='0'.$a;}
														echo "<option value='".$aa."' ".$selected.">".$aa."</option>";
													}
												echo "</select>";
							
												echo "<p>".htmlaccent('Jour')."</p>";
											echo "</div>";
											
											// MOIS
											echo "<div style='float:left;'>";
												echo select_mois('month_end_eq_'.$i,12,1);
												echo "<p>".htmlaccent('Mois')."</p>";
											echo "</div>";
											
											
											// ANNEE
											echo "<div style='float:left;'>\n";	
												echo "<select name='year_end_eq_".$i."' id='year_end_eq_".$i."' style='width:60px;'>";
													
													/*
													$year_first = $today_year_import - 30;
													$year_end = $today_year_import + 30;
													*/
												
													for($a=$year_first_all;$a<=$year_end_all;$a++)
													{
														$selected='';if($a==$year_end_all){$selected='selected';}
														echo "<option value='".$a."' ".$selected." >".$a."</option>";
													}
												echo "</select>";
												echo "<p>".htmlaccent('Année')."</p>";	
											echo "</div>\n";
											
										echo "</td>";	
										
										if($i<($nb_data_temp+10))
										{
											echo "<td style='width:1%;'>";
											
												echo "<img src='".DIR_WS_IMG_ICO."plus_cross.png' id='img_plus_".$i."' style='width:15px;margin-bottom:50px;cursor:pointer;' title='Plus de lignes' onClick=\"document.getElementById('ligne_visible_".($i+1)."').style.display = 'block';document.getElementById('img_plus_".$i."').style.display = 'none';\">";
											
											echo "</td>";
										}
										else
										{echo "<td style='width:30px;'>&nbsp;</td>";}
										
									echo "</tr>";
									
							
							
							echo "</table>";
				
						echo "</div>";
						
						$nb_data++;
						
					}
		
		
		
	echo "<hr>\n";
	echo "</div>\n";
		
	echo "<div style='width:100%;border-bottom:1px solid #eef2f6;'></div>\n";	
		
	//---------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------
	
	
	// Calcul data - Check
	echo "<div id='boite1' class='first'>\n";
			
		echo "<h2>".htmlaccent('Convertir toutes les données limnimétriques de la station en débit')."</h2>\n";
		
		$check = '';
		echo "<input type='checkbox' name='check_calcul' id='check_calcul' ".$check.">";			
	
	echo "</div>\n";
	
	echo "<div style='width:100%;border-bottom:1px solid #eef2f6;'></div>\n";
	
	
	//---------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------
	
	/*
	//Observations						
	echo "<div id='boite1' class='first'>\n";
	
		echo "<h2>".htmlaccent('Observations')."</h2>\n";
		
		//if($modif){echo "<textarea name='obs_jaugeage' style='width:90%;height:100px;'>".$obs_jaugeage."</textarea>\n";}
		//else{echo "<textarea name='obs_jaugeage' style='width:90%;height:100px;'></textarea>\n";}
	
	echo "<hr>\n";
	echo "</div>\n";
	
	echo "<div style='width:100%;border-bottom:1px solid #eef2f6;'></div>\n";
	*/
	
echo "<hr>\n";
echo "</div>\n";


echo "<input type='hidden' name='nb_data' id='nb_data' value='".$nb_data."'>";
?>
