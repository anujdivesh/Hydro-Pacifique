<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet d'écrire dans un fichier les infos sur l'export
----------------------------------------
*/

// ----------------------------------------------
// nécessaire pour la configuration du script

require('../../config.php');
require('../../database_tables.php');

require('../../function/date.php');	
require('../../function/database.php');	
require('../../function/html_output.php');
require('../../function/general.php');

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');


// Récupération des données JSON envoyées depuis la requête AJAX
$jsonDataInfo = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataInfo = json_decode($jsonDataInfo, true);

// Accéder aux données du tableau récupérer
$list_station_txt = $dataInfo['list_station_txt'];
$date_1 = $dataInfo['date_1'];
$date_2 = $dataInfo['date_2'];

// Initialisation de variables complémentaires
$nb_stations_ref = 0;
$nb_chron_all = 0;
$nb_data_all = 0;

$id_station_encours = 0;

$min_date_all = null;
$max_date_all = null;


// Chargement de table nécessaire au traitement de l'algorithme

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background, type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('id_eq_type' => $eq_type_tab['id_eq_type'],
														'nom_eq_type' => htmlaccent(html_entity_decode($eq_type_tab['nom_eq_type'] ?? $default_string)),
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_color_border' => $eq_type_tab['type_color_border'],
                                                        'type_color_background' => $eq_type_tab['type_color_background'],
														'type_graph' => $eq_type_tab['type_graph']
                                                    );
}

// DATA TYPE AXE 
$sql_data_type_axe = "SELECT DISTINCT id, axe, unite FROM ".TABLE_DATA_TYPE_AXE;
$data_type_axe_query = tep_db_query($sql_link,$sql_data_type_axe);
while ($data_type_axe = tep_db_fetch_array($data_type_axe_query))
{				
	$data_type_axe_array[$data_type_axe['id']] = array('axe' => $data_type_axe['axe'],
														'unite' => $data_type_axe['unite']
														);
} 

// TABLE STATION
$sql_station_all = "SELECT DISTINCT id_station, nom_station, code_station, active_station, station_type
				FROM ".TABLE_STATION;
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
	$station_all_array[$station_all['id_station']] = array('nom_station' => $station_all['nom_station'],
													        'code_station' => html_entity_decode($station_all['code_station'] ?? $default_string),
													        'type_station' => html_entity_decode($station_all['station_type'] ?? $default_string)
                                                        );
}


// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, to_periode, id_chon_periode
				  FROM ".TABLE_TYPE_DATA." 
				  ORDER BY init_type_data ASC";
			  

$type_chron_query = tep_db_query($sql_link,$sql_type_chron);									
while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
{
    $axe_nom = '';
    if(isset($data_type_axe_array[$type_chron_tab['axe_data']]['axe'])){$axe_nom = $data_type_axe_array[$type_chron_tab['axe_data']]['axe'];}

	$type_chron_array[$type_chron_tab['id_data_type']] = array('init_type_data' => $type_chron_tab['init_type_data'],
															'nom_type_data' => $type_chron_tab['nom_type_data'],
															'id_eq_type_data' => $type_chron_tab['id_eq_type_data'],
															'axe_nom' => $axe_nom,
															'unite' => $type_chron_tab['unite'],
															'to_periode' => $type_chron_tab['to_periode'],
															'id_chon_periode' => $type_chron_tab['id_chon_periode']
															);
}

// --------------------------------------
// Requête SQL - Récupération de toutes les données - Type de données (hydro, pluvio, piezo), Stations, Chroniques, RA, JGE, ETL 

// LIST STATION
$sql_station_select = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
                    FROM ".TABLE_STATION." 
                    WHERE id_station IN (".$list_station_txt.") 
                    ORDER BY station_type ASC, nom_station ASC";
$station_select_query = tep_db_query($sql_link,$sql_station_select);


$typedata_temp = 0;

while ($station_select = tep_db_fetch_array($station_select_query))
{	
    // L'affichage se fait par type de données enregistrés par station (Hydro, Plu, Piezo, ...). Ici on ne sélectionne que les types liés aux stations sélectionnées
    if($typedata_temp <> $station_select['station_type'])
    {
        $typedata_array[] = $station_select['station_type'];
        $typedata_temp = $station_select['station_type'];
    }

    ${$typedata_temp.'_station_select_array'}[] = $station_select['id_station'];

	// Requête qui permet de connaitre les données disponibles pour les stations sélectionnées en fonction d'une période. Les stations sont ensuite regroupé par type de mesure (Hydro, Plu, Piezo)
    $sql_chroniques_data = "SELECT COUNT(*) as nb_data, MIN(da.dateheure) as min_date, MAX(da.dateheure) as max_date, 
									dm.id_station, dm.id, dm.id_typedata, td.init_type_data, td.nom_type_data, unite 
							FROM ".TABLE_DATA_ALL." da
							JOIN ".TABLE_DATA_META." dm ON da.id_meta=dm.id	
							JOIN ".TABLE_TYPE_DATA." td ON dm.id_typedata=td.id_data_type 
							WHERE dm.id_station = ".$station_select['id_station']."
							AND da.dateheure >= '".datefr_us($date_1)." 00:00:00'
							AND da.dateheure <= '".datefr_us($date_2)." 23:59:59'
							GROUP BY dm.id_typedata
							ORDER BY td.init_type_data ASC";
	
	$data_chron_query = tep_db_query($sql_link,$sql_chroniques_data);
	while ($data_chron_tab = tep_db_fetch_array($data_chron_query))
	{
		$min_date = '';
		$max_date = '';

		if(isset($data_chron_tab['min_date']) && tep_not_null($data_chron_tab['min_date']))
		{
			$min_date_array = explode(" ",$data_chron_tab['min_date']); 
			$min_date = dateus_fr($min_date_array[0]);
		}

		if(isset($data_chron_tab['max_date']) && tep_not_null($data_chron_tab['max_date']))
		{
			$max_date_array = explode(" ",$data_chron_tab['max_date']); 
			$max_date = dateus_fr($max_date_array[0]);
		}
        
        // On convertit la date de la données au format dateTime pour la comparer avec date_min et date_max
        $datetime_chron = DateTime::createFromFormat('Y-m-d H:i:s', $data_chron_tab['min_date']); 

        // Pour trouver la date la plus ancienne et la plus récente de toutes les données
        if(is_null($min_date_all)){$min_date_all = $datetime_chron;}
        else
        {
            if($datetime_chron < $min_date_all){$min_date_all = $datetime_chron;}
        }

        $datetime_chron = DateTime::createFromFormat('Y-m-d H:i:s', $data_chron_tab['max_date']); 
        if(is_null($max_date_all)){$max_date_all = $datetime_chron;}
        else
        {
            if($datetime_chron > $max_date_all){$max_date_all = $datetime_chron;}
        }
		
		if(tep_not_null($data_chron_tab['init_type_data'])){$init_chron_data = htmlaccent(html_entity_decode($data_chron_tab['init_type_data'] ?? $default_string));}
		else {$init_chron_data = '';}
		if(tep_not_null($data_chron_tab['nom_type_data'])){$nom_chron_data = htmlaccent(html_entity_decode($data_chron_tab['nom_type_data'] ?? $default_string));}
		else {$nom_chron_data = '';}
        if(tep_not_null($data_chron_tab['unite'])){$unite_data = htmlaccent(html_entity_decode($data_chron_tab['unite'] ?? $default_string));}
		else {$unite_data = '';}

		${'chron_data_'.$data_chron_tab['id_station'].'_array'}[] = array('id' => $data_chron_tab['id'],
                                                                        'id_station' => $station_select['id_station'],								
                                                                        'id_chron_data' => $data_chron_tab['id_typedata'],
                                                                        'init_chron_data' => $init_chron_data,
                                                                        'nom_chron_data' => $nom_chron_data,
                                                                        'unite_data' => $unite_data,
                                                                        'nb_data' => $data_chron_tab['nb_data'],
                                                                        'min_date' => $min_date,
                                                                        'max_date' => $max_date
                                                                        );

		if($data_chron_tab['nb_data']>0){$nb_chron_all++;}			
	}			

    // LAB (Pluie - Format NC)
    // Requpete SQL pour récupérer les infos des LAB de la station en cours 
    $sql_lab = "SELECT DISTINCT lab.id, lab.id_station,
                                MIN(lab.date_heure) AS date_heure_lab_min, 
                                MAX(lab.date_heure) AS date_heure_lab_max,
                                COUNT(lab.id) AS nb_lab
                FROM ".TABLE_DATA_LAB." lab
                WHERE lab.id_station=".$station_select['id_station']."
                AND lab.date_heure >= '".datefr_us($date_1)." 00:00:00'
                AND lab.date_heure <= '".datefr_us($date_2)." 23:59:59'";

    $data_lab_query = tep_db_query($sql_link,$sql_lab);
    $data_lab_tab = tep_db_fetch_array($data_lab_query);

    $min_date_lab = '';
    $max_date_lab = '';

    if (!empty($data_lab_tab) && ($data_lab_tab['nb_lab'] > 0))  
    {
        if(isset($data_lab_tab['date_heure_lab_min']) && tep_not_null($data_lab_tab['date_heure_lab_min']))
        {
            $min_date_array = explode(" ",$data_lab_tab['date_heure_lab_min']); 
            $min_date_lab = dateus_fr($min_date_array[0]);
        }

        if(isset($data_lab_tab['date_heure_lab_max']) && tep_not_null($data_lab_tab['date_heure_lab_max']))
        {
            $max_date_array = explode(" ",$data_lab_tab['date_heure_lab_max']); 
            $max_date_lab = dateus_fr($max_date_array[0]);
        }

        ${'lab_'.$station_select['id_station'].'_array'} = array(
                                                                'nb_lab' => $data_lab_tab['nb_lab'],
                                                                'min_date_lab' => $min_date_lab,
                                                                'max_date_lab' => $max_date_lab
                                                                );
    };


    // TOT (Pluie - Format NC)
    // Requpete SQL pour récupérer les infos des TOT de la station en cours 
    $sql_tot = "SELECT DISTINCT tot.id, tot.id_station,
                                MIN(tot.date_heure) AS date_heure_tot_min, 
                                MAX(tot.date_heure) AS date_heure_tot_max,
                                COUNT(tot.id) AS nb_tot
                FROM ".TABLE_DATA_TOT." tot
                WHERE tot.id_station=".$station_select['id_station']."
                AND tot.date_heure >= '".datefr_us($date_1)." 00:00:00'
                AND tot.date_heure <= '".datefr_us($date_2)." 23:59:59'";

    $data_tot_query = tep_db_query($sql_link,$sql_tot);
    $data_tot_tab = tep_db_fetch_array($data_tot_query);

    $min_date_tot = '';
    $max_date_tot = '';


    if (!empty($data_tot_tab) && ($data_tot_tab['nb_tot'] > 0))  
    {
        if(isset($data_tot_tab['date_heure_tot_min']) && tep_not_null($data_tot_tab['date_heure_tot_min']))
        {
            $min_date_array = explode(" ",$data_tot_tab['date_heure_tot_min']); 
            $min_date_tot = dateus_fr($min_date_array[0]);
        }

        if(isset($data_tot_tab['date_heure_tot_max']) && tep_not_null($data_tot_tab['date_heure_tot_max']))
        {
            $max_date_array = explode(" ",$data_tot_tab['date_heure_tot_max']); 
            $max_date_tot = dateus_fr($max_date_array[0]);
        }

        ${'tot_'.$station_select['id_station'].'_array'} = array(
                                                                'nb_tot' => $data_tot_tab['nb_tot'],
                                                                'min_date_tot' => $min_date_tot,
                                                                'max_date_tot' => $max_date_tot
                                                                );
    };


    $ra_true = false;
    $jge_true = false;
    $etl_true = false;
    /*
    $rep_true = false;
    $cte_true = false;
    */
    $diac_true = false;



    
    // RA
    // Requpete SQL pour récupérer les infos des RA de la station en cours 
    
    $sql_ra = "SELECT DISTINCT ra.id_ra, ra.id_station,
                                MIN(ra.date_heure_ra) AS date_heure_ra_min, 
                                MAX(ra.date_heure_ra) AS date_heure_ra_max,
                                COUNT(ra.id_ra) AS nb_ra
                FROM ".TABLE_DATA_RA." ra
                WHERE ra.id_station=".$station_select['id_station']."
                AND ra.date_heure_ra >= '".datefr_us($date_1)." 00:00:00'
                AND ra.date_heure_ra <= '".datefr_us($date_2)." 23:59:59'";
    
    $data_ra_query = tep_db_query($sql_link,$sql_ra);
    $data_ra_tab = tep_db_fetch_array($data_ra_query);

    $min_date_ra = '';
    $max_date_ra = '';

    if (!empty($data_ra_tab) && ($data_ra_tab['nb_ra'] > 0))  
    {
        if(isset($data_ra_tab['date_heure_ra_min']) && tep_not_null($data_ra_tab['date_heure_ra_min']))
        {
            $min_date_array = explode(" ",$data_ra_tab['date_heure_ra_min']); 
            $min_date_ra = dateus_fr($min_date_array[0]);
        }

        if(isset($data_ra_tab['date_heure_ra_max']) && tep_not_null($data_ra_tab['date_heure_ra_max']))
        {
            $max_date_array = explode(" ",$data_ra_tab['date_heure_ra_max']); 
            $max_date_ra = dateus_fr($max_date_array[0]);
        }

        ${'ra_'.$station_select['id_station'].'_array'} = array(
                                                                'nb_ra' => $data_ra_tab['nb_ra'],
                                                                'min_date_ra' => $min_date_ra,
                                                                'max_date_ra' => $max_date_ra
                                                                );
        $ra_true = true;
    }


    // JGE et ETL
    if($station_select['station_type'] == 11) // Uniquement les Stations Hydrométriques
    {
        // JGE
        // Requpete SQL pour récupérer les infos des JGE de la station en cours 
        
        $sql_jge = "SELECT DISTINCT jge.id, jge.id_station,
                                    MIN(jge.datetime) AS date_heure_jge_min, 
                                    MAX(jge.datetime) AS date_heure_jge_max,
                                    COUNT(jge.id) AS nb_jge
                    FROM ".TABLE_DATA_JGE." jge
                    WHERE jge.id_station=".$station_select['id_station']."
                    AND jge.datetime >= '".datefr_us($date_1)." 00:00:00'
                    AND jge.datetime <= '".datefr_us($date_2)." 23:59:59'";
    
        $data_jge_query = tep_db_query($sql_link,$sql_jge);
        $data_jge_tab = tep_db_fetch_array($data_jge_query);

        $min_date_jge = '';
        $max_date_jge = '';

        if (!empty($data_jge_tab) && ($data_jge_tab['nb_jge'] > 0)) 
        {
            if(isset($data_jge_tab['date_heure_jge_min']) && tep_not_null($data_jge_tab['date_heure_jge_min']))
            {
                $min_date_array = explode(" ",$data_jge_tab['date_heure_jge_min']); 
                $min_date_jge = dateus_fr($min_date_array[0]);
            }

            if(isset($data_jge_tab['date_heure_jge_max']) && tep_not_null($data_jge_tab['date_heure_jge_max']))
            {
                $max_date_array = explode(" ",$data_jge_tab['date_heure_jge_max']); 
                $max_date_jge = dateus_fr($max_date_array[0]);
            }

            ${'jge_'.$station_select['id_station'].'_array'} = array(
                                                                    'nb_jge' => $data_jge_tab['nb_jge'],
                                                                    'min_date_jge' => $min_date_jge,
                                                                    'max_date_jge' => $max_date_jge
                                                                    );

            $jge_true = true;
        }


        // ETL
        // Requpete SQL pour récupérer les infos des ETL de la station en cours 
        
        $sql_etl = "SELECT DISTINCT etl.id, etl.id_station,
                                    MIN(etl.datetime_first) AS date_heure_etl_min, 
                                    MAX(etl.datetime_end) AS date_heure_etl_max,
                                    COUNT(etl.id) AS nb_etl
                    FROM ".TABLE_DATA_ETL." etl
                    WHERE etl.id_station=".$station_select['id_station']."
                    AND etl.datetime_first >= '".datefr_us($date_1)." 00:00:00'
                    AND etl.datetime_end <= '".datefr_us($date_2)." 23:59:59'";
    
        $data_etl_query = tep_db_query($sql_link,$sql_etl);
        $data_etl_tab = tep_db_fetch_array($data_etl_query);

        $min_date_etl = '';
        $max_date_etl = '';

        if (!empty($data_etl_tab) && ($data_etl_tab['nb_etl'] > 0)) 
        {
            if(isset($data_etl_tab['date_heure_etl_min']) && tep_not_null($data_etl_tab['date_heure_etl_min']))
            {
                $min_date_array = explode(" ",$data_etl_tab['date_heure_etl_min']); 
                $min_date_etl = dateus_fr($min_date_array[0]);
            }

            if(isset($data_etl_tab['date_heure_etl_max']) && tep_not_null($data_etl_tab['date_heure_etl_max']))
            {
                $max_date_array = explode(" ",$data_etl_tab['date_heure_etl_max']); 
                $max_date_etl = dateus_fr($max_date_array[0]);
            }

            ${'etl_'.$station_select['id_station'].'_array'} = array(
                                                                    'nb_etl' => $data_etl_tab['nb_etl'],
                                                                    'min_date_etl' => $min_date_etl,
                                                                    'max_date_etl' => $max_date_etl
                                                                    );
            
            $etl_true = true;                                                        
        }

    }

    // Station Piezo - Diagraphie 
    
    if($station_select['station_type'] == 5) // Uniquement les Stations Piézométriques
    {
        // Repere
        // Requete SQL pour récupérer les infos des Repere de la station en cours 
        /*
        $sql_rep = "SELECT DISTINCT rep.id, rep.id_station,
                                    MIN(rep.date_debut_valid) AS date_heure_rep_min, 
                                    MAX(rep.date_debut_valid) AS date_heure_rep_max,
                                    COUNT(rep.id) AS nb_rep
                    FROM ".TABLE_STATION_PIEZO_REPERE." rep
                    WHERE rep.id_station=".$station_select['id_station']."
                    AND rep.date_debut_valid >= '".datefr_us($date_1)." 00:00:00'
                    AND rep.date_debut_valid <= '".datefr_us($date_2)." 23:59:59'";
    
        $data_rep_query = tep_db_query($sql_link,$sql_rep);
        $data_rep_tab = tep_db_fetch_array($data_rep_query);

        $min_date_rep = '';
        $max_date_rep = '';

        if (!empty($data_rep_tab) && ($data_rep_tab['nb_rep'] > 0)) 
        {
            if(isset($data_rep_tab['date_heure_rep_min']) && tep_not_null($data_rep_tab['date_heure_rep_min']))
            {
                $min_date_array = explode(" ", $data_rep_tab['date_heure_rep_min'] ?? '');
                $min_date_rep = dateus_fr($min_date_array[0]);
            }

            if(isset($data_rep_tab['date_heure_rep_max']) && tep_not_null($data_rep_tab['date_heure_rep_max']))
            {
                $max_date_array = explode(" ",$data_rep_tab['date_heure_rep_max'] ?? ''); 
                $max_date_rep = dateus_fr($max_date_array[0]);
            }

            ${'rep_'.$station_select['id_station'].'_array'} = array(
                                                                    'nb_rep' => $data_rep_tab['nb_rep'],
                                                                    'min_date_rep' => $min_date_rep,
                                                                    'max_date_rep' => $max_date_rep
                                                                    );
            
            $rep_true = true;                                                        
        }
        */

        // Caractéristique
        // Requete SQL pour récupérer les infos des Caractéristique de la station en cours 
        
        /*
        $sql_cte = "SELECT DISTINCT cte.id, cte.id_station,
                                    MIN(cte.date) AS date_heure_cte_min, 
                                    MAX(cte.date) AS date_heure_cte_max,
                                    COUNT(cte.id) AS nb_cte
                    FROM ".TABLE_STATION_PIEZO_CARACTERISTIQUE." cte
                    WHERE cte.id_station=".$station_select['id_station']."
                    AND cte.date >= '".datefr_us($date_1)." 00:00:00'
                    AND cte.date <= '".datefr_us($date_2)." 23:59:59'";
    
        $data_cte_query = tep_db_query($sql_link,$sql_cte);
        $data_cte_tab = tep_db_fetch_array($data_cte_query);

        $min_date_cte = '';
        $max_date_cte = '';

        if (!empty($data_cte_tab) && ($data_cte_tab['nb_cte'] > 0)) 
        {
            if(isset($data_cte_tab['date_heure_cte_min']) && tep_not_null($data_cte_tab['date_heure_cte_min']))
            {
                $min_date_array = explode(" ",$data_cte_tab['date_heure_cte_min'] ?? '');
                $min_date_cte = dateus_fr($min_date_array[0]);
            }

            if(isset($data_cte_tab['date_heure_cte_max']) && tep_not_null($data_cte_tab['date_heure_cte_max']))
            {
                $max_date_array = explode(" ",$data_cte_tab['date_heure_cte_max'] ?? ''); 
                $max_date_cte = dateus_fr($max_date_array[0]);
            }

            ${'cte_'.$station_select['id_station'].'_array'} = array(
                                                                    'nb_cte' => $data_cte_tab['nb_cte'],
                                                                    'min_date_cte' => $min_date_cte,
                                                                    'max_date_cte' => $max_date_cte
                                                                    );
            
            $cte_true = true;                                                        
        }

        */

        // Diagraphie de conductivité DIAC
        // Requete SQL pour récupérer les infos des Diagraphies de Conductivité
        
        $sql_diac = "SELECT DISTINCT ra.id_station,
                                    MIN(ra.date_heure_ra) AS date_heure_diac_min, 
                                    MAX(ra.date_heure_ra) AS date_heure_diac_max,
                                    COUNT(pp.id) AS nb_diac
                    FROM ".TABLE_DATA_RA_PIEZO_PROFIL." pp
                    JOIN ".TABLE_DATA_RA." ra ON ra.id_ra=pp.id_ra
                    WHERE ra.id_station=".$station_select['id_station']."
                    AND ra.date_heure_ra >= '".datefr_us($date_1)." 00:00:00'
                    AND ra.date_heure_ra <= '".datefr_us($date_2)." 23:59:59'";

        $data_diac_query = tep_db_query($sql_link,$sql_diac);
        $data_diac_tab = tep_db_fetch_array($data_diac_query);

        $min_date_diac = '';
        $max_date_diac = '';

        if (!empty($data_diac_tab) && ($data_diac_tab['nb_diac'] > 0)) 
        {
            if(isset($data_diac_tab['date_heure_diac_min']) && tep_not_null($data_diac_tab['date_heure_diac_min']))
            {
                $min_date_array = explode(" ",$data_diac_tab['date_heure_diac_min'] ?? '');
                $min_date_diac = dateus_fr($min_date_array[0]);
            }

            if(isset($data_diac_tab['date_heure_diac_max']) && tep_not_null($data_diac_tab['date_heure_diac_max']))
            {
                $max_date_array = explode(" ",$data_diac_tab['date_heure_diac_max'] ?? ''); 
                $max_date_diac = dateus_fr($max_date_array[0]);
            }

            ${'diac_'.$station_select['id_station'].'_array'} = array(
                                                                    'nb_diac' => $data_diac_tab['nb_diac'],
                                                                    'min_date_diac' => $min_date_diac,
                                                                    'max_date_diac' => $max_date_diac
                                                                    );
            
            $diac_true = true;                                                        
        }

    }
    

}

// Transformation des dates min et max en chaine de caractère pour l'affichage
if (!is_null($min_date_all))
{
    $date_1 = $min_date_all->format('d-m-Y');
    $month_1 = $min_date_all->format('m');
    $year_1 = $min_date_all->format('Y');

}
if (!is_null($max_date_all))
{
    $date_2 = $max_date_all->format('d-m-Y');
    $month_2 = $max_date_all->format('m');
    $year_2 = $max_date_all->format('Y');

}

$result_html = '';

// On parcours les types de données Pour affichage par colonne (Débit / Pluie / Piezo)
for($td=0;$td<sizeof($typedata_array);$td++)
{
    $id_typedata = $typedata_array[$td];

    $margin_left = '';
    if($td > 0){$margin_left = '0.5%';}
    
    $result_html .= "<div style='float:left;min-width:300px;max-width:380px;padding:0 8px;margin-left:".$margin_left.";background-color:".$eq_type_array[$id_typedata]['type_color_background'].";'>";
    
        // Cadre du titre du type de données
        $result_html .= "<div id='cadre_data_station_lgt' style='padding-bottom:0px;'>";
            $result_html .= "<p class='titre_box' style='float:left;width:50%;font-size:14px;color:".$eq_type_array[$id_typedata]['type_color_border'].";'>";
                $result_html .= $eq_type_array[$id_typedata]['nom_eq_type'];
            $result_html .= "</p>";

            // Permet de sélectionner toutes les chroniques du type de données d'un seul coup
            $result_html .= "<span class='selectAll' style='float:right;font-size:12px;font-weight:normal;cursor:pointer;' onclick='toggleCheckboxes(0,".$id_typedata.",0);'>";
                $result_html .= htmlaccent('Select +/-');
            $result_html .= "</span>";

        $result_html .= "</div>";
        
        // Liste des types de chronique disponibles. Permet de sélectionner qu'un seul type de chronique
        $result_html .= "<div id='boite_small' style='float:left;width:100%;margin-top:10px;'>";		

            $result_html .= "<p style='float:left;width:60%;margin-bottom:0;color:#000;text-align:left;font-size:11px;padding-top:3px;'>".htmlaccent('Sélectionner un type de chronique')."</p>";	
        
            $result_html .= "<select name='select_type_chron_".$id_typedata."' id='select_type_chron_".$id_typedata."' style='float:right;width:40%;'  onchange='handleSelectChange(this);'>";
                                        
                $result_html .= "<option value='-1' >".htmlaccent('-')."</option>";
                
                $selected = '';

                if(isset($type_chron_array))
                {
                    foreach ($type_chron_array as $id_type_chron => $type_chron)
                    {
                        $selected = '';
                        if($type_chron['id_eq_type_data']==$id_typedata)
                        {
                            $result_html .= "<option value='".$id_type_chron."' ".$selected." >".$type_chron['init_type_data']." - ".$type_chron['nom_type_data']."</option>";
                        }
                    }
                }

                if($ra_true)
                {
                    $selected = '';
                    $result_html .= "<option value='ra' ".$selected." >RA - Rapport d'Activité</option>";
                }

                if($jge_true)
                {
                    $selected = '';
                    $result_html .= "<option value='jge' ".$selected." >JGE - Jaugeage</option>";
                }

                if($etl_true)
                {
                    $selected = '';
                    $result_html .= "<option value='etl' ".$selected." >ETL - Etalonnage</option>";
                }

                /*

                if($rep_true)
                {
                    $selected = '';
                    $result_html .= "<option value='rep' ".$selected." >REP - Repère puits</option>";
                }

                if($cte_true)
                {
                    $selected = '';
                    $result_html .= "<option value='cte' ".$selected." >CARACT - Caractéristiques puits</option>";
                }

                */

                if($diac_true)
                {
                    $selected = '';
                    $result_html .= "<option value='diac' ".$selected." >DIAG - Diagraphie de Conductivité</option>";
                }

                
            $result_html .= "</select>";
                
        $result_html .= "</div>";
    
        $print_table_1 = '';
        $print_table_0 = '';

        
        // Affichage d'un tableau avec la liste des chroniques disponibles par station
        for($st=0;$st<sizeof(${$id_typedata.'_station_select_array'});$st++)	
        {
            $id_station_encours = ${$id_typedata.'_station_select_array'}[$st];
            $row=0;
            $print_table = ''; 

            // contenu du tableau
            if(isset(${'chron_data_'.$id_station_encours.'_array'}))
            {
                // Maintenant on parcourt la liste des chroniques par station
                for($md=0;$md<sizeof(${'chron_data_'.$id_station_encours.'_array'});$md++)
                {
                    if(${'chron_data_'.$id_station_encours.'_array'}[$md]['nb_data']>0)
                    {
                        $row++;
                        $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                        $nb_data_chron = ${'chron_data_'.$id_station_encours.'_array'}[$md]['nb_data'];
                        
                        $print_table .= "<tr ".$row_l." >"; 
                        
                            $print_table .= "<td style='height:20px;' title='".${'chron_data_'.$id_station_encours.'_array'}[$md]['nom_chron_data']."'>".${'chron_data_'.$id_station_encours.'_array'}[$md]['init_chron_data']."</td>";
                            $print_table .= "<td style='height:20px;'>".${'chron_data_'.$id_station_encours.'_array'}[$md]['unite_data']."</td>";
                            
                            $print_table .= "<td style='height:20px;'>".number_format($nb_data_chron, 0, '.', ' ')."</td>";	
                            $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_".${'chron_data_'.$id_station_encours.'_array'}[$md]['id_chron_data']."' value='".$nb_data_chron."'>"; // input cacher pour récupérer le nombre de données

                            $print_table .= "<td style='height:20px;'>".${'chron_data_'.$id_station_encours.'_array'}[$md]['min_date']."</td>";	
                            $print_table .= "<td style='height:20px;'>".${'chron_data_'.$id_station_encours.'_array'}[$md]['max_date']."</td>";	
                            $print_table .= "<td style='height:20px;text-align:center;'>";
                                $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_".${'chron_data_'.$id_station_encours.'_array'}[$md]['id_chron_data']."' >";//."_".$nb_data_chron."' >";
                            $print_table .= "</td>";	
                        
                        $print_table .= "</tr>";
                    }
                }
            }		

            // Affichage de la ligne data LAB si elle existe
                
            if(isset(${'lab_'.$id_station_encours.'_array'}))
            {
                $row++;
                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $nb_lab = ${'lab_'.$id_station_encours.'_array'}['nb_lab'];
                $min_date_lab = ${'lab_'.$id_station_encours.'_array'}['min_date_lab'];
                $max_date_lab = ${'lab_'.$id_station_encours.'_array'}['max_date_lab'];

                $print_table .= "<tr ".$row_l." >"; 
                    
                    $print_table .= "<td style='height:20px;' title='".htmlaccent('Données LAB (Pluie)')."'>".htmlaccent('LAB')."</td>";
                    $print_table .= "<td style='height:20px;'>-</td>";

                    $print_table .= "<td style='height:20px;'>".number_format($nb_lab, 0, '.', ' ')."</td>";
                    $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_55' value='".$nb_lab."'>"; // input cacher pour récupérer le nombre de données

                    $print_table .= "<td style='height:20px;'>".$min_date_lab."</td>";	
                    $print_table .= "<td style='height:20px;'>".$max_date_lab."</td>";
                    
                    $print_table .= "<td style='height:20px;text-align:center;'>";
                        $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_55' >";
                    $print_table .= "</td>";	
                    
                $print_table .= "</tr>";
            }

            // Affichage de la ligne data TOT si elle existe
                
            if(isset(${'tot_'.$id_station_encours.'_array'}))
            {
                $row++;
                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $nb_tot = ${'tot_'.$id_station_encours.'_array'}['nb_tot'];
                $min_date_tot = ${'tot_'.$id_station_encours.'_array'}['min_date_tot'];
                $max_date_tot = ${'tot_'.$id_station_encours.'_array'}['max_date_tot'];

                $print_table .= "<tr ".$row_l." >"; 
                    
                    $print_table .= "<td style='height:20px;' title='".htmlaccent('Données TOT (Pluie)')."'>".htmlaccent('TOT')."</td>";
                    $print_table .= "<td style='height:20px;'>-</td>";

                    $print_table .= "<td style='height:20px;'>".number_format($nb_tot, 0, '.', ' ')."</td>";
                    $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_58' value='".$nb_tot."'>"; // input cacher pour récupérer le nombre de données

                    $print_table .= "<td style='height:20px;'>".$min_date_tot."</td>";	
                    $print_table .= "<td style='height:20px;'>".$max_date_tot."</td>";
                    
                    $print_table .= "<td style='height:20px;text-align:center;'>";
                        $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_58' >";
                    $print_table .= "</td>";	
                    
                $print_table .= "</tr>";
            }

            // Affichage de la ligne data RA si elle existe
            if(isset(${'ra_'.$id_station_encours.'_array'}))
            {
                $row++;
                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $nb_ra = ${'ra_'.$id_station_encours.'_array'}['nb_ra'];
                $min_date_ra = ${'ra_'.$id_station_encours.'_array'}['min_date_ra'];
                $max_date_ra = ${'ra_'.$id_station_encours.'_array'}['max_date_ra'];

                $print_table .= "<tr ".$row_l." >"; 
                    
                    $print_table .= "<td style='height:20px;' title='".htmlaccent('Rapport d\'Activité')."'>".htmlaccent('RA')."</td>";
                    $print_table .= "<td style='height:20px;'>-</td>";

                    $print_table .= "<td style='height:20px;'>".number_format($nb_ra, 0, '.', ' ')."</td>";
                    $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_ra' value='".$nb_ra."'>"; // input cacher pour récupérer le nombre de données

                    $print_table .= "<td style='height:20px;'>".$min_date_ra."</td>";	
                    $print_table .= "<td style='height:20px;'>".$max_date_ra."</td>";
                    
                    $print_table .= "<td style='height:20px;text-align:center;'>";
                        $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_ra' >";
                    $print_table .= "</td>";	
                    
                $print_table .= "</tr>";
            }

            // Affichage de la ligne data JGE si elle existe
            if(isset(${'jge_'.$id_station_encours.'_array'}))
            {
                $row++;
                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $nb_jge = ${'jge_'.$id_station_encours.'_array'}['nb_jge'];
                $min_date_jge = ${'jge_'.$id_station_encours.'_array'}['min_date_jge'];
                $max_date_jge = ${'jge_'.$id_station_encours.'_array'}['max_date_jge'];

                $print_table .= "<tr ".$row_l." >"; 
                    
                    $print_table .= "<td style='height:20px;' title='".htmlaccent('Jaugeages')."'>".htmlaccent('JGE')."</td>";
                    $print_table .= "<td style='height:20px;'>-</td>";

                    $print_table .= "<td style='height:20px;'>".number_format($nb_jge, 0, '.', ' ')."</td>";
                    $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_jge' value='".$nb_jge."'>"; // input cacher pour récupérer le nombre de données

                    $print_table .= "<td style='height:20px;'>".$min_date_jge."</td>";	
                    $print_table .= "<td style='height:20px;'>".$max_date_jge."</td>";
                    
                    $print_table .= "<td style='height:20px;text-align:center;'>";
                        $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_jge' >";
                    $print_table .= "</td>";		
                    
                $print_table .= "</tr>";
            }

            // Affichage de la ligne data ETL si elle existe
            if(isset(${'etl_'.$id_station_encours.'_array'}))
            {
                $row++;
                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $nb_etl = ${'etl_'.$id_station_encours.'_array'}['nb_etl'];
                $min_date_etl = ${'etl_'.$id_station_encours.'_array'}['min_date_etl'];
                $max_date_etl = ${'etl_'.$id_station_encours.'_array'}['max_date_etl'];

                $print_table .= "<tr ".$row_l." >"; 
                    
                    $print_table .= "<td style='height:20px;' title='".htmlaccent('Etalonnages')."'>".htmlaccent('ETL')."</td>";
                    $print_table .= "<td class='t_cont_xs' style='height:20px;'>-</td>";

                    $print_table .= "<td style='height:20px;'>".number_format($nb_etl, 0, '.', ' ')."</td>";
                    $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_etl' value='".$nb_etl."'>"; // input cacher pour récupérer le nombre de données

                    $print_table .= "<td style='height:20px;'>".$min_date_etl."</td>";	
                    $print_table .= "<td style='height:20px;'>".$max_date_etl."</td>";
                    
                    $print_table .= "<td style='height:20px;text-align:center;'>";
                        $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_etl' >";
                    $print_table .= "</td>";		
                    
                $print_table .= "</tr>";
            }

            /*
            // Affichage de la ligne data REP si elle existe (Repere)
            if(isset(${'rep_'.$id_station_encours.'_array'}))
            {
                $row++;
                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $nb_rep = ${'rep_'.$id_station_encours.'_array'}['nb_rep'];
                $min_date_rep = ${'rep_'.$id_station_encours.'_array'}['min_date_rep'];
                $max_date_rep = ${'rep_'.$id_station_encours.'_array'}['max_date_rep'];

                $print_table .= "<tr ".$row_l." >"; 
                    
                    $print_table .= "<td style='height:20px;' title='".htmlaccent('Répère puits')."'>".htmlaccent('REP')."</td>";
                    $print_table .= "<td class='t_cont_xs' style='height:20px;'>-</td>";

                    $print_table .= "<td style='height:20px;'>".number_format($nb_rep, 0, '.', ' ')."</td>";
                    $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_rep' value='".$nb_rep."'>"; // input cacher pour récupérer le nombre de données

                    $print_table .= "<td style='height:20px;'>".$min_date_rep."</td>";	
                    $print_table .= "<td style='height:20px;'>".$max_date_rep."</td>";
                    
                    $print_table .= "<td style='height:20px;text-align:center;'>";
                        $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_rep' >";
                    $print_table .= "</td>";		
                    
                $print_table .= "</tr>";
            }

            // Affichage de la ligne data CARACT si elle existe (Caractéristique puits)
            if(isset(${'cte_'.$id_station_encours.'_array'}))
            {
                $row++;
                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $nb_cte = ${'cte_'.$id_station_encours.'_array'}['nb_cte'];
                $min_date_cte = ${'cte_'.$id_station_encours.'_array'}['min_date_cte'];
                $max_date_cte = ${'cte_'.$id_station_encours.'_array'}['max_date_cte'];

                $print_table .= "<tr ".$row_l." >"; 
                    
                    $print_table .= "<td style='height:20px;' title='".htmlaccent('Caractéristiques puits')."'>".htmlaccent('CTE')."</td>";
                    $print_table .= "<td class='t_cont_xs' style='height:20px;'>-</td>";

                    $print_table .= "<td style='height:20px;'>".number_format($nb_cte, 0, '.', ' ')."</td>";
                    $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_cte' value='".$nb_cte."'>"; // input cacher pour récupérer le nombre de données

                    $print_table .= "<td style='height:20px;'>".$min_date_cte."</td>";	
                    $print_table .= "<td style='height:20px;'>".$max_date_cte."</td>";
                    
                    $print_table .= "<td style='height:20px;text-align:center;'>";
                        $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_cte' >";
                    $print_table .= "</td>";		
                    
                $print_table .= "</tr>";
            }
            */

            // Affichage de la ligne data DIAC si elle existe (Caractéristique puits)
            if(isset(${'diac_'.$id_station_encours.'_array'}))
            {
                $row++;
                $row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";

                $nb_diac = ${'diac_'.$id_station_encours.'_array'}['nb_diac'];
                $min_date_diac = ${'diac_'.$id_station_encours.'_array'}['min_date_diac'];
                $max_date_diac = ${'diac_'.$id_station_encours.'_array'}['max_date_diac'];

                $print_table .= "<tr ".$row_l." >"; 
                    
                    $print_table .= "<td style='height:20px;' title='".htmlaccent('Diagraphie de Conductivité')."'>".htmlaccent('DIAG')."</td>";
                    $print_table .= "<td class='t_cont_xs' style='height:20px;'>-</td>";

                    $print_table .= "<td style='height:20px;'>".number_format($nb_diac, 0, '.', ' ')."</td>";
                    $print_table .= "<input type='hidden' name='nb_".$id_station_encours."_".$id_typedata."_diac' value='".$nb_diac."'>"; // input cacher pour récupérer le nombre de données

                    $print_table .= "<td style='height:20px;'>".$min_date_diac."</td>";	
                    $print_table .= "<td style='height:20px;'>".$max_date_diac."</td>";
                    
                    $print_table .= "<td style='height:20px;text-align:center;'>";
                        $print_table .= "<input type='checkbox' name='check_chron[]' value='".$id_station_encours."_".$id_typedata."_diac' >";
                    $print_table .= "</td>";		
                    
                $print_table .= "</tr>";
            }

            
            // en-tête des colonnes du tableau
            if($row>0)
            {
                $print_table_1 .= "<div id='cadre_data_station_lgt'>";

                    $print_table_1 .= "<p class='titre_box' style='float:left;width:78%;margin-bottom:0;'>";
                        $print_table_1 .= "<a href='modif_station.php?ref=".$id_station_encours."' target='_blank' >";
                            $print_table_1 .= $station_all_array[${$id_typedata.'_station_select_array'}[$st]]['code_station']." - ".$station_all_array[${$id_typedata.'_station_select_array'}[$st]]['nom_station'];
                        $print_table_1 .= "</a>";
                    $print_table_1 .= "</p>";
                    
                    //    $print_table_1 .= "<p style='float:left;width:40%;margin-bottom:0;'>";
                    //        $print_table_1 .= "<a href='modif_station.php?ref=".$id_station_encours."' target='_blank' >".htmlaccent('>> Fiche station')."</a>";
                    //    $print_table_1 .= "</p>";
                    
                        $print_table_1 .= "<p style='float:right;width:20%;text-align:right;font-weight:normal;font-size:10px;color:#d9534f;margin-bottom:0;'>";  
                            $print_table_1 .= $row.htmlaccent(' chron.');
                        $print_table_1 .= "</p>";
                
                    $print_table_1 .= "<table id='table_tri' cellspacing='0'>";
                
                        $print_table_1 .= "<thead>";
                            $print_table_1 .= "<tr>";
                                $print_table_1 .= "<th style='width:110px;font-size:11px;'>".htmlaccent(' Chron.')."</th>";
                                $print_table_1 .= "<th style='width:60px;font-size:11px;'>".htmlaccent('Unité')."</th>";						
                                $print_table_1 .= "<th style='width:80px;font-size:11px;'>".htmlaccent('Nb data')."</th>";						
                                $print_table_1 .= "<th style='width:130px;font-size:11px;'>".htmlaccent('Date début')."</th>";										
                                $print_table_1 .= "<th style='width:130px;font-size:11px;'>".htmlaccent('Date fin')."</th>";										
                                $print_table_1 .= "<th style='width:40px;font-size:11px;color:#000;text-align:center;cursor:pointer' onclick='toggleCheckboxes(".${$id_typedata.'_station_select_array'}[$st].",0,0);'>";	
                                    $print_table_1 .= "<span class='selectAll'>".htmlaccent('+/-')."</span>";
                                $print_table_1 .= "</th>";   
                            $print_table_1 .= "</tr>";
                        $print_table_1 .= "</thead>";

                        $print_table_1 .= $print_table;

                    $print_table_1 .= "</table>";	
                
                $print_table_1 .= "</div>";
            }
            else // Si aucune chronique n'existe pour cette station sur cette période
            {
                $print_table_0 .= "<div id='cadre_data_station_lgt' style='font-size:14px;'>";

                    $print_table_0 .= "<p class='titre_box' style='float:left;width:99%;margin-bottom:0;'>";
                        $print_table_0 .= "<a href='modif_station.php?ref=".$id_station_encours."' target='_blank' >";
                            $print_table_0 .= $station_all_array[${$id_typedata.'_station_select_array'}[$st]]['code_station']." - ".$station_all_array[${$id_typedata.'_station_select_array'}[$st]]['nom_station'];
                        $print_table_0 .= "</a>";
                    $print_table_0 .= "</p>";
                    
                    $print_table_0 .= "<p style='margin-bottom:0px;'>";
                        $print_table_0 .= "<span>".htmlaccent('Aucune chronique n\'a été trouvée sur cette période')."</span>";
                    $print_table_0 .= "</p>";
                
                $print_table_0 .= "</div>";						
            }	
        }

        $result_html .= $print_table_1;
        $result_html .= $print_table_0;		

    $result_html .= "<hr>";
    $result_html .= "</div>";
}  


$responseData = array(
    'result_html' =>  $result_html,
    'date_debut' =>  $result_html,
    'date_fin' =>  $result_html
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>