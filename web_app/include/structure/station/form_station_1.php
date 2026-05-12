<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Page Station - Formulaire de saisie des données de la station
*/


echo "<div id='onglet_contenu' style='overflow-y: auto;height:75vh;'>\n";

	// Type de mesure et Etat Station
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;'>\n";
					
		// Type de mesure (Hydro / Pluie / Piézo / ...)
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Type de mesure')."</h2>\n";
				
			//echo "<select name='select_type_mesure' id='select_type_mesure' style='width:200px;height:30px;font-size:16px;border: 3px solid ".$eq_type_array[$id_eq_type]['type_color_border'].";' >";
			echo "<select name='select_type_mesure' id='select_type_mesure' style='width:200px;height:35px;font-size:16px;font-weight:bold;' >";
									
				$selected = '';		
								
				if(isset($eq_type_array))
				{
					foreach($eq_type_array as $key => $value)
					{
						if($key == $id_eq_type){$selected="selected";}	
						else{$selected = '';}											
						echo "<option value='".$key."' style='' ".$selected.">".$value['nom_eq_type']."</option>";
					}
				}

			echo "</select>";		
					
		echo "</div>\n";	

		// Station Active
		echo "<div id='boite_small' style='margin-left:20px;'>\n";			
						
			echo "<h2 style='float:left;'>";
				echo htmlaccent('Statut :');
			echo "</h2>";

			echo "<select name='select_statut_station' id='select_statut_station' style='width:150px;' >";

				$selected = '';
				if($modif && $active_station==1){$selected = 'selected';}
				echo "<option value='1' ".$selected.">"."Station active"."</option>";

				$selected = '';
				if($modif && $active_station==0){$selected = 'selected';}
				echo "<option value='0' ".$selected.">"."Station historique (fermée)"."</option>";

			echo "</select>";	
		
		echo "</div>\n";

		// Station Suivie
		echo "<div id='boite_small'>\n";			

			echo "<h2 style='float:left;'>";
				echo htmlaccent('Suivi :');
			echo "</h2>";
			
			echo "<select name='select_suivi_station' id='select_suivi_station' style='width:150px;' >";

				$selected = '';
				if($modif && $suivi_station==1){$selected = 'selected';}
				echo "<option value='1' ".$selected.">"."Mesures en continu"."</option>";

				$selected = '';
				if($modif && $suivi_station==0){$selected = 'selected';}
				echo "<option value='0' ".$selected.">"."Mesures ponctuelles"."</option>";

			echo "</select>";	
		
		echo "</div>\n";

		// Station en Panne
		echo "<div id='boite_small'>\n";			
			
			echo "<h2 style='float:left;width:150px;'>";
				echo htmlaccent('Equipement en panne :');
			echo "</h2>";

			echo "<br>";

			$check = '';
			if($modif && $armee_station==1){$check = 'checked';}
			echo "<input type='checkbox' name='check_armee_station' id='check_armee_station' ".$check." style='float:left;width:20px;height:20px;' >";
		
		echo "</div>\n";

	echo "<hr>\n";
	echo "</div>\n";	

	// -----------------------------------------------------------
	
	echo "<div style='width:100%;border-bottom:2px solid #176B87;'></div>\n";
	
	// -----------------------------------------------------------


	// Station - Noms, Code
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;'>\n";
		
		/*
			echo "<input type='hidden' name='id_station_old' value='".$id_station_old."' />\n";	
			if($modif){echo "<input type='hidden' name='id_station_old' value='".$id_station_old."' />\n";	}
			else{echo "<input type='hidden' name='id_station_old' value='' />\n";	}
		*/	
	
		echo "<div id='boite_small' style='width:240px;'>\n";
			
			echo "<h2>".htmlaccent('Code Station / Numéro Station')."</h2>\n";
				
			if($modif){echo "<input name='code_station' id='code_station' value='".$code_station."' class='input_texte' type='text'>";}
			else{echo "<input name='code_station' id='code_station' value='' class='input_texte' type='text'>";}
			
		echo "</div>\n";
		
		echo "<div id='boite_small'>\n";		
				
			echo "<h2>".htmlaccent('Nom de la station')."</h2>\n";
				
			if($modif){echo "<input name='nom_station' id='nom_station' value='".$nom_station."' class='titre' type='text'>";}
			else{echo "<input name='nom_station' id='nom_station' value='' class='titre' type='text'>";}
		
		echo "</div>\n";				
								
		echo "<div id='boite_small'>\n";		
				
			echo "<h2>".htmlaccent('Nom abrégé')."</h2>\n";
				
			if($modif){echo "<input name='nom_court' id='nom_court' value='".$nom_court."' class='input_texte_200' type='text'>";}
			else{echo "<input name='nom_court' id='nom_court' value='' class='input_tinput_texte_200exte' type='text'>";}
		
		echo "</div>\n";
		
		echo "<div id='boite_small'>\n";		
				
			echo "<h2>".htmlaccent('Numéro IRH')."</h2>\n";
				
			if($modif){echo "<input name='num_irh' id='num_irh' value='".$num_irh."' class='input_texte' type='text'>";}
			else{echo "<input name='num_irh' id='num_irh' value='' class='input_texte' type='text'>";}
		
		echo "</div>\n";	
				
	
	echo "<hr>\n";
	echo "</div>\n";
	
	// -----------------------------------------------------------
	
	// Situation géographique
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;'>\n";
				
		// Territoire
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent($territoire_region)."</h2>\n";
				
			$sql_region = "SELECT id_region, nom_region FROM ".TABLE_REGION." WHERE id_territoire=".$territoire_id;
			$regions_query = tep_db_query($sql_link,$sql_region);
			$selected_verif = false;			

			//$begin_region = true;
			
			
			//echo $region_station;
			echo "<select name='select_region' id='select_region' style='width:170px;' onchange=\"document.getElementById('button_ordre').style.display = 'none';import_select_region_commune_ajax();\">";				
				while($regions_list = tep_db_fetch_array($regions_query))
				{
					//if($id_region < 1 && $begin_region){$id_region = $regions_list['id_region'];}
					//$begin_region = false;

					$selected = '';					
					if($regions_list['id_region'] == $id_region){$selected = 'selected';} // Définition de $id_region dans modif_station.php par défaut : $id_region = $region_default;
					//if(!$selected_verif && $regions_list['id'] == $region_default){$selected = 'selected';}
					
					echo "<option value='".$regions_list['id_region']."' ".$selected.">".htmlaccent($regions_list['nom_region'])."</option>";
					
				}


			echo "</select>";
					
		echo "</div>\n";
			
		// Commune				
		echo "<div id='boite_small' >\n";
			
			echo "<h2>".htmlaccent('Commune')."</h2>\n";

			echo "<div id='commune'>";
							
				if($id_region < 1)
				{$sql_commune = "SELECT id_commune, nom_commune FROM ".TABLE_COMMUNE." WHERE id_territoire=".$territoire_id." ORDER BY nom_commune";}
				else{$sql_commune = "SELECT id_commune, nom_commune FROM ".TABLE_COMMUNE." WHERE id_region=".$id_region." ORDER BY nom_commune";}
				
				$commune_query = tep_db_query($sql_link,$sql_commune);
				$selected_verif = false;			
						
				echo "<select name='select_commune' id='select_commune' style='width:170px;' >";
					while($commune_list = tep_db_fetch_array($commune_query))
					{
						$selected = '';
						if($commune_list['id_commune'] == $id_commune){$selected = 'selected';}
						
						echo "<option value='".$commune_list['id_commune']."' ".$selected.">".htmlaccent($commune_list['nom_commune'])."</option>";
						
					}
				echo "</select>";
				
			echo "</div>\n";
					
		echo "</div>\n";
	
		// Site
		echo "<div id='boite_small'>\n";		
				
			echo "<h2>".htmlaccent('Site')."</h2>\n";
				
			if($modif){echo "<input name='site_station' id='site_station' value='".$site_station."' class='titre' type='text'>";}
			else{echo "<input name='site_station' id='site_station' value='' class='titre' type='text'>";}
		
		echo "</div>\n";
	
		// Tournée
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Tournée')."</h2>\n";
				
			echo "<select name='select_tournee' id='select_tournee' style='width:120px;' >";
				
				echo "<option value='0'>-</option>";
					
				$selected = '';									
				if(isset($tournee_array))
				{
					foreach($tournee_array as $key => $value)
					{
						if($key == $id_tournee){$selected="selected";}	
						else{$selected = '';}											
						echo "<option value='".$key."' ".$selected." >".$value."</option>";
					}
				}

			echo "</select>";
						
		echo "</div>\n";
	
	echo "<hr>\n";
	echo "</div>\n";
	
	
	// -----------------------------------------------------------
	
	
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;'>\n";
				
		// Inutile
		/*
		// Vallée ou bassin versant		
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Région hydrologique / Bassin Versant')."</h2>\n";
				
			if($modif){echo "<input name='vallee_station' id='vallee_station' value='".$vallee_station."' class='titre' type='text'>";}
			else{echo "<input name='vallee_station' id='vallee_station' value='' class='titre' type='text'>";}
						
		echo "</div>\n";
		*/

		// Région hydrologique
		echo "<div id='boite_small' >\n";
			
			echo "<h2 title='".htmlaccent('Bassin Versant')."'>".htmlaccent('Bassin Versant')."</h2>\n";
				
			echo "<select name='select_regionhydro' id='select_regionhydro' style='width:180px;' >";
				
				echo "<option value='0'>-</option>";
					
				$selected = '';									
				if(isset($regionhydro_array))
				{
					foreach($regionhydro_array as $key => $value)
					{
						if($key == $id_regionhydro){$selected="selected";}	
						else{$selected = '';}											
						echo "<option value='".$key."' ".$selected." >".$value."</option>";
					}
				}

			echo "</select>";
						
		echo "</div>\n";
		
		// Rivière		
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Rivière')."</h2>\n";
				
			if($modif){echo "<input name='riviere_station' id='riviere_station' value='".$riviere_station."' class='titre' type='text'>";}
			else{echo "<input name='riviere_station' id='riviere_station' value='' class='titre' type='text'>";}
						
		echo "</div>\n";


		// Aquifère
		echo "<div id='boite_small'>\n";
			
			echo "<h2>".htmlaccent('Aquifère')."</h2>\n";
				
			echo "<select name='select_aquifere' id='select_aquifere' style='width:150px;' >";
				
				echo "<option value='0'>-</option>";
					
				$selected = '';									
				if(isset($aquifere_array))
				{
					foreach($aquifere_array as $key => $value)
					{
						if($key == $id_aquifere){$selected="selected";}	
						else{$selected = '';}											
						echo "<option value='".$key."' ".$selected." >".$value."</option>";
					}
				}

			echo "</select>";
						
		echo "</div>\n";
	
	echo "<hr>\n";
	echo "</div>\n";
	
	// -----------------------------------------------------------
	
	echo "<div style='width:100%;border-bottom:2px solid #176B87;'></div>\n";
	
	// -----------------------------------------------------------
	
	// Données Topo et GPS (différents systèmes)
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;'>\n";
				
		echo "<div id='boite_small' style='width:150px;'>\n";
			
			echo "<h2>".htmlaccent('Altitude (en m)')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('altitude_station',$altitude_station,'class=\'input_texte_small\'');}
			else{echo tep_draw_input_field('altitude_station','','class=\'input_texte_small\'');}
						
		echo "</div>\n";
		
		echo "<div id='boite_small' style='width:200px;'>\n";
			
			echo "<h2>".htmlaccent('Orientation du bassin versant')."</h2>\n";
			
			$selected = '';
						
			echo "<select name='orientation_station'  id='orientation_station' style='width:120px;'>";
				
				echo "<option value='-' ".$selected.">".htmlaccent('-')."</option>";	
				
				for($i=0;$i<sizeof($tab_orientation);$i++)
				{
					if($modif && $orientation_station==$tab_orientation[$i]){$selected = 'selected';}
					else{$selected = '';}
					
					echo "<option value='Nord' ".$selected.">".$tab_orientation[$i]."</option>";	
				}
				
			echo "</select>";
					
		echo "</div>\n";
				
	
	echo "<hr>\n";
	echo "</div>\n";
	
	// -----------------------------------------------------------
	
	// Coordonnées géographique UTM, LAMB, IGN
	
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;'>\n";
		
		// Longitude
		echo "<div id='boite_small' style='width:150px;'>\n";
			
			echo "<h2>".htmlaccent('Longitude')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('longitude_station',$longitude_station,'class=\'input_texte\'');}
			else{echo tep_draw_input_field('longitude_station','','class=\'input_texte\'');}
						
		echo "</div>\n";
		
		// Latitude
		echo "<div id='boite_small' style='width:150px;'>\n";
			
			echo "<h2>".htmlaccent('Latitude')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('latitude_station',$latitude_station,'class=\'input_texte\'');}
			else{echo tep_draw_input_field('latitude_station','','class=\'input_texte\'');}
						
		echo "</div>\n";
		
		// UTM - X (PF essentiellement)
		echo "<div id='boite_small' style='width:180px;margin-left:50px;'>\n";
			
			echo "<h2>".htmlaccent('Coord. UTM - X (WGS 84)')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('utm_station_x',$utm_station_x,'class=\'input_texte\'');}
			else{echo tep_draw_input_field('utm_station_x','','class=\'input_texte\'');}
						
		echo "</div>\n";	
		
		// UTM - Y (PF essentiellement)
		echo "<div id='boite_small' style='width:180px;'>\n";
			
			echo "<h2>".htmlaccent('Coord. UTM - Y (WGS 84)')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('utm_station_y',$utm_station_y,'class=\'input_texte\'');}
			else{echo tep_draw_input_field('utm_station_y','','class=\'input_texte\'');}
						
		echo "</div>\n";
	
	echo "<hr>\n";
	echo "</div>\n";
	
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;display:none;'>\n";
				
		// IGN - X (NC essentiellement)
		echo "<div id='boite_small' style='width:150px;'>\n";
			
			echo "<h2>".htmlaccent('Coord. IGN - X')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('ign_station_x',$ign_station_x,'class=\'input_texte\'');}
			else{echo tep_draw_input_field('ign_station_x','','class=\'input_texte\'');}
						
		echo "</div>\n";
		
		// IGN - Y (NC essentiellement)
		echo "<div id='boite_small' style='width:150px;'>\n";
			
			echo "<h2>".htmlaccent('Coord. IGN - Y')."</h2>\n";
				
			if($modif){echo tep_draw_input_field('ign_station_y',$ign_station_y,'class=\'input_texte\'');}
			else{echo tep_draw_input_field('ign_station_y','','class=\'input_texte\'');}
						
		echo "</div>\n";
		
		// LAMP - X (NC essentiellement)
		if($territoire_init == 'NC')
		{
			echo "<div id='boite_small' style='width:180px;margin-left:50px;'>\n";
				
				echo "<h2>".htmlaccent('Coord. Lamb - X (RGNC 91)')."</h2>\n";
					
				if($modif){echo tep_draw_input_field('lamb_station_x',$lamb_station_x,'class=\'input_texte\'');}
				else{echo tep_draw_input_field('lamb_station_x','','class=\'input_texte\'');}
							
			echo "</div>\n";	
			
			// LAMP - Y (NC essentiellement)
			echo "<div id='boite_small' style='width:180px;'>\n";
				
				echo "<h2>".htmlaccent('Coord. Lamb - Y (RGNC 91)')."</h2>\n";
					
				if($modif){echo tep_draw_input_field('lamb_station_y',$lamb_station_y,'class=\'input_texte\'');}
				else{echo tep_draw_input_field('lamb_station_y','','class=\'input_texte\'');}
							
			echo "</div>\n";
		}
	
	echo "<hr>\n";
	echo "</div>\n";
		
	// -----------------------------------------------------------
	
	echo "<div style='width:100%;border-bottom:2px solid #176B87;'></div>\n";
	
	// -----------------------------------------------------------
	
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;'>\n";
				
		// Date Installation
		
		echo "<div id='boite_small' style='width:200px;'>\n";
			
			echo "<h2>".htmlaccent('Date d\'installation (jj/mm/aaaa)')."</h2>\n";
				
			if($modif){echo "<input class='input_texte' style='width:80px;' name='date_installation_station' id='date_installation_station' value='".$date_installation_station."' type='text'  onclick=\"javascript:displayCalendar(document.forms[0].date_installation_station,'dd-mm-yyyy',this);\" >";}
			else{echo "<input class='input_texte' style='width:80px;' name='date_installation_station' id='date_installation_station' value='".$today_fr."' type='text'  onclick=\"javascript:displayCalendar(document.forms[0].date_installation_station,'dd-mm-yyyy',this);\" >";}
						
		echo "</div>\n";
		
		// Date Fermeture 
		
		echo "<div id='boite_small' style='width:200px;'>\n";
			
			echo "<h2>".htmlaccent('Date de démontage (jj/mm/aaaa)')."</h2>\n";
				
			if($modif){echo "<input class='input_texte' style='width:80px;' name='date_fermeture_station' id='date_fermeture_station' value='".$date_fermeture_station."' type='text'  onclick=\"javascript:displayCalendar(document.forms[0].date_fermeture_station,'dd-mm-yyyy',this);\" >";}
			else{echo "<input class='input_texte' style='width:80px;' name='date_fermeture_station' id='date_fermeture_station' value='' type='text' onclick=\"javascript:displayCalendar(document.forms[0].date_fermeture_station,'dd-mm-yyyy',this);\" >";}
						
		echo "</div>\n";
			
	echo "<hr>\n";
	echo "</div>\n";
	
	
	
	//Description						
	echo "<div id='boite1' style='margin-top:10px;margin-bottom:0;'>\n";
		echo "<h2>".htmlaccent('Description')."</h2>\n";
	
		if($modif){echo "<textarea name='description_station' id='description_station' style='width:40%;height:170px;'>".$description_station."</textarea>\n";}
		else{echo "<textarea name='description_station' id='description_station' style='width:40%;height:100px;'></textarea>\n";}
	
	echo "<hr>\n";
	echo "</div>\n";
	
	//echo "<div style='width:100%;border-bottom:2px solid #64CCC5;'></div>\n";
	
	
echo "<hr>\n";
echo "</div>\n";
?>
