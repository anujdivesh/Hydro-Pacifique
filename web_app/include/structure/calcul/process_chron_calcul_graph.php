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
$jsonDataGraph = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataGraph = json_decode($jsonDataGraph, true);

// Accéder aux données du tableau récupérer
$territoire_id = $dataGraph['territoireId'];
$lang = $dataGraph['lang'];

// RECUPERATION DU TEXT - POUR LA TRADUCTION
require('../../text_content_'.$lang.'.php');

// Accéder aux données du tableau récupérer
$cle_station = $dataGraph['cle_station'];
$id_typedata = $dataGraph['type_station'];
$typedata_array = $dataGraph['typedata_array'];
$color_tab = $dataGraph['colorTab'];
$min_x = $dataGraph['min_x'];
$max_x = $dataGraph['max_x'];
$id_correction = $dataGraph['id_correction'];

$min_y = 9999;
$max_y = 0;

$color_chron = '#2471a3';

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

// TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
$sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, 
                                to_periode, id_chon_periode, traitement, type_graph
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
															'id_chon_periode' => $type_chron_tab['id_chon_periode'],
															'traitement' => $type_chron_tab['traitement'],
															'typegraph' => $type_chron_tab['type_graph']
															);
}

// TABLE QUALITE
$sql_quality = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data, info_qualite_data, id_eq_type 
				FROM ".TABLE_DATA_QUALITE."
				WHERE init_qualite_data<>'' 
                ORDER BY init_qualite_data ASC";
$quality_query = tep_db_query($sql_link,$sql_quality);

// --------------------------------------------------------------------------


// Récupération de l'identifiant du type de chronique de la chronique en cours de correction
$array_keys = array_keys($typedata_array);
$typedata_chron = $array_keys[0];

$tab_data_graph[] = array('sql' => $typedata_array[$typedata_chron],
                            'description' => '',
                            'correction' => 0,
                            'new_lacune' => 0,                            
                            'color' => $color_tab['data_init'],
                        );


// Intégration des corrections en cours dans le graphique
// S'il y a des corrections, il faut incrémenter le tableau $typedata_array

if($id_correction > 0)
{
    $sql_correction = "SELECT mc.id, c.datetime_correction, mc.id_correction, mc.type_correction, mc.info_correction, mc.axe_correction, 
                            mc.datetime_first, mc.datetime_end
                        FROM ".TABLE_DATA_META_CORRECTION." mc
                        JOIN ".TABLE_DATA_CORRECTION." c ON c.id=mc.id_correction
                        WHERE mc.id_correction = ".$id_correction."
                        ORDER BY mc.id DESC";
    
    $correction_query = tep_db_query($sql_link,$sql_correction);
    while ($correction_tab = tep_db_fetch_array($correction_query))
    {        
        $sql_chron_correction = "SELECT da.dateheure, da.valeur, dm.id_station, dm.id, dm.id_typedata
                                FROM ".TABLE_DATA_ALL_CORRECTION." da
                                JOIN ".TABLE_DATA_META_CORRECTION." dm ON da.id_meta=dm.id
                                WHERE dm.id = ".$correction_tab['id']."
                                AND dm.id_station = ".$cle_station."
                                ORDER BY da.dateheure ASC";

        $new_lacune = 0;
        if($correction_tab['info_correction'] == 'Lacune'){$new_lacune = 1;}

        $tab_data_graph[] = array('sql' => $sql_chron_correction,
                                    'description' => $correction_tab['info_correction'],
                                    'correction' => 1,
                                    'new_lacune' => $new_lacune,                                    
                                    'color' => $color_tab[$correction_tab['type_correction']],
                                );                
    }
}

//print_r($tab_data_graph);



// Script de chargement des données pour l'édition du graphique

    $lacune_date_first = '';

    ${'js_config_trace_'.$cle_station} ='';
    ${'js_load_trace_'.$cle_station} ='';

    ${'edit_lacune_'.$cle_station} = '';
    ${'html_tab_lacune_'.$cle_station} = '';

    // INIT échelle des axes - Il ne peut y avoir que 2 axes  
    ${'min_'.$cle_station} = 9999;
    ${'max_'.$cle_station} = 0;
    ${'nb_chron_'.$cle_station} = 0;

    ${'hidden_check_chron_'.$cle_station} = '';


    // Adaptation de la boucle pour intégrer les correction de la chroniques en cours de construction
    foreach($tab_data_graph as $cle_graph => $value_tab) 
    {      
        $sql_chron = $value_tab['sql'];
        $init_chron = $type_chron_array[$typedata_chron]['init_type_data'];        
        $name_chron = $type_chron_array[$typedata_chron]['nom_type_data'];
        if($value_tab['correction'] > 0){$init_chron .= ' -> ('.$value_tab['description'] .')';}

        ${'nb_chron_'.$cle_station}++;
        
        ${'nb_data_'.$cle_station.'_'.$typedata_chron} = 0;    
        
        ${'min_'.$cle_station.'_'.$typedata_chron} = 9999;
        ${'max_'.$cle_station.'_'.$typedata_chron} = 0;
            
        ${'nb_lacunes_'.$cle_station.'_'.$typedata_chron} = 0;
        ${'tab_lacunes_'.$cle_station.'_'.$typedata_chron} = array();  

        ${'graph_x_'.$cle_station.'_'.$typedata_chron} = '';
        ${'graph_y_'.$cle_station.'_'.$typedata_chron} = '';
        ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} = '';
        ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron} = '';

        //$id_typedata = $station_all_array[$cle_station]['type_station'];
        
        // On crée un champs invisible pour récupérer les données stations, typedata et chroniques pour la page de correction de données (si besoin)
        ${'hidden_check_chron_'.$cle_station} .= "<input type='hidden' name='check_chron[]' value='".$cle_station.'_'.$id_typedata.'_'.$typedata_chron."' />\n";
       
        // Construction des donnees pour les graphs
        // Pour chaque chronique on va chercher les données dans la base
        
        $valeur = 0; // Initialisation de la vairaible contenant une donnée (nécessaire pour l'affichage en cumul)
        $lacune_date_first=''; // Réinitialisation des lacunes

        $data_chron_query = tep_db_query($sql_link,$sql_chron);
        while($data_chron_tab = tep_db_fetch_array($data_chron_query))
        {   
            ${'nb_data_'.$cle_station.'_'.$typedata_chron}++;
            
            // Convertir la date de la donnée en objet DateTime
            $date_chron = new DateTime($data_chron_tab['dateheure']);

            // Convertir les dates minimales et maximales en objets DateTime
            $min_x_dt = new DateTime($min_x);
            $max_x_dt = new DateTime($max_x);

            // Comparer les dates pour définir au fur et à mesure la plus petite et la plus grande date
            if ($date_chron < $min_x_dt){$min_x = $date_chron->format('d-m-Y H:i:s');}
            if ($date_chron > $max_x_dt){$max_x = $date_chron->format('d-m-Y H:i:s');}

                                    
            // Si il y a une lacune en cours
            if(tep_not_null($lacune_date_first))
            {                
                ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= $edit_lacune_temp;
                ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron} .= $html_tab_lacune_temp;

                if($data_chron_tab['valeur'] > (-8888) && $data_chron_tab['valeur'] < (99999) )
                {
                    ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."',";

                    /*
                    if($type_chron_array[$typedata_chron]['traitement'] == 0){$valeur = abs($data_chron_tab['valeur']);} // données simple (valeur)
                    if($type_chron_array[$typedata_chron]['traitement'] == 1){$valeur += abs($data_chron_tab['valeur']);} // Cumul
                    */
                    if($type_chron_array[$typedata_chron]['traitement'] == 0){$valeur = $data_chron_tab['valeur'];} // données simple (valeur)
                    if($type_chron_array[$typedata_chron]['traitement'] == 1){$valeur += $data_chron_tab['valeur'];} // Cumul

                    ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= $valeur.",";      

                    if($valeur > ${'max_'.$cle_station.'_'.$typedata_chron}){${'max_'.$cle_station.'_'.$typedata_chron} = $valeur;}
                    if($valeur < ${'min_'.$cle_station.'_'.$typedata_chron}){${'min_'.$cle_station.'_'.$typedata_chron} = $valeur;}

                    ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "   x1: '".$lacune_date_first."',";
                    ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron} .= "<td style='height:15px;'>".$lacune_date_first_fr."</td></tr>";
                }
                else // nous avons une valeur désignée comme lacune (fermeture de la période lacune)
                {
                    ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."',";                    
                    ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= 'null,';

                    $chron_dateheure_tab = explode(' ',$data_chron_tab['dateheure']); 
                    $chron_dateheure_fr = dateus_fr($chron_dateheure_tab[0]).' '.$chron_dateheure_tab[1];

                    ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "   x1: '".$data_chron_tab['dateheure']."',";
                    ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron} .= "<td style='height:15px;'>".$chron_dateheure_fr."</td></tr>";
                    ${'tab_lacunes_'.$cle_station.'_'.$typedata_chron}[${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}]['date_end'] = $data_chron_tab['dateheure'];                     
                }

                // On colorie différemment une lacune nouvellement crée
                $color_lacune = $color_chron;
                if($value_tab['new_lacune'] > 0){$color_lacune = $value_tab['color'];};


                ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "       y1: 1,
                                                            fillcolor: '".$color_lacune."',
                                                            opacity: 0.15,
                                                            line: {
                                                                width: 0
                                                            }
                                                        },";
                ${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}++;                      
                $lacune_date_first=''; // réinitialisation lacune
            }
            else // pas de lacune en cours
            {                                
                if($data_chron_tab['valeur'] > (-8888) && $data_chron_tab['valeur'] < (99999) )
                {   
                    ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."',";

                    /*
                    if($type_chron_array[$typedata_chron]['traitement'] == 0){$valeur = abs($data_chron_tab['valeur']);} // données simple (valeur)
                    if($type_chron_array[$typedata_chron]['traitement'] == 1){$valeur += abs($data_chron_tab['valeur']);} // Cumul
                    */

                    if($type_chron_array[$typedata_chron]['traitement'] == 0){$valeur = $data_chron_tab['valeur'];} // données simple (valeur)
                    if($type_chron_array[$typedata_chron]['traitement'] == 1){$valeur += $data_chron_tab['valeur'];} // Cumul

                    ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= $valeur.",";

                    if($valeur > ${'max_'.$cle_station.'_'.$typedata_chron}){${'max_'.$cle_station.'_'.$typedata_chron} = $valeur;}
                    if($valeur < ${'min_'.$cle_station.'_'.$typedata_chron}){${'min_'.$cle_station.'_'.$typedata_chron} = $valeur;}            


                    /*
                    if(abs($data_chron_tab['valeur']) > ${'max_'.$cle_station.'_'.$typedata_chron}){${'max_'.$cle_station.'_'.$typedata_chron} = abs($data_chron_tab['valeur']);}
                    if(abs($data_chron_tab['valeur']) < ${'min_'.$cle_station.'_'.$typedata_chron}){${'min_'.$cle_station.'_'.$typedata_chron} = abs($data_chron_tab['valeur']);}
                    */
                    if($data_chron_tab['valeur'] > ${'max_'.$cle_station.'_'.$typedata_chron}){${'max_'.$cle_station.'_'.$typedata_chron} = $data_chron_tab['valeur'];}
                    if($data_chron_tab['valeur'] < ${'min_'.$cle_station.'_'.$typedata_chron}){${'min_'.$cle_station.'_'.$typedata_chron} = $data_chron_tab['valeur'];}
                }
                else // nous avons une valeur désignée comme lacune
                {
                    ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."',";                    
                    ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= "'null',";

                    $html_tab_lacune_temp = '';
                    //${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= ",";
                    
                    if(${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}<1)
                    {
                        $html_tab_lacune_temp = "
                                                    <div class='table-container' style='float:left;width:22%;height:40vh;margin:0;margin-bottom:5px;' >\n
                                                        <table id='table_tri' cellspacing='0'>
                                                            <thead>
                                                                <tr class='header-row'>		
                                                                    <th style='width:50px;text-align:center;font-size:11px;'>".htmlaccent('Chron.')."</th>\n
                                                                    <th style='width:200px;font-size:11px;'>".htmlaccent('Date début')."</th>\n
                                                                    <th style='width:200px;font-size:11px;'>".htmlaccent('Date fin')."</th>\n                          
                                                                </tr>\n
                                                            </thead>\n                                                
                                                    ";
                    }
                    

                    $edit_lacune_temp = "        
                                    {
                                        type: 'rect',
                                        xref: 'x', // x-reference is assigned to the x-values                               
                                        yref: 'paper',  // y-reference is assigned to the plot paper [0,1]                           
                                        x0: '".$data_chron_tab['dateheure']."',
                                        y0: 0,
                                    ";

                    $lacune_date_first = $data_chron_tab['dateheure'];
                    $lacune_date_first = $data_chron_tab['dateheure'];
                    $lacune_date_first_tab = explode(' ',$lacune_date_first); 
                    $lacune_date_first_fr = dateus_fr($lacune_date_first_tab[0]).' '.$lacune_date_first_tab[1];

                    ${'tab_lacunes_'.$cle_station.'_'.$typedata_chron}[${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}]['date_first'] = $lacune_date_first; 

                    $html_tab_lacune_temp .= "
                                        <tr>
                                            <td style='height:15px;text-align:center;'>".$type_chron_array[$typedata_chron]['init_type_data']."</td>
                                            <td style='height:15px;'>".$lacune_date_first_fr."</td>
                                        ";
                }     
            }

        }

        // Choix du type de graphique (Lignes ou Bar)
        $code_type_graph = '';
        if($type_chron_array[$typedata_chron]['typegraph']=='lines')
        {
            //$code_type_graph = "mode: 'lines+markers',";
            $code_type_graph .= "mode: 'lines',";
            //mode: 'lines+markers',
            $code_type_graph .= "type: 'scatter',";
            $code_type_graph .= "line:{color:'".$value_tab['color']."',width: 1.8},";
        }
        if($type_chron_array[$typedata_chron]['typegraph']=='bar')
        {
            $code_type_graph = "type: 'bar',";
            $code_type_graph .= "marker:{
                                        color:'".$value_tab['color']."',
                                        line:{
                                            color: '".$value_tab['color']."',
                                            width: 1.1  
                                            }
                                        },";
        }

        // ECHELLE        
        if(${'max_'.$cle_station.'_'.$typedata_chron} > ${'max_'.$cle_station}){${'max_'.$cle_station} = ${'max_'.$cle_station.'_'.$typedata_chron};}
        if(${'min_'.$cle_station.'_'.$typedata_chron} < ${'min_'.$cle_station}){${'min_'.$cle_station} = ${'min_'.$cle_station.'_'.$typedata_chron};}

        ${'graph_x_'.$cle_station.'_'.$typedata_chron} = rtrim(${'graph_x_'.$cle_station.'_'.$typedata_chron}, ',');
        ${'graph_y_'.$cle_station.'_'.$typedata_chron} = rtrim(${'graph_y_'.$cle_station.'_'.$typedata_chron}, ',');

        // Paramétrage de la config générale du graphique (JS)
        ${'js_config_trace_'.$cle_station} .=   "
                                                var trace_".$cle_station."_".$typedata_chron."_".$cle_graph." = 
                                                { 
                                                    x: [".${'graph_x_'.$cle_station.'_'.$typedata_chron}."],
                                                    y: [".${'graph_y_'.$cle_station.'_'.$typedata_chron}."],
                                                    
                                                    ".$code_type_graph." // Bar, lines, scatter, ...

                                                    name: '".$init_chron." - ".$name_chron."',

                                                    // Format d'étiquette des données au survol
                                                    hovertemplate: '<br>' +
                                                                    '<b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>' +
                                                                    '<b>".$type_chron_array[$typedata_chron]['axe_nom']."</b> : %{y:.3f} ".$type_chron_array[$typedata_chron]['unite']."' 
                                                                    + '<extra></extra>',
                                                };
                                                ";

        ${'js_load_trace_'.$cle_station} .= "trace_".$cle_station."_".$typedata_chron."_".$cle_graph.",";
        
        ${'edit_lacune_'.$cle_station} .= ${'edit_lacune_'.$cle_station.'_'.$typedata_chron};
        ${'html_tab_lacune_'.$cle_station} .= ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron}."</table></div>"; // variable liée à la station contenant le tableau des lacunes
    } 


    // On fait la même chose pour l'axe des Y c'est à dire pour les données
    if(${'max_'.$cle_station} > $max_y){$max_y = ${'max_'.$cle_station};}
    if(${'min_'.$cle_station} < $min_y){$min_y = ${'min_'.$cle_station};}

    ${'edit_lacune_'.$cle_station} = rtrim(${'edit_lacune_'.$cle_station}, ',');



    // Configuration de l'échelle des axes 
    $pad_y = max(0.5, 0.1 * ($max_y - $min_y));

    $min_y_graph = $min_y - $pad_y;
    $max_y_graph = $max_y + $pad_y;

    // Affichage des Field Visit Log pour avoir des infos sur les observations réalisées manuellement
    $fieldVisit_y = $min_y_graph + (abs($pad_y) * 0.2);

    // Convertir les dates minimales et maximales en objets DateTime
    $min_x_dt = new DateTime($min_x);
    $max_x_dt = new DateTime($max_x);

    $graph_x_fieldVisit = '';
    $graph_y_fieldVisit = ''; 
    $graph_label_fieldVisit = ''; 


    /*
        SELECT DISTINCT ra.id_ra,
								ra.date_heure_ra, ra.id_eq_type,
								ra.type_appareil, ra.num_appareil, ra.etat_ra, 
								ra.hydro_heure_cote, ra.hydro_h_sonde, ra.hydro_h_echelle_1, ra.hydro_h_echelle_2, ra.hydro_num_sonde,
								ra.plu_tot_type, ra.plu_tot_first, ra.plu_tot_last, ra.plu_tot_heure_basc,
								ra.plu_cumul_tot, ra.plu_cumul_plu, ra.plu_diff_tot_plu, ra.plu_recalage_heure_plu, ra.plu_test_auget, ra.plu_nb_basculement,
								ra.nb_octet, ra.num_batterie, ra.tension_batterie, ra.num_cassette, ra.heure_init_cassette,
								ra.hydro_h_sonde_cassette, ra.plu_heure_bascul1_cassette,
								ra.hydro_recalage_sonde, ra.hydro_recalage_heure_sonde, ra.hydro_purge_sonde, 
								ra.hydro_ra_jaugeage, ra.plu_ra_bouchage, ra.plu_ra_huile_tot, ra.ra_debroussaillage, ra.ra_eau_batterie, ra.ra_transfert_data, ra.ra_delete_memory, 
								ra.piezo_conductivite, ra.piezo_temperature, ra.piezo_recalage_diff, ra.piezo_nature_repere,
								ra.piezo_instrument, ra.piezo_num_instrument, ra.piezo_prof_toitnappe, ra.piezo_prof_totale, ra.piezo_x_terrain, ra.piezo_y_terrain, ra.piezo_gps_precision,
								ra.piezo_systeme_coord, ra.piezo_pompage_encours, ra.piezo_pompage_proche, ra.piezo_pluie_crue, ra.piezo_temps_sec, ra.piezo_photos,
								ra.ra_obs, ra.ra_futur, ra.name_file_data, ra.obs_file_data, ra.pre_marquant, ra.fait_marquant, ra.agents_complement 
							FROM data_ra ra
							WHERE ra.id_station=59
							AND ra.date_heure_ra >= '2000-08-25 00:00:00'
							AND ra.date_heure_ra <='2015-08-25 23:59:59'ORDER BY ra.date_heure_ra DESC
    */

    $sql_fieldVisit = "SELECT DISTINCT ra.id_ra,
								ra.date_heure_ra, ra.id_eq_type,
								ra.type_appareil, ra.num_appareil, ra.etat_ra, 
								ra.piezo_pompage_encours, ra.piezo_pompage_proche, ra.piezo_pluie_crue, ra.piezo_temps_sec, ra.piezo_photos,
								ra.ra_obs, ra.ra_futur, ra.name_file_data, ra.obs_file_data, ra.pre_marquant, ra.fait_marquant, ra.agents_complement 
							FROM ".TABLE_DATA_RA." ra
							WHERE ra.id_station=".$cle_station."
							AND ra.date_heure_ra >= '".$min_x_dt->format('Y-m-d')." 00:00:00'
							AND ra.date_heure_ra <='".$max_x_dt->format('Y-m-d')." 23:59:59'
                            AND ra.fait_marquant = 1
                            ORDER BY ra.date_heure_ra DESC";


    $fieldVisit_query = tep_db_query($sql_link, $sql_fieldVisit);
    while($fieldVisit_tab = tep_db_fetch_array($fieldVisit_query)) 
    {
        // Convertir la date de la donnée en objet DateTime                
        $date_fieldVisit = new DateTime($fieldVisit_tab['date_heure_ra']);

        $graph_x_fieldVisit .= "'".$fieldVisit_tab['date_heure_ra']."'";
        $graph_y_fieldVisit .= $fieldVisit_y; 

        $text_label = $fieldVisit_tab['ra_obs'];
        // si plus de 30 caractères, on coupe en 2 lignes
        if (strlen($text_label) > 30) 
        {
            $text_label = wordwrap($text_label, 30, "<br>", true);
        }
        $graph_label_fieldVisit .= json_encode($text_label);

        $graph_x_fieldVisit .= ',';
        $graph_y_fieldVisit .= ','; 
        $graph_label_fieldVisit .= ',';        
    }
    
        // Paramétrisation pour le graphique
        ${'js_config_trace_'.$cle_station} .= 
        "
            var trace_".$cle_station."_fieldVisit = 
            { 
                x: [".$graph_x_fieldVisit."],
                y: [".$graph_y_fieldVisit."],
                text: [".$graph_label_fieldVisit."],

                mode: 'markers', // type de trace (scatter plot)
                type: 'scatter', // type de graphique par points
                marker: { 
                            size: 6, // taille des marqueurs  
                            color: '#FFE100',         // jaune
                            symbol: 'square',                        // forme carrée
                            line: {                                  // contour
                                width: 1, 
                                color: 'black'
                            }
                        }, 
                        
                name: \"".TEXT_CHRON_RA."\",

                // Format d'étiquette des données au survol
                hovertemplate: '<br>'
                                + '<b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>'
                                + '<b>Obs</b> : %{text}<extra></extra> ',
                    
            };
        ";

        ${'js_load_trace_'.$cle_station} .= "trace_".$cle_station."_fieldVisit,";



    // --------------------------------------------------

    // Configuration des axes et de la légende (JS)
    ${'js_layout_'.$cle_station} = 
                                "
                                    var layout_".$cle_station." = 
                                    {
                                        xaxis: 
                                        {
                                            title: {
                                                //text: 'Date',
                                                standoff: 5 // Ajuster la distance entre le titre et l'axe
                                            },
                                            rangeselector: {
                                                buttons: [
                                                    {
                                                        step: 'month',
                                                        stepmode: 'backward',
                                                        count: 1,
                                                        label: '1 mois'
                                                    },
                                                    {
                                                        step: 'month',
                                                        stepmode: 'backward',
                                                        count: 6,
                                                        label: '6 mois'
                                                    },
                                                    {
                                                        step: 'year',
                                                        stepmode: 'backward',
                                                        count: 1,
                                                        label: '1 an'
                                                    },
                                                    {
                                                        step: 'year',
                                                        stepmode: 'backward',
                                                        count: 10,
                                                        label: '10 ans'
                                                    },
                                                    {
                                                        step: 'all',
                                                        label: 'Tout'
                                                    },
                                                    {
                                                        step: 'year',
                                                        stepmode: 'todate',
                                                        count: 1,
                                                        label: 'Année en cours'
                                                    }
                                                ],
                                                font: 
                                                {
                                                    size: 12,
                                                    color: '#fff'
                                                },
                                                bgcolor: '#C1D8C3',
                                                activecolor: '#6A9C89'
                                                
                                            },

                                            type: 'date',

                                            showgrid: true,      // Affiche le quadrillage
                                            gridcolor: '#ddd',   // Couleur des lignes du quadrillage
                                            gridwidth: 1,         // Largeur des lignes du quadrillage

                                            autorange: true,
                                            
                                            tickfont: {size: 12,// Taille des caractères des graduations
                                                        family: 'roboto, arial, helvetica',
                                                        }, 

                                            titlefont: {family: 'roboto, arial, helvetica',
                                                        size: 12,
                                                        bold: true,
                                                        color: '#000000'},
                                                
                                            tickangle: 0,
                                            ticklen: 5,
                                            showline: true,
                                            linewidth: 1,
                                            automargin: true,

                                            //fixedrange: false // Désactive le panning sur l'axe des ordonnées                                            
                                        },
                                        yaxis:
                                        {
                                            //type: 'log',
                                            title: {
                                                text: '".$type_chron_array[$typedata_chron]['axe_nom']." (".$type_chron_array[$typedata_chron]['unite'].")',
                                                standoff: 15 // Ajuster la distance entre le titre et l'axe
                                            },
                                            automargin: true, 

                                            tickfont: {size: 11}, // Taille des caractères des graduations
                                            titlefont: {family: 'roboto, arial, helvetica',
                                                    size: 14,
                                                    bold: true,
                                                    color: '#000000'},
                                            tickformat: ',.1f',
                                            ticklen: 5,
                                            showline: true,
                                            linewidth: 1,

                                            autorange: false,
                                            range: [".$min_y_graph.", ".$max_y_graph."],

                                            //fixedrange: true // Désactive le panning sur l'axe des ordonnées
                                        },

                                        hovermode: 'x unified',
                                        cursor: 'pointer',
                                        margin: {l: 50, r: 10, t: 30, b: 10}, // Par défault : l: 60, r: 60, t: 80, b: 60 
                                        
                                        showlegend: true,
                                        legend: 
                                        {
                                            x: 0,
                                            y: 0.99,
                                            orientation: 'h',
                                        },

                                        barmode: 'group',
                                        bargap: 0.05,
                                        bargroupgap: 0,

                                        // Affichage lacunes
                                        shapes: [".${'edit_lacune_'.$cle_station}."],
                                        
                                    };      
                                ";  


${'data_'.$cle_station} = "var data_".$cle_station." = [".substr(${'js_load_trace_'.$cle_station}, 0, -1)."];"; // substr($str, 0, -1) pour enlever la dernière virgule de la chaine de caractère


// Implémentation des fonctions dynamiques des graphiques 
$textGraphFonction = "
                    document.getElementById('plot_".$cle_station."').on('plotly_relayout', function(eventData) 
                    {
                        var x1_format = '';
                        var x2_format = '';
                        
                        if ((eventData['xaxis.range[0]'] && eventData['xaxis.range[1]']) ||
                            (eventData['yaxis.range[0]'] && eventData['yaxis.range[1]'])) 
                        {
                            var x1 = eventData['xaxis.range[0]'];
                            var x2 = eventData['xaxis.range[1]'];
                            var y1 = eventData['yaxis.range[0]'];
                            var y2 = eventData['yaxis.range[1]'];
                            
                            // Convertir les dates au format 'yyyy-mm-dd' en 'dd-mm-yyyy'
                            if (x1 && typeof x1 === 'string') 
                            {
                                x1_date_time = x1.split(' ');

                                x1_date = x1_date_time[0].split('-').reverse().join('-');
                                x1_time = x1_date_time[1].split('.')[0]; // Récupérer l'heure

                                document.getElementById('x1Zoom').value = x1_date;
                                document.getElementById('x1Zoom_h').value = x1_time;

                                text_periode_lacune = 'du '+x1_date+' à '+x1_time;
                                document.getElementById('periode_lacune_first').value = text_periode_lacune;

                            }
                            
                            if (x2 && typeof x2 === 'string') 
                            {
                                x2_date_time = x2.split(' ');

                                x2_date = x2_date_time[0].split('-').reverse().join('-');
                                x2_time = x2_date_time[1].split('.')[0]; // Récupérer l'heure

                                document.getElementById('x2Zoom').value = x2_date;
                                document.getElementById('x2Zoom_h').value = x2_time;

                                text_periode_lacune = 'du '+x2_date+' à '+x2_time;
                                document.getElementById('periode_lacune_end').value = text_periode_lacune;
                            }
                                            
                            if (typeof y1 !== 'undefined' && !isNaN(y1)) {
                                document.getElementById('y1Zoom').value = parseInt(y1);
                            }
                            if (typeof y2 !== 'undefined' && !isNaN(y2)) {
                                document.getElementById('y2Zoom').value = parseInt(y2);
                            }
                        }
                    });


                    document.getElementById('plot_".$cle_station."').on('plotly_doubleclick', function() 
                    {
                        // Récupérer les valeurs minimale et maximale de l'axe x après le dézoom
                        var xMin = '".$min_x."';
                        var xMax = '".$max_x."';
                        
                        var x1_date_time = xMin.split(' ');
                        var x2_date_time = xMax.split(' ');

                        // Convertir les dates au format 'yyyy-mm-dd' en 'dd-mm-yyyy'
                        var x1_date = x1_date_time[0];//.split('-').reverse().join('-');
                        var x1_time = x1_date_time[1].split('.')[0]; // Récupérer l'heure

                        var x2_date = x2_date_time[0];//.split('-').reverse().join('-');
                        var x2_time = x2_date_time[1].split('.')[0]; // Récupérer l'heure
                        
                        // Réinitialisez vos valeurs ici lorsque le dézoom est effectué par double-clic
                        document.getElementById('x1Zoom').value = x1_date;
                        document.getElementById('x1Zoom_h').value = x1_time;
                        
                        document.getElementById('x2Zoom').value = x2_date;
                        document.getElementById('x2Zoom_h').value = x2_time;

                        text_periode_lacune = 'du '+x1_date+' au '+x1_time;
                        document.getElementById('periode_lacune_first').value = text_periode_lacune;

                        text_periode_lacune = 'du '+x2_date+' au '+x2_time;
                        document.getElementById('periode_lacune_end').value = text_periode_lacune;
       
                        document.getElementById('y1Zoom').value = parseInt(".$min_y_graph.");
                        document.getElementById('y2Zoom').value = parseInt(".$max_y_graph.");
                    });


";


// Préparation des données à renvoyer coté Client

$textGraph = ${'js_config_trace_' . $cle_station} . ${'data_'.$cle_station} . ${'js_layout_' . $cle_station};
$textGraph .= "Plotly.newPlot('plot_".$cle_station."', data_".$cle_station.", layout_".$cle_station.", config);";
$textGraph .= "addLogScaleButton('plot_".$cle_station."','log_".$cle_station."','yaxis');";

$text_lacunes = ${'html_tab_lacune_'.$cle_station};

$responseData = array(
    'js_layout' => ${'js_layout_' . $cle_station},
    'js_text' => $textGraph.$textGraphFonction,
    'text_lacunes' => $text_lacunes,
    'min_x' => $min_x,
    'max_x' => $max_x,
    'min_y' => $min_y_graph,
    'max_y' => $max_y_graph   
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>