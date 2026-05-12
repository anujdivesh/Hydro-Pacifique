<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Dans fiche station : Onglet Access pour Station
*/



// Première ligne pour avoir un formulaire vide
$access_array = array(
					'proprietaire' => '',
					'contact_nom' => '',
					'contact_phone' => '',
					'contact_mail' => '',
					'contact_adresse' => '',
					'contact_bp' => '',
					'contact_cp' => '',
					'contact_commune' => 0,
					'info_access' => '',
					'pedestre_access' => 0,
					'time_access' => '',
					'difficulty_access' => '',
					'remarque_access' => '');

// Requête sur Repere sur le puits de la station 
$sql_access = "SELECT DISTINCT proprietaire, 
                                contact_nom, contact_phone, contact_mail, contact_adresse, contact_bp, contact_cp, contact_commune,
                                info_access, pedestre_access, time_access, difficulty_access, remarque_access
				FROM ".TABLE_STATION_ACCESS."
                WHERE id_station = ".$id_station;

$access_query = tep_db_query($sql_link,$sql_access);
while($access_tab = tep_db_fetch_array($access_query))
{
	$proprietaire = html_entity_decode($access_tab['proprietaire'] ?? $default_string);	

	$contact_nom = html_entity_decode($access_tab['contact_nom'] ?? $default_string);	
	$contact_phone = html_entity_decode($access_tab['contact_phone'] ?? $default_string);	
	$contact_mail = html_entity_decode($access_tab['contact_mail'] ?? $default_string);
	$contact_adresse = html_entity_decode($access_tab['contact_adresse'] ?? $default_string);	
	$contact_bp = html_entity_decode($access_tab['contact_bp'] ?? $default_string);
	$contact_cp = html_entity_decode($access_tab['contact_cp'] ?? $default_string);	
	$contact_commune = html_entity_decode($access_tab['contact_commune'] ?? $default_string);
	$info_access = html_entity_decode($access_tab['info_access'] ?? $default_string);	
	$pedestre_access = html_entity_decode($access_tab['pedestre_access'] ?? $default_string);
	$time_access = html_entity_decode($access_tab['time_access'] ?? $default_string);	
	$difficulty_access = html_entity_decode($access_tab['difficulty_access'] ?? $default_string);
	$remarque_access = html_entity_decode($access_tab['remarque_access'] ?? $default_string);	



	$access_array = array('proprietaire' => $proprietaire,
							'contact_nom' => $contact_nom,
							'contact_phone' => $contact_phone,
							'contact_mail' => $contact_mail,
							'contact_adresse' => $contact_adresse,
							'contact_bp' => $contact_bp,
							'contact_cp' => $contact_cp,
							'contact_commune' => $contact_commune,
							'info_access' => $info_access,
							'pedestre_access' => $pedestre_access,
							'time_access' => $time_access,
							'difficulty_access' => $difficulty_access,
							'remarque_access' => $remarque_access);

}



echo "

	<div id='onglet_contenu' style='overflow-y: auto;height:75vh;padding-left:10px;'>\n

		
			<div id='button_pdf' style='float:left;margin-top:20px;margin-bottom:0;margin-left:10px;width:200px;'>

				<img src='".DIR_WS_IMG_ICO."pdf.png' style='float:left;width:22px;margin-top:3px;margin-right:10px;'>
				<p style='margin-top:12px;' id='textPDF' >
					<span style='font-size:13px;'>
						"."Exporter la fiche d'accès"."
					</span>	
				</p>					

			</div>

			<hr>
    ";
		

	
	//$backgroundColor = '#ECFAE5;';
	$titre = "Informations pour l'accès au site"; 


	$check_pedestre = '';
	if($access_array['pedestre_access'] == 1){$check_pedestre = 'checked';}

	$commune_options = "<option value=''></option>";

	if(isset($commune_array))
	{
		foreach($commune_array as $key_commune => $value_commune)
		{
			$selected = '';
			if($access_array['contact_commune']==$key_commune){$selected = 'selected';}

			$commune_options .= "<option value='".$key_commune."' ".$selected.">".$value_commune."</option>";
		}	
	}	

	// Affichage Access
	$hmtl_photo_access = "<img src='".DIR_WS_DATA_PHOTOS."default.png' style='width:200px;'>";

	$filePath = DIR_WS_STATION_PHOTO_ACCESS.$code_station.'_access.jpg';
	if($modif && file_exists($filePath))
	{
		$hmtl_photo_access = "<img src='".$filePath."' style='width:400px;'>";
	}

	

	echo "
			<div id='bloc_access' style='width:97%;margin:10px;margin-top:0;padding-top:5px;padding-bottom:30px;'>\n
				

                <div style='
							float:left;
							width:45%;
                            padding:20px;
							padding-top:15px;
							border: 1px solid #e0e0e0;
                            border-radius: 8px;
                            background-color: #fff;
                            box-shadow: 5px 20px 38px -27px #232323;'>


					<p class='titre_box' style='font-size:16px;'>".
						"Fiche d'accès".
					"</p>								

                    <div id='boite1' style='float:left;width:100%;margin:0;margin-top:10px;'>\n

                        <div id='boite_small' >\n
                            
                            <h2 style='float:left;width:150px;padding-top:5px;color:#930000;'>"
                                ."Propriétaire du site".
                            "</h2>\n					
                            <input name='proprietaire' id='proprietaire' value='".$access_array['proprietaire']."' class='input_texte' style='width:200px;' type='text'>
                            
                        </div>\n

                    </div>\n


                    <div id='boite1' style='float:left;width:100%;margin:0;margin-top:20px;'>\n

                        <div id='boite_small' style='margin-bottom:10px;'>\n
                        
                            <h2 style='float:left;width:150px;padding-top:5px;'>"
                                ."Personne à contacter".
                            "</h2>\n		
                            <input name='contact_nom' id='contact_nom' value='".$access_array['contact_nom']."' class='input_texte' style='width:280px;' type='text'>
                            
                        </div>\n
					
					</div>\n

                    <div id='boite1' style='float:left;width:100%;margin:0;margin-top:15px;'>\n

                        <div id='boite_small' style='float:left;width:320px;'>\n
                        
                            <h2 style='float:left;width:70px;padding-top:5px;'>"
                                ."Téléphone".
                            "</h2>\n		
                            <input name='contact_phone' id='contact_phone' value='".$access_array['contact_phone']."' class='input_texte' style='width:150px;' type='text'>
                            
                        	<hr>\n

                            <h2 style='float:left;width:70px;padding-top:5px;'>"
                                ."Email".
                            "</h2>\n		
                            <input name='contact_mail' id='contact_mail' value='".$access_array['contact_mail']."' class='input_texte' style='width:220px;' type='text'>
                            
                        </div>\n

						<div id='boite_small' style='float:left;width:50%;'>\n
                        
                            <h2 style='float:left;width:80px;padding-top:5px;'>"
                                ."Adresse".
                            "</h2>\n		
                            <input name='contact_adresse' id='contact_adresse' value='".$access_array['contact_adresse']."' class='input_texte' style='width:320px;' type='text'>
                            

                        	<hr>\n
                        
                            <h2 style='float:left;width:80px;padding-top:5px;'>"
                                ."Boîte Postale".
                            "</h2>\n		
                            <input name='contact_bp' id='contact_bp' value='".$access_array['contact_bp']."' class='input_texte' style='width:80px;' type='text'>
                       
							<hr>\n

                            <h2 style='float:left;width:80px;padding-top:5px;'>"
                                ."Code Postal".
                            "</h2>\n		
                            <input name='contact_cp' id='contact_cp' value='".$access_array['contact_cp']."' class='input_texte' style='width:80px;' type='text'>
                                                    
							<hr>\n

                            <h2 style='float:left;width:80px;padding-top:5px;'>"
                                ."Commune".
                            "</h2>\n		
                            
							<select name='contact_commune'  id='contact_commune' style='width:200px;'>
				
								".$commune_options."
								
							</select>
                            
                        </div>\n

                    </div>\n



					<div id='boite1' style='float:left;width:100%;margin:0;margin-top:35px;'>\n

                        <div id='boite_small' style='margin-right:35px;'>\n
                        
                            <h2 style='float:left;width:200px;padding-top:5px;'>"
                                ."Informations sur l'accès".
                            "</h2>\n		
							<br>
							<textarea name='info_access' id='info_access' style='width:320px;height:60px;font-size:13px;'>".$access_array['info_access']."</textarea>\n
                            
                        </div>\n

						<div id='boite_small' style='margin-top:20px;'>\n
                        
                            <h2 style='float:left;width:110px;padding-top:5px;'>"
                                ."Accès pédestre".
                            "</h2>\n		
							<input type='checkbox' name='pedestre_access' id='pedestre_access' style='float:left;width:20px;height:20px;margin:0;' ".$check_pedestre.">

							<hr>
                        
                            <h2 style='float:left;width:110px;padding-top:5px;'>"
                                ."Temps d'accès".
                            "</h2>\n
							<input name='time_access' id='time_access' value='".$access_array['time_access']."' class='input_texte' style='width:100px;' type='text'>	
							
                        </div>\n

					</div>\n

					<div id='boite1' style='float:left;width:100%;margin:0;margin-top:15px;'>\n

                        <div id='boite_small'>\n
                        
                            <h2 style='float:left;width:200px;padding-top:5px;'>"
                                ."Difficultées d'accès".
                            "</h2>\n		
							<br>
							<textarea name='difficulty_access' id='difficulty_access' style='width:320px;height:60px;font-size:13px;'>".$access_array['difficulty_access']."</textarea>\n
                            
                        </div>\n


						<div id='boite_small'>\n
                        
                            <h2 style='float:left;width:200px;padding-top:5px;'>"
                                ."Remarques complémentaires".
                            "</h2>\n		
							<br>
							<textarea name='remarque_access' id='remarque_access' style='width:320px;height:60px;font-size:13px;'>".$access_array['remarque_access']."</textarea>\n
                            
                        </div>\n

					</div>\n
                
                <hr>
                </div>
			

				
				<div style='
							float:right;
							width:45%;
                            padding:20px;
							padding-top:15px;
							border: 1px solid #e0e0e0;
                            border-radius: 8px;
                            background-color: #fff;
                            box-shadow: 5px 20px 38px -27px #232323;'>


					<p class='titre_box' style='font-size:16px;'>".
						"Plan d'accès".
					"</p>		

					<p style='float:left;width:100%;margin-top:20px;margin-bottom:3px;font-weight:bold;color:#000;font-size:13px;'>".
                		"Charger le plan (formats : .jpg .jpeg .png)".
                		"<br>".   
                		"La taille du fichier ne doit pas dépasser 2 Mo".
            		"</p>   
					
					<hr>

					<div style='margin-bottom:70px;'>
						<input type='file' id='file_photo_access' name='file_photo_access' style='float:left;background-color:#fff;'>
						
						<button id='new_photo_access' class='zoom_graph' style='width:150px;margin-left:35px;padding:8px 5px;display:block;' >
							"."Enregistrer la photo"." 
						</button>

					</div>

					".$hmtl_photo_access."
				<hr>
				</div>

			<hr>
			</div>\n";

	

		
echo "<hr>\n";
echo "</div>\n";
?>

<script>



</script>