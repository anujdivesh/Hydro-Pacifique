<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export au format xlsx 
- Ce script permet de générer les fichiers xlsx directement sur le serveur en tâche caché.
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


// Appels à la librairie phpspreadsheet

// Librairy PhpSpreadsheet
require('../../../php-excel/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetManager {
    private static $spreadsheet;

    public static function getSpreadsheet() {
        if (!isset(self::$spreadsheet)) {
            self::$spreadsheet = new Spreadsheet();
        }
        return self::$spreadsheet;
    }
}


// Commun

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');

// Récupération des données JSON envoyées depuis la requête AJAX
$jsonData = file_get_contents('php://input');

// Décodage des données JSON en tableau associatif
$data = json_decode($jsonData, true);

// Accès aux données transmise depuis Client
$territoire_id = $data['idTerritoire'];
$listStation = $data['listStation'];
$chemin_folder = $data['cheminFolder'];


// --------------------------------------
// Récupération des données dans la BDD



// TABLE TERRITOIRE
$sql_territoire = "SELECT DISTINCT t.id_territoire, t.init_territoire, t.nom_territoire, t.theme_region
					FROM ".TABLE_TERRITOIRE." t 
					WHERE t.id_territoire=".$territoire_id;
$territoire_query = tep_db_query($sql_link,$sql_territoire);
$territoire = tep_db_fetch_array($territoire_query);
$init_territoire = $territoire['init_territoire'];
$nom_territoire = $territoire['nom_territoire'];
$theme_region = $territoire['theme_region'];


// TABLE REGION / TERRITOIRE : Province pour NC - Iles pour PF et WF
$sql_region = "SELECT DISTINCT id_region, nom_region 
				FROM ".TABLE_REGION." 
				WHERE id_territoire=".$territoire_id;
$region_query = tep_db_query($sql_link,$sql_region);
while ($region = tep_db_fetch_array($region_query))
{
	$region_array[$region['id_region']] = $region['nom_region'];
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

// TABLE REGIONHYDRO
$sql_regionhydro = "SELECT DISTINCT id, nom 
                FROM ".TABLE_REGIONHYDRO." 
                WHERE id_territoire=".$territoire_id."
				ORDER BY nom ASC";
$regionhydro_query = tep_db_query($sql_link,$sql_regionhydro);
while ($regionhydro = tep_db_fetch_array($regionhydro_query))
{
	$regionhydro_array[$regionhydro['id']] = $regionhydro['nom'];
}

// TABLE NATURE (PIEZO)
$sql_nature = "SELECT DISTINCT id, libelle 
                FROM ".TABLE_STATION_NATURE;
$nature_query = tep_db_query($sql_link,$sql_nature);
while ($nature_tab = tep_db_fetch_array($nature_query))
{
	$nature_array[$nature_tab['id']] = $nature_tab['libelle'];
}



// TABLE STATION
$sql_station = "SELECT DISTINCT id_station, id_station_old, station_type, 
                                nom_station, nom_court, code_station, num_irh, 
                                active_station, suivi, armee,
                                id_territoire, id_region, id_commune, id_regionhydro, id_riviere
                                id_tournee, site_station, vallee_station, riviere_station, 
                                altitude_station, orientation_station, 
                                latitude_station, longitude_station, ign_station_x, ign_station_y, lamb_station_x, lamb_station_y,
                                date_installation_station, date_fermeture_station,
                                description_station, source_info, proprio_station,
                                transmission_station, correct_station,
                                piezo_id_nature, piezo_sonde, piezo_precision, piezo_maitre_ouvrage, piezo_date_realisation, z_sol
					FROM ".TABLE_STATION." 
				    WHERE id_station IN (".$listStation.")";

// TABLE CARACTERISTIQUE
$sql_caract = "SELECT DISTINCT s.nom_station, s.code_station,
                                c.id, c.date, c.prof, c.materiaux_tete, c.dim_tete_ext, c.materiaux_tub_inter, c.diam_tub_inter, 
                                c.materiaux_dalle, c.dim_dalle, c.dist_capto_tube, c.dist_tube_dalle, c.dist_dalle_sol, c.presence_capot,
                                c.etat, c.activite, c.utilisation, c.equipement_exploitation, c.schema_tete, c.schema_protect,
                                c.obs
                    FROM ".TABLE_STATION_PIEZO_CARACTERISTIQUE." c
                    JOIN ".TABLE_STATION." s ON c.id_station=s.id_station
                    WHERE c.id_station IN (".$listStation.")
                    ORDER BY c.date DESC";


// TABLE REPERE
// Requête sur Repere sur le puits de la station 
$sql_repere = "SELECT DISTINCT s.nom_station, s.code_station,
                                r.id, r.nature_repere, r.code_repere, r.z_repere, r.precision_repere, r.date_debut_valid, r.date_fin_valid,
								r.nature_repere_1, r.z_repere_g1, r.nature_repere_2, r.z_repere_g2, r.obs 
                    FROM ".TABLE_STATION_PIEZO_REPERE." r
                    JOIN ".TABLE_STATION." s ON r.id_station=s.id_station
                    WHERE r.id_station IN (".$listStation.")
                    ORDER BY r.date_debut_valid DESC";

// --------------------------------------
// Initialisation de variables locales

$todayTime = new DateTime(); // Crée un objet DateTime pour la date actuelle
$today_formatted = $todayTime->format('dmY');


// --------------------------------------
// CREATION FICHIER OU MODIFICATION FICHIER EXISTANT

// Librairie pour créer le fichier XLSX -> Spreadsheet
// On crée un object Spreadsheet
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();


// Temps au début du script
$startTime = microtime(true);
    
    // FEUILLE 1
    $spreadsheet->createSheet();
    $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
    $spreadsheet->setActiveSheetIndex($nb_feuil);

    $sheet1 = $spreadsheet->getActiveSheet();
    $nomFeuil = 'Identification'; 
    $sheet1->setTitle($nomFeuil);
    
               
    // En-tête de colonne
    //$sheet1->getColumnDimension('A')->setWidth(25);

    $sheet1->setCellValue('A1', 'Code Station');
    $sheet1->setCellValue('B1', 'Nom Station');
    $sheet1->setCellValue('C1', 'Site');    
    $sheet1->setCellValue('D1', $theme_region);    
    $sheet1->setCellValue('E1', 'Commune');
    $sheet1->setCellValue('F1', 'Région Hydrographique');
    $sheet1->setCellValue('G1', 'Nappe');
    $sheet1->setCellValue('H1', 'X_RGNC');
    $sheet1->setCellValue('I1', 'Y_RGNC');
    $sheet1->setCellValue('J1', 'X_WGS');
    $sheet1->setCellValue('K1', 'Y_WGS');
    $sheet1->setCellValue('L1', 'Statut (active / historique');
    $sheet1->setCellValue('M1', 'Description');  
    $sheet1->setCellValue('N1', 'Z sol');
    $sheet1->setCellValue('O1', 'Précision');
    $sheet1->setCellValue('P1', 'Acquifère capté');
    $sheet1->setCellValue('Q1', 'Nature');    
    $sheet1->setCellValue('R1', 'Maître d\'ouvrage');    
    $sheet1->setCellValue('S1', 'Date de réalisation');
    $sheet1->setCellValue('T1', 'Sonde'); 
    

    // Contenu
    $nb_station = 0;
    $num_ligne = 2;
    $piezo_encours = false;

    
    $station_query = tep_db_query($sql_link,$sql_station);
    while($station_tab = tep_db_fetch_array($station_query))
    {   
        $type_data = $station_tab['station_type'];
        
        $code_station = $station_tab['code_station'];
        $nom_station = $station_tab['nom_station'];
        $nom_court = $station_tab['nom_court'];

        $site = $station_tab['site_station'];        

        
        $region_geo = '';
        if(!empty($station_tab['id_region']) && isset($region_array[$station_tab['id_region']]))
        {$region_geo = $region_array[$station_tab['id_region']];}

        $commune = '';
        if(!empty($station_tab['id_commune']) && isset($commune_array[$station_tab['id_commune']]))
        {$commune = $commune_array[$station_tab['id_commune']];}

        $region_hydro = '';
        if(!empty($station_tab['id_regionhydro']) && isset($regionhydro_array[$station_tab['id_regionhydro']]))
        {$region_hydro = $regionhydro_array[$station_tab['id_regionhydro']];}

        $nappe = ''; // pas dans la base pour le moment 

        $lamb_station_x = $station_tab['lamb_station_x']; 
        $lamb_station_y = $station_tab['lamb_station_y'];
        $ign_station_x = $station_tab['ign_station_x']; 
        $ign_station_y = $station_tab['ign_station_y']; 

        $altitude_station = $station_tab['altitude_station']; // pas utilisé pour le moment

        $active_station = 'Historique';
        if($station_tab['active_station'] > 0){$active_station = 'Active';}
        
        $description_station = $station_tab['description_station'];
        
        if($type_data == 5)
        {
            $piezo_encours = true;

            $z_sol = $station_tab['z_sol']; 
            $precision = $station_tab['piezo_precision'];            
            $aquifere = ''; // pas dans la base pour le moment 
            
            $nature = $station_tab['piezo_id_nature'];
            if(isset($nature_array[$station_tab['piezo_id_nature']]))
            {$nature = $nature_array[$station_tab['piezo_id_nature']];}
            
            $precision = $station_tab['piezo_precision'];
            $maitre_ouvrage = $station_tab['piezo_maitre_ouvrage'];

            $date_realisation_formated = '';
            $date_realisation = $station_tab['piezo_date_realisation'];
            if(!empty($date_realisation))
            {
                $date_object = DateTime::createFromFormat('Y-m-d H:i:s', $date_realisation);
                $date_realisation_formated = $date_object->format('d-m-Y');
            }

            $sonde_piezo = 'Non';
            if($station_tab['piezo_sonde'] > 0){$sonde_piezo = 'Oui';}
            
        }

        $sheet1->setCellValue('A'.$num_ligne, $code_station);
        $sheet1->setCellValue('B'.$num_ligne, $nom_station);
        $sheet1->setCellValue('C'.$num_ligne, $site);
        $sheet1->setCellValue('D'.$num_ligne, $region_geo);
        $sheet1->setCellValue('E'.$num_ligne, $commune);
        $sheet1->setCellValue('F'.$num_ligne, $region_hydro);
        $sheet1->setCellValue('G'.$num_ligne, $nappe);
        $sheet1->setCellValue('H'.$num_ligne, $lamb_station_x);
        $sheet1->setCellValue('I'.$num_ligne, $lamb_station_y);
        $sheet1->setCellValue('J'.$num_ligne, $ign_station_x);
        $sheet1->setCellValue('K'.$num_ligne, $ign_station_y);
        $sheet1->setCellValue('L'.$num_ligne, $active_station);
        $sheet1->setCellValue('M'.$num_ligne, $description_station);   
        if($type_data == 5)
        {
            $sheet1->setCellValue('N'.$num_ligne, $z_sol);
            $sheet1->setCellValue('O'.$num_ligne, $precision);
            $sheet1->setCellValue('P'.$num_ligne, $aquifere);
            $sheet1->setCellValue('Q'.$num_ligne, $nature);    
            $sheet1->setCellValue('R'.$num_ligne, $maitre_ouvrage);    
            $sheet1->setCellValue('S'.$num_ligne, $date_realisation_formated);
            $sheet1->setCellValue('T'.$num_ligne, $sonde_piezo);
        }

        $num_ligne++;         
        $nb_station++;
    }
    

    // Mise en forme des cellules 

    // Appliquer le style gras à la première ligne
    
    $sheet1->getStyle('A1:T1')->getFont()->setBold(true);

    // Ajuster la largeur des colonnes en fonction du contenu
    foreach(range('A','T') as $columnID) 
    {
        $sheet1->getColumnDimension($columnID)->setAutoSize(true);
    }
    

    
    if($piezo_encours)
    {
        // -------------------------------------------------------------------
        // FEUILLE 2
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet2 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Historique Repères'; 
        $sheet2->setTitle($nomFeuil);



        // En-tête de colonne
        $sheet2->setCellValue('A1', 'Code Station');
        $sheet2->setCellValue('B1', 'Nom Station');
        $sheet2->setCellValue('C1', 'Nature du Repère');    
        $sheet2->setCellValue('D1', 'Z Repère');    
        $sheet2->setCellValue('E1', 'Précision');
        $sheet2->setCellValue('F1', 'Code Repère');
        $sheet2->setCellValue('G1', 'Date début');
        $sheet2->setCellValue('H1', 'Date fin');
        $sheet2->setCellValue('I1', 'Nature Repère Geomètre 1');
        $sheet2->setCellValue('J1', 'Z Repère Geomètre 1');
        $sheet2->setCellValue('K1', 'Nature Repère Geomètre 2');
        $sheet2->setCellValue('L1', 'Z Repère Geomètre 2');
        $sheet2->setCellValue('M1', 'Observation');


        $num_ligne = 2; 
        $repere_query = tep_db_query($sql_link,$sql_repere);
        while($repere_tab = tep_db_fetch_array($repere_query))
        {
            $code_station = $repere_tab['code_station'];
            $nom_station = $repere_tab['nom_station'];

            $id_repere = $repere_tab['id'];

            $date_debut_valid_formated = '';
            $date_debut_valid = $repere_tab['date_debut_valid'];
            if(!empty($date_debut_valid) && $date_debut_valid != '0000-00-00')
            {
                $date_object = DateTime::createFromFormat('Y-m-d', $date_debut_valid);
                $date_debut_valid_formated = $date_object->format('d-m-Y');
            }

            $date_fin_valid_formated = '';
            $date_fin_valid = $repere_tab['date_fin_valid'];
            if(!empty($date_fin_valid) && $date_fin_valid != '0000-00-00')
            {
                $date_object = DateTime::createFromFormat('Y-m-d', $date_fin_valid);
                $date_fin_valid_formated = $date_object->format('d-m-Y');
            }
            
            $nature_repere = $repere_tab['nature_repere'];

            $code_repere = $repere_tab['code_repere'] ;
            
            $z_repere = $repere_tab['z_repere'] ?? '';
            if ($z_repere !== '') 
            {
                $z_repere = str_replace(',', '.', $z_repere);
                $z_repere = round(floatval($z_repere), 3);
            }
            

            $precision_repere = $repere_tab['precision_repere'];

            $nature_repere_1 = $repere_tab['nature_repere_1'];
            
            $z_repere_g1 = $repere_tab['z_repere_g1'] ?? '';
            if ($z_repere_g1 !== '') 
            {
                $z_repere_g1 = str_replace(',', '.', $z_repere_g1);
                $z_repere_g1 = round(floatval($z_repere_g1), 3);
            }

            $nature_repere_2 = $repere_tab['nature_repere_2'];
            

            $z_repere_g2 = $repere_tab['z_repere_g2'] ?? '';
            if ($z_repere_g2 !== '') 
            {
                $z_repere_g2 = str_replace(',', '.', $z_repere_g2);
                $z_repere_g2 = round(floatval($z_repere_g2), 3);
            }
            $z_repere_g2 = $repere_tab['z_repere_g2'];  
            
    
            $obs = $repere_tab['obs'];

            $sheet2->setCellValue('A'.$num_ligne, $code_station);
            $sheet2->setCellValue('B'.$num_ligne, $nom_station);
            $sheet2->setCellValue('C'.$num_ligne, $nature_repere);    
            $sheet2->setCellValue('D'.$num_ligne, $z_repere);    
            $sheet2->setCellValue('E'.$num_ligne, $precision_repere);
            $sheet2->setCellValue('F'.$num_ligne, $code_repere);
            $sheet2->setCellValue('G'.$num_ligne, $date_debut_valid_formated);
            $sheet2->setCellValue('H'.$num_ligne, $date_fin_valid_formated);
            $sheet2->setCellValue('I'.$num_ligne, $nature_repere_1);
            $sheet2->setCellValue('J'.$num_ligne, $z_repere_g1);
            $sheet2->setCellValue('K'.$num_ligne, $nature_repere_2);
            $sheet2->setCellValue('L'.$num_ligne, $z_repere_g2);
            $sheet2->setCellValue('M'.$num_ligne, $obs);

            $num_ligne++; 
        }  
        
        // Appliquer le style gras à la première ligne
        $sheet2->getStyle('A1:T1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','T') as $columnID) 
        {
            $sheet2->getColumnDimension($columnID)->setAutoSize(true);
        }


        // -----------------------------------------------------

        // FEUILLE 3
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet3 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Caractéristiques'; 
        $sheet3->setTitle($nomFeuil);

        // En-tête de colonne
        $sheet3->setCellValue('A1', 'Code Station');
        $sheet3->setCellValue('B1', 'Nom Station');
        $sheet3->setCellValue('C1', 'Date d\'observation');    
        $sheet3->setCellValue('D1', 'Profondeur');    
        $sheet3->setCellValue('E1', 'Matériaux tête');
        $sheet3->setCellValue('F1', 'Dim. Tête Ext.');
        $sheet3->setCellValue('G1', 'Matériaux Tubage Intérieur');
        $sheet3->setCellValue('H1', 'Diam. Tubage Intérieur');
        $sheet3->setCellValue('I1', 'Matériaux Dalle');
        $sheet3->setCellValue('J1', 'Dim. Dalle');
        $sheet3->setCellValue('K1', 'Dist. Capot Tubage');
        $sheet3->setCellValue('L1', 'Dist. Tubage Dalle');
        $sheet3->setCellValue('M1', 'Dist. Dalle Sol');
        $sheet3->setCellValue('N1', 'Présence Capot');  
        $sheet3->setCellValue('O1', 'Etat');
        $sheet3->setCellValue('P1', 'En activité');
        $sheet3->setCellValue('Q1', 'Usage');
        $sheet3->setCellValue('R1', 'Equipement Exploitation');    
        $sheet3->setCellValue('S1', 'Schéma Tête Ouvrage');    
        $sheet3->setCellValue('T1', 'Remarque');


        $num_ligne = 2; 
        $caract_query = tep_db_query($sql_link,$sql_caract);
        while($caract_tab = tep_db_fetch_array($caract_query))
        {
            $code_station = $caract_tab['code_station'];
            $nom_station = $caract_tab['nom_station'];

            $id_caract = $caract_tab['id'];

            $date_caract_formated = '';
            $date_caract = $caract_tab['date'];
            if(!empty($date_caract))
            {
                $date_object = DateTime::createFromFormat('Y-m-d', $date_caract);
                $date_caract_formated = $date_object->format('d-m-Y');
            }

            $prof = $caract_tab['prof'];	
            $materiaux_tete = $caract_tab['materiaux_tete'];	

            $dim_tete_ext = $caract_tab['dim_tete_ext'];	

            $materiaux_tub_inter = $caract_tab['materiaux_tub_inter'];

            $diam_tub_inter = $caract_tab['diam_tub_inter'] ?? '';
            if ($diam_tub_inter !== '') 
            {
                $diam_tub_inter = str_replace(',', '.', $diam_tub_inter);
                $diam_tub_inter = round(floatval($diam_tub_inter), 3);
            }

            $materiaux_dalle = $caract_tab['materiaux_dalle'];	
            $dim_dalle = $caract_tab['dim_dalle'];	

            $dist_capto_tube = $caract_tab['dist_capto_tube'] ?? '';
            if ($dist_capto_tube !== '') 
            {
                $dist_capto_tube = str_replace(',', '.', $dist_capto_tube);
                $dist_capto_tube = round(floatval($dist_capto_tube), 3);
            }

            $dist_tube_dalle = $caract_tab['dist_tube_dalle'] ?? '';
            if ($dist_capto_tube !== '') 
            {
                $dist_tube_dalle = str_replace(',', '.', $dist_tube_dalle);
                $dist_tube_dalle = round(floatval($dist_tube_dalle), 3);
            }

            $dist_dalle_sol = $caract_tab['dist_dalle_sol'] ?? '';
            if ($dist_capto_tube !== '') 
            {
                $dist_dalle_sol = str_replace(',', '.', $dist_dalle_sol);
                $dist_dalle_sol = round(floatval($dist_dalle_sol), 3);
            }

            $presence_capot = 'Non';
            if($caract_tab['presence_capot'] > 0){$presence_capot = 'Oui';}

            $etat = $caract_tab['etat'];	
            $activite = $caract_tab['activite'];	
            $utilisation = $caract_tab['utilisation'];	
            $equipement_exploitation = $caract_tab['equipement_exploitation'];	
            $schema_tete = 'SO_'.$caract_tab['schema_tete'];	
            $schema_protect = $caract_tab['schema_protect'];	
            $obs = $caract_tab['obs'];	

            $sheet3->setCellValue('A'.$num_ligne, $code_station);
            $sheet3->setCellValue('B'.$num_ligne, $nom_station);
            $sheet3->setCellValue('C'.$num_ligne, $date_caract_formated);    
            $sheet3->setCellValue('D'.$num_ligne, $prof);    
            $sheet3->setCellValue('E'.$num_ligne, $materiaux_tete);
            $sheet3->setCellValue('F'.$num_ligne, $dim_tete_ext);
            $sheet3->setCellValue('G'.$num_ligne, $materiaux_tub_inter);
            $sheet3->setCellValue('H'.$num_ligne, $diam_tub_inter);
            $sheet3->setCellValue('I'.$num_ligne, $materiaux_dalle);
            $sheet3->setCellValue('J'.$num_ligne, $dim_dalle);
            $sheet3->setCellValue('K'.$num_ligne, $dist_capto_tube);
            $sheet3->setCellValue('L'.$num_ligne, $dist_tube_dalle);
            $sheet3->setCellValue('M'.$num_ligne, $dist_dalle_sol);
            $sheet3->setCellValue('N'.$num_ligne, $presence_capot);  
            $sheet3->setCellValue('O'.$num_ligne, $etat);
            $sheet3->setCellValue('P'.$num_ligne, $activite);
            $sheet3->setCellValue('Q'.$num_ligne, $utilisation);
            $sheet3->setCellValue('R'.$num_ligne, $equipement_exploitation);    
            $sheet3->setCellValue('S'.$num_ligne, $schema_tete);    
            $sheet3->setCellValue('T'.$num_ligne, $obs);

            $num_ligne++; 
        }   

        // Mise en forme des cellules 

        // Appliquer le style gras à la première ligne
        $sheet3->getStyle('A1:T1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','T') as $columnID) 
        {
            $sheet3->getColumnDimension($columnID)->setAutoSize(true);
        }
    }


        // Définir le style à l'extérieur de la boucle
        /*
        $style = $sheet1->getStyle('A:A');
        $style->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');
        */
        
          
    
    // SpreadSheet - Création du fichier   
    
    // Supprimez le classeur spécifique en utilisant son nom
    
    $nomClasseurSuppr = 'Worksheet 1'; 
    $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName($nomClasseurSuppr));
    $spreadsheet->removeSheetByIndex($sheetIndex);

    // Définir la première feuille comme feuille active
    $spreadsheet->setActiveSheetIndex(0);
        
    // créer un gestionnaire d'écriture pour le fichier Excel
    $writer = new Xlsx($spreadsheet);

    
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->setPreCalculateFormulas(false);
    

    // Mise à jour du dossier depuis le serveur
    $chemin_folder_process = '../../../' . $chemin_folder;

    
    if($nb_station > 1)
    {
        $Filename = 'InfoStations_'.$today_formatted.'.xlsx'; 
    }
    else
    {      
        $nom_station_filename = ucfirst(strtolower(nettoyerNomFichier($nom_station)));
        $Filename = $nom_station_filename.'_'.$today_formatted.'.xlsx';   
    }


    $xlsFilename = $chemin_folder_process.'/'.$Filename;

    // Écrire le fichier Excel sur le système de fichiers
    $writer->save($xlsFilename);    
    
$endTime = microtime(true); // Temps à la fin du script


// Calcul de la durée d'exécution en secondes
$executionTime = number_format($endTime - $startTime,1);


// Remplissage du tableau de retour
$responseData = array(
    'statut' => true,
    'executionTime' => $executionTime,
    'xlsFile' => $Filename
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData, JSON_UNESCAPED_UNICODE);

// Envoi des données coté Client
echo $jsonResponse;

    


?>