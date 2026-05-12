<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Parti FILTRE des stations
Cette page initialise les variables qui sont utilisé dans les filtres et collecte les données issues du formulaire de choix
- Région (Privince ou îles) / Commune / Tournee / RégionHydro (BV) / Rivière
- Type de Données (Pluie, Débit, Piézo)
- Stations Actives (ou historique) / Stations Mesures en continu (ou ponctuelles) / Station en fonctionnement (ou en panne)

On appelle également les données de la BDD qui sont utilise dans les traitements des filtres et même dans l'affichage des pages

----------------------------------------
*/

//---------------------------------------------------------------
// INITIALISATION DES PARAMETRES ET RECUPERATION DES DONNEES FORMULAIRES

// Search Station - Champs libre
$search_station = '';
$where_search = '';

// Type de données (Hydro, Pluvio, Piezo)
$select_type_encours = 0;
$where_and_type = '';

if($affiche_select_type)
{
	if(isset($_POST['select_type_data']) && $_POST['select_type_data']>0)
	{
		$select_type_encours = $_POST['select_type_data'];
		$where_and_type = " AND s.station_type=".$select_type_encours;
	}
}

if($affiche_search)
{
	if(isset($_POST['search_station']) || isset($_GET['search_station']) || isset($_GET['search_st']))
	{
		if(isset($_POST['search_station'])){$search_station = post_secure($sql_link,$_POST['search_station']);}
		if(isset($_GET['search_station'])){$search_station = post_secure($sql_link,$_GET['search_station']);}
		if(isset($_GET['search_st'])){$search_station = post_secure($sql_link,$_GET['search_st']);}
		
		$where_search = search_station($search_station,'');	
	}
}

// Région : Province pour NC - Iles pour PF et WF
$select_region_encours = 0;
$where_and_region = '';
$where_and_region_commune = '';

if(isset($_POST['select_region']) && $_POST['select_region']>0)
{
	$select_region_encours = $_POST['select_region'];
	$where_and_region = " AND s.id_region=".$select_region_encours; // pour la selection station
	$where_and_region_commune = " AND r.id_region=".$select_region_encours; // pour la selection region dans table commune
}

// Commune
$select_commune_encours = 0;
$where_and_commune = '';

if(isset($_POST['select_commune']) && $_POST['select_commune']>0)
{
	$select_commune_encours = $_POST['select_commune'];	
	$where_and_commune = " AND s.id_commune=".$select_commune_encours; // pour la selection commune	
}

// Tournee
$select_tournee_encours = 0;
$where_and_tournee = '';

if(isset($_POST['select_tournee']) && $_POST['select_tournee']>0)
{
	$select_tournee_encours = $_POST['select_tournee'];
	$where_and_tournee = " AND s.id_tournee=".$select_tournee_encours;
}

// Région hydrologique
$select_regionhydro_encours = 0;
$where_and_regionhydro = '';

if(isset($_POST['select_regionhydro']) && $_POST['select_regionhydro']>0)
{
	$select_regionhydro_encours = $_POST['select_regionhydro'];
	$where_and_regionhydro = " AND s.id_regionhydro=".$select_regionhydro_encours;
}

// Rivière
$select_riviere_encours = 0;
$where_and_riviere = '';

if($affiche_select_riviere)
{
	if(isset($_POST['select_riviere']) && $_POST['select_riviere']>0)
	{
		$select_riviere = $_POST['select_riviere'];
		$where_and_riviere = " AND s.id_riviere=".$select_riviere_encours;
	}
}




// Station
$select_station_encours = 0;
$where_and_station = '';

if($affiche_select_station)
{
	if(isset($_POST['select_station']) && $_POST['select_station']>0)
	{
		$select_station_encours = $_POST['select_station'];
		$where_and_station = " AND s.id_station=".$select_station_encours;

		// On récupère le type de données liée à la station sélectionnée
		$sql_station_type = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.station_type
					FROM ".TABLE_STATION." s 
					WHERE s.active_station=1 ". // Seule sont valider les stations actives
						$where_and_station;

		$station_type_query = tep_db_query($sql_link,$sql_station_type);
		$station_type = tep_db_fetch_array($station_type_query);

		$select_type_encours = $station_type['station_type'];
	}
}



// ACTIVE - SUIVI - FONCTIONNEMENT (ARMEE)
$where_and_active = '';
$where_and_suivi = '';
$where_and_armee = '';

if($affiche_select_statut_station)
{
	// Stations active (donc qui fonctionne)
	$select_active_encours = 1;
	$where_and_active = " AND s.active_station=1";

	if(isset($_POST['select_active']))
	{
		$select_active_encours = $_POST['select_active'];
		if($_POST['select_active'] == 0){$where_and_active = "";} 
		if($_POST['select_active'] == 1){$where_and_active = " AND s.active_station=1";} // pour la selection des stations active	
		if($_POST['select_active'] == 2){$where_and_active = " AND s.active_station=0";} // pour la selection des stations non-active	
	}

	// Mesures en continu ou Ponctuelles
	$select_suivi_encours = 0;
	$where_and_suivi = "";

	if(isset($_POST['select_suivi']))
	{
		$select_suivi_encours = $_POST['select_suivi'];
		if($_POST['select_suivi'] == 0){$where_and_suivi = "";} 
		if($_POST['select_suivi'] == 1){$where_and_suivi = " AND s.suivi=1";} // pour la selection des stations Mesures en continu	
		if($_POST['select_suivi'] == 2){$where_and_suivi = " AND s.suivi=0";} // pour la selection des stations mesures ponctuelles	
	}


	$select_armee_encours = 0;
	$where_and_armee = "";

	// Stations Armées (en fonctionnement ou en panne)
	if(isset($_POST['select_armee']))
	{
		$select_armee_encours = $_POST['select_armee'];
		if($_POST['select_armee'] == 0){$where_and_armee = "";} 
		if($_POST['select_armee'] == 1){$where_and_armee = " AND s.armee=1";} // pour la selection des stations Mesures en continu	
	}
}

//---------------------------------------------------------------

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA
// Les données extraites de la BDD sont mis dans des tableaux qui sont faciles d'accès
// Cela permet de gagner bcp de temps de traitement


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
				WHERE r.id_territoire=".$territoire_id.$where_and_region_commune." 
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

// TABLE RIVIERE
$sql_riviere = "SELECT DISTINCT r.id, r.nom, r.id_territoire FROM ".TABLE_RIVIERE." r
					WHERE r.id_territoire=".$territoire_id." 
					ORDER BY nom ASC";
$riviere_query = tep_db_query($sql_link,$sql_riviere);
while ($riviere = tep_db_fetch_array($riviere_query))
{
	$riviere_array[$riviere['id']] = htmlaccent(html_entity_decode($riviere['nom'] ?? $default_string));
}


// TABLE STATION
if($affiche_select_station)
{
	$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station
					FROM ".TABLE_STATION." s 
					JOIN ".TABLE_REGION." r ON s.id_region=r.id_region
					LEFT JOIN ".TABLE_STATION_TO_TOURNEE." st ON st.id_station = s.id_station
					WHERE r.id_territoire=".$territoire_id." AND s.active_station=1 ". // Seule sont valider les stations actives
						$where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_type.$where_and_tournee.
						$where_and_active.$where_and_suivi.$where_and_armee." 
					ORDER BY s.nom_station ASC";

	$station_query = tep_db_query($sql_link,$sql_station);
	while ($station = tep_db_fetch_array($station_query))
	{	
		$nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));
		$code_station =  htmlaccent(html_entity_decode($station['code_station'] ?? $default_string));
		
			
		$station_array[$station['id_station']] = array(
														'code_station' => $code_station,
														'nom_station' => $nom_station
													);	
	}
}

?>


