<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Dans fiche station : Onglet caractéristique pour station piézométrique
*/

$nb_caract = 0;

$tab_etat = array('','Bon','Moyen','Mauvais','Abandonné','Colmaté','Rebouché','Non accessible','Disparu');

// Accès aux données des schémas de puits
//$sql_piezo_schema = "SELECT DISTINCT id, nom_schema, img, id_nature, capot, dist_ct, dist_td, dist_ds
$sql_piezo_schema = "SELECT DISTINCT id, nom_schema, img, capot, dist_ct, dist_td, dist_ds
				FROM ".TABLE_STATION_PIEZO_SCHEMA."
				ORDER BY id ASC";
$piezo_schema_query = tep_db_query($sql_link,$sql_piezo_schema);
while($piezo_schema_tab = tep_db_fetch_array($piezo_schema_query))	
{
	$piezo_schema_array[$piezo_schema_tab['id']] = array('nom_schema' => $piezo_schema_tab['nom_schema'],
														'img' => $piezo_schema_tab['img'],
														//'id_nature' => $piezo_schema_tab['id_nature'],
														'capot' => $piezo_schema_tab['capot'],
														'dist_ct' => $piezo_schema_tab['dist_ct'],
														'dist_td' => $piezo_schema_tab['dist_td'],
														'dist_ds' => $piezo_schema_tab['dist_ds']);
}


// Première ligne pour avoir un formulaire vide
$caract_array[0] = array(
						'date_caract' => $today_fr,
						'prof' => '',
						'materiaux_tete' => '',
						'dim_tete_ext' => '',
						'materiaux_tub_inter' => '',
						'diam_tub_inter' => '',
						'materiaux_dalle' => '',
						'dim_dalle' => '',
						'dist_capto_tube' => '',
						'dist_tube_dalle' => '',
						'dist_dalle_sol' => '',
						'presence_capot' => 0,
						'etat' => '',
						'activite' => 0,
						'utilisation' => '',
						'equipement_exploitation' => '',
						'schema_tete' => 0,
						'schema_protect' => 0,
						'obs' => '');





$hidden_id_caract = '';

// Requête sur Repere sur le puits de la station 
$sql_caract = "SELECT DISTINCT c.id, c.date, c.prof, c.materiaux_tete, c.dim_tete_ext, c.materiaux_tub_inter, c.diam_tub_inter, 
								c.materiaux_dalle, c.dim_dalle, c.dist_capto_tube, c.dist_tube_dalle, c.dist_dalle_sol, c.presence_capot,
								c.etat, c.activite, c.utilisation, c.equipement_exploitation, c.schema_tete, c.schema_protect,
								c.obs
				FROM ".TABLE_STATION_PIEZO_CARACTERISTIQUE." c
                WHERE id_station = ".$id_station."
				ORDER BY date DESC";
$caract_query = tep_db_query($sql_link,$sql_caract);
while($caract_tab = tep_db_fetch_array($caract_query))
{
	$nb_caract++;
	
	$id_caract = $caract_tab['id'];

	$hidden_id_caract .= $id_caract.'_';

	if($caract_tab['date']=='0000-00-00'){$date =  "";}
	else{$date_caract =  dateus_fr($caract_tab['date']);}

	$prof = html_entity_decode($caract_tab['prof'] ?? $default_string);	
	$materiaux_tete = html_entity_decode($caract_tab['materiaux_tete'] ?? $default_string);	

	$dim_tete_ext = html_entity_decode($caract_tab['dim_tete_ext'] ?? $default_string);	

	$materiaux_tub_inter = html_entity_decode($caract_tab['materiaux_tub_inter'] ?? $default_string);

	$diam_tub_inter = html_entity_decode($caract_tab['diam_tub_inter'] ?? $default_string);		
	$diam_tub_inter = str_replace(',', '.', $diam_tub_inter);
	$diam_tub_inter = round(floatval($diam_tub_inter),3);
	
	$materiaux_dalle = html_entity_decode($caract_tab['materiaux_dalle'] ?? $default_string);	
	$dim_dalle = html_entity_decode($caract_tab['dim_dalle'] ?? $default_string);	

	$dist_capto_tube = html_entity_decode($caract_tab['dist_capto_tube'] ?? $default_string);
	$dist_capto_tube = str_replace(',', '.', $dist_capto_tube);
	$dist_capto_tube = round(floatval($dist_capto_tube),3);

	$dist_tube_dalle = html_entity_decode($caract_tab['dist_tube_dalle'] ?? $default_string);
	$dist_tube_dalle = str_replace(',', '.', $dist_tube_dalle);
	$dist_tube_dalle = round(floatval($dist_tube_dalle),3);	

	$dist_dalle_sol = html_entity_decode($caract_tab['dist_dalle_sol'] ?? $default_string);
	$dist_dalle_sol = str_replace(',', '.', $dist_dalle_sol);
	$dist_dalle_sol = round(floatval($dist_dalle_sol),3);	

	$presence_capot = $caract_tab['presence_capot'];	
	$etat = html_entity_decode($caract_tab['etat'] ?? $default_string);	
	$activite = $caract_tab['activite'];	
	$utilisation = html_entity_decode($caract_tab['utilisation'] ?? $default_string);	
	$equipement_exploitation = html_entity_decode($caract_tab['equipement_exploitation'] ?? $default_string);	
	$schema_tete = html_entity_decode($caract_tab['schema_tete'] ?? $default_string);	
	$schema_protect = $caract_tab['schema_protect'];	
	$obs = nettoyer_et_echapper($caract_tab['obs'] ?? $default_string);	


	$caract_array[$id_caract] = array('date_caract' => $date_caract,
									'prof' => $prof,
									'materiaux_tete' => $materiaux_tete,
									'dim_tete_ext' => $dim_tete_ext,
									'materiaux_tub_inter' => $materiaux_tub_inter,
									'diam_tub_inter' => $diam_tub_inter,
									'materiaux_dalle' => $materiaux_dalle,
									'dim_dalle' => $dim_dalle,
									'dist_capto_tube' => $dist_capto_tube,
									'dist_tube_dalle' => $dist_tube_dalle,
									'dist_dalle_sol' => $dist_dalle_sol,
									'presence_capot' => $presence_capot,
									'etat' => $etat,
									'activite' => $activite,
									'utilisation' => $utilisation,
									'equipement_exploitation' => $equipement_exploitation,
									'schema_tete' => $schema_tete,
									'schema_protect' => $schema_protect,
									'obs' => $obs);

}
// Enlevez le dernier underscore à la fin de la chaîne, si nécessaire
$hidden_id_caract = rtrim($hidden_id_caract, '_');


echo "

	<div id='onglet_contenu' style='overflow-y: auto;height:75vh;'>\n

		<p class='titre_box' style='margin: 10px 20px;'>
			<input type='checkbox' name='new_caract' id='new_caract' style='float:left;width:20px;height:20px;margin-right:10px;' >
			<span style='float:left;margin-top:5px;'>".htmlaccent('Nouvelle observation')."</span>
		</p>
		
		<hr>";

	foreach($caract_array as $key => $value)
	{
		$backgroundColor = '#FFF8E3;';
		$titre = htmlaccent('Observation du '); 
		$display_newBox = "";

		if($key == 0)
		{
			$backgroundColor = '#FFEFEF;';
			$titre = htmlaccent('(New) Observation du '); 
			$display_newBox = "display:none;";
		}

		$check_schema_protect = '';
		if($value['schema_protect'] == 1){$check_schema_protect = 'checked';}

		$check_presence_capot = '';
		if($value['presence_capot'] == 1){$check_presence_capot = 'checked';}
		
		$check_enActivite = '';
		if($value['activite'] == 1){$check_enActivite = 'checked';}

		$etat_options = '';
		foreach($tab_etat as $option_etat)
		{
			$selected = '';
			if($value['etat']==$option_etat){$selected = 'selected';}

			$etat_options .= "<option value='".$option_etat."' ".$selected.">".$option_etat."</option>";
		}

		$schema_options = "<option value=''></option>";
		foreach($piezo_schema_array as $key_schema => $value_schema)
		{
			$selected = '';
			if($value['schema_tete']==$key_schema){$selected = 'selected';}

			$schema_options .= "<option value='".$key_schema."' ".$selected.">".$value_schema['nom_schema']."</option>";
		}	


		echo "
			<div id='bloc_caract_".$key."' style='margin:10px;padding:10px 20px;border:1px solid #F2EFE5;background-color:".$backgroundColor.$display_newBox.";'>\n

				<div id='del' style='float:right;' onclick=\"del_caracteristique('".$key."');\"><span style='font-size:20px;cursor:pointer;'>X</span></div>

				<div style='float:left;width:640px;margin-right:3%;' >\n
			
					<div id='boite1' class='first' style='float:left;width:100%;margin:0;border-bottom:2px solid #176B87;'>\n
						<p class='titre_box' >
							<span style='float:left;'>".$titre."</span>
							<input class='input_texte' style='width:80px;margin-left:10px;margin-top:-5px;text-align:center;' name='date_caract_".$key."' id='date_caract_".$key."' value='".$value['date_caract']."' type='text'  onclick=\"javascript:displayCalendar(document.forms[0].date_caract_".$key.",'dd-mm-yyyy',this);\" >
						</p>
					</div>\n

					<hr>

					<div id='boite1' style='float:left;width:300px;margin:0;'>\n

						<div id='boite_small' style='width:300px;'>\n
							
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Profondeur [m]')."</h2>\n					
							<input name='prof_".$key."' id='prof_".$key."' value='".$value['prof']."' class='input_texte' style='width:80px;' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Matériaux Tête')."</h2>\n		
							<br>			
							<input name='materiaux_tete_".$key."' id='materiaux_tete_".$key."' value='".$value['materiaux_tete']."' class='input_texte' style='width:280px;' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Dimension extérieure')."</h2>\n					
							<input name='dim_tete_ext_".$key."' id='dim_tete_ext_".$key."' value='".$value['dim_tete_ext']."' class='input_texte' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Matériaux tubage intérieur')."</h2>\n					
							<input name='materiaux_tub_inter_".$key."' id='materiaux_tub_inter_".$key."' value='".$value['materiaux_tub_inter']."' class='input_texte'type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Dimension du tubage [mm]')."</h2>\n					
							<input name='diam_tub_inter_".$key."' id='diam_tub_inter_".$key."' value='".$value['diam_tub_inter']."' class='input_texte' style='width:80px;' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Schéma')."</h2>\n			
							
							<select name='schema_tete_".$key."'  id='schema_tete_".$key."' style='width:120px;'>
				
								".$schema_options."
								
							</select>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Protection')."</h2>\n
							
							<input type='checkbox' name='schema_protect_".$key."' id='schema_protect_".$key."' style='float:left;width:20px;height:20px;margin:0;' ".$check_schema_protect.">
							
							
						</div>\n

					</div>\n

					<div id='boite1' style='float:left;width:300px;'>\n

						<div id='boite_small' style='width:300px;'>\n
							
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Matériaux dalle')."</h2>\n					
							<input name='materiaux_dalle_".$key."' id='materiaux_dalle_".$key."' value='".$value['materiaux_dalle']."' class='input_texte' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Dimension de la dalle')."</h2>\n					
							<input name='dim_dalle_".$key."' id='dim_dalle_".$key."' value='".$value['dim_dalle']."' class='input_texte' type='text'>
							
						</div>\n
						
						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Présence d\'un capot')."</h2>\n								
							
							<input type='checkbox' name='presence_capot_".$key."' id='presence_capot_".$key."' style='float:left;width:20px;height:20px;margin:0;' ".$check_presence_capot.">
							
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Dist. Capot/Tubage (1)')."</h2>\n					
							<input name='dist_capto_tube_".$key."' id='dist_capto_tube_".$key."' value='".$value['dist_capto_tube']."' class='input_texte' style='width:80px;' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Dist. Tubage/Dalle (2)')."</h2>\n					
							<input name='dist_tube_dalle_".$key."' id='dist_tube_dalle_".$key."' value='".$value['dist_tube_dalle']."' class='input_texte' style='width:80px;' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Dist. Dalle/Sol (3)')."</h2>\n					
							<input name='dist_dalle_sol_".$key."' id='dist_dalle_sol_".$key."' value='".$value['dist_dalle_sol']."' class='input_texte' style='width:80px;' type='text'>
							
						</div>\n

					</div>\n
				
				</div>\n

				<div style='float:left;width:320px;'>\n
			
					<div id='boite1' class='first' style='float:left;width:100%;margin:0;border-bottom:2px solid #176B87;'>\n
						<p class='titre_box' >".htmlaccent('Usage')."</p>
					</div>\n

					<hr>

					<div id='boite1' style='float:left;width:300px;margin:0;'>\n

						<div id='boite_small' style='width:300px;'>\n
							
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Etat')."</h2>\n			
							
							<select name='etat_".$key."'  id='etat_".$key."' style='width:120px;'>
				
								".$etat_options."
								
							</select>

						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('En activité')."</h2>\n								
							
							<input type='checkbox' name='activite_".$key."' id='activite_".$key."' style='float:left;width:20px;height:20px;margin:0;' ".$check_enActivite.">
							
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Usage')."</h2>\n					
							<input name='utilisation_".$key."' id='utilisation_".$key."' value='".$value['utilisation']."' class='input_texte' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Equipement')."</h2>\n					
							<input name='equipement_exploitation_".$key."' id='equipement_exploitation_".$key."' value='".$value['equipement_exploitation']."' class='input_texte' type='text'>
							
						</div>\n

						<hr>\n

						<div id='boite_small' style='width:300px;'>\n
						
							<h2 style='float:left;width:150px;padding-top:5px;'>".htmlaccent('Observations')."</h2>\n		
							<br>\n	
							<textarea name='obs_".$key."' id='obs_".$key."' style='width:100%;height:80px;'>".$value['obs']."</textarea>\n
							
						</div>\n

					</div>\n
				
				</div>\n
			
			<hr>
			</div>\n";

	}

		
echo "<hr>\n";
echo "</div>\n";
?>

<script>

	// Initialisation des variables
	var new_caract = document.getElementById('new_caract'); 
	var newBox = document.getElementById('bloc_caract_0');

	new_caract.addEventListener('change', function() {

		if (this.checked) 
		{
			newBox.style.display = 'block';
		} else {
			newBox.style.display = 'none';
		}

	});


	// Fonction de lancement de la procédure AJAX permettant de supprimer une saisie de caractéristique
	function del_caracteristique(id_caract)
	{
		// Créer un objet JavaScript contenant les données à envoyer
		var dataToSend = {
							id_caract: id_caract
						};
		
		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/station/process_delcaracteristique.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{
				// Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				del_caract = jsonResponse['del_caract'];
				message_info = jsonResponse['message_info'];

				if(del_caract)
				{
					contenuInfo.innerHTML = message_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					document.getElementById('bloc_caract_'+id_caract).style.display='none';
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