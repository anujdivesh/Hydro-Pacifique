<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Page d'accueil de la plateforme
----------------------------------------
*/

// Appel du fichier de configuration - Sécurité - Identique pour chaque Page
require('include/application_top.php');


// --------------------------------------------------------
// GESTION DES DONNEES GEOGRAPHIQUES

// Inclure l'autoloader de Composer pour charger Proj4php
require 'vendor/autoload.php';

use proj4php\Proj4php;
use proj4php\Point;
use proj4php\Proj;

// Initialiser Proj4php
$proj4 = new Proj4php();


// Fonction pour effectuer la conversion des coordonnées quelques soient leur origine (Lambert ou UTM) pour arriver au format universel Latitude / Longitude
function convertCoordinates($x, $y, $projFrom, $projTo, $proj4) 
{
    $point = new Point(floatval($x), floatval($y), $projFrom);
    $pointWGS84 = $proj4->transform($projTo, $point);
    return [$pointWGS84->x, $pointWGS84->y];  // Format [lat, long]
}

function dmsToDecimal($dms, $direction) 
{
    // Extraire les degrés, minutes et secondes avec une expression régulière
    preg_match('/(\d+)\D+(\d+)\D+(\d+(\.\d+)?)/', $dms, $parts);

    // Convertir les parties en valeurs numériques
    $degrees = (int)$parts[1];
    $minutes = (int)$parts[2] / 60;
    $seconds = (float)str_replace(',', '.', $parts[3]) / 3600;

    // Calculer la valeur décimale
    $decimal = $degrees + $minutes + $seconds;

    // Inverser le signe si la direction est Sud ou Ouest
    if ($direction === 'S' || $direction === 'W') {
        $decimal *= -1;
    }

    return $decimal;
}

// Récupération des coordonnées liés à l'utilisation de la carte
$mapLong = $territoire_mapLong;
$mapLat = $territoire_mapLat; 
$mapZoom = $territoire_mapZoom;
$mapMinZoom = $territoire_mapMinZoom;

$sql_query = "SELECT map_zoom, map_long, map_lat FROM " . TABLE_USER_COORD . " WHERE id_user = ?";

$stmt = $sql_link->prepare($sql_query);
$stmt->bind_param("i", $id_user);
$stmt->execute();

$result = $stmt->get_result();
$user_coord = $result->fetch_assoc();
if(isset($user_coord))
{
	$mapZoom = (float)$user_coord['map_zoom'];
	$mapLong = (float)$user_coord['map_long'];
	$mapLat = (float)$user_coord['map_lat'];
}

$stmt->close();


// Définir la projection de destination (WGS84, EPSG:4326) pour toutes les conversions
/* Les EPSG ne sont pas bien définit dans les librairies, il vaut mieux les définir manuellement
$fromProjectionLamb_NC = 'EPSG:2975'; // Lambert : projection unique pour l'ensemble de la Nouvelle-Calédonie
$fromProjectionLamb_NC = 'EPSG:29891'; // Lambert Nouvelle-Calédonie
$fromProjectionUTM_NC = 'EPSG:32758 '; // UTM 58S pour Nouvelle-Calédonie
$fromProjectionUTM_PF = 'EPSG:32706'; // UTM 6S pour la Polynésie française. Sachant que ça ne fonctionne que pour Tahiti et les ISLV
$fromProjectionUTM_WF = 'EPSG:32701'; // UTM 1S pour Wallis-et-Futuna
$territoriesProjections['PF']['utm'] = new Proj($fromProjectionUTM_PF, $proj4);  
$territoriesProjections['WF']['utm'] = new Proj($fromProjectionUTM_WF, $proj4);  
*/

// Pour le PHP si gestion des conversion coté serveur
$projWGS84 = new Proj('EPSG:4326', $proj4); // WGS84


// Tableau des projections pour chaque territoire
// Gestion coté Serveur 


$territoriesProjections['NC']['lambert'] = new Proj(
    '+proj=lcc +lat_1=-20.66666666666667 +lat_2=-22.33333333333333 +lat_0=-21.5 +lon_0=166 ' .
    '+x_0=400000 +y_0=300000 +ellps=intl +towgs84=197.025,-193.922,175.185,0,0,0,0 +units=m +no_defs',
    $proj4
);


// Définir la projection UTM Zone 58S (EPSG:32758) pour la Nouvelle-Calédonie
$territoriesProjections['NC']['utm']  = new Proj(
	'+proj=utm +zone=58 +south +datum=WGS84 +units=m +no_defs', 
	$proj4
); 


$territoriesProjections['PF']['utm'] = new Proj(
    '+proj=utm +zone=6 +south +datum=WGS84 +units=m +no_defs', 
    $proj4
);
$territoriesProjections['WF']['utm'] = new Proj(
    '+proj=utm +zone=1 +south +datum=WGS84 +units=m +no_defs', 
    $proj4
);


// UTM Zone 60S (Île du Nord, incluant Auckland, Wellington)
$territoriesProjections['NZ_N']['utm'] = new Proj(
    '+proj=utm +zone=60 +south +datum=WGS84 +units=m +no_defs',
    $proj4
);

// UTM Zone 59S (Île du Sud, incluant Christchurch, Dunedin)
$territoriesProjections['NZ']['utm'] = new Proj(
    '+proj=utm +zone=59 +south +datum=WGS84 +units=m +no_defs',
    $proj4
);



// --------------------------------------------------------

// Initialisation des variables de base pour la sélection des stations
$select_active_encours = 1;
$where_and_active = " AND s.active_station=1";

$select_suivi_encours = 1;
$where_and_suivi = " AND s.suivi=1";

$select_armee_encours = 0;
$where_and_armee = "";

$select_region_encours = 0;
$where_and_region = '';

$select_tournee_encours = 0;
$where_and_tournee = '';

$select_regionhydro_encours = 0;
$where_and_regionhydro = '';

$select_commune_encours = 0;
$where_and_commune = '';
$where_and_commune_station = '';

$where_and_type = '';
$where_and_type_data = '';


//---------------------------------------------------------------
// Récupération des champs de formulaires pour sélection
// Récupération des données TABLES SQL

// Initialisation des var. pour les filtres les plus commun
$affiche_select_type = true;
$affiche_select_tournee = true;
$affiche_search = false;
$affiche_select_riviere = false;
$affiche_select_station = false;
$affiche_select_statut_station = true;
require(DIR_WS_FILTRE . 'filtre_stations_var.php');




// TABLE STATION
// Pour la navigation cartographique


// Récupération de la liste des stations à afficher sur la carte

// Initialisation des variables nécessaires à la construction de la carte
$var_carte_coord_all = ''; // var texte pour définir les coordonnées des stations
$var_marker_all = ''; // var texte pour définir les markers des stations
$var_marker_group_all ='' ; // var texte pour définir lier un markers à un group de markers.

// On s'occupe d'abord du type de station active dans la plateforme
// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, init_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('id_eq_type' => $eq_type_tab['id_eq_type'],
														'init_eq_type' => $eq_type_tab['init_eq_type'],
														'nom_eq_type' => $eq_type_tab['nom_eq_type'],
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_color_border' => $eq_type_tab['type_color_border'],
                                                        'type_color_background' => $eq_type_tab['type_color_background'],
														'type_graph' => $eq_type_tab['type_graph']
                                                    );

	${'legend'.ucfirst($eq_type_tab['init_eq_type'])}=0;
	${'legend'.ucfirst($eq_type_tab['init_eq_type']).'NonActive'}=0;
	${'legend'.ucfirst($eq_type_tab['init_eq_type']).'Desarmee'}=0;
	${'legend'.ucfirst($eq_type_tab['init_eq_type'].'Ponctuel')}=0;													
}



$nb_station = 0;
$nb_station_active = 0;
$nb_station_suivi = 0;
$nb_station_panne = 0;

// TABLE STATION avec les conditions des différents champs de sélection 
$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, 
								s.id_commune, s.vallee_station, s.riviere_station,
                                s. active_station, s.station_type, s.suivi, s.armee,
                                s.date_installation_station, 
                                s.lamb_station_x, s.lamb_station_y, s.utm_station_x, s.utm_station_y, 
                                s.latitude_station, s.longitude_station 
				FROM ".TABLE_STATION." s
				WHERE s.id_territoire=".$territoire_id.$where_and_type.
										$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_tournee.
										$where_and_active.$where_and_suivi.$where_and_armee."
				ORDER BY code_station DESC";
$station_query = tep_db_query($sql_link,$sql_station);
while ($station = tep_db_fetch_array($station_query))
{	
	$id_station = $station['id_station'];
    
    $nom_station = $station['nom_station'];
    $code_station = $station['code_station'];    

	$nom_regionhydro = $station['vallee_station'];    
    //$id_regionhydro = html_entity_decode($station['id_commune'] ?? $default_string);
    //$nom_regionhydro = $regionhydro_array[$id_regionhydro];
    
    
    $id_commune = $station['id_commune'];
    $nom_commune = '';
    if(tep_not_null($id_commune) && isset($commune_array[$id_commune])){$nom_commune = $commune_array[$id_commune];}
    
    $nom_riviere = $station['riviere_station'];
    /* Pour le moment riviere_station est un champs de texte mais il va être modifier en int avec id_rivière et relié à la table Geo_rivietre qui n'existe pas encore
    $id_riviere = html_entity_decode($station['id_riviere'] ?? $default_string);
    $nom_riviere = $riviere_array[$id_riviere];
    */

    $type_data_station = $station['station_type'];  
    
    $active_station = $station['active_station'];  
    $suivi_station = $station['suivi'];  
    $armee_station = $station['armee'];  

	$nb_station++;
	if($active_station > 0){$nb_station_active++;}
	if($suivi_station > 0){$nb_station_suivi++;}
	if($armee_station > 0){$nb_station_panne++;}

    $date_installation = dateus_fr($station['date_installation_station']); 

    
	// ---------------------------------------
    // Récupération des infos du dernier RA, dernier passage

    // On compte le nombre de RA qui ont été validés
    $sql_last_ra = "SELECT id_ra, MAX(date_heure_ra) as latest_date_heure_ra
                    FROM ".TABLE_DATA_RA."						
                    WHERE id_station=".$id_station;

    $last_ra_query = tep_db_query($sql_link,$sql_last_ra);
    $last_ra_tab = tep_db_fetch_array($last_ra_query);

    // Petit code permettant d'obtenir le délais depuis le dernier RA validé
    $last_date_ra_formatted = '';
    $text_delais_last_ra = '';
    $id_ra_last = 0;

    if(isset($last_ra_tab['latest_date_heure_ra']) && ($last_ra_tab['latest_date_heure_ra'] !== null))
    {
        $id_ra_last = $last_ra_tab['id_ra'];
        
        // Crée un objet DateTime pour la dernière date valide
        $last_datetime_ra = new DateTime($last_ra_tab['latest_date_heure_ra']);

        // Formatte la date au format "d-m-Y"
        $last_date_ra_formatted = $last_datetime_ra->format('d-m-Y');

        // Calcule la différence entre latest_date_heure_ra et aujourd'hui
        $delais_last_ra = $today->diff($last_datetime_ra);

        // Affiche le résultat formaté
        if ($delais_last_ra->y > 0) {$text_delais_last_ra .= $delais_last_ra->y . " année(s) ";}
        if ($delais_last_ra->m > 0) {$text_delais_last_ra .= $delais_last_ra->m . " mois ";}
        if ($delais_last_ra->d > 0) {$text_delais_last_ra .= $delais_last_ra->d . " jour(s) ";}
    }

    // ---------------------------------------
    // Récupération et conversion (si nécessaire) des coordonnées géographiques des stations
	
	$var_carte_coord='';

	// On récupère les données de la station
	$latitude = str_replace(",", ".",$station['latitude_station']);  
	$longitude = str_replace(",", ".",$station['longitude_station']);  
	$utm_station_x = str_replace(",", ".",$station['utm_station_x']);  
	$utm_station_y = str_replace(",", ".",$station['utm_station_y']);  
	$lamb_station_x = str_replace(",", ".",$station['lamb_station_x']);
	$lamb_station_y = str_replace(",", ".",$station['lamb_station_y']);  

	
	if(tep_not_null($latitude) && tep_not_null($longitude)) // Cas où latitude et longitude sont disponibles directement
	{		
		if(is_numeric($latitude)){$coords_latitude = $latitude;}
		else{$coords_latitude = dmsToDecimal($latitude, 'S');}
		
		if(is_numeric($longitude)){$coords_longitude = $longitude;}
		else{$coords_longitude = dmsToDecimal($longitude, 'W');}
		
		$convertedCoords = [floatval($coords_longitude), floatval($coords_latitude)];  // Format [lon, lat]
	} 
	else 
	{	
		if (tep_not_null($utm_station_x) && tep_not_null($utm_station_y)) // Utilisation de UTM si disponible 
		{
			// Si les coordonnées UTM sont présentes pour le territoire choisi
			$convertedCoords = convertCoordinates($utm_station_x, $utm_station_y,$territoriesProjections[$territoire_init]['utm'], $projWGS84, $proj4);
			
		}
		else if (tep_not_null($lamb_station_x) && tep_not_null($lamb_station_y)) // Sinon, conversion en latitude/longitude depuis Lambert
		{			
			// Version conversion sur le serveur
			$convertedCoords = convertCoordinates($lamb_station_x, $lamb_station_y,$territoriesProjections[$territoire_init]['lambert'], $projWGS84, $proj4);
		}
	}
 
	// Introduction des coordonnées dans le js pour affichage dans leaflet si la station à des coordonnées
	if(isset($convertedCoords)) 
	{
        $var_carte_coord = "var convertedCoords = ".json_encode($convertedCoords).";";
    
		// Gestion des légendes et des marks sur la carte

		$text_toolTip = '';
		$text_statut = '';
		$var_marker = '';
		$var_marker_group = '';
		$icon_type = '';
            
        // On trouve le statut de la station
		$init_type_data_station = ucfirst($eq_type_array[$type_data_station]['init_eq_type']);

		if($active_station==1)
		{
			$icon_type = 'icon'.$init_type_data_station.'Active';
			${'legend'.$init_type_data_station}++;
			$text_statut .= TEXT_FILTER_STATUTACTIVE;

			if($suivi_station==0)
			{
				$icon_type = 'icon'.$init_type_data_station.'Ponctuel';
				${'legend'.$init_type_data_station.'Ponctuel'}++;
				$text_statut .= ' - '.TEXT_FILTER_SUIVIPONCTUEL;
			}
		}

		if($active_station==0)
		{
			$icon_type = 'icon'.$init_type_data_station.'NonActive';
			${'legend'.$init_type_data_station.'NonActive'}++;
			$text_statut .= TEXT_FILTER_STATUTHISTORIQUE;

			if($suivi_station==0)
			{
				${'legend'.$init_type_data_station.'Ponctuel'}++;
				$text_statut .= ' - '.TEXT_FILTER_SUIVIPONCTUEL;
			}
		}

		if($armee_station==1)
		{
			$icon_type = 'icon'.$init_type_data_station.'Desarmee';
			${'legend'.$init_type_data_station.'Desarmee'}++;
			$text_statut .= ' - <span style=\"color:#B80000;\">'.TEXT_FILTER_ETATPANNE.'</span> ';
		}

        // Box qui s'affiche au survol de la carte sur les markers
		$text_toolTip = 
                    '<div class=\"tooltip-map\">'. 
                        '<h2><span>'.$eq_type_array[$type_data_station]['nom_eq_type'].'</span></h2>'.
                        '<div class=\"tooltip-item\">'.
                            '<p><span>'.TEXT_STATION_NOM.' : </span>'.$nom_station.'</p>'.
                            '<p><span>'.TEXT_STATION_CODE.' : </span>'.$code_station.'</p>'. 

                            '<p style=\"margin-top:10px;\"><span>'.TEXT_FILTER_CITY.' : </span>'.$nom_commune.'</p>'. 
                            '<p><span>'.TEXT_FILTER_BV.' : </span>'.$nom_regionhydro.'</p>';

							
                           
			if(tep_not_null($nom_riviere))
			{
				$text_toolTip .= '<p><span>'.TEXT_FILTER_RIVER.' : </span>'.$nom_riviere.'</p>';
			}

			$text_toolTip .= '<p style=\"margin-top:10px;\">'.
								'<span>'.TEXT_MAP_LONG.' : </span>'.round($convertedCoords[0],3).
								'<span style=\"margin-left:5px;\">'.TEXT_MAP_LAT.' : </span>'.round($convertedCoords[1],3).	
							'</p>'; 
			
			$text_toolTip .= '<p style=\"margin-top:10px;\"><span>'.TEXT_STATION_STATUT.' : </span>'.$text_statut.'</p>';                            
			
			if(tep_not_null($date_installation))
			{
				$text_toolTip .= '<p style=\"margin-top:10px;\"><span>'.TEXT_STATION_DATE_INSTALL.' : </span>'.$date_installation.'</p>';
			} 

			if(tep_not_null($last_date_ra_formatted))
			{
				$text_toolTip .= '<p style=\"margin-top:10px;\"><span>'.TEXT_STATION_DATE_LASTGO.' : </span>'.$last_date_ra_formatted.'</p>';
				$text_toolTip .= '<p><span>'.TEXT_STATION_DELAY_LASTGO.' : </span>'.$text_delais_last_ra.'</p>';
			} 
        
        $text_toolTip .= '  </div>'.
                        '</div>';   


        // Popup plus complet qui s'affiche quand on clique sur un marker
        $text_popup = 
                    '<div class=\"tooltip-map\" >'. 
                        '<h2 style=\"border-radius: 5px;\"><span>'.$eq_type_array[$type_data_station]['nom_eq_type'].'</span></h2>'.
                        
						'<div class=\"tooltip-item\">'.

							'<p><span>'.TEXT_STATION_NOM.' : </span>'.$nom_station.'</p>'.
							'<p><span>'.TEXT_STATION_CODE.' : </span>'.$code_station.'</p>'. 
							'<p style=\"margin-top:10px;\"><span>'.TEXT_FILTER_CITY.' : </span>'.$nom_commune.'</p>'. 
							'<p><span>'.TEXT_FILTER_BV.' : </span>'.$nom_regionhydro.'</p>';
					   
				if(tep_not_null($nom_riviere))
				{
					$text_popup .= '<p><span>'.TEXT_FILTER_RIVER.' : </span>'.$nom_riviere.'</p>';
				}

				$text_popup .= '<p style=\"margin-top:10px;\">'.
								'<span>'.TEXT_MAP_LONG.' : </span>'.round($convertedCoords[0],3).
								'<span style=\"margin-left:5px;\">'.TEXT_MAP_LAT.' : </span>'.round($convertedCoords[1],3).	
							'</p>'; 
				
				$text_popup .= '<p style=\"margin-top:10px;\"><span>'.TEXT_STATION_STATUT.' : </span>'.$text_statut.'</p>';                            
				
				if(tep_not_null($date_installation))
				{
					$text_popup .= '<p style=\"margin-top:10px;\"><span>'.TEXT_STATION_DATE_INSTALL.' : </span>'.$date_installation.'</p>';
				} 

				if(tep_not_null($last_date_ra_formatted))
				{
					$text_popup .= '<p style=\"margin-top:10px;\"><span>'.TEXT_STATION_DATE_LASTGO.' : </span>'.$last_date_ra_formatted.'</p>';
					$text_popup .= '<p><span>'.TEXT_STATION_DELAY_LASTGO.' : </span>'.$text_delais_last_ra.'</p>';
				} 
		
		$text_popup .= '<div class=\"tooltip-ligne\"></div>';     

		$text_popup .= '<p><a href=\"modif_station.php?ref='.$id_station.'\" target=\"_blank\" style=\"font-size:13px;\">'.TEXT_STATION_LINK_FICHE.'</a></p>';
		$text_popup .= '<p><a href=\"data_chron.php?id_st='.$id_station.'\" target=\"_blank\" style=\"font-size:13px;\">'.TEXT_STATION_LINK_DATA.'</a></p>';
        if($id_ra_last > 0)
        {   
            $text_popup .= '<p><a href=\"list_ra.php?search_st='.$code_station.'\" target=\"_blank\" style=\"font-size:13px;\">'.TEXT_STATION_LINK_LAST_RA.'</a></p>';  
        }  
        

        $text_popup .= '</div>'.
                    '</div>';    


        // Définition du marker
        $var_marker = "        
                        var marker".$id_station." = L.marker([convertedCoords[1], convertedCoords[0]],{icon: createCustomIcon(".$icon_type.", [22, 22])})
                                                    .bindTooltip(\"". $text_toolTip."\")
                                                    .bindPopup(\"". $text_popup."\", { minWidth: 400 })
                                                    .addTo(mymap);

						// On insère dans un tableau contenant la liste des markers		
						marker".$id_station.".iconUrl = ".$icon_type.";			
						markers.push(marker".$id_station.");
                        ";	
  

		$var_carte_coord_all .= $var_carte_coord.$var_marker;
		//$var_marker_group_all .= $var_marker_group;
	}
                                   
}

// On prépare la légende pour la carte
// IL FAUDRA AUTOMATISE 9A POUR L ENSEMBLE DES TYPE DE DONNEES - C4EST TROP FIGE LA
$text_legend = 'var legendHTML = \'<div class="legend">' .
'<h4>'.TEXT_MAP_LEGEND_TITLE.'</h4>';
  
foreach($eq_type_array as $key => $value)
{
	$init_type_data_station = $value['init_eq_type'];
	$nom_type_data_station = ucfirst($value['nom_eq_type']);

	if (${'legend'.ucfirst($init_type_data_station)} > 0) 
	{$text_legend .= '<div class="legend-item"><img src="' . DIR_WS_IMG_ICO . 'hydro/icon'.$init_type_data_station.'.png" >'.$nom_type_data_station.' - '.TEXT_FILTER_STATUTACTIVE.'</div>';} 

	if (${'legend'.ucfirst($init_type_data_station).'NonActive'} > 0) 
	{$text_legend .= '<div class="legend-item"><img src="' . DIR_WS_IMG_ICO . 'hydro/icon'.$init_type_data_station.'none.png" >'.$nom_type_data_station.' - '.TEXT_FILTER_STATUTHISTORIQUE.'</div>';} 

	if (${'legend'.ucfirst($init_type_data_station).'Ponctuel'} > 0) 
	{$text_legend .= '<div class="legend-item"><img src="' . DIR_WS_IMG_ICO . 'hydro/icon'.$init_type_data_station.'ponctuel.png" >'.$nom_type_data_station.' - '.TEXT_FILTER_SUIVIPONCTUEL.'</div>';} 

	if (${'legend'.ucfirst($init_type_data_station).'Desarmee'} > 0) 
	{$text_legend .= '<div class="legend-item"><img src="' . DIR_WS_IMG_ICO . 'hydro/icon'.$init_type_data_station.'alert.png" >'.$nom_type_data_station.' - '.TEXT_FILTER_ETATPANNE.'</div>';} 
}

$text_legend .= '</div>\';';


// ----------------------------------------
// Récupération de fichiers TXT pour présentation et infos

$contenu_fichier_presentation = '';
$chemin_fichier_presentation = DIR_WS_TXT.'service_presentation.txt'; // Chemin vers le fichier de présentation du service

// Vérifier si le fichier existe
if (file_exists($chemin_fichier_presentation)) {
    // Lire le contenu du fichier
    $contenu_fichier_presentation = htmlspecialchars(file_get_contents($chemin_fichier_presentation));
}


$contenu_fichier_plateforme = '';
$chemin_fichier_plateforme = DIR_WS_TXT.'plateforme.txt'; // Chemin vers le fichier de présentation du service

// Vérifier si le fichier existe
if (file_exists($chemin_fichier_plateforme)) {
    // Lire le contenu du fichier
    $contenu_fichier_plateforme = htmlspecialchars(file_get_contents($chemin_fichier_plateforme));
}



// ----------------------------------------
// Edition HTML

// En-Tête HTML
require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

	require(DIR_WS_INDEX . 'block_index_affiche.php'); // Block pour affichage d'une fiche RA en premier plan

	require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
	include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

	echo "<div id='contour_general'>";

		echo "<div id='contenu_info' style='display:none;'></div>";
		
		echo "<div id='contenu_centre'>";

			//echo "<div id='contenu_box2' >";	
						//-------------------------
						// Cadre de la Carte Interractive et les options
							
						echo "<div id='cadre_index_cell_map' style='width:98%;height: calc(95vh - 60px);'>";	

							// Bando titre de la boite
							echo "<div style='float:left;width:100%;margin-bottom:10px;border-bottom: 1px solid #aa1002;'>";
							
								echo "<div style='float:left;width:32%;'>";

									echo "<p class='aa' style='text-align:left;height:20px;margin-top:3px;font-size:16px;'>";
										echo TEXT_MAP_TITLE;	
									echo "</p>"; 

								echo "</div>";

								echo "<div style='float:left;width:30%;padding-top:7px;'>";

									echo "<p style='float:left;margin-top:-3px;'>";
										echo "<img id='map_center' src='".DIR_WS_IMG_ICO."tracking.png' 
												style='width:20px;margin-right:15px;cursor: pointer;' 
												title=\"".TEXT_MAP_BACK."\"
												/>";
									echo "</p>";

									echo "<p style='float:left;width:40px;font-weight:bold;'>".TEXT_MAP_ZOOM."</p>";
									echo "<input type='text' id='mapZoom_input' name='mapZoom_input' value='' readonly style='float:left;width:25px;padding:0;font-size:13px;background:none;border:none;'>";
																		
									echo "<p style='float:left;width:40px;font-weight:bold;'>".TEXT_MAP_LONG."</p>";	
									echo "<input type='text' id='mapLong_input' name='mapLong_input'  value='' readonly style='float:left;width:70px;padding:0;font-size:13px;background:none;border:none;'>";
									
									echo "<p style='float:left;width:30px;font-weight:bold;'>".TEXT_MAP_LAT."</p>";
									echo "<input type='text' id='mapLat_input' name='mapLat_input'  value='' readonly style='float:left;width:70px;padding:0;font-size:13px;background:none;border:none;'>";
										
									
								echo "</div>";

								// Capture de carte (IMG)
								echo "<div style='float:left;width:35%;'>";

									// Capture IMG
									echo "<div id='contenu_infos_load' style='width:160px;margin:0;'>";

										echo "<img src='".DIR_WS_IMG_ICO."img.png' style='float:left;width:22px;margin-right:5px;'>"; 
										echo "<p id='captureMap' style='margin-top:5px;'>";
											echo "<a href='#' style='font-size:11px;'>";
												echo TEXT_MAP_SAVEIMG;
											echo "</a>";	
										echo "</p>";							
										echo "<img src='".DIR_WS_IMG."wait.gif' style='width:20px;display:none;' id='waitMap'>"; 

									echo "</div>";

									// Download IMG
									echo "<div id='contenu_download' style='width:130px;margin:0;margin-left:1%;display:none;'>";

										echo "<img src='".DIR_WS_IMG_ICO."download.png' style='float:left;width:22px;margin-right:5px;'>"; 
										echo "<p style='margin-top:5px;'>";
											echo "<a href='#' id='downloadImgMap' style='font-size:11px;'>";
												echo TEXT_MAP_DLIMG;
											echo "</a>";
										echo "</p>";

									echo "</div>";
								
								echo "</div>";
								
							echo "</div>";
							

							// Conteneur pour la carte 
							echo "<div id='map' style='float:left;width:83%;height:calc(100% - 50px);font-size:12px;border:4px solid #000;border-radius: 8px;'></div>";


							// Conteneur pour les options de la carte 							
							echo "<div style='float:right;width:15%;height:85%;overflow-y: auto;'>";

								echo "<div id='boxpopup' class='select-top' style='width:95%;border:none;box-shadow:none;'>\n";

									$lien_form = tep_href_link('index.php');	
									$name_form = 'form_carto';			
									echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";

										echo "<input type='hidden' id='form_carto_src' name='form_carto_src' value='' />"; // Champ caché

										require(DIR_WS_FILTRE . 'filtre_stations_html.php');

									echo "</form>";	
								
								echo "</div>";

								// Affichage nombre de stations ; nbre stations activse ; nbre stations suivies - Cadre jaune
								echo "<div id='contenu_infos' style='width:85%;margin-top:10px;'>";
												
										echo "<p style='padding-bottom:5px;'>";
											echo "<span style='margin:0px;'>".TEXT_FILTER_NBSTATION.' : '.number_format($nb_station,0,'.',' ')."</span>";
											echo "<br><br>";
											echo "<span style='margin:0px;font-weight:normal;'>".TEXT_FILTER_STATUTACTIVE.' : '.number_format($nb_station_active,0,'.',' ')."</span>";		
											echo "<br>";
											echo "<span style='margin:0px;font-weight:normal;'>".TEXT_FILTER_SUIVICONTINU.' : '.number_format($nb_station_suivi,0,'.',' ')."</span>";
											echo "<br>";
											echo "<span style='margin:0px;font-weight:normal;'>".TEXT_FILTER_ETATPANNE.' : '.number_format($nb_station_panne,0,'.',' ')."</span>";
										echo "</p>";

								echo "</div>";

								echo "<div id='contenu_infos_load' style='width:150px;margin-top:15px;padding:5px;'>";

									echo "<img src='".DIR_WS_IMG_ICO."detail.png' style='float:left;width:22px;margin-right:5%;'>"; 
									echo "<p id='affiche_last_ra' style='float:left;margin-top:5px;'>";
										echo "<a href='#' style='font-size:11px;'>";
											echo TEXT_BUTTON_RA;
										echo "</a>";	
									echo "</p>";							

								echo "</div>";

								echo "<hr style='margin:0;padding:0;'>";

								echo "<div id='contenu_infos_load' style='width:150px;padding:5px;'>";

									echo "<img src='".DIR_WS_IMG_ICO."save.png' style='float:left;width:22px;margin-right:5%;'>"; 
									echo "<p id='affiche_last_import' style='float:left;margin-top:5px;'>";
										echo "<a href='#' style='font-size:11px;'>";
											echo TEXT_BUTTON_IMPORT;
										echo "</a>";	
									echo "</p>";							

								echo "</div>";

							echo "</div>";
							
						echo "<hr>";
						echo "</div>";


						//-------------------------
						// Modules d'affichage et d'informations rapides
						
						

						//-------------------------
						// En-dessous, Informations générales sur la plateforme

						/*
						echo "<div id='col_left' style='float:left;width:50%; '>";

							echo "<div id='cadre_index_cell'  style='width:100%;height:300px;'>";	
								
								echo "<p class='aa' style='border-bottom: 1px solid #aa1002;'>".htmlaccent(TITRE_SITE)."</p>"; 
								echo "<p>".htmlaccent($contenu_fichier_plateforme)."</p>";
								
							echo "</div>";
						
						echo "</div>";

						echo "<div id='col_left' style='float:left;width:44%; margin-left:3%;'>";

							echo "<div id='cadre_index_cell'  style='width:100%;height:300px;'>";
								
								echo "<p class='aa' style='border-bottom: 1px solid #aa1002;'>".htmlaccent(TITLE_SERVICE)."</p>"; 

								echo "<div style='float:left;width:100%;height:260px;margin-top:0px;overflow-y:auto;' >";
									echo "<p>".htmlaccent($contenu_fichier_presentation)."</p>";
								echo "</div>";

							echo "</div>";			

						echo "</div>";
						*/
					
					echo "</div>";
								
			//echo "<hr>";
			//echo "</div>";		
		
		
		echo "</div>";

	echo "</div>";

	//require('include/application_bottom.php'); 

echo "</body>";

echo "</html>";

?>	

<script type="text/javascript" src="include/javascript/leaflet/leaflet.js"></script> <!-- Appel du script pour la carte leaflet.js -->
<script type="text/javascript" src="include/javascript/leaflet/markercluster/leaflet.markercluster.js"></script> <!-- Appel du script pour de groupement des mark dans leaflet -->
<script type="text/javascript" src="include/javascript/leaflet/fullscreen/Leaflet.fullscreen.js"></script> <!-- Appel du script pour l'affichage plein écran de la carte -->
<script type="text/javascript" src="include/javascript/coordmap/proj4.js"></script> <!-- Inclure la bibliothèque proj4js pour la conversion de coordonnées -->
<script type="text/javascript" src="include/javascript/coordmap/epsg.io_2154.js"></script> <!-- Définitions de projection pour Lambert 93 -->
<script type="text/javascript" src="include/javascript/leaflet/leaflet-image.js"></script> <!-- Appel du script pour enregistrer en image une carte leaflet -->


<script>

	var id_user = <?php echo json_encode($id_user); ?>;

	var afficheLastRA = document.getElementById('affiche_last_ra');
	var afficheLastImport = document.getElementById('affiche_last_import');

	var boxData = document.getElementById('box_data');
	var titleBox = document.getElementById('title_box');
	var contenuBox = document.getElementById('cadre_index_cell');
	var waitBox = document.getElementById('cadre_wait');
	
	
	afficheLastRA.onclick = function() 
	{
		boxData.style.display = 'block';
		titleBox.textContent = 'Derniers Rapports d\'Activités (RA)';
		waitBox.style.display = 'block';

		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							territoireId: '<?php echo $territoire_id; ?>'
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/index/process_index_last_ra.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {	
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);
				js_html = jsonResponse['js_html'];
					
				contenuBox.innerHTML = js_html;
				waitBox.style.display = 'none';	
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
	};

	afficheLastImport.onclick = function() 
	{
		boxData.style.display = 'block';
		titleBox.textContent = 'Dernières donnnées importées';
		waitBox.style.display = 'block';

		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							territoireId: '<?php echo $territoire_id; ?>'
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/index/process_index_last_import.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {	
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);
				js_html = jsonResponse['js_html'];
					
				contenuBox.innerHTML = js_html;
				waitBox.style.display = 'none';	
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
	};

	var territoire_init = '<?php echo $territoire_init; ?>';
	var territoireMapLong = '<?php echo $territoire_mapLong; ?>';
	var territoireMapLat = '<?php echo $territoire_mapLat; ?>';
	var territoireMapZoom = '<?php echo $territoire_mapZoom; ?>';
	var mapLong = <?php echo $mapLong; ?>;
	var mapLat = <?php echo $mapLat; ?>;
	var mapZoom = <?php echo $mapZoom; ?>;
	var mapMinZoom = <?php echo $mapMinZoom; ?>;
	var mapZoom_input = document.getElementById('mapZoom_input');
	var mapLong_input = document.getElementById('mapLong_input');
	var mapLat_input = document.getElementById('mapLat_input');
	var mapCenter = document.getElementById('map_center');

	// Tableau pour stocker tous les marqueurs
	var markers = [];

	// Script pour vérifier si le navigateur est connecté et afficher un message d'information le cas échéant

	// Permet de savoir si on le navigateur est onLine
	function updateConnectionStatus() 
	{
		if (!navigator.onLine) 
		{
            document.getElementById('contenu_info').style.display = 'block';
			document.getElementById('contenu_info').innerText = '<?php echo TEXT_POPUP_NOCONNEXION; ?>';
        }
    }

    // Mettre à jour le statut de connexion au chargement de la page
    updateConnectionStatus();

	// Script pour édition de la carte interactive

	function createCustomIcon(iconUrl, size) 
	{
		return L.icon({
			iconUrl: iconUrl,
			iconSize: size,
			iconAnchor: [size[0] / 2, size[1] / 2],
			popupAnchor: [8, -size[1] / 2],
			tooltipAnchor: [size[0], size[1] - 5]
		});
	}

	// Initialiser la carte Leaflet 

	//Variable de centrage
    var centerData = [mapLat,mapLong];
    var zoomFirst = mapZoom;
    var minZoomData = mapMinZoom;
    
    var mymap = L.map('map', {
            center: centerData, // Corrdonnées centrale
            zoom: zoomFirst, // Zoom par défaut
            minZoom: minZoomData, // Limiter le zoom out à la vue initiale de la carte
        });

	// Ajout du contrôle Fullscreen avec des titres personnalisés
	mymap.addControl(new L.Control.Fullscreen({
		title: {
			'false': '<?php echo TEXT_MAP_FULLSCREEN; ?>',
			'true': '<?php echo TEXT_MAP_WINDOWED; ?>'
		},
		position: 'topleft' // Optionnel : position du bouton
	}));

	// -----------------------------------
	// Fonds de cartes

		// Ajouter des couches de fond de carte
		var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
			maxZoom: 18
		});

		/*
		var cartoLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
			attribution: '&copy; <a href="https://www.carto.com/">Carto</a>',
			maxZoom: 18
		}).addTo(mymap);
		*/
		
		var satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
			attribution: '&copy; <a href="https://www.arcgisonline.com/">ArcgisOnline</a>',
			maxZoom: 18
		});

		var openTopoMap = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href="https://opentopomap.org">OpenTopoMap</a>',
			maxZoom: 17
		});

		/*
		var esriTopoMap = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
			attribution: '&copy; <a href="https://www.esri.com">Esri</a>',
			maxZoom: 16
		}).addTo(mymap);
		*/

		// Ajouter la couche OpenStreetMap comme fond de carte par défaut
		satelliteLayer.addTo(mymap);

	// -----------------------------------
	// Configuration des Marks 
	
	// Définition des icônes personnalisées

	// Plu
    var iconPluActive = '<?php echo DIR_WS_IMG_ICO."hydro/iconplu.png"; ?>';
    var iconPluNonActive = '<?php echo DIR_WS_IMG_ICO."hydro/iconplunone.png"; ?>';
    var iconPluPonctuel = '<?php echo DIR_WS_IMG_ICO."hydro/iconpluponctuel.png"; ?>';
    var iconPluDesarmee = '<?php echo DIR_WS_IMG_ICO."hydro/iconplualert.png"; ?>';

    // Hydro
    var iconHydroActive = '<?php echo DIR_WS_IMG_ICO."hydro/iconhydro.png"; ?>';
    var iconHydroNonActive = '<?php echo DIR_WS_IMG_ICO."hydro/iconhydronone.png"; ?>';
    var iconHydroPonctuel = '<?php echo DIR_WS_IMG_ICO."hydro/iconhydroponctuel.png"; ?>';
    var iconHydroDesarmee = '<?php echo DIR_WS_IMG_ICO."hydro/iconhydroalert.png"; ?>';

    // Piezo
    var iconPiezoActive = '<?php echo DIR_WS_IMG_ICO."hydro/iconpiezo.png"; ?>';
    var iconPiezoNonActive = '<?php echo DIR_WS_IMG_ICO."hydro/iconpiezonone.png"; ?>';
    var iconPiezoPonctuel = '<?php echo DIR_WS_IMG_ICO."hydro/iconpiezoponctuel.png"; ?>';
    var iconPiezoDesarmee = '<?php echo DIR_WS_IMG_ICO."hydro/iconpiezoalert.png"; ?>';

	// -----------------------------------
	// Coordonnées géographiques et convertion - JS
	
		/*
		// Gestion côté client si besoin 
		proj4.defs('EPSG:29891', '+proj=lcc +lat_1=-20.66666666666667 +lat_2=-22.33333333333333 +lat_0=-21.5 +lon_0=166 +x_0=400000 +y_0=300000 +ellps=intl +towgs84=197.025,-193.922,175.185,0,0,0,0 +units=m +no_defs');
		proj4.defs('EPSG:2975', '+proj=lcc +lat_1=-21.61666666666667 +lat_2=-20.96666666666667 +lat_0=-21.28333333333333 +lon_0=165.5 +x_0=1600000 +y_0=1600000 +ellps=intl +towgs84=198,-320,-147,0,0,0,0 +units=m +no_defs');
		proj4.defs('EPSG:3163', '+proj=lcc +lat_1=-21 +lat_2=-22.5 +lat_0=-21.5 +lon_0=166.5 +x_0=700000 +y_0=6600000 +ellps=intl +towgs84=198,-320,-147,0,0,0,0 +units=m +no_defs');

		var toProjection = 'EPSG:4326';   // WGS84 (latitude et longitude)
	*/


	// --------------------------------------
	// CHARGER LES STATIONS POUR LA CARTE
	// On ne va pas l'utiliser pour l'instant il faudrait aussi changer le mode de fonctionnement des filtres et ça aura de l'incidence sur toutes les pages
	/*
	function loadStation() 
	{
		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							territoireId: '<?php echo $territoire_id; ?>'
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/index/process_index_map.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {	
				// Analyser la réponse JSON
				var jsonResponse = JSON.parse(xhr.responseText);
				js_map = jsonResponse['js_map'];
					
				//contenuBox.innerHTML = js_map;
				//waitBox.style.display = 'none';
				console.log(js_map);	
				eval(js_map);
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
	};
	loadStation();
	*/

    <?php 
        //echo $var_carte_coord_all; // var texte pour définir les coordonnées des stations
    ?> 


	// Ajouter les contrôles de basemap pour permettre à l'utilisateur de choisir
	var baseMaps = {
		"OpenStreetMap": osmLayer,
    	//"EsriTopoMap": esriTopoMap,
		//"Carto": cartoLayer,
		"OpenTopo": openTopoMap,
    	"Satellite": satelliteLayer
	};


	// -----------------------------------
    // Editer les popup et la légende de la carte en HTML
    <?php 
		echo $var_carte_coord_all; // var texte pour définir les coordonnées des stations
		echo $text_legend;
	?>

    // Créez un contrôle de légende personnalisé Leaflet
	var legendControl = L.Control.extend({
        onAdd: function(map) {
            var div = L.DomUtil.create('div', 'leaflet-control legend-control');
            div.innerHTML = legendHTML;
            return div;
        }
    });
    
    // Ajoutez la légende à la carte
    var legend = new legendControl({ position: 'bottomleft' });
    legend.addTo(mymap);


    // -----------------------------------
    // On génère la carte

	L.control.layers(baseMaps).addTo(mymap);

	mapZoom_input.value = mymap.getZoom();
	var center = mymap.getCenter();
	mapLong_input.value = (center.lng).toFixed(5);
	mapLat_input.value = (center.lat).toFixed(5);

	// Pour enregistrer les coordonnées de la carte à chaque mouvement de la carte
	mymap.on('moveend', function() 
	{
		mapZoom_input.value = mymap.getZoom();
		var center = mymap.getCenter();
		mapLong_input.value = (center.lng).toFixed(5);
		mapLat_input.value = (center.lat).toFixed(5);

		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							idUser: id_user,
							mapZoom: mymap.getZoom(),
							mapLong: (center.lng).toFixed(5),
							mapLat: (center.lat).toFixed(5),
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/index/process_index_map_coord.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");
        xhr.send(JSON.stringify(dataToSend));
	});


	// Pour revenir au zoom initial (coordonnées liées à la version du territoire)
	mapCenter.onclick = function()
	{
		centerData = [territoireMapLat,territoireMapLong];
		//mymap.flyTo(centerData, territoireMapZoom);
		mymap.setView(centerData, territoireMapZoom);
	}

	
	// Fonction pour capturer la carte et permettre le téléchargement de l'image
	var captureMap = document.getElementById('captureMap');
	var downloadImgMap = document.getElementById('downloadImgMap');	
	var contenuDownload = document.getElementById('contenu_download');
	var waitMap = document.getElementById('waitMap');


	function captureMapImage(event)
	{	
		captureMap.style.display = 'none';
		contenuDownload.style.display = 'none';
		downloadImgMap.style.display = 'none';		
		waitMap.style.display = 'block';

		// Capturer la carte sous forme de canvas
		leafletImage(mymap, function(err, canvas) 
		{
			if (err) 
			{
				console.error('Erreur lors de la capture de image :', err);
				return;
			}

			// Convertir le canvas en URL d'image (format PNG)
			var imgData = canvas.toDataURL('image/png');

			// Créer un lien de téléchargement et définir son href
			downloadImgMap.href = imgData;  // Lien vers l'image capturée
			downloadImgMap.download = 'map.png'; // Nom du fichier image
			downloadImgMap.style.display = 'inline'; // Afficher le lien de téléchargement
			contenuDownload.style.display = 'block';
			captureMap.style.display = 'block';
			waitMap.style.display = 'none';
		});
	}

	// Associer la fonction de capture au clic sur le lien
	captureMap.onclick = captureMapImage;

</script>