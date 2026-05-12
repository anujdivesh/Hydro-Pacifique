<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Procédure pour afficher dans un RA dans le bloc d'affichage
Processus asynchrone AJAX coté serveur
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
$territoire_id = $dataInfo['territoire_id'];
$timezone_php = $dataInfo['timezone_php'];
$id_user = $dataInfo['id_user'];
$id_ra = $dataInfo['id_ra'];
$where_station = $dataInfo['where_station'];
$check_modif = $dataInfo['check_modif'];
$ra_nav_array = json_decode($dataInfo['ra_nav_json'], true);

//-------------------------
// Gestion du temps en fonction du fuseau horaire du territoire

date_default_timezone_set($timezone_php); 

$today_date = new DateTime(); // Crée un objet DateTime pour la date actuelle
$today_date_formatted = $today_date->format('d-m-Y');  // Formatage de la date (uniquement la partie 'Y-m-d') et stockage dans une variable ou affichage

$today_time = new DateTime(); // Crée un objet DateTime pour la date actuelle
$today_time_formatted = $today_time->format('H:i:s');  // Formatage de la date (uniquement la partie 'Y-m-d') et stockage dans une variable ou affichage

$today_datetime = new DateTime(); // Crée un objet DateTime pour la date actuelle
$today_datetime_formatted = $today_datetime->format('d-m-Y H:i:s'); // Formatage de la date et de l'heure (format complet 'Y-m-d H:i:s') et stockage ou affichage

//-------------------------
$ra_exist = false;
$type_data =  11; // 11 : hydro

if($id_ra > 0) // sinon c'est un nouvel RA
{
    $id_type_ra = $ra_nav_array[$id_ra]['id_type_ra'];
    $prev_id_ra = $ra_nav_array[$id_ra]['prev_id_ra'];
    $prev_type_ra = $ra_nav_array[$id_ra]['prev_type_ra'];
    $next_id_ra = $ra_nav_array[$id_ra]['next_id_ra'];
    $next_type_ra = $ra_nav_array[$id_ra]['next_type_ra'];
    $num_ra = $ra_nav_array[$id_ra]['num_ra'];
    $nb_ra = $ra_nav_array[$id_ra]['nb_ra'];

    $first_id_ra = array_key_first($ra_nav_array); // Récupérer la clé du premier élément du tableau
    $first_type_ra = $ra_nav_array[$first_id_ra]['id_type_ra'];

    $last_id_ra = array_key_last($ra_nav_array); // Récupérer la clé du premier élément du tableau
    $last_type_ra = $ra_nav_array[$last_id_ra]['id_type_ra'];
}
else
{
    $id_type_ra = $type_data;
    $prev_id_ra = 0;
    $prev_type_ra = 0;
    $next_id_ra = 0;
    $next_type_ra = 0;
    $num_ra = 1;
    $nb_ra = 1;

    $first_id_ra = 0; // Récupérer la clé du premier élément du tableau
    $first_type_ra = 0;

    $last_id_ra = 0; // Récupérer la clé du premier élément du tableau
    $last_type_ra = 0;

    $ra_exist = true;
}


//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// TABLE USER
$sql_user_list = "SELECT DISTINCT id, id_statut, login, nom, prenom FROM ".TABLE_USER;
$user_list_query = tep_db_query($sql_link,$sql_user_list);
while ($user_list = tep_db_fetch_array($user_list_query))
{
    $id = $user_list['id'];
    $id_statut = $user_list['id_statut'];
	$login = htmlaccent(html_entity_decode($user_list['login'] ?? $default_string));
	$nom = ucfirst(strtolower(htmlaccent(html_entity_decode($user_list['nom'] ?? $default_string))));
	$prenom = ucfirst(strtolower(htmlaccent(html_entity_decode($user_list['prenom'] ?? $default_string))));

	$user_list_array[$id] = array('id_statut' => $id_statut,
                                    'login' => $login,
                                    'nom' => $nom,
                                    'prenom' => $prenom
                                    );
}

// TABLE STATION
$sql_station_all = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.station_type, s.active_station,
                                    s.id_region, s.id_regionhydro, s.id_riviere, s.id_tournee, s.id_commune
					FROM ".TABLE_STATION." s
                    WHERE s.station_type=".$type_data." ".$where_station."
				    ORDER BY s.nom_station ASC";
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
    $nom_station = htmlaccent(html_entity_decode($station_all['nom_station'] ?? $default_string));

	$station_all_array[$station_all['id_station']] = array('code_station' => $station_all['code_station'],
															'nom_station' => $nom_station,
															'station_type' => $station_all['station_type'],
															);
}

// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_color_background,type_graph 
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
	$eq_type_array[$eq_type_tab['id_eq_type']] = array('nom_eq_type' => $eq_type_tab['nom_eq_type'],
                                                        'unite_eq_type' => $eq_type_tab['unite_eq_type'],
                                                        'valeur_data_type' => $eq_type_tab['valeur_data_type'],
														'type_graph' => $eq_type_tab['type_graph'],
                                                        'type_color_border' => $eq_type_tab['type_color_border'],
                                                        'type_color_background' => $eq_type_tab['type_color_background'],
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

// TABLE AGENT - Appel pour stocker l'info dans un tableau
$sql_agent = "SELECT DISTINCT id, nom, prenom 
                FROM ".TABLE_AGENT." 
                WHERE terrain=1 
				ORDER BY nom ASC";
$agent_query = tep_db_query($sql_link,$sql_agent);
while($agent = tep_db_fetch_array($agent_query))
{
	// Nettoyage des noms et prénoms
    $nom_agent = strtoupper(noaccent(html_entity_decode($agent['nom'] ?? $default_string)));

    $prenom_agent = noaccent(html_entity_decode($agent['prenom'] ?? $default_string));

	$agent_array[$agent['id']] = $prenom_agent." ".$nom_agent;
}

// -------------------------------------------------------------


// Initialisation Variables Globales
$tab_html = '';
$row = 0;

// Initialisation des variables

// GENERALES
$nom_data = $eq_type_array[$type_data]['nom_eq_type'];
$etat_ra = 0;

$date_heure_saisie_fr = $today_datetime_formatted;
$date_ra = $today_date_formatted;
$heure_ra = $today_time_formatted;


$id_agent_user = $id_user;
$id_station = 0;

$name_file_data = '';

// APPAREILS
$type_appareil = '';

$num_appareil = '';
$heure_appareil = '';

$hydro_num_sonde = '';

$nb_octet = '';
$num_batterie = '';
$tension_batterie = '';

// Côte Limnimétrique
$hydro_heure_cote = '';
$hydro_h_sonde = '';
$hydro_h_echelle_1 = ''; 
$hydro_h_echelle_2 = '';        

// NOUVELLE CASSETTE - ça c'est pour du vieux matériel
$num_cassette = '';
$heure_init_cassette = '';
$hydro_h_sonde_cassette = '';

// Contrôle Hydro
$hech_hsonde = '';
$hydro_recalage_sonde = '';
$hydro_recalage_heure_sonde = '';  
$hydro_purge_sonde = 0; // checked box 0 ou 1


// Observations	
$check_jaugeage = 0; // checked box 0 ou 1 
$check_debrouss = 0; // checked box 0 ou 1 
$check_eaubat = 0; // checked box 0 ou 1 
$check_transfert = 0; // checked box 0 ou 1 
$check_deletememory = 0; // checked box 0 ou 1 
$ra_obs = ''; // text
$ra_futur = ''; // text

$obs_file_data = ''; // text 

$check_premarquant = 0; // checked box 0 ou 1
$check_faitmarquant = 0; // checked box 0 ou 1

// variable pour le moment non renseigné... ancien truc
$duree_nb_jour = '';
$duree_nb_heure = '';
$duree_nb_min = '';
$dernier_nb_jour = '';
$dernier_nb_heure = '';
$dernier_nb_min = '';

$agents_complement = '';



if($id_ra > 0)
{    
    // Requête d'accès aux RA
    $sql_RA = "SELECT DISTINCT ra.id_ra, ra.datetime_saisie, ra.id_agent_user, ra.id_station, 
                                ra.date_heure_ra, ra.id_eq_type,
                                ra.type_appareil, ra.num_appareil, ra.heure_appareil, ra.etat_ra, 							    
                                ra.nb_octet, ra.num_batterie, ra.tension_batterie, ra.num_cassette, ra.heure_init_cassette,
                                ra.hydro_heure_cote, ra.hydro_h_sonde, ra.hydro_h_echelle_1, ra.hydro_h_echelle_2, ra.hydro_num_sonde,
                                ra.hydro_h_sonde_cassette, 
                                ra.hydro_recalage_sonde, ra.hydro_recalage_heure_sonde, ra.hydro_purge_sonde, ra.hydro_ra_jaugeage,  
                                ra.ra_debroussaillage, ra.ra_eau_batterie, ra.ra_transfert_data, ra.ra_delete_memory, 
                                ra.ra_obs, ra.ra_futur, ra.name_file_data, ra.obs_file_data, ra.pre_marquant, ra.fait_marquant, ra.agents_complement 
                FROM ".TABLE_DATA_RA." ra
                WHERE id_ra = ".$id_ra;       

    $RA_query = tep_db_query($sql_link,$sql_RA);

    // Vérifier si la requête a retourné des résultats
    if (tep_db_num_rows($RA_query) > 0) 
    {
        $ra_exist = true;

        $RA_tab = tep_db_fetch_array($RA_query);

            $etat_ra = $RA_tab['etat_ra'];
            
            if(tep_not_null($RA_tab['id_agent_user'])){$id_agent_user = $RA_tab['id_agent_user'];} // si le nom du user pour la saisie initaile n'est pas renseigner on le laisse vide
            else{$id_agent_user = 0;}

            $id_station = $RA_tab['id_station'];

            if($RA_tab['datetime_saisie'] !== null)
            {
                $date_heure_saisie_tab =  explode(" ",$RA_tab['datetime_saisie']);
                $date_heure_saisie_fr = dateus_fr($date_heure_saisie_tab[0]).' '.$date_heure_saisie_tab[1];
            }
            else{$date_heure_saisie_fr='';}

            $date_heure_ra_tab =  explode(" ",$RA_tab['date_heure_ra']);
            $date_ra = dateus_fr($date_heure_ra_tab[0]);
            $heure_ra = $date_heure_ra_tab[1];

            $name_file_data = $RA_tab['name_file_data'];

            // APPAREILS
            $type_appareil = $RA_tab['type_appareil'];

            $num_appareil = $RA_tab['num_appareil'];
            $heure_appareil = $RA_tab['heure_appareil'];
                
            $hydro_num_sonde = $RA_tab['hydro_num_sonde']; // à voir si on ne peux pas faire mieux en liant à la table des formats de données et une nouvelle table de gestion du matériel 
            $nb_octet = nettoyer_et_echapper($RA_tab['nb_octet']);
            $num_batterie = nettoyer_et_echapper($RA_tab['num_batterie']);
            $tension_batterie = nettoyer_et_echapper($RA_tab['tension_batterie']);

            // Côte Limnimétrique
            $hydro_heure_cote = $RA_tab['hydro_heure_cote'];
            $hydro_h_sonde = $RA_tab['hydro_h_sonde']; // hauteur sonde
            $hydro_h_echelle_1 = $RA_tab['hydro_h_echelle_1']; 
            $hydro_h_echelle_2 = $RA_tab['hydro_h_echelle_2'];        

            // NOUVELLE CASSETTE - ça c'est pour du vieux matériel
            $hydro_h_sonde_cassette = $RA_tab['hydro_h_sonde_cassette'];
            $num_cassette = $RA_tab['num_cassette'];
            $heure_init_cassette = $RA_tab['heure_init_cassette'];        
            if($heure_init_cassette = '00:00:00'){$heure_init_cassette = '';}

            // Contrôle Hydro
            $hech_hsonde = round($hydro_h_echelle_1 - $hydro_h_sonde,2);
            $hydro_recalage_sonde = $RA_tab['hydro_recalage_sonde'];
            $hydro_recalage_heure_sonde = $RA_tab['hydro_recalage_heure_sonde'];  
            if($hydro_recalage_heure_sonde = '00:00:00'){$hydro_recalage_heure_sonde = '';}
            $hydro_purge_sonde = $RA_tab['hydro_purge_sonde']; // checked box 0 ou 1 	
            

            // Observations	
            $check_jaugeage = $RA_tab['hydro_ra_jaugeage']; // checked box 0 ou 1 
            $check_debrouss = $RA_tab['ra_debroussaillage']; // checked box 0 ou 1 
            $check_eaubat = $RA_tab['ra_eau_batterie']; // checked box 0 ou 1 
            $check_transfert = $RA_tab['ra_transfert_data']; // checked box 0 ou 1 
            $check_deletememory = $RA_tab['ra_delete_memory']; // checked box 0 ou 1 

            $ra_obs = nettoyer_et_echapper($RA_tab['ra_obs']); // text
            $ra_futur = nettoyer_et_echapper($RA_tab['ra_futur']); // text

            $name_file_data = nettoyer_et_echapper($RA_tab['name_file_data']); // text - varchar
            $obs_file_data = nettoyer_et_echapper($RA_tab['obs_file_data']); // text 

            $check_premarquant = $RA_tab['pre_marquant']; // checked box 0 ou 1
            $check_faitmarquant = $RA_tab['fait_marquant']; // checked box 0 ou 1

            $agents_complement = nettoyer_et_echapper($RA_tab['agents_complement']); // text
    }
}


// ----------------------------------------------------------------------------------
// Edition HTML
// ----------------------------------------------------------------------------------

$border_cadre = 'border: 6px solid '.$eq_type_array[$type_data]['type_color_border'].';';
$background_cadre = 'background-color:'.$eq_type_array[$type_data]['type_color_background'].';';

$tab_html .= "<div id='cadre_view' class='cadre_view' style='width:1180px;max-height: 90vh; overflow-y: auto;".$border_cadre.$background_cadre."' >\n";
	
    $tab_html .= "<form id='formRA'>";

        $tab_html .= "<input type='hidden' value='".$type_data."' name='type_data' id=='type_data'>";
        $tab_html .= "<input type='hidden' value='".$id_ra."' name='id_ra' id=='id_ra'>";

        $tab_html .= "<div id='cadre_limit'>";	

            if(isset($station_all_array) && sizeof($station_all_array) > 0)
            {
                if($ra_exist)
                {
                    // TITRE - Cartouche du haut - (ALL)
                    $tab_html .= "<table id='tab_titre_popup' cellspacing='0'>";
                                        
                        $tab_html .= "<tr>";
                            
                            $tab_html .= "<td class='titre'>";
                            
                                $checked = '';
                                if($check_modif==1){$checked = 'checked';}
                                $tab_html .= "<p style='margin-right:10px;margin-top:0px;'>";
                                    $tab_html .= "   
                                                <span style='font-size:12px;color:#B4B4B8;'>".htmlaccent('modifier')."</span>
                                                <br>                                         
                                                <input type='checkbox' name='check_modif_ra' id='check_modif_ra' 
                                                    title='".htmlaccent('Modifier les RA')."' 
                                                    style='float:left;width:25px;height:25px;margin-left:0px;' ".$checked.">
                                                ";
                                $tab_html .= "</p> \n";
                                
                                // Affichage Type de Mesure - Liste défilante
                                $tab_html .= "<p style='margin-right:20px;'>";

                                    $tab_html .= "<input type='text' value='".$nom_data."' disabled style='font-weight:bold;font-size:22px;'>";
                                    
                                $tab_html .= "</p> \n";
                                
                                // Affichage Station
                                $tab_html .= "<p style='width:500px;'>";
                                    
                                    $tab_html .= "<select name='select_station_ra' id='select_station_ra' class='titre_ra' style='width:100%;height:40px;' >";
                                            
                                        $selected = '';			

                                        if(isset($station_all_array))
                                        {
                                            foreach($station_all_array as $key => $value)
                                            {
                                                if($key == $id_station){$selected="selected";}	
                                                else{$selected = '';}											
                                                $tab_html .= "<option value='".$key."' ".$selected.">".$value['code_station']." - ".$value['nom_station']."</option>";
                                            }
                                        }
                                    
                                    $tab_html .= "</select>";	
                                    
                                $tab_html .= "</p> \n";
                                
                                // Puce validation RA 
                                if($etat_ra > 0){$tab_html .= "<img src='".DIR_WS_IMG_ICO."puce_verte.png' id='valid_puce_ok' title='".htmlaccent('RA validé')."'>";}
                                else{$tab_html .= "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' id='valid_puce_no' title='".htmlaccent('RA non validé')."'>";}
                                
                                // Affichage de la date de Saisie de de l'agent qui s'est connectée sur la plateforme pour saisir qui a saisie
                                $tab_html .= "<p style='float:right;margin-top:0px;'>";
                                    
                                    $tab_html .= "<span>".htmlaccent('Saisi le : ')."</span>";
                                    $tab_html .= "<input type='text' name='date_heure_saisie_fr' id='date_heure_saisie_fr' value='".$date_heure_saisie_fr."' readonly>";
                                    $tab_html .= "<br>";
                                    if(isset($user_list_array[$id_agent_user]))
                                    {
                                        $tab_html .= "<span>".htmlaccent('par : ')."</span>";
                                        $tab_html .= "<input type='text' style='input_texte_300' name='agent_saisie' id='agent_saisie' value='".$user_list_array[$id_agent_user]['prenom'].' '.$user_list_array[$id_agent_user]['nom']."' disabled>";
                                    }
                                    else{$tab_html .= "<span>".htmlaccent('par : - ')."</span>";}
                                                                    
                                $tab_html .= "</p> \n";

                            $tab_html .= "</td>";					
                            
                        $tab_html .= "</tr>";
                        
                    $tab_html .= "</table>";

                    // ----------------------------------------------------------------------------
                    // LIGNE 1				  	

                    // Releve des données sur un enregistreur (fichier de données brutes) - (ALL)
                    $tab_html .= "<div id='boxpopup'>\n";
                    
                        $tab_html .= "<h2>".htmlaccent('Relève')."</h2>\n";
                    
                        // Date Releve
                        

                        $tab_html .= "<div id='boite_small'>\n";
                            
                            $tab_html .= "<p>".htmlaccent('Date (jj-mm-aaaa)')."</p>\n";	
                            $tab_html .= "<input class='input_texte' style='width:80px;' name='date_releve' id='date_releve' type='text' value='".$date_ra."' >\n"; 
                                    
                        $tab_html .= "</div>\n";
                        
                        // Heure Releve
                        $tab_html .= "<div id='boite_small'>\n";
                                                        
                            $tab_html .= "<p>".htmlaccent('Heure (hh:mm)')."</p>";
                            $tab_html .= "<input name='heure_releve' id='heure_releve' value='".$heure_ra."' class='input_texte_small' type='text'>";
                                    
                        $tab_html .= "</div>\n";
                        
                        // Fichier Releve
                        $tab_html .= "<div id='boite_small'>\n";
                                                        
                            $tab_html .= "<p>".htmlaccent('Nom du fichier de relève / Num Cassette')."</p>";
                            $tab_html .= "<input name='fichier_releve' id='fichier_releve' value='".$name_file_data."' class='input_texte'  style='width:250px;' type='text'>";
                                    
                        $tab_html .= "</div>\n";
                        
                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";	
                    
                    // Appareil - Type, Num, HeureAppareil (ALL)
                    $tab_html .= "<div id='boxpopup'>\n";
                    
                        $tab_html .= "<h2>".htmlaccent('Appareil')."</h2>\n";
                    
                        // Type Appareil
                        $tab_html .= "<div id='boite_small'>\n";
                                                        
                            $tab_html .= "<p>".htmlaccent('Type')."</p>";
                            $tab_html .= "<input name='type_appareil' id='type_appareil' value='".$type_appareil."' class='input_texte' type='text' >";
                                    
                        $tab_html .= "</div>\n";
                        
                        // Numéro Appareil
                        $tab_html .= "<div id='boite_small'>\n";
                                                        
                            $tab_html .= "<p>".htmlaccent('Numéro')."</p>";	
                            $tab_html .= "<input name='num_appareil' id='num_appareil' value='".$num_appareil."' class='input_texte_small' type='text'>";
                                            
                        $tab_html .= "</div>\n";
                        
                        // Heure Appareil
                        $tab_html .= "<div id='boite_small'>\n";
                                                        
                            $tab_html .= "<p>".htmlaccent('Heure (hh:mm:ss)')."</p>";	
                            $tab_html .= "<input name='heure_appareil' id='heure_appareil' value='".$heure_appareil."' class='input_texte_small' type='text'>";						
                                    
                        $tab_html .= "</div>\n";

                        
                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";


                    // ----------------------------------------------------------------------------
                    // LIGNE 2						  

                    // COTE LIMNIMETRIQUE
                                $tab_html .= "<div id='boxpopup' class='elt_boite_hydro' >\n";
                                    
                                $tab_html .= "<h2>".htmlaccent('Côtes limnimétriques')."</h2>\n";									
                                
                                $tab_html .= "<div id='boite_small'>\n";
                                                            
                                    $tab_html .= "<p>".htmlaccent('Heure (hh:mm:ss)')."</p>";		
                                    $tab_html .= "<input name='hydro_heure_cote' id='hydro_heure_cote' value='".$hydro_heure_cote."' class='input_texte_small' type='text'>";
                                            
                                $tab_html .= "</div>\n";
                                
                                $tab_html .= "<div id='boite_small'>\n";
                                        
                                    $tab_html .= "<p>".htmlaccent('H. sonde (cm)')."</p>";		
                                    $tab_html .= "<input name='hydro_h_sonde' id='hydro_h_sonde' value='".$hydro_h_sonde."' class='input_texte_small' type='text' oninput='hydro_calcDiff()'>";	
                                            
                                $tab_html .= "</div>\n";
                                
                                $tab_html .= "<div id='boite_small'>\n";
                                        
                                    $tab_html .= "<p>".htmlaccent('H. échelle (cm)')."</p>";		
                                    $tab_html .= "<input name='hydro_h_echelle_1' id='hydro_h_echelle_1' value='".$hydro_h_echelle_1."' class='input_texte_small' type='text' oninput='hydro_calcDiff()'>";	
                                            
                                $tab_html .= "</div>\n";
                                
                                $tab_html .= "<div id='boite_small'>\n";
                                        
                                    $tab_html .= "<p>".htmlaccent('H. échelle 2 (cm)')."</p>";		
                                    $tab_html .= "<input name='hydro_h_echelle_2' id='hydro_h_echelle_2' value='".$hydro_h_echelle_2."' class='input_texte_small' type='text' >";	
                                            
                                $tab_html .= "</div>\n";
                                
                            $tab_html .= "<hr>\n";
                            $tab_html .= "</div>\n";
                            
                            // CONTROLE HYDRO
                            $tab_html .= "<div id='boxpopup' class='elt_boite_hydro' >\n";
                                
                                $tab_html .= "<h2>".htmlaccent('Contrôle des mesures de hauteur')."</h2>\n";									
                                


                                $tab_html .= "<div id='boite_small'>\n";
                                        
                                    $tab_html .= "<p>".htmlaccent('H. échelle - H sonde (cm)')."</p>";		
                                    $tab_html .= "<input name='hech_hsonde' id='hech_hsonde' value='".$hech_hsonde."' class='input_texte' style='width:80px;;' type='text' >";	
                                            
                                $tab_html .= "</div>\n";
                                
                                $tab_html .= "<div id='boite_small'>\n";
                                        
                                    $tab_html .= "<p>".htmlaccent('Recalage sonde')."</p>";		
                                    $tab_html .= "<input name='hydro_recalage_sonde' id='hydro_recalage_sonde' value='".$hydro_recalage_sonde."' class='input_texte_small' type='text'>";	
                                            
                                $tab_html .= "</div>\n";
                                
                                $tab_html .= "<div id='boite_small'>\n";
                                        
                                    $tab_html .= "<p>".htmlaccent('Recalage heure (hh:mm)')."</p>";		
                                    $tab_html .= "<input name='hydro_recalage_heure_sonde' id='hydro_recalage_heure_sonde' value='".$hydro_recalage_heure_sonde."' class='input_texte_small' type='text'>";	
                                            
                                $tab_html .= "</div>\n";
                                
                                $checked = '';
                                if($hydro_purge_sonde>0){$checked = 'checked';}
                                $tab_html .= "<div id='boite_small'>\n";
                                        
                                    $tab_html .= "<p>".htmlaccent('Purge / Etat sonde')."</p>";
                                    $tab_html .= "<input class='input_texte' style='width:25px;height:20px;margin-right:10px;' name='check_purge_sonde' id='check_purge_sonde' type='checkbox' ".$checked.">";
                                    
                                $tab_html .= "</div>\n";
                                
                            $tab_html .= "<hr>\n";
                            $tab_html .= "</div>\n";

                    


                    // ----------------------------------------------------------------------------
                    // LIGNE 3


                    // Etat Appareil / APPAREIL
                    $tab_html .= "<div id='boxpopup'>\n";
                        
                        $tab_html .= "<h2>".htmlaccent('Etat de l\'appareil')."</h2>\n";									
                        
                        // ALL        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Nb d\'octets')."</p>";		
                            $tab_html .= "<input name='nb_octet' id='nb_octet' value='".$nb_octet."' class='input_texte_xsmall' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Num batterie')."</p>";		
                            $tab_html .= "<input name='num_batterie' id='num_batterie' value='".$num_batterie."' class='input_texte' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Tension batterie')."</p>";		
                            $tab_html .= "<input name='tension_batterie' id='tension_batterie' value='".$tension_batterie."' class='input_texte_xsmall' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";


                    // NOUVELLE CASSETTE (A mettre ensuite en option) (PLU / HYDRO)
                    $tab_html .= "<div id='boxpopup' class='elt_boite_pluhydro' >\n";
                                        
                        $tab_html .= "<h2>".htmlaccent('Nouvelle cassette')."</h2>\n";									
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Numéro cassette')."</p>";		
                            $tab_html .= "<input name='num_cassette' id='num_cassette' value='".$num_cassette."' class='input_texte' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Heure initialisation')."</p>";		
                            $tab_html .= "<input name='heure_init_cassette' id='heure_init_cassette' value='".$heure_init_cassette."' class='input_texte_small' type='text'>";	
                                    
                        $tab_html .= "</div>\n";

                        // HYDRO
                        $tab_html .= "<div id='boite_small' class='elt_boite_hydro'>\n";
                                            
                            $tab_html .= "<p>".htmlaccent('Hauteur sonde (cm)')."</p>";		
                            $tab_html .= "<input name='hydro_h_sonde_cassette' id='hydro_h_sonde_cassette' value='".$hydro_h_sonde_cassette."' class='input_texte_small' type='text'>";	
                                            
                        $tab_html .= "</div>\n";
                        
                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";


                    // Cette partie là ne s'affichera que si besoin avec un slide en attendant :  style='display:none;'
                    // OPTION

                    // DUREE EENREGISTREMENT
                    $tab_html .= "<div id='boxpopup' style='display:none;'>\n";
                        
                        $tab_html .= "<h2>".htmlaccent('Durée de l\'Enregistrement')."</h2>\n";									
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Nb Jour')."</p>";		
                            $tab_html .= "<input name='duree_nb_jour id='duree_nb_jour' value='".$duree_nb_jour."' class='input_texte_xsmall' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Nb Heure')."</p>";		
                            $tab_html .= "<input name='duree_nb_heure' id='duree_nb_heure' value='".$duree_nb_heure."' class='input_texte_xsmall' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Nb Min')."</p>";		
                            $tab_html .= "<input name='duree_nb_min' id='duree_nb_min' value='".$duree_nb_min."' class='input_texte_xsmall' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";
                    
                    
                    // DERNIER ENREGISTREMENT
                    $tab_html .= "<div id='boxpopup' style='display:none;'>\n";
                        
                        $tab_html .= "<h2>".htmlaccent('Dernier Enregistrement')."</h2>\n";									
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Nb Jour')."</p>";		
                            $tab_html .= "<input name='dernier_nb_jour id='dernier_nb_jour' value='".$dernier_nb_jour."' class='input_texte_xsmall' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Nb Jour')."</p>";		
                            $tab_html .= "<input name='dernier_nb_heure' id='dernier_nb_heure' value='".$dernier_nb_heure."' class='input_texte_xsmall' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                        $tab_html .= "<div id='boite_small'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Nb Min')."</p>";		
                            $tab_html .= "<input name='dernier_nb_min' id='dernier_nb_min' value='".$dernier_nb_min."' class='input_texte_xsmall' type='text'>";	
                                    
                        $tab_html .= "</div>\n";
                        
                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";



                    // ----------------------------------------------------------------------------
                    // LIGNE 4

                    // OBSERVATIONS
                    $tab_html .= "<div id='boxpopup'>\n";

                        $tab_html .= "<h2 style='height:15px;'	>";
                        
                            $tab_html .= "<p style='float:left;width:150px;font-size: 13px;font-weight: bold;color: #336699;'>";
                            $tab_html .= "Observations / Actions";
                            $tab_html .= "</p>";

                            $checked = '';
                            if($check_faitmarquant>0){$checked = 'checked';}

                            $tab_html .= "<div style='float:left;width:150px;'>\n";
                                $tab_html .= "<input class='input_texte' style='width:20px;' name='check_faitmarquant' id='check_faitmarquant' type='checkbox' ".$checked.">";
                                $tab_html .= "<span style='float:left;margin-top:2px;width:90px;font-size:12px;'>".htmlaccent('Fait marquant')."</span>";													
                                $tab_html .= "<hr>";
                            $tab_html .= "</div>\n";
                        
                        $tab_html .= "</h2>\n";	

                        $tab_html .= "<div id='boite_small' style='margin:0;'>\n";
                                                    
                            $tab_html .= "<textarea name='ra_obs' id='ra_obs' style='width:280px;height:70px;'>".$ra_obs."</textarea>\n";
                                    
                        $tab_html .= "</div>\n";


                        // Partie des cases à cocher

                        // Pour PLU et HYDRO
                        $tab_html .= "<div id='boite_small' class='elt_boite_pluhydro' style='margin:0;' >\n";
                            
                            $checked = '';
                            if($check_jaugeage>0){$checked = 'checked';}
                            
                            $tab_html .= "<div class='elt_boite_hydro' style='display:none;' >\n";
                                $tab_html .= "<input class='input_texte' style='width:25px;' name='check_jaugeage' id='check_jaugeage' type='checkbox' ".$checked.">";
                                $tab_html .= "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Jaugeage')."</span>";													
                                $tab_html .= "<hr>";
                            $tab_html .= "</div>\n";

                            $checked = '';
                            if($check_debrouss>0){$checked = 'checked';}
                            
                            $tab_html .= "<div class='elt_boite_pluhydro' >\n";
                                $tab_html .= "<input class='input_texte' style='width:25px;' name='check_debrouss' id='check_debrouss' type='checkbox' ".$checked.">";
                                $tab_html .= "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Débroussaillage')."</span>";														
                                $tab_html .= "<hr>";
                            $tab_html .= "</div>\n";												

                        $tab_html .= "</div>\n";


                        // Pour PLU et HYDRO
                        $tab_html .= "<div id='boite_small' class='elt_boite_pluhydro' style='margin:0;'>\n";

                            $checked = '';
                            if($check_eaubat>0){$checked = 'checked';}                
                            $tab_html .= "<input class='input_texte' style='width:25px;' name='check_eaubat' id='check_eaubat' type='checkbox' ".$checked.">";
                            $tab_html .= "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Eau batterie')."</span>";													
                            $tab_html .= "<hr>";

                            $checked = '';
                            if($check_transfert>0){$checked = 'checked';}
                            $tab_html .= "<input class='input_texte' style='width:25px;' name='check_transfert' id='check_transfert' type='checkbox' ".$checked.">";
                            $tab_html .= "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Transfert')."</span>";													
                            $tab_html .= "<hr>";

                            $checked = '';
                            if($check_deletememory>0){$checked = 'checked';}
                            $tab_html .= "<input class='input_texte' style='width:25px;' name='check_deletememory' id='check_deletememory' type='checkbox' ".$checked.">";
                            $tab_html .= "<span style='float:left;margin-top:5px;width:90px;font-size:12px;'>".htmlaccent('Mémoire effacée')."</span>";													
                                    
                        $tab_html .= "</div>\n";
                    
                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";


                    // NEXT TOURNEE
                    $tab_html .= "<div id='boxpopup' style='width:420px;' >\n";
                        
                        $tab_html .= "<h2 style='height:15px;'>";
                            
                            $tab_html .= "<p style='float:left;width:240px;font-size: 13px;font-weight: bold;color: #336699;'>";
                                $tab_html .= "Actions à réaliser - Prochaine tournée";
                            $tab_html .= "</p>";

                            $checked = '';
                            if($check_premarquant>0){$checked = 'checked';}

                            $tab_html .= "<div style='float:left;width:150px;'>\n";
                                $tab_html .= "<input class='input_texte' style='width:20px;' name='check_premarquant' id='check_premarquant' type='checkbox' ".$checked.">";
                                $tab_html .= "<span style='float:left;margin-top:2px;width:110px;font-size:12px;'>".htmlaccent('Prévoir marquant')."</span>";													
                                $tab_html .= "<hr>";
                            $tab_html .= "</div>\n";
                            
                        $tab_html .= "</h2>\n";									
                            
                        $tab_html .= "<div id='boite_small'>\n";
                                                    
                            $tab_html .= "<textarea name='ra_futur' id='ra_futur' style='width:280px;height:70px;'>".$ra_futur."</textarea>\n";
                                    
                        $tab_html .= "</div>\n";

                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";


                    // ----------------------------------------------------------------------------
                    // LIGNE 5
                                
                    // AGENT					
                    $tab_html .= "<div id='boxpopup' style='width:95%;'>\n";
                        
                        $tab_html .= "<h2>".htmlaccent('Agents ayant participé')."</h2>\n";									
                        
                        $tab_html .= "<div id='boite_small' style='width:50%;'>\n";

                            if (!isset($agents_complement) || is_null($agents_complement)) 
                            {
                                $agents_complement = '';
                            }
                            
                            if(isset($agent_array))
                            {
                                foreach($agent_array as $key => $value)
                                {
                                    $checked = (strpos($agents_complement, $value) !== false) ? 'checked' : '';
                                    
                                    $tab_html .= "<div style='float:left;'>\n";
                                        $tab_html .= "<input class='input_texte' style='width:25px;padding:0;' name='check_agent_".$key."' id='check_agent_".$key."' type='checkbox' data-value='".$value."' onchange='updateSelectedAgents();' ".$checked.">";	
                                        $tab_html .= "<span style='float:left;margin-right:8px;font-size:12px;'>".$value."</span>";
                                    $tab_html .= "<hr>\n";
                                    $tab_html .= "</div>\n";
                                }
                            }

                        $tab_html .= "</div>\n";
                        
                        $tab_html .= "<div id='boite_small' style='width:45%;'>\n";
                                
                            $tab_html .= "<p>".htmlaccent('Participants')."</p>";
                            $tab_html .= "<input name='agents_complement' id='agents_complement' value='".$agents_complement."' class='input_texte'  style='width:100%;' type='text' >";
                                    
                        $tab_html .= "</div>\n";
                        
                    $tab_html .= "<hr>\n";
                    $tab_html .= "</div>\n";


                    // ----------------------------------------------------------------------------
                    // -- LIGNE FINALE

                    $tab_html .= "<hr>";
                                
                    // Barre de navigation + Bouttons de validation
                    
                    $tab_html .= "<div id='popup_barredown' style='height:60px;'>\n";
                        
                        $tab_html .= "<div id='popup_nav' style='width:470px;'>\n";
                                
                            // Flèches Previous	
                            $tab_html .= "<div id='content_arrow' class='content_arrow'>";
                                
                                if($num_ra > 1)
                                {
                                    $tab_html .= "<div id='arrow_previous' >";
                                        
                                        $tab_html .= "<a id='arrow_first_a' href='#' onclick='loadRA(".$first_id_ra.",".$first_type_ra."); return false;'>";
                                            $tab_html .= "<img src='".DIR_WS_IMG_ICO."arrow_first.png' style='width:50px;margin-right:30px;cursor:pointer;' title='".htmlaccent('Premier RA')."' 
                                                            onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_first_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_first.png';\" >";
                                        $tab_html .= "</a>";
                                    
                                        $tab_html .= "<a id='arrow_previous_a' href='#' onclick='loadRA(".$prev_id_ra.",".$prev_type_ra."); return false;'>";
                                            $tab_html .= "<img src='".DIR_WS_IMG_ICO."arrow_previous.png' style='width:25px;cursor:pointer;' title='".htmlaccent('RA précédent')."' 
                                                        onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_previous_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_previous.png';\" >";
                                        $tab_html .= "</a>";
                                    
                                    $tab_html .= "</div>";
                                }
                            
                            $tab_html .= "</div>";
                            
                            // Compteur de RA
                            $tab_html .= "<div id='content_arrow' class='content_arrow'>";
                                
                                $tab_html .= "<input type='text' value='".$num_ra." / ".$nb_ra."' id='num_fiche' disabled>";
                                
                            $tab_html .= "</div>";
                            
                            // Flèches Next
                            if($num_ra < $nb_ra)
                            {
                                $tab_html .= "<div id='content_arrow' class='content_arrow'>";
                                
                                    $tab_html .= "<div id='arrow_next' >";
                                    
                                        $tab_html .= "<a id='arrow_next_a' href='#' onclick='loadRA(".$next_id_ra.",".$next_type_ra."); return false;'>";
                                            $tab_html .= "<img src='".DIR_WS_IMG_ICO."arrow_next.png' style='width:25px;cursor:pointer;' title='".htmlaccent('RA suivant')."' onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_next_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_next.png';\" >";
                                        $tab_html .= "</a>";
                                        
                                        $tab_html .= "<a id='arrow_last_a' href='#' onclick='loadRA(".$last_id_ra.",".$last_type_ra."); return false;'>";
                                            $tab_html .= "<img src='".DIR_WS_IMG_ICO."arrow_end.png' style='width:50px;margin-left:30px;cursor:pointer;' title='".htmlaccent('Dernier RA')."' onmouseover=\"this.src='".DIR_WS_IMG_ICO."arrow_end_over.png';\" onmouseout=\"this.src='".DIR_WS_IMG_ICO."arrow_end.png';\" >";
                                        $tab_html .= "</a>";
                                        
                                    $tab_html .= "</div>";
                                
                                $tab_html .= "</div>";
                            }
                            
                        $tab_html .= "</div>\n";	
                        
                        $display_modif_ra = 'display:none';
                        if($check_modif==1){$display_modif_ra = 'display:block';}
                        $tab_html .= "<div id='popup_nav' class='modif_ok' style='width:550px;margin-left:20px;".$display_modif_ra."'>\n";
                            
                            $tab_html .= "<table id='stats_select' cellspacing='0' >";
                    
                                $tab_html .= "<tr style='margin:0;'>";
                                    
                                    $tab_html .= "<td class='bold' style='width:350px;'>";

                                        $checked = '';
                                        if($etat_ra > 0){$checked = 'checked';}

                                        $tab_html .= "<div id='bloc_valid_ra' >";
                                            $tab_html .= "<p style='float:left;font-size:14px;text-align:center;'>".htmlaccent('Validation <br> RA')."</p>";
                                            $tab_html .= "<input type='checkbox' name='check_valid_ra' id='check_valid_ra' style='float:left;width:30px;height:30px;margin-left:20px;' ".$checked.">";
                                        $tab_html .= "</div>";
                                        
                                    $tab_html .= "</td>";

                                    $tab_html .= "<td style='width:30px;'>&nbsp;</td>";
                                    
                                    $tab_html .= "<td class='bold'><input type='submit' class='button' id='save_ra' name='save_ra' value='Enregistrer' onclick='saveRA(event);'/></td>";
                                    
                                    $tab_html .= "<td style='width:30px;'>&nbsp;</td>";
                                    
                                    $tab_html .= "<td class='bold'><input type='button' id='button_close' class='button_close'  value='Annuler' ></td>";
                                                                    
                                $tab_html .= "</tr>";
                                
                            $tab_html .= "</table>";
                            
                            
                        $tab_html .= "</div>\n";
                    
                    $tab_html .= "</div>\n";
                }
                else
                {
                    // Le RA n'existe pas 

                    $tab_html .= "<table id='tab_titre_popup' cellspacing='0'>";
                                    
                        $tab_html .= "<tr>";
                            
                            $tab_html .= "<td class='titre' style='border:none;'>";

                                $tab_html .= "<p style='display:none;'>";
                                    $tab_html .= "<input type='checkbox' name='check_modif_ra' id='check_modif_ra' 
                                                    title='".htmlaccent('Modifier les RA')."'>";
                                $tab_html .= "</p> \n";
                            
                                // Affichage Type de Mesure du RA
                                $tab_html .= "<p style='width:100%;margin:30px 0;text-align:center;'>";
                                    $tab_html .= "!!! Le Rapport d'Activité n'a pas été trouvé !!!";                            
                                $tab_html .= "</p>";

                            $tab_html .= "</td>";					
                            
                        $tab_html .= "</tr>";
                        
                    $tab_html .= "</table>";

                    
                    $tab_html .= "<div style='float:left;margin:10px 45%;'>";
                            $tab_html .= "<input type='button' id='button_close' class='button_close'  value='Annuler'>";
                    $tab_html .= "</div>";
                }

            }
            else
            {
                // Il n'est pas possible de créer un nouvel RA

                $tab_html .= "<table id='tab_titre_popup' cellspacing='0'>";
                                    
                    $tab_html .= "<tr>";
                        
                        $tab_html .= "<td class='titre' style='border:none;'>";

                            $tab_html .= "<p style='display:none;'>";
                                $tab_html .= "<input type='checkbox' name='check_modif_ra' id='check_modif_ra' 
                                                title='".htmlaccent('Modifier les RA')."'>";
                            $tab_html .= "</p> \n";
                        
                            // Affichage Type de Mesure du RA
                            $tab_html .= "<p style='width:100%;margin:30px 0;text-align:center;'>";
                                $tab_html .= "!!! Il n'est pas possible de créer un nouvel RA (".$nom_data.") avec les filtres utilisés !!!";                            
                            $tab_html .= "</p>";

                        $tab_html .= "</td>";					
                        
                    $tab_html .= "</tr>";
                    
                $tab_html .= "</table>";

                
                $tab_html .= "<div style='float:left;margin:10px 45%;'>";
                        $tab_html .= "<input type='button' id='button_close' class='button_close'  value='Annuler'>";
                $tab_html .= "</div>";
            }


        $tab_html .= "</div>\n";

    $tab_html .= "</form>\n";


$tab_html .= "</div>\n";



// Remplissage du tableau de retour

$responseData = array(
    'tab_html' => $tab_html
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>