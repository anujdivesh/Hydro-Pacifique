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
require('../../function/math.php');	
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

$cle_station = $dataGraph['cle_station'];
$id_typedata = $dataGraph['type_station'];
$typedata_array = $dataGraph['typedata_array'];

$min_x = $dataGraph['max_x'];
$max_x = $dataGraph['min_x'];

$min_y = 99999;
$max_y = 0;

$tab_param = $dataGraph['tab_param'];

$colorGraph = colorList();





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


// DATA CODE QUALITE
$sql_code_qual = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data 
                FROM ".TABLE_DATA_QUALITE." 
                ORDER BY id_data_qualite";
$code_qual_query = tep_db_query($sql_link,$sql_code_qual);
while ($code_qual = tep_db_fetch_array($code_qual_query))
{				
	$code_qual_array[$code_qual['id_data_qualite']] = array('init_qualite_data' => htmlaccent(html_entity_decode($code_qual['init_qualite_data'] ?? $default_string)),
                                                            'nom_qualite_data' => htmlaccent(html_entity_decode($code_qual['nom_qualite_data'] ?? $default_string))
                                                            );
} 


// On parcours la table contenant toutes les chroniques à traiter
/*
foreach($station_chron_array as $cle_station => $typedata_array) 
{
    */   
    //$nb_data = 0;
    $lacune_date_first = '';

    ${'js_config_trace_'.$cle_station} ='';
    ${'js_load_trace_'.$cle_station} ='';

    ${'edit_lacune_'.$cle_station} = '';
    ${'html_tab_lacune_'.$cle_station} = '';

    // INIT échelle des axes - Il ne peut y avoir que 2 axes  
    ${'max_'.$cle_station} = 0;
    ${'min_'.$cle_station} = 99999;
    ${'nb_chron_'.$cle_station} = 0;

    ${'hidden_check_chron_'.$cle_station} = '';

    $text_yaxis = '';
    foreach($typedata_array as $typedata_chron => $sql_chron) 
    {   

        // On affiche l'information des relevés de hauteurs manuels
        // On prépare les données pour projeter les relevés ou mesure manuel lors des RA sur le graph
        if($typedata_chron == 'ra') 
        {
            ${'nb_data_'.$cle_station.'_ra'} = 0; 
            ${'graph_x_'.$cle_station.'_ra'} = '';
            ${'graph_y_'.$cle_station.'_ra'} = '';
            
            ${'max_'.$cle_station.'_ra'} = 0;
            ${'min_'.$cle_station.'_ra'} = 9999;       

            // Données
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                // Il faudra changer le champs valeur si l'on veut afficher des valeurs des RA pluvio ou piezo

                /*
                $valeur = abs((float)$data_chron_tab['hydro_h_echelle_1']);
                if(!tep_not_null($valeur)){$valeur = abs((float)$data_chron_tab['hydro_h_sonde']);}
                */

                if($id_typedata==11) // HYDRO
                {
                    $valeur = (float)$data_chron_tab['hydro_h_echelle_1'];                    
                    if(!tep_not_null($valeur)){$valeur = (float)$data_chron_tab['hydro_h_echelle_2'];}
                    if(!tep_not_null($valeur)){$valeur = (float)$data_chron_tab['hydro_h_sonde'];}
                }

                if($id_typedata==5) // PIEZO
                {
                    $valeur = (float)$data_chron_tab['piezo_prof_toitnappe'] * 100; // parceque la donnée est saisie en m et on veut l'afficher en cm
                }


                if(tep_not_null($valeur))
                {
                    ${'nb_data_'.$cle_station.'_ra'}++;
                    
                    ${'graph_x_'.$cle_station.'_ra'} .= "'".$data_chron_tab['date_heure_ra']."'";
                    ${'graph_y_'.$cle_station.'_ra'} .= $valeur; 

                    ${'graph_x_'.$cle_station.'_ra'} .= ',';
                    ${'graph_y_'.$cle_station.'_ra'} .= ','; 

                    // -----------------------------------                        
                    // Limites du graph sur axe des abscisses X


                    // Convertir la date de la donnée en objet DateTime                
                    $date_chron = new DateTime($data_chron_tab['date_heure_ra']);

                    // Convertir les dates minimales et maximales en objets DateTime
                    $min_x_dt = new DateTime($min_x);
                    $max_x_dt = new DateTime($max_x);

                    // Comparer les dates
                    if ($date_chron < $min_x_dt) {$min_x = $date_chron->format('d-m-Y');}

                    if ($date_chron > $max_x_dt) {$max_x = $date_chron->format('d-m-Y');}


                    // -----------------------------------
                    // Limites du graph sur axe des ordonnées Y
                    if(${'min_'.$cle_station.'_ra'} > $valeur){${'min_'.$cle_station.'_ra'} = $valeur;}
                    if(${'max_'.$cle_station.'_ra'} < $valeur){${'max_'.$cle_station.'_ra'} = $valeur;}

                }
            }

            // Actualisation des bornes pour l'affichage de l'axe Y
            if(${'min_'.$cle_station.'_ra'} < $min_y)
            {
                ${'min_'.$cle_station} = ${'min_'.$cle_station.'_ra'};
                $min_y = ${'min_'.$cle_station.'_ra'};
            }
            if(${'max_'.$cle_station.'_ra'} > $max_y)
            {
                ${'max_'.$cle_station} = ${'max_'.$cle_station.'_ra'};
                $max_y = ${'max_'.$cle_station.'_ra'};
            }

            // Paramétrisation pour le graphique
            ${'js_config_trace_'.$cle_station} .= 
            "
                var trace_".$cle_station."_ra = 
                { 
                    hovermode: 'closest',
                    x: [".${'graph_x_'.$cle_station.'_ra'}."],
                    y: [".${'graph_y_'.$cle_station.'_ra'}."],

                    mode: 'markers', // type de trace (scatter plot)
                    type: 'scatter', // type de graphique par points
                    marker: { 
                            size: 8, // taille des marqueurs  
                            color: 'rgb(196, 12, 12)', // Couleur des marqueurs  
                            symbol: 'x' 
                            }, 

                    name: \"".TEXT_CHRON_RA_HEIGHT."\",

                    // Format d'étiquette des données au survol
                    hovertemplate: '<br>'
                                    + '<b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>'
                                    + '<b>"."Hauteur"."</b> : %{y:.1f} cm'
                                    + '<extra></extra>',
                                    
                        
                };
            ";

            ${'js_load_trace_'.$cle_station} .= "trace_".$cle_station."_ra,";
        }  

        // Jaugeage réalisé sur cette station
        if($typedata_chron == 'jge')
        {
            ${'nb_data_'.$cle_station.'_jge'} = 0; 
            ${'graph_x_'.$cle_station.'_jge'} = '';
            ${'graph_y_'.$cle_station.'_jge'} = '';

            
            ${'max_'.$cle_station.'_jge'} = 0;
            ${'min_'.$cle_station.'_jge'} = 9999; 

            // Données
            $data_chron_query = tep_db_query($sql_link, $sql_chron);
            while($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
            {
                // Il faudra changer le champs valeur si l'on veut afficher des valeurs des RA pluvio ou piezo

                //$valeur = abs((float)$data_chron_tab['depouil_q']);
                
                //$valeur = (float)$data_chron_tab['depouil_h'];
                $valeur = (float)$data_chron_tab['depouil_q'];

                if(tep_not_null($valeur))
                {
                    ${'nb_data_'.$cle_station.'_jge'}++;
                    
                    ${'graph_x_'.$cle_station.'_jge'} .= "'".$data_chron_tab['datetime']."'";
                    ${'graph_y_'.$cle_station.'_jge'} .= $valeur; 

                    ${'graph_x_'.$cle_station.'_jge'} .= ',';
                    ${'graph_y_'.$cle_station.'_jge'} .= ','; 
                }

                // -----------------------------------                        
                // Limites du graph sur axe des abscisses X


                // Convertir la date de la donnée en objet DateTime                
                $date_chron = new DateTime($data_chron_tab['datetime']);

                // Convertir les dates minimales et maximales en objets DateTime
                $min_x_dt = new DateTime($min_x);
                $max_x_dt = new DateTime($max_x);

                // Comparer les dates
                if ($date_chron < $min_x_dt) {$min_x = $date_chron->format('d-m-Y');}

                if ($date_chron > $max_x_dt) {$max_x = $date_chron->format('d-m-Y');}


                // -----------------------------------
                // Limites du graph sur axe des ordonnées Y
                if(${'min_'.$cle_station.'_jge'} > $valeur){${'min_'.$cle_station.'_jge'} = $valeur;}
                if(${'max_'.$cle_station.'_jge'} < $valeur){${'max_'.$cle_station.'_jge'} = $valeur;}


            }
            

            // Actualisation des bornes pour l'affichage de l'axe Y
            if(${'min_'.$cle_station.'_jge'} < $min_y)
            {
                ${'min_'.$cle_station} = ${'min_'.$cle_station.'_jge'};
                $min_y = ${'min_'.$cle_station.'_jge'};
            }
            if(${'max_'.$cle_station.'_jge'} > $max_y)
            {
                ${'max_'.$cle_station} = ${'max_'.$cle_station.'_jge'};
                $max_y = ${'max_'.$cle_station.'_jge'};
            }



            // Paramétrisation pour le graphique
            ${'js_config_trace_'.$cle_station} .= 
            "
                var trace_".$cle_station."_jge = 
                { 
                    hovermode: 'closest',
                    x: [".${'graph_x_'.$cle_station.'_jge'}."],
                    y: [".${'graph_y_'.$cle_station.'_jge'}."],

                    mode: 'markers', // type de trace (scatter plot)
                    type: 'scatter', // type de graphique
                    marker: { 
                            size: 8, // taille des marqueurs  
                            color: 'rgb(21, 21, 21)', // Couleur des marqueurs  
                            symbol: 'x' 
                            }, 

                    name: '".TEXT_CHRON_JGE."',

                    // Format d'étiquette des données au survol
                    hovertemplate: '<br>'
                                    + '<b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>'
                                    + '<b>"."Débit"."</b> : %{y:.3f} m³/s'
                                    + '<extra></extra>',
                };
            ";

            ${'js_load_trace_'.$cle_station} .= "trace_".$cle_station."_jge,";
        }


        // Chronique de données hors 'RA' et 'JGE'
        if($typedata_chron != 'ra' && $typedata_chron != 'jge')
        {   
            $text_yaxis = $type_chron_array[$typedata_chron]['axe_nom']." (".$type_chron_array[$typedata_chron]['unite'].")";  

            ${'nb_chron_'.$cle_station}++;
            
            ${'nb_data_'.$cle_station.'_'.$typedata_chron} = 0;      
            ${'max_'.$cle_station.'_'.$typedata_chron} = 0;
            ${'min_'.$cle_station.'_'.$typedata_chron} = 9999;             
            
            ${'nb_lacunes_'.$cle_station.'_'.$typedata_chron} = 0; 
            ${'tab_lacunes_'.$cle_station.'_'.$typedata_chron} = array();           

            ${'graph_x_'.$cle_station.'_'.$typedata_chron} = '';
            
            ${'graph_y_'.$cle_station.'_'.$typedata_chron} = '';    
            ${'tab_y_'.$cle_station.'_'.$typedata_chron} = array();  

            ${'codequal_'.$cle_station.'_'.$typedata_chron} = '';
            ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} = '';
            ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron} = '';

            //$id_typedata = $station_all_array[$cle_station]['type_station'];
            
            // On crée un champs invisible pour récupérer les données stations, typedata et chroniques pour la page de correction de données (si besoin)
            ${'hidden_check_chron_'.$cle_station} .= "<input type='hidden' name='check_chron[]' value='".$cle_station.'_'.$id_typedata.'_'.$typedata_chron."' />\n";
        
            // Construction des donnees pour les graphs
            // Pour chaque chronique on va chercher les données dans la base
            
            $valeur = 0; // Initialisation de la vairiable contenant une donnée (nécessaire pour l'affichage en cumul)


            $data_chron_query = tep_db_query($sql_link,$sql_chron);
            while($data_chron_tab = tep_db_fetch_array($data_chron_query))
            {   
                if(${'nb_data_'.$cle_station.'_'.$typedata_chron}>0)
                {
                    ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= ',';
                    ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= ',';
                    ${'codequal_'.$cle_station.'_'.$typedata_chron} .= ','; 
                }
                else
                {
                    ${'min_x_'.$cle_station.'_'.$typedata_chron} = $data_chron_tab['dateheure'];
                }

                
                ${'max_x_'.$cle_station.'_'.$typedata_chron} = $data_chron_tab['dateheure'];
                
                // Convertir la date de la donnée en objet DateTime                
                $date_chron = new DateTime($data_chron_tab['dateheure']);
                
                // Convertir les dates minimales et maximales en objets DateTime
                $min_x_dt = new DateTime($min_x);
                $max_x_dt = new DateTime($max_x);

                // Comparer les dates
                if ($date_chron < $min_x_dt) {$min_x = $date_chron->format('d-m-Y');}
                if ($date_chron > $max_x_dt) {$max_x = $date_chron->format('d-m-Y');}

                
                
                        
                ${'nb_data_'.$cle_station.'_'.$typedata_chron}++;
                
                // Si il y a des lacunes
                if(tep_not_null($lacune_date_first))
                {                
                    ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= $edit_lacune_temp;
                    ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron} .= $html_tab_lacune_temp;

                    //if($data_chron_tab['valeur'] != (-8888) && $data_chron_tab['valeur'] != (-9999) && $data_chron_tab['valeur'] != (-99999) && $data_chron_tab['valeur'] != (-88888) && $data_chron_tab['valeur'] != (99999) )
                    if(abs($data_chron_tab['valeur']) < 8888)
                    {
                        ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."'";

                        // Version en convertissant les données négatives en données positives
                        /*
                        if($type_chron_array[$typedata_chron]['traitement'] == 0){$valeur = abs($data_chron_tab['valeur']);} // données simple (valeur)
                        if($type_chron_array[$typedata_chron]['traitement'] == 1){$valeur += abs($data_chron_tab['valeur']);} // Cumul
                        */

                        // Version en conservant les données négatives
                        if($type_chron_array[$typedata_chron]['traitement'] == 0){$valeur = $data_chron_tab['valeur'];} // données simple (valeur)
                        if($type_chron_array[$typedata_chron]['traitement'] == 1){$valeur += $data_chron_tab['valeur'];} // Cumul
                        

                        ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= (float)$valeur;  
                        ${'tab_y_'.$cle_station.'_'.$typedata_chron}[] = (float)$valeur;      

                        if($valeur > ${'max_'.$cle_station.'_'.$typedata_chron}){${'max_'.$cle_station.'_'.$typedata_chron} = $valeur;}
                        if($valeur < ${'min_'.$cle_station.'_'.$typedata_chron}){${'min_'.$cle_station.'_'.$typedata_chron} = $valeur;}

                        ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "   x1: '".$lacune_date_first."',";
                        ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron} .= "<td style='height:15px;'>".$lacune_date_first_fr."</td></tr>";

                        // Assignation du code qualité
                        
                        $codequal = 'null';
                        if(isset($code_qual_array[$data_chron_tab['id_codequal']]))
                        {$codequal = $code_qual_array[$data_chron_tab['id_codequal']]['nom_qualite_data'];}                        
                        //{$codequal = $code_qual_array[$data_chron_tab['id_codequal']]['init_qualite_data'];}

                        ${'codequal_'.$cle_station.'_'.$typedata_chron} .= "'".$codequal."'";    
                    }
                    else
                    {
                        ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."'";                    
                        ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= 'null';
                        ${'codequal_'.$cle_station.'_'.$typedata_chron} .= 'null';  
                        
                        $chron_dateheure_tab = explode(' ',$data_chron_tab['dateheure']); 
                        $chron_dateheure_fr = dateus_fr($chron_dateheure_tab[0]).' '.$chron_dateheure_tab[1];

                        ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "   x1: '".$data_chron_tab['dateheure']."',";
                        ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron} .= "<td style='height:15px;'>".$chron_dateheure_fr."</td></tr>";
                        ${'tab_lacunes_'.$cle_station.'_'.$typedata_chron}[${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}]['date_end'] = $data_chron_tab['dateheure']; 
                    }

                    $color_lacune = '#FF6F69';
                    ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= "       y1: 1,
                                                                                fillcolor: '".$colorGraph[$tab_param[$typedata_chron]['color']]."',
                                                                                opacity: 0.15,
                                                                                line: {width: 0}
                                                                            }";                

                    ${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}++;                      
                    $lacune_date_first=''; // réinitialisation lacune
                }
                else // pas de lacune en cours
                {
                    //if($data_chron_tab['valeur'] != (-8888) && $data_chron_tab['valeur'] != (-9999) && $data_chron_tab['valeur'] != (-99999) && $data_chron_tab['valeur'] != (-88888) && $data_chron_tab['valeur'] != (99999))
                    if(abs($data_chron_tab['valeur']) < 8888)
                    {
                        ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."'";

                        // Version en convertissant les données négatives en données positives
                        /*
                        if($type_chron_array[$typedata_chron]['traitement'] == 0){$valeur = abs($data_chron_tab['valeur']);} // données simple (valeur)
                        if($type_chron_array[$typedata_chron]['traitement'] == 1){$valeur += abs($data_chron_tab['valeur']);} // Cumul
                        */

                        // Version en conservant les données négatives
                        if($type_chron_array[$typedata_chron]['traitement'] == 0){$valeur = $data_chron_tab['valeur'];} // données simple (valeur)
                        if($type_chron_array[$typedata_chron]['traitement'] == 1){$valeur += $data_chron_tab['valeur'];} // Cumul
                        

                        ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= (float)$valeur;
                        ${'tab_y_'.$cle_station.'_'.$typedata_chron}[] = (float)$valeur;  

                        if($valeur > ${'max_'.$cle_station.'_'.$typedata_chron}){${'max_'.$cle_station.'_'.$typedata_chron} = $valeur;}
                        if($valeur < ${'min_'.$cle_station.'_'.$typedata_chron}){${'min_'.$cle_station.'_'.$typedata_chron} = $valeur;}

                        // Assignation du code qualité
                        
                        $codequal = 'null';
                        if(isset($code_qual_array[$data_chron_tab['id_codequal']]))
                        {$codequal = $code_qual_array[$data_chron_tab['id_codequal']]['nom_qualite_data'];}
                        //{$codequal = $code_qual_array[$data_chron_tab['id_codequal']]['init_qualite_data'];}

                        ${'codequal_'.$cle_station.'_'.$typedata_chron} .= "'".$codequal."'";    
                    }
                    else
                    {
                        ${'graph_x_'.$cle_station.'_'.$typedata_chron} .= "'".$data_chron_tab['dateheure']."'";                    
                        ${'graph_y_'.$cle_station.'_'.$typedata_chron} .= 'null';                        
                        ${'codequal_'.$cle_station.'_'.$typedata_chron} .= 'null';  
    
                        $html_tab_lacune_temp = '';
                        ${'edit_lacune_'.$cle_station.'_'.$typedata_chron} .= ","; // on ajoute une virgule entre chaque lacunes

                        if(${'nb_lacunes_'.$cle_station.'_'.$typedata_chron}<1)
                        {
                            $html_tab_lacune_temp = "
                                                    <div class='table-container' style='float:left;width:25%;height:40vh;margin:0;margin-bottom:5px;' >\n
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


                // Actualisation des bornes pour l'affichage de l'axe Y
                if($min_y > ${'min_'.$cle_station.'_'.$typedata_chron}){$min_y = ${'min_'.$cle_station.'_'.$typedata_chron};}
                if($max_y < ${'max_'.$cle_station.'_'.$typedata_chron}){$max_y = ${'max_'.$cle_station.'_'.$typedata_chron};}

            }
            

            // Choix du type de graphique (Lignes ou Bar)
            $code_type_graph = '';

            /*
            $id_eq_type = $type_chron_array[$typedata_chron]['id_eq_type_data'];
            if($eq_type_array[$id_eq_type]['type_graph']=='lines')
            {
                //$code_type_graph = "mode: 'lines+markers',";
                $code_type_graph = "mode: 'lines',";
                //mode: 'lines+markers',
                $code_type_graph .= "type: 'scatter',";
            }
            if($eq_type_array[$id_eq_type]['type_graph']=='bar')
            {$code_type_graph = "type: 'bar',";}
            */
            
            if($type_chron_array[$typedata_chron]['typegraph']=='lines')
            {
                //$code_type_graph = "mode: 'lines+markers',";
                $code_type_graph = "mode: 'lines',";
                $code_type_graph .= "type: 'scatter',";
            }
            if($type_chron_array[$typedata_chron]['typegraph']=='bar')
            {
                $code_type_graph = "type: 'bar',";
            }


            // ECHELLE        
            if(${'max_'.$cle_station.'_'.$typedata_chron} > ${'max_'.$cle_station}){${'max_'.$cle_station} = ${'max_'.$cle_station.'_'.$typedata_chron};}
            if(${'min_'.$cle_station.'_'.$typedata_chron} < ${'min_'.$cle_station}){${'min_'.$cle_station} = ${'min_'.$cle_station.'_'.$typedata_chron};}

            // Paramétrage de la config générale du graphique (JS)

            
            ${'js_config_trace_'.$cle_station} .= 
                                                "
                                                    var trace_".$cle_station."_".$typedata_chron." = 
                                                    { 
                                                        x: [".${'graph_x_'.$cle_station.'_'.$typedata_chron}."],
                                                        y: [".${'graph_y_'.$cle_station.'_'.$typedata_chron}."],
                                                        text: [".${'codequal_'.$cle_station.'_'.$typedata_chron}."], // Utiliser les valeurs absolues (peut etre ??),
                                                        
                                                        ".$code_type_graph." // Bar, lines, scatter, ...

                                                        legendgroup: 'tdc_".$typedata_chron."', // groupe logique pour retrouver la trace pour un paramétrage par l'utilisateur

                                                        name: '".$type_chron_array[$typedata_chron]['init_type_data']." - ".$type_chron_array[$typedata_chron]['nom_type_data']."',

                                                        // Format d'étiquette des données au survol
                                                        hovertemplate: '<br>'
                                                                        + '<b>Date</b> : %{x|%d-%m-%Y %H:%M:%S}<br>' 
                                                                        + '<b>".$type_chron_array[$typedata_chron]['axe_nom']."</b> : %{y:.3f} ".$type_chron_array[$typedata_chron]['unite']."<br>' 
                                                                        + '<b>"."Code Qualité"."</b> : %{text} '
                                                                        + '<extra></extra>',
                                                        marker: {
                                                                    color: '".$colorGraph[$tab_param[$typedata_chron]['color']]."',
                                                                    line: {
                                                                        color: '".$colorGraph[$tab_param[$typedata_chron]['color']]."',                                                                       
                                                                        width: 1.8,  
                                                                    } 
                                                        },
                                                                    
                                                        line: {
                                                            color: '".$colorGraph[$tab_param[$typedata_chron]['color']]."',
                                                            width: 1.8,  
                                                        }                                                      
                                                    };

                                                ";

            ${'js_load_trace_'.$cle_station} .= "trace_".$cle_station."_".$typedata_chron.",";
            
            ${'edit_lacune_'.$cle_station} .= ${'edit_lacune_'.$cle_station.'_'.$typedata_chron};
            ${'html_tab_lacune_'.$cle_station} .= ${'html_tab_lacune_'.$cle_station.'_'.$typedata_chron}."</table></div>"; // variable liée à la station contenant le tableau des lacunes

        } 
     
        
        
                                                 
    } 


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

    
    // Configuration de l'échelle des axes 
    $pad_y = max(0.5, 0.1 * ($max_y - $min_y));

    $min_y_graph = $min_y - $pad_y;
    $max_y_graph = 1.1*($max_y + $pad_y);

    // Affichage des Field Visit Log pour avoir des infos sur les observations réalisées manuellement
    $fieldVisit_y = $min_y_graph + (abs($pad_y) * 0.2);

    // Convertir les dates minimales et maximales en objets DateTime
    $min_x_dt = new DateTime($min_x);
    $max_x_dt = new DateTime($max_x);

    $graph_x_fieldVisit = '';
    $graph_y_fieldVisit = ''; 
    $graph_label_fieldVisit = ''; 

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

                xaxis: 'x2',

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

    
    // Configuration d'affichage du graphique

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
                                                    /*
                                                    {
                                                        step: 'year',
                                                        stepmode: 'todate',
                                                        count: 1,
                                                        label: 'Année en cours'
                                                    }
                                                    */    
                                                ],
                                                font: 
                                                {
                                                    size: 12,
                                                    color: '#fff'
                                                },
                                                bgcolor: '#C1D8C3',
                                                activecolor: '#6A9C89',
                                                y: -0.3,  yanchor: 'top',      // <— sous le plot un peu en-dessous du graph
                                                x: 1,  xanchor: 'right',
                                            },
                                            
                                            rangeslider: {
                                                visible: true,
                                                thickness: 0.05,
                                                bgcolor: '#F2F2F2',
                                                yaxis: {
                                                            rangemode: 'fixed',
                                                            range: [".$min_y_graph.", ".$max_y_graph."] 
                                                        }
                                            },
                                            
                                            type: 'date',

                                            showgrid: true,      // Affiche le quadrillage
                                            gridcolor: '#ddd',   // Couleur des lignes du quadrillage
                                            gridwidth: 1,         // Largeur des lignes du quadrillage

                                            autorange: true,
                                                                                        
                                            titlefont: {family: 'roboto, arial, helvetica',
                                                size: 12,
                                                bold: true,
                                                color: '#000000'},
                                                
                                            tickangle: 0,
                                            ticklen: 5,
                                            showline: true,
                                            linewidth: 1,
                                            automargin: true,

                                            fixedrange: false // Désactive le panning sur l'axe des ordonnées                                            
                                        },
                                        xaxis2: {
                                            overlaying: 'x',
                                            matches: 'x',
                                            showgrid: false,
                                            zeroline: false,
                                            showticklabels: false, // masque les labels
                                            ticks: ''
                                        },
                                        yaxis:
                                        {
                                            title: {
                                                text: '".$text_yaxis."',
                                                standoff: 15 // Ajuster la distance entre le titre et l'axe
                                            },
                                            automargin: true, 

                                            tickfont: {size: 11,// Taille des caractères des graduations
                                                        family: 'roboto, arial, helvetica',
                                                        }, 
                                            
                                            titlefont: {family: 'roboto, arial, helvetica',
                                                    size: 13,
                                                    bold: true,
                                                    color: '#000000'},
                                            tickformat: ',.1f',
                                            ticklen: 5,
                                            showline: true,
                                            linewidth: 1,

                                            autorange: false,
                                            range: [".$min_y_graph.", ".$max_y_graph."],
                                        
                                            fixedrange: false // Désactive le panning sur l'axe des ordonnées
                                        },

                                        dragmode: 'zoom', // Permet le panoramique

                                        
                                        //hovermode: 'x',
                                        //hovermode: 'closest',
                                        //hoverdistance: 200,

                                        hovermode: 'x unified',
                                        
                                        cursor: 'pointer',
                                        margin: {l: 50, r: 10, t: 30, b: 0}, // Par défault : l: 60, r: 60, t: 80, b: 60 
                                        
                                        showlegend: true,
                                        legend: 
                                        {
                                            x: 0,
                                            y: 0.94,
                                            orientation: 'h',
                                            font: {size: 11},
                                        },

                                        barmode: 'group',
                                        bargap: 0.05,
                                        bargroupgap: 0,
                                        
                                        
                                        // Affichage lacunes
                                        shapes: [".${'edit_lacune_'.$cle_station}."],

                                        
                                    };      
                                ";  





    // Gestion complémentaire si une seul chronique s'affiche
    $text_button_calcul = "";
    $text_button_stats = "";    
    $text_button_tab = "";

    if(${'nb_chron_'.$cle_station} == 1 && $typedata_chron <> 'ra' && $typedata_chron <> 'jge')
    {
        
        // Calculs et affichage des lignes de stats

        $min_x_trace = ${'min_x_'.$cle_station.'_'.$typedata_chron};
        $max_x_trace = ${'max_x_'.$cle_station.'_'.$typedata_chron};

        $value_mean = mean(${'tab_y_'.$cle_station.'_'.$typedata_chron});
        $value_mean_format = rtrim(rtrim(round($value_mean, 3), '0'), '.');

        // Preparation pour affichage de lognes de stats (moyn, médianne, p25, p75)
        ${'js_config_trace_'.$cle_station} .= 
                                            "
                                                // Moyenne 
                                                var meanValue = ".round($value_mean, 3).";

                                                var meanTrace_".$cle_station." = 
                                                {
                                                    x: ['".$min_x_trace."','".$max_x_trace."'],
                                                    y: [meanValue, meanValue],
                                                    mode: 'lines',
                                                    line: {
                                                        color: '#000', // Choisissez une couleur pour la ligne de la moyenne -- Violet
                                                        width: 3,
                                                        dash: 'dashdot' // Optionnel : rend la ligne en pointillés
                                                    },
                                                    name: 'Moyenne : '+meanValue,
                                                    hoverinfo: 'skip', // Désactive l'affichage des infos au survol pour cette trace
                                                    visible: false
                                                };
                                            ";

        // Définir un tableau de configurations pour les percentiles et quartiles
        $percentileConfigurations = [
                                        ['threshold' => 99, 'id' => 'p99', 'color' => '#059212', 'description' => 'Percentile (99%)'], // Vert foncé
                                        ['threshold' => 90, 'id' => 'p90', 'color' => '#6930C3', 'description' => 'Percentile (90%)'], // bleu violet
                                        ['threshold' => 75, 'id' => 'q3', 'color' => '#F58634', 'description' => 'Last quartile (75%)'], // orange
                                        ['threshold' => 50, 'id' => 'q2', 'color' => '#FF0000', 'description' => 'Médiane'], // Rouge
                                        ['threshold' => 25, 'id' => 'q1', 'color' => '#FFE227', 'description' => 'First quartile (25%)'], // jaune fort
                                        ['threshold' => 10, 'id' => 'p10', 'color' => '#00B7C2', 'description' => 'Percentile (10%)'], // bleu turquoise
                                        ['threshold' => 1, 'id' => 'p1', 'color' => '#54E346', 'description' => 'Percentile (1%)'], // Vert flash
                                    ];

        // Itérer sur le tableau de configurations
        //print_r(${'tab_y_' . $cle_station . '_' . $typedata_chron});
        foreach ($percentileConfigurations as $config) 
        {
            $valueP = calculerPercentile(${'tab_y_' . $cle_station . '_' . $typedata_chron}, $config['threshold']);
            //if($config['threshold']=='75'){echo $cle_station.','. $config['id'].','. $valueP.','. $min_x_trace.','. $max_x_trace.','. $config['color'].','. $config['description'];}
            ${'js_config_trace_' . $cle_station} .= generateQuartileTrace($cle_station, $config['id'], $valueP, $min_x_trace, $max_x_trace, $config['color'], $config['description']);
        }                           
        
        // Ajouter les traces à js_load_trace
        $traceNames = array_map(function($config) use ($cle_station) {
                                                                        return $config['id'] . "Trace_" . $cle_station;
                                                                    }, $percentileConfigurations);

        // Ajouter la trace de la moyenne au début de la liste des traces
        array_unshift($traceNames, "meanTrace_" . $cle_station);

        // Implode les noms des traces pour js_load_trace
        ${'js_load_trace_' . $cle_station} .= implode(",", $traceNames) . ",";

        //----------------------------------------------------------------------------


        $text_button_calcul = 
                "
                    <form name='calcul_chron_".$cle_station."' id='calcul_chron_".$cle_station."' action='data_chron.php' target='_blank' method='post' enctype='multipart/form-data'>\n
                                                                                    
                        <input type='hidden' name='date_1' id='date_1' value='".$min_x."' />\n
                        <input type='hidden' name='date_2' id='date_2' value='".$max_x."' />\n "
                    
                        .${'hidden_check_chron_'.$cle_station}. // Affichage des Hidden pour soumission formulaire pour page Calcul des chroniques    
                    
                        "<input type='submit' class='button_calcul' name='button_calcul' 
                                value='"."Modifier ou Corriger la Chronique"."' />


                    </form>
                "; 
        
        $text_button_stats = 
                "
                    <input type='button' class='button_stats' name='button_stats' 
                            value='"."Statistiques"."' 
                            style=''
                            onClick='afficheStats(".$cle_station.",".$id_typedata.",".$typedata_chron.")'/>
                ";

        $text_button_tab = 
                "
                    <input type='button' class='button_tab' name='button_tab' 
                            value='"."Data"."' 
                            style=''
                            onClick='afficheTab(".$cle_station.",".$id_typedata.",".$typedata_chron.")'/>
                ";
            
    }

${'data_'.$cle_station} = "var data_".$cle_station." = [".substr(${'js_load_trace_'.$cle_station}, 0, -1)."];"; // substr($str, 0, -1) pour enlever la dernière virgule de la chaine de caractère

// Préparation des données à renvoyer coté Client
$textGraph = ${'js_config_trace_' . $cle_station} . ${'data_'.$cle_station} . ${'js_layout_' . $cle_station};
$textGraph .= "Plotly.newPlot('plot_".$cle_station."', data_".$cle_station.", layout_".$cle_station.", config);";
$textGraph .= "addLogScaleButton('plot_".$cle_station."','log_".$cle_station."','yaxis');";


// Implémentation des fonctions dynamiques des graphiques 
// Implémentation des fonctions dynamiques des graphiques 
$textGraphFonction = "
                    var gd = document.getElementById('plot_".$cle_station."');

                    gd.on('plotly_relayout', function(eventData) 
                    {
                        var x1_format = '';
                        var x2_format = '';

                        // --- X: gérer les deux formats + autorange + fallback layout ---
                        var x1 = eventData['xaxis.range[0]'];
                        var x2 = eventData['xaxis.range[1]'];

                        if ((x1 === undefined || x2 === undefined) && Array.isArray(eventData['xaxis.range'])) 
                        {
                            x1 = eventData['xaxis.range'][0];
                            x2 = eventData['xaxis.range'][1];
                        }

                        if (eventData['xaxis.autorange'] === true) {
                            x1 = '".$min_x."';
                            x2 = '".$max_x."';
                        }

                        if ((x1 === undefined || x2 === undefined) && gd && gd.layout && gd.layout.xaxis && Array.isArray(gd.layout.xaxis.range)) {
                            x1 = gd.layout.xaxis.range[0];
                            x2 = gd.layout.xaxis.range[1];
                        }

                        // --- Y: gérer tableau ou indexés + autorange ---
                        var y1 = eventData['yaxis.range[0]'];
                        var y2 = eventData['yaxis.range[1]'];
                        if ((y1 === undefined || y2 === undefined) && Array.isArray(eventData['yaxis.range'])) {
                            y1 = eventData['yaxis.range'][0];
                            y2 = eventData['yaxis.range'][1];
                        }
                        if (eventData['yaxis.autorange'] === true && gd && gd.layout && gd.layout.yaxis && Array.isArray(gd.layout.yaxis.range)) {
                            y1 = gd.layout.yaxis.range[0];
                            y2 = gd.layout.yaxis.range[1];
                        }

                        // --- MAJ des champs (dates en dd-mm-yyyy si string) ---
                        if (x1 && typeof x1 === 'string') {
                            x1_format = x1.split(' ')[0].split('-').reverse().join('-');
                            document.getElementById('x1Zoom').value = x1_format;
                        }
                        if (x2 && typeof x2 === 'string') {
                            x2_format = x2.split(' ')[0].split('-').reverse().join('-');
                            document.getElementById('x2Zoom').value = x2_format;
                        }
                        if (typeof y1 !== 'undefined' && !isNaN(y1)) {
                            document.getElementById('y1Zoom').value = parseInt(y1);
                        }
                        if (typeof y2 !== 'undefined' && !isNaN(y2)) {
                            document.getElementById('y2Zoom').value = parseInt(y2);
                        }
                    });

                    // Suivi pendant le drag du rangeslider ET du zoom Y pour MAJ en temps réel
                    gd.on('plotly_relayouting', function(eventData) 
                    {
                        // --- X en temps réel (rangeslider) ---
                        var xr = eventData['xaxis.range'] || [eventData['xaxis.range[0]'], eventData['xaxis.range[1]']];
                        if (Array.isArray(xr) && xr[0] !== undefined && xr[1] !== undefined) {
                            var x1_format = '';
                            var x2_format = '';
                            if (typeof xr[0] === 'string') {
                                x1_format = xr[0].split(' ')[0].split('-').reverse().join('-');
                                document.getElementById('x1Zoom').value = x1_format;
                            }
                            if (typeof xr[1] === 'string') {
                                x2_format = xr[1].split(' ')[0].split('-').reverse().join('-');
                                document.getElementById('x2Zoom').value = x2_format;
                            }
                        }

                        // --- Y en temps réel (drag/zoom/pan sur Y) ---
                        var yr = eventData['yaxis.range'] || [eventData['yaxis.range[0]'], eventData['yaxis.range[1]']];
                        if (Array.isArray(yr) && yr[0] !== undefined && yr[1] !== undefined) {
                            if (!isNaN(yr[0])) { document.getElementById('y1Zoom').value = parseInt(yr[0]); }
                            if (!isNaN(yr[1])) { document.getElementById('y2Zoom').value = parseInt(yr[1]); }
                        }
                    });

                    gd.on('plotly_doubleclick', function() 
                    {
                        // Reset X d'après tes bornes initiales PHP
                        var xMin = '".$min_x."';
                        var xMax = '".$max_x."';

                        var x1_date = xMin;
                        var x2_date = xMax;

                        document.getElementById('x1Zoom').value = x1_date;
                        document.getElementById('x2Zoom').value = x2_date;
                        document.getElementById('y1Zoom').value = parseInt(".$min_y_graph.");
                        document.getElementById('y2Zoom').value = parseInt(".$max_y_graph.");
                    });
";



$text_lacunes = ${'html_tab_lacune_'.$cle_station};


$responseData = array(
    'js_text' => $textGraph.$textGraphFonction,
    'text_lacunes' => $text_lacunes,
    'text_button_calcul' => $text_button_calcul,
    'text_button_stats' => $text_button_stats,
    'text_button_tab' => $text_button_tab,
    'min_x' => $min_x,
    'max_x' => $max_x,
    'min_y' => $min_y_graph,
    'max_y' => $max_y_graph,   
    'sql' => $sql_chron
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;




// FONCTION PHP POUR AUTOMATISER 

// TRACE DE PERCENTILE
function generateQuartileTrace($cle_station, $percent, $value, $min, $max, $color, $name) 
{
    $value_format = 0;
    if($value <> 0){$value_format = rtrim(rtrim(round($value, 3), '0'), '.');}


    $js_code = "
        

        var ".$percent."Trace_{$cle_station} = 
        {
            x: ['".$min."','".$max."'],
            y: [".$value_format.", ".$value_format."],
            mode: 'lines',
            line: {
                color: '".$color."',
                width: 3,
                dash: 'dashdot'
            },
            name: '".$name." : ' + ".$value_format.",
            hoverinfo: 'skip',
            visible: false
        };
    ";

    return $js_code;
}












?>