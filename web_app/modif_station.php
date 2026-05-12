<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Page d'édition des information d'une station
Cette page est le points d'entrée des Fichers stations. 
- Récupération des données Stations
- Affichages des données liées à la station (Champs des Choniques et RA)
- Pour les stations Piézo affichage des onglets Repères et Caractéristiques
- Gestions de photos pour la station
*/

//---------------------------------------------------------------
// Appel du fichier contenant les infos de connexions et de configuration chargée à chaque page

use PhpOffice\PhpSpreadsheet\Writer\Html;

require('include/application_top.php');

// -----------------------------
// Initialisation des Var.

$action = false;
$message_info = '';
$message_suprr_liaison = '';
$error_station = false;

$row = 0;
$reference = '';
$libelle = '';
$today = new DateTime(); // Crée un objet DateTime pour la date actuelle
$today_us = date('Y-m-d'); 
$today_fr = date('d-m-Y'); 
$date_format = 'd-m-Y';

$id_region = $region_default;
$id_commune = 0;
$id_station = 0; // Initialisation pour la création d'une nouvelle fiche station
$id_station_old = '';

$nb_ra_Avalider = 0;
$nb_ra_valid = 0;
$nb_ra = 0;
$last_datetime_ra_Avalider = '';
$last_datetime_ra_valid = '';

$html_tab_data_station = '';
$html_tab_code_cal = '';

$chron_data_array = []; // Initialisation avec un tableau vide

$tab_orientation = array('Nord','Nord-Est','Est','Sud-Est','Sud','Sud-Ouest','Ouest','Nord-Ouest');

$modif=false;

//---------------------------------------------------------------
// TABLE SQL - Recupération Données formulaires

if(isset($_GET['ref'])){$id_station = mysqli_real_escape_string($sql_link,trim(addslashes($_GET['ref'])));$modif=true;}


//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// TABLE TYPE DATA (DEBIT, PLUIE, PIEZO, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, type_color_border, type_color_background 
				FROM ".TABLE_EQ_TYPE." 
				WHERE active_eq_type=1 
				ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);	
while ($eq_type = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type['id_eq_type']] = array('nom_eq_type' => htmlaccent(html_entity_decode($eq_type['nom_eq_type'] ?? $default_string)),
													'type_color_border' => htmlaccent(html_entity_decode($eq_type['type_color_border'] ?? $default_string)),							
													'type_color_background' => htmlaccent(html_entity_decode($eq_type['type_color_background'] ?? $default_string)),
													);
}


// TABLE REGION / TERRITOIRE : Province pour NC - Iles pour PF et WF
$sql_region = "SELECT DISTINCT id_region, nom_region 
				FROM ".TABLE_REGION." 
				WHERE id_territoire=".$territoire_id;
$region_query = tep_db_query($sql_link,$sql_region);
while ($region = tep_db_fetch_array($region_query))
{
	$region_array[$region['id_region']] = htmlaccent(html_entity_decode($region['nom_region'] ?? $default_string));
}

// TABLE COMMUNE
$sql_commune = "SELECT DISTINCT c.id_commune, c.nom_commune 
				FROM ".TABLE_COMMUNE." c
				JOIN ".TABLE_REGION." r ON c.id_region=r.id_region
				WHERE r.id_territoire=".$territoire_id."
				ORDER BY c.nom_commune ASC";

$commune_query = tep_db_query($sql_link,$sql_commune);
while ($commune = tep_db_fetch_array($commune_query))
{
	$commune_array[$commune['id_commune']] = htmlaccent(html_entity_decode($commune['nom_commune'] ?? $default_string));
}

// TABLE TOURNEE
$sql_tournee = "SELECT DISTINCT t.id, t.nom, t.id_territoire 
				FROM ".TABLE_TOURNEE." t
				WHERE t.id_territoire=".$territoire_id." 
				ORDER BY nom ASC";
$tournee_query = tep_db_query($sql_link,$sql_tournee);
while ($tournee = tep_db_fetch_array($tournee_query))
{
	$tournee_array[$tournee['id']] = htmlaccent(html_entity_decode($tournee['nom'] ?? $default_string));
}

// TABLE REGION HYDROLOGIQUE
$sql_regionhydro = "SELECT DISTINCT rh.id, rh.nom, rh.id_territoire FROM ".TABLE_REGIONHYDRO." rh
					WHERE rh.id_territoire=".$territoire_id." 
					ORDER BY nom ASC";
$regionhydro_query = tep_db_query($sql_link,$sql_regionhydro);
while ($regionhydro = tep_db_fetch_array($regionhydro_query))
{
	$regionhydro_array[$regionhydro['id']] = htmlaccent(html_entity_decode($regionhydro['nom'] ?? $default_string));
}

// TABLE AQUIFERE
$sql_aquifere = "SELECT DISTINCT ga.id, ga.nom, ga.description 
				FROM ".TABLE_GEO_AQUIFERE." ga
				ORDER BY nom ASC";
$aquifere_query = tep_db_query($sql_link,$sql_aquifere);
while ($aquifere = tep_db_fetch_array($aquifere_query))
{
	$aquifere_array[$aquifere['id']] = $aquifere['nom'];
}

//---------------------------------------------------------------
// Enregistrement des données de la station
//if(isset($_POST['save_station'])){require(DIR_WS_FORMULAIRE . 'ctrl_station.php');}

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA STATION

	
// requête sql pour récupérer les données de la station
$sql_station = "SELECT DISTINCT s.id_station, s.id_station_old, s.nom_station, s.nom_court, s.code_station, s.num_irh, 
								s.id_region, s.id_commune, s.site_station, s.vallee_station, s.riviere_station, s.id_aquifere, 
								s.altitude_station, s.orientation_station, s.longitude_station, s.latitude_station, 
								s.utm_station_x, s.utm_station_y, s.ign_station_x, s.ign_station_y, s.lamb_station_x, 
								s.lamb_station_y, s.source_info, s.station_type, s.transmission_station, 
								s.active_station, s.suivi, s.armee,
								s.id_tournee, s.id_regionhydro,
								s.date_installation_station, s.date_fermeture_station, s.description_station
				FROM ".TABLE_STATION." s
				WHERE s.id_station=".$id_station;

$station_query = tep_db_query($sql_link,$sql_station);
$station = tep_db_fetch_array($station_query);

$id_eq_type = 0;
$id_regionhydro = 0;
$id_region = 0;
$id_commune = 0;
$id_tournee = 0;

if(isset($station))
{	
	$id_station_old = $station['id_station_old'];

	$nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));	
	$nom_court =  htmlaccent(html_entity_decode($station['nom_court'] ?? $default_string));
	$code_station =  html_entity_decode($station['code_station'] ?? $default_string);	
	$num_irh = html_entity_decode($station['num_irh'] ?? $default_string);	
	
	$id_region =  $station['id_region']; // Ile pour PF - Province pour NC
	$id_commune =  $station['id_commune'];	
	$site_station =  htmlaccent(html_entity_decode($station['site_station'] ?? $default_string));	
	$id_aquifere =  $station['id_aquifere'];	
	
	$id_eq_type =  $station['station_type']; // Type de données	
	$nom_data_type =  $eq_type_array[$id_eq_type]['nom_eq_type'];	
	$type_color_border =  $eq_type_array[$id_eq_type]['type_color_border'];

	$vallee_station =  htmlaccent(html_entity_decode($station['vallee_station'] ?? $default_string));	
	$riviere_station =  htmlaccent(html_entity_decode($station['riviere_station'] ?? $default_string));	

	$id_tournee = $station['id_tournee'];
	$id_regionhydro = $station['id_regionhydro'];
	
	$altitude_station = '';
	if(tep_not_null($station['altitude_station'])){$altitude_station =  str_replace(',', '.', $station['altitude_station']);}	
	if(is_numeric($altitude_station)){$altitude_station =  number_format(floatval($altitude_station),3);	}
	else{$altitude_station = '';}
	
	$orientation_station =  $station['orientation_station'];

	// Coordonnées GPS

	$longitude_station = '';
	if(tep_not_null($station['longitude_station'])){$longitude_station =  str_replace(',', '.', $station['longitude_station']);}	
	$longitude_station = str_replace(["Ã", "", "Â"], "", $longitude_station);
	/*
	if(is_numeric($longitude_station)){$longitude_station =  number_format(floatval($longitude_station),3);	}
	else{$longitude_station = '';}
	*/
	$latitude_station =  '';
	if(tep_not_null($station['latitude_station'])){$latitude_station =  str_replace(',', '.', $station['latitude_station']);}
	$latitude_station = str_replace(["Ã", "", "Â"], "", $latitude_station);
	/*
	if(is_numeric($latitude_station)){$latitude_station =  number_format(floatval($latitude_station),3);}
	else{$latitude_station = '';}
	*/

	$utm_station_x = '';
	if(tep_not_null($station['utm_station_x'])){$longutm_station_xitude_station =  str_replace(',', '.', $station['utm_station_x']);}	
	/*
	if(is_numeric($utm_station_x)){$utm_station_x =  number_format(floatval($utm_station_x),3);	}
	else{$utm_station_x = '';}
	*/
	$utm_station_y =  '';
	if(tep_not_null($station['utm_station_y'])){$utm_station_y =  str_replace(',', '.', $station['utm_station_y']);}	
	/*
	if(is_numeric($utm_station_y)){$utm_station_y =  number_format(floatval($utm_station_y),3);}
	else{$utm_station_y = '';}
	*/

	$ign_station_x = '';
	if(tep_not_null($station['ign_station_x'])){$ign_station_x =  str_replace(',', '.', $station['ign_station_x']);}	
	/*
	if(is_numeric($ign_station_x)){$ign_station_x =  number_format(floatval($ign_station_x),3);	}
	else{$ign_station_x = '';}
	*/
	$ign_station_y =  '';
	if(tep_not_null($station['ign_station_y'])){$ign_station_y =  str_replace(',', '.', $station['ign_station_y']);}	
	if(is_numeric($ign_station_y)){$ign_station_y =  number_format(floatval($ign_station_y),3);}
	else{$ign_station_y = '';}

	$lamb_station_x = '';
	if(tep_not_null($station['lamb_station_x'])){$lamb_station_x =  str_replace(',', '.', $station['lamb_station_x']);}	
	/*
	if(is_numeric($lamb_station_x)){$lamb_station_x =  number_format(floatval($lamb_station_x), 3, '.', '');}
	else{$lamb_station_x = '';}
	*/
	$lamb_station_y =  '';
	if(tep_not_null($station['lamb_station_y'])){$lamb_station_y =  str_replace(',', '.', $station['lamb_station_y']);}	
	/*
	if(is_numeric($lamb_station_y)){$lamb_station_y =  number_format(floatval($lamb_station_y), 3, '.', '');}
	else{$lamb_station_y = '';}
	*/

	$source_info = $station['source_info']; // GPS ... à mettre dans formulaire un peu plus tard
	
	// Active / Suivie / Armee

	$active_station = $station['active_station'];
	$suivi_station = $station['suivi'];
	$armee_station = $station['armee'];
	
	$transmission_station = $station['transmission_station']; // Je ne sais plus à quoi cela correspond


	// Dates
			
	if($station['date_installation_station']=='0000-00-00'){$date_installation_station =  "";}
	else{$date_installation_station =  dateus_fr($station['date_installation_station']);}
	
	if($station['date_fermeture_station']=='0000-00-00'){$date_fermeture_station =  "";}
	else{$date_fermeture_station =  dateus_fr($station['date_fermeture_station']);}
	
	$description_station =  $station['description_station'];		
}	
else
{$error_station=true;}
if(isset($_GET['new']) && $_GET['new']==1){$error_station=false;} // si la station est nouvelle pas d'erreur

// ---------------------------------------------------

// EDITION HTML

// Indication d'affichage de la page en HTML
require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

	echo "<div id='contenu_info' style='display:none;'></div>";

	require(DIR_WS_STRUCTURE . 'block_wait.php'); // Block d'attente pendant les interrogations au serveur
    require(DIR_WS_BOX . 'block_img.php'); // Block pour affichage des informations sur les Chroniques
	require(DIR_WS_BOX . 'block_info_chron.php'); // Block pour affichage des informations sur les Chroniques
	require(DIR_WS_BOX . 'block_history_chron.php'); // Block pour affichage des informations sur les Chroniques

	require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
	include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

	echo "<div id='contour_general'>";

		echo "<div id='contenu_centre'>";
			
			echo "<div id='contenu_box2'>";

				if(!$error_station)
				{
					// FORMULAIRE

					echo "<form id='formStation'>";

						echo "<input type='hidden' value='".$id_station."' name='id_station' id='id_station'>";
						echo "<input type='hidden' value='".$id_user."' name='id_user_agent'>";
						echo "<input type='hidden' value='".$territoire_id."' name='territoire_id'>";
					
						// Titre de la page avec variation en fonction d'une Station existante ou d'une nouvelle station à créer
						echo "<h1>";
						
							if($modif)
							{
								echo "<span style=font-weight:bold;>".htmlaccent($nom_data_type.' - Station de mesure : ')." </span><span>".htmlaccent($code_station.' - '.$nom_station)."</span>";
								
								echo "<input type='button' class='button' id='save_station' name='save_station' style='float:right;' value='Enregistrer' />";
								/*
								echo "<p style='float:right;margin-right:2%;'>";
									echo "<img src='".DIR_WS_IMG_ICO."xls.png' 
											style='width:30px;cursor:pointer;' 
											title='".htmlaccent('Télécharger les données de la Station')."'
											onClick=\"downloadStation_xls('".$id_station."')\";>";
								echo "</p>";
								*/
							}
							else{
								echo "<span>".htmlaccent('Nouvelle Station de mesure')."</span>";

								echo "<input type='button' class='button' id='save_station' name='save_station' style='float:right;' value='Enregistrer' />";
							}

						echo "</h1>";				
						
						
						// Affichage de plusieurs onglet (Suivi (Métat Données) / Fiche de Saisie / Repère puits pour la piézo. )
						echo "<div id='onglet'>";
							echo "<ul id='menu_onglet'>";
							
								if($modif)
								{
									if($id_eq_type == 5) // Station Piézométrie : on affiche les repères
									{
										
										echo "<li onClick=\"javascript:ChangeOnglet_2(2, 6, 'onglet-', 'contenu-');\" id='onglet-2'  style='width:50px;' class='actif' >".htmlaccent('Suivi')."</li>\n";
										echo "<li onClick=\"javascript:ChangeOnglet_2(1, 6, 'onglet-', 'contenu-');\" id='onglet-1' style='width:50px;' >".htmlaccent('Fiche')."</li>\n";
										echo "<li onClick=\"javascript:ChangeOnglet_2(3, 6, 'onglet-', 'contenu-');\" id='onglet-3'  style='width:70px;'  >".htmlaccent('Repère puits')."</li>\n";								
										echo "<li onClick=\"javascript:ChangeOnglet_2(4, 6, 'onglet-', 'contenu-');\" id='onglet-4'  style='width:70px;'  >".htmlaccent('Caract. Puits')."</li>\n";														
										echo "<li onClick=\"javascript:ChangeOnglet_2(5, 6, 'onglet-', 'contenu-');\" id='onglet-5' style='width:50px;' >".htmlaccent('Accès')."</li>\n";
										echo "<li onClick=\"javascript:ChangeOnglet_2(6, 6, 'onglet-', 'contenu-');\" id='onglet-6'  style='width:50px;'  >".htmlaccent('Photos')."</li>\n";
									}
									else
									{
										echo "<li onClick=\"javascript:ChangeOnglet_2(2, 4, 'onglet-', 'contenu-');\" id='onglet-2' style='width:50px;' class='actif' >".htmlaccent('Suivi')."</li>\n";
										echo "<li onClick=\"javascript:ChangeOnglet_2(1, 4, 'onglet-', 'contenu-');\" id='onglet-1' style='width:50px;' >".htmlaccent('Fiche')."</li>\n";
										echo "<li onClick=\"javascript:ChangeOnglet_2(3, 4, 'onglet-', 'contenu-');\" id='onglet-3' style='width:50px;' >".htmlaccent('Accès')."</li>\n";
										echo "<li onClick=\"javascript:ChangeOnglet_2(4, 4, 'onglet-', 'contenu-');\" id='onglet-4'  style='width:50px;'  >".htmlaccent('Photos')."</li>\n";
									}
								}
								else
								{
									echo "<li onClick=\"javascript:ChangeOnglet_2(1, 1, 'onglet-', 'contenu-');\" id='onglet-1' style='width:50px;' class='actif'>".htmlaccent('Fiche')."</li>\n";
								}
												
							echo "</ul>";
						
						
							if($modif)
							{
								echo "<div id='contenu-2' class='contenu'>";
							
									require(DIR_WS_STATION . 'form_station_2.php'); // Page de suivi (Metadonnées)
						
								echo "</div>";
								
								echo "<div id='contenu-1' class='contenu' style='display:none;'>";
							
									require(DIR_WS_STATION . 'form_station_1.php'); // Page Fiche pour saisie 
							
								echo "</div>";

								$num_onglet = 3;

								if($id_eq_type == 5) // Station Piézométrie : on affiche les repères
								{
									echo "<div id='contenu-".$num_onglet."' class='contenu' style='display:none;'>";
							
										require(DIR_WS_STATION . 'form_station_repere.php'); // Page des repères pour la piézométrie
								
									echo "</div>";
									$num_onglet++;

									echo "<div id='contenu-".$num_onglet."' class='contenu' style='display:none;'>";
							
										require(DIR_WS_STATION . 'form_station_caracteristique.php'); // Page des caractéristiques pour la piézométrie
								
									echo "</div>";
									$num_onglet++;
								}


								echo "<div id='contenu-".$num_onglet."' class='contenu' style='display:none;'>";
							
									require(DIR_WS_STATION . 'form_station_access.php'); // Page photos pour la station
							
								echo "</div>";
								$num_onglet++;

								echo "<div id='contenu-".$num_onglet."' class='contenu' style='display:none;'>";
							
									require(DIR_WS_STATION . 'form_station_photos.php'); // Page photos pour la station
							
								echo "</div>";
							}
							else
							{
								echo "<div id='contenu-1' class='contenu'>";
							
									require(DIR_WS_STATION . 'form_station_1.php'); // Page Fiche pour saisie 
							
								echo "</div>";
							}
													
						echo "</div>";
						
					echo "</form>\n"; // Balise de fin de formulaire
				}
				else
				{
					echo "<h1>";
						echo "<span>";
							echo "Fiche : Station de mesure";
						echo "</span>";
					echo "</h1>";

					echo "<div id='boxpopup' style='padding:10px;'>\n";
						echo "<p class='alert' >";
							echo "Aucune station n'a été trouvée";
						echo "</p>";

						echo "<p style='margin-top:15px;'>";
							echo "<a href='list_stations.php' style='font-size:12px;'>";
								echo ">>  "."Retourner à la liste des stations";
							echo "</a>";	
						echo "</p>";
					echo "</div>";
				}
				
				
		
			echo "<hr>";
			echo "</div>";
			
		
		echo "<hr>";
		echo "</div>";
			
		
	echo "<hr>";
	echo "</div>";
	
	// Pied de page
	require('include/application_bottom.php'); 
	
echo "</body>";

echo "</html>";

?>	

<script>

	// Initialisation des variables
	var boxWait = document.getElementById('box_wait'); // Attente lors des opérations sur les RA, occupe l'ensemble de la page
	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info
	var plot = document.getElementById('plot');

	var buttonSaveStation = document.getElementById('save_station');
	var buttonPDF = document.getElementById('button_pdf');
	var waitPDF = document.getElementById('waitPDF');
	var textPDF = document.getElementById('textPDF');

	var idTerritoire = <?php echo $territoire_id; ?>;
	var idStation = <?php echo $id_station; ?>; 
	var idEqType = <?php echo $id_eq_type; ?>; 


	buttonSaveStation.addEventListener('click', function(event) {saveStation(event);});

	function saveStation(event)
	{
		// Empêcher la soumission du formulaire
		event.preventDefault();

		boxWait.style.display = 'block';	

		var form = document.getElementById('formStation'); // Récupérer l'élément du formulaire		
		var formData = new FormData(form); // Créer un objet FormData à partir du formulaire

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/station/process_station_save.php", true);

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				erreur = jsonResponse['erreur'];
				newStation = jsonResponse['new_station'];
				idStation = jsonResponse['id_station'];
				msg_info = jsonResponse['msg_info'];

				if(!erreur)
				{
					document.getElementById('id_station').value = idStation;
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					// Redirect to the new URL - modif
					console.log(newStation);
					if(newStation){window.location.href = 'modif_station?ref=' + idStation;}                                        
				}
				else
				{
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #930000'; // bordure en rouge

				}

				boxWait.style.display = 'none';
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(formData);
	}

	// ---------------------------------------
    // Function pour le téléchargement des Informations Stations
    function downloadStation_xls(listStation)
    {
		cheminFolder = 'data/export/temp';

        // Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							idTerritoire: idTerritoire,
							listStation: listStation,
							cheminFolder: cheminFolder,
                        };

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/export/process_station_download_xls.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4)
            {
                if (xhr.status === 200) 
                {
                    // Analyser la réponse JSON
                    var jsonResponse = JSON.parse(xhr.responseText);  

                    if(jsonResponse['statut'])
                    {
                        // Créer un lien invisible pour déclencher le téléchargement
                        var downloadLink = document.createElement('a');
                        downloadLink.href = cheminFolder+'/'+jsonResponse['xlsFile']; // URL du fichier CSV
                        downloadLink.download = jsonResponse['xlsFile']; // Nom du fichier
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        document.body.removeChild(downloadLink);
                    }
                    else 
                    {
                        contenuInfo.innerHTML  = 'Erreur lors de la génération du fichier.';
                        contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
                        contenuInfo.style.display = 'block';
                    }

                } 
                else 
                {
                    contenuInfo.innerHTML  = 'Erreur lors de la requête au serveur.';
                    contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
                    contenuInfo.style.display = 'block';
                }
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
    }


	// ------------------------------------------------------------------------------------ 
	// Bouton pour génér l'action du bouton le PDF
	if(buttonPDF)
	{
		buttonPDF.addEventListener('click', function () {

			
			// Si le graphique existe et contient des données	
			if (plot && plot.data && plot.data.length > 0) 
			{
				// Récupérer les dimensions du graphique		
				
				var boundingBox = plot.getBoundingClientRect();
				var width = boundingBox.width;
				var height = boundingBox.height;

				// Ajuster les dimensions en conservant la proportion
				var targetWidth = width; // Largeur souhaitée
				var targetHeight = (height / width) * targetWidth;

				//var targetWidth = 800; // Largeur souhaitée
				
				Plotly.toImage(plot, {
											format: 'png',   // Format de l'image ('jpeg', 'png', 'svg', 'webp')
											width: targetWidth,	// Largeur de l'image en pixels	
											height: targetHeight,		
										}).then(function (dataUrl) {
											envoyerPDF(dataUrl);
										});

			} else 
			{
				// Pas de graphique ou données vides
				envoyerPDF(null); // Envoyer une requête sans graphique
			}
		});
	}


	function envoyerPDF(graphImage) 
	{	
		textPDF.style.display = 'none';
		waitPDF.style.display = 'block';

		// Mise au format JSON des données à envoyer au serveur
		var dataToSend = {
			territoire_id: <?php echo $territoire_id; ?>,
			territoire_nom: <?php echo json_encode($territoire_nom); ?>,
			territoire_region: <?php echo json_encode($territoire_region); ?>,
			timezone_php: <?php echo json_encode($timezone_php); ?>,
			id_user: <?php echo $id_user; ?>,
			//html_tab_data_station: <?php //echo json_encode($html_tab_data_station); ?>,
			html_tab_data_station: html_tab_data,
			html_tab_code_cal: <?php echo json_encode($html_tab_code_cal); ?>,
			//html_tab_code_cal: html_tab_code_cal,
			chron_data_array: <?php echo json_encode($chron_data_array); ?>,
			//graphImage: 'null',
			graphImage: graphImage,
			idStation: <?php echo $id_station; ?>
		};

		// Convertir l'objet en JSON
		var jsonData = JSON.stringify(dataToSend);

		// Effectuer une requête AJAX asynchrone
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "include/structure/station/process_station_pdf.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

		xhr.onreadystatechange = function() 
		{
			if (xhr.readyState === 4 && xhr.status === 200) 
			{				
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);

				textPDF.style.display = 'block';
				waitPDF.style.display = 'none';

				if (jsonResponse['status'] === 'success') 
				{
					// Ouvrir le PDF généré avec le nom personnalisé
					$linkPDF = 'data/pdf/' + jsonResponse['fileName'];
					window.open($linkPDF, '_blank');
				} else 
				{
					msg_info = jsonResponse['msg_info'];
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
				}

			}
		};

		// Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
		xhr.send(jsonData);	
	}


</script>