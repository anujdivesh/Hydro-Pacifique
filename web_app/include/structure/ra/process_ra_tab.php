<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Procédure pour afficher dans un tableau les RA en cours dans la page RA.
Processus asynchrone AJX coté serveur
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
$where_ra = $dataInfo['where_ra'];
$order_ra = $dataInfo['order_ra'];
$limit_ra = $dataInfo['limit_ra'];

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

// TABLE COMMUNE
$sql_commune = "SELECT DISTINCT c.id_commune, c.nom_commune 
				FROM ".TABLE_COMMUNE." c
				JOIN ".TABLE_REGION." r ON c.id_region=r.id_region
				WHERE r.id_territoire=".$territoire_id."
				ORDER BY c.nom_commune ASC";

$commune_query = tep_db_query($sql_link,$sql_commune);
while ($commune = tep_db_fetch_array($commune_query))
{
	$commune_array[$commune['id_commune']] = $commune['nom_commune'];
}

// TABLE STATION
$sql_station_all = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
					FROM ".TABLE_STATION." 
				    ORDER BY nom_station ASC";
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
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type, unite_eq_type, valeur_data_type, type_color_border, type_graph 
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
                                                        'type_color_border' => $eq_type_tab['type_color_border']
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

// -------------------------------------------------------------


// Initialisation Variables
$tab_html = '';
$nb_ra = 0;
$nb_ra_valid = 0;
$num_ra = 0;
$prev_id_ra = 0;
$prev_type_data = 0;
$next_id_ra = 0;
$next_type_data = 0;
$row = 0;

$ra_nav_array = array();


// Requête d'accès aux RA
$sql_RA = "SELECT DISTINCT ra.id_ra, ra.id_agent_user, s.id_region, s.id_station, s.nom_station, s.code_station, s.id_commune,
                            ra.date_heure_ra, ra.id_eq_type, ra.etat_ra,							
                            ra.ra_obs, ra.ra_futur, ra.pre_marquant, ra.fait_marquant,
                            ra.agents_complement
            FROM ".TABLE_DATA_RA." ra
            JOIN ".TABLE_STATION." s ON ra.id_station=s.id_station
            LEFT JOIN ".TABLE_STATION_TO_TOURNEE." st ON st.id_station = s.id_station
            WHERE ".$where_ra." 
            ORDER BY ".$order_ra." s.active_station DESC, s.suivi DESC, s.armee ASC ".
            $limit_ra;       

$RA_query = tep_db_query($sql_link,$sql_RA);

if($RA_query)
{
    $nb_ra = mysqli_num_rows($RA_query);
    $num_ra = 0;

    // Tampon pour stocker la ligne actuelle et la prochaine ligne
    $prev_RA_tab = null;

    $RA_tab = tep_db_fetch_array($RA_query);
    $next_RA_tab = tep_db_fetch_array($RA_query);
    
    while($RA_tab)
    {   
        $num_ra++;
        $id_ra =  $RA_tab['id_ra'];
        $id_type_ra = $RA_tab['id_eq_type']; // Type de données : Débit, pluie, piezo

        // Récupération des infos nécessaires à la navigation des ra précédents et suivants        
        if($prev_RA_tab)
        {
            $prev_id_ra = $prev_RA_tab['id_ra'];
            $prev_type_ra = $prev_RA_tab['id_eq_type'];
        }

        if($next_RA_tab)
        {
            $next_id_ra = $next_RA_tab['id_ra'];
            $next_type_ra = $next_RA_tab['id_eq_type'];
        }
        
        // Stocker les données dans le tableau associatif
        $ra_nav_array[$id_ra] = array('id_type_ra' => $id_type_ra,
                                      'prev_id_ra' => isset($prev_id_ra) ? $prev_id_ra : null,
                                      'prev_type_ra' => isset($prev_type_ra) ? $prev_type_ra : null,
                                      'next_id_ra' => isset($next_id_ra) ? $next_id_ra : null,
                                      'next_type_ra' => isset($next_type_ra) ? $next_type_ra : null,
                                      'num_ra' => $num_ra,
                                      'nb_ra' => $nb_ra);


        // nettoyer_et_echapper() est une fonction php créer dans function/general.php permettent d'éviter les bugs du à des caractères spéciaux : ',",(,),...
        
        $id_agent_user =  $RA_tab['id_agent_user'];
        
        $id_region =  $RA_tab['id_region'];
        $id_station =  $RA_tab['id_station'];
        $nom_station =  nettoyer_et_echapper($RA_tab['nom_station']);
        $code_station =  nettoyer_et_echapper($RA_tab['code_station']);
        
        $id_commune = $RA_tab['id_commune'];
        $nom_commune = '';
        if(isset($commune_array[$id_commune])){$nom_commune = $commune_array[$id_commune];}
        
        // Date RA
        $date_heure_ra_tab =  explode(" ",$RA_tab['date_heure_ra']);
        $date_ra =  dateus_fr($date_heure_ra_tab[0]);
        $heure_ra =  $date_heure_ra_tab[1];
        $date_heure_ra =  $date_ra.' '.$heure_ra;
        // RA
        $etat_ra = $RA_tab['etat_ra']; // Terrain / en cours de validation / validé 
        $puce_ra = "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title='".htmlaccent('Etape 0')."'>";
        if($etat_ra > 0)
        {
            $nb_ra_valid++;
            $puce_ra = "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('Etape 1')."'>";
        }
        
        $ra_obs = nettoyer_et_echapper($RA_tab['ra_obs']); // text
        $ra_futur = nettoyer_et_echapper($RA_tab['ra_futur']); // text
        $pre_marquant = $RA_tab['pre_marquant']; 
        $fait_marquant = $RA_tab['fait_marquant']; 
        
        // Agents Select
        $agents_complement = nettoyer_et_echapper($RA_tab['agents_complement']); // text
        
        // Remplissage d'un tableau pour la avoir la liste des RA
        
        // Colaration d'une ligne au survol de la souris
        $row++;
        if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
        else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
        
        $color_type = '';
        
        if(isset($eq_type_array[$id_type_ra]) && tep_not_null($eq_type_array[$id_type_ra]['type_color_border']))
        {$color_type = 'color:'.$eq_type_array[$id_type_ra]['type_color_border'].';';}
        
        $tab_html .= "<tr ".$row_l." >";
                                
            // Est-ce que le RA a été validé ou non
            $tab_html .= "<td style='text-align:center;cursor:pointer;' 
                            onClick='loadRA(".$id_ra.",".$id_type_ra.");'
                            >";	
                $tab_html .= $puce_ra;
            $tab_html .= "</td>";	
            
            // Data Heure
            $tab_html .= "<td style='cursor:pointer;'
                            onClick='loadRA(".$id_ra.",".$id_type_ra.");'
                            >";	
                $tab_html .= $date_heure_ra;
            $tab_html .= "</td>\n";

            // Type de données (Debit, Pluie, Piezo)															
            $tab_html .= "<td style='cursor:pointer;'
                            onClick='loadRA(".$id_ra.",".$id_type_ra.");'
                            >";	
                $tab_html .= "<span style='".$color_type."'>".$eq_type_array[$id_type_ra]['nom_eq_type']."</span>";
            $tab_html .= "</td>\n";
            
            // Code de la station liée
            $tab_html .= "<td style='cursor:pointer;'
                            onClick='loadRA(".$id_ra.",".$id_type_ra.");'
                            >";	
                $tab_html .= $code_station;
            $tab_html .= "</td>\n";

            // Nom de la station liée
            $tab_html .= "<td style='cursor:pointer;'
                            onClick='loadRA(".$id_ra.",".$id_type_ra.");'
                             title='".$nom_station."' >";
                $tab_html .= affichelettres($nom_station,50);
            $tab_html .= "</td>\n";
                        
            // Nom de la commune
            $tab_html .= "<td style='cursor:pointer;'
                            onClick='loadRA(".$id_ra.",".$id_type_ra.");'
                            >";	
                $tab_html .= $nom_commune;
            $tab_html .= "</td>\n";
            
            // Pour la liste des agents, en cours
            $tab_html .= "<td style='cursor:pointer;'
                            onClick='loadRA(".$id_ra.",".$id_type_ra.");' 
                             title='".$agents_complement."'>";	
                $tab_html .= $agents_complement;//affichelettres($agents_complement,50);
            $tab_html .= "</td>\n";
            
            // Lien pour la suppression du RA
            $tab_html .= "<td  style='text-align:center;'>";	
            
                $tab_html .= "
                    <a style='font-size:12px;font-weight:bold;' id='del_".$id_ra."' onClick='verifDelRA(".$id_ra.");' title='".htmlaccent('Supprimer le RA')."'>
                    X
                    </a>";
            
            $tab_html .= "</td>\n";
            
        $tab_html .=  "</tr>";	

        // Avancer le tampon
        $prev_RA_tab = $RA_tab;
        $RA_tab = $next_RA_tab;
        $next_RA_tab = tep_db_fetch_array($RA_query);
    }      

    // Convertir le tableau PHP en JSON - Pour envoyer dans la fonction js loadRA() définie dans list_ra   
    $ra_nav_json = json_encode($ra_nav_array);           
}
else
{
    $tab_html .= "<div id='boxpopup' style='margin-left: 1%;'>\n";
        $tab_html .= "<p class='alert'>".htmlaccent('Aucun - Rapport d\'Activité (RA) - n\'a été trouvé')."</p>";
    $tab_html .= "</div>";
}


// Remplissage du tableau de retour

$responseData = array(
    'nb_ra' => $nb_ra,
    'nb_ra_valid' => $nb_ra_valid,
    'tab_html' => $tab_html,
    'ra_nav_json' => $ra_nav_json
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>