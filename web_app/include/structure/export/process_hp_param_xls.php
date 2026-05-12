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
$listParam = $data['listParam'];
$chemin_folder = $data['cheminFolder'];

$paramArray = explode(',', $listParam);


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


// TABLE EQ_TYPE (TYPE DATA - Pluie, Débit, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type
                FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1 
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);									
while ($eq_type_tab = tep_db_fetch_array($eq_type_query))
{
    $eq_type_array[$eq_type_tab['id_eq_type']] = $eq_type_tab['nom_eq_type'];
}


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
    
    if(in_array('zonegeo',$paramArray)) 
    {
        // -------------------------------------------------------------------
        // FEUILLE 1 - REGION GEOGRAPHIQUE
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet1 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Regions Geographiques'; 
        $sheet1->setTitle($nomFeuil);
        
                
        // En-tête de colonne
        $sheet1->setCellValue('A1', 'Ident');
        $sheet1->setCellValue('B1', 'Nom Region');   
        
        $num_ligne = 2;

        // TABLE REGION / TERRITOIRE : Province pour NC - Iles pour PF et WF
        $sql_region = "SELECT DISTINCT id_region, nom_region 
                        FROM ".TABLE_REGION." 
                        WHERE id_territoire=".$territoire_id;
        $region_query = tep_db_query($sql_link,$sql_region);
        while ($region_tab = tep_db_fetch_array($region_query))
        {
            $sheet1->setCellValue('A'.$num_ligne, $region_tab['id_region']);
            $sheet1->setCellValue('B'.$num_ligne, $region_tab['nom_region']);   

            $region_array[$region_tab['id_region']] = $region_tab['nom_region'];

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet1->getStyle('A1:B1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','B') as $columnID){$sheet1->getColumnDimension($columnID)->setAutoSize(true);}
        

        // -------------------------------------------------------------------
        // FEUILLE 2 - COMMUNES
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet2 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Communes'; 
        $sheet2->setTitle($nomFeuil);
                    
        // En-tête de colonne
        $sheet2->setCellValue('A1', 'Ident');
        $sheet2->setCellValue('B1', 'Nom Commune');   
        $sheet2->setCellValue('C1', 'Ident Region Geo');       
        $sheet2->setCellValue('D1', 'Nom Region Geo');
        
        $num_ligne = 2;

        // TABLE COMMUNE
        $sql_commune = "SELECT DISTINCT c.id_commune, c.nom_commune, c.id_region
        FROM ".TABLE_COMMUNE." c
        WHERE c.id_territoire=".$territoire_id."
        ORDER BY c.nom_commune ASC";

        $commune_query = tep_db_query($sql_link,$sql_commune);
        while ($commune_tab = tep_db_fetch_array($commune_query))
        {
            $sheet2->setCellValue('A'.$num_ligne, $commune_tab['id_commune']);
            $sheet2->setCellValue('B'.$num_ligne, $commune_tab['nom_commune']);
            $sheet2->setCellValue('C'.$num_ligne, $commune_tab['id_region']);
            $sheet2->setCellValue('D'.$num_ligne, $region_array[$commune_tab['id_region']]);   

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet2->getStyle('A1:D1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','D') as $columnID){$sheet2->getColumnDimension($columnID)->setAutoSize(true);}


        // -------------------------------------------------------------------
        // FEUILLE 3 - REGIONS HYDRO
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet3 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Regions Hydrologiques'; 
        $sheet3->setTitle($nomFeuil);
                    
        // En-tête de colonne
        $sheet3->setCellValue('A1', 'Ident');
        $sheet3->setCellValue('B1', 'Nom Region Hydro');   
        $sheet3->setCellValue('C1', 'Description');      
        
        $num_ligne = 2;
        
        // TABLE REGIONHYDRO
        $sql_regionhydro = "SELECT DISTINCT id, nom, description
                            FROM ".TABLE_REGIONHYDRO." 
                            WHERE id_territoire=".$territoire_id."
                            ORDER BY LOWER(nom) ASC";
        $regionhydro_query = tep_db_query($sql_link,$sql_regionhydro);
        while ($regionhydro_tab = tep_db_fetch_array($regionhydro_query))
        {
            $sheet3->setCellValue('A'.$num_ligne, $regionhydro_tab['id']);
            $sheet3->setCellValue('B'.$num_ligne, $regionhydro_tab['nom']);  
            $sheet3->setCellValue('C'.$num_ligne, $regionhydro_tab['description']);  

            $regionhydro_array[$regionhydro_tab['id']] = $regionhydro_tab['nom'];

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet3->getStyle('A1:D1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','D') as $columnID){$sheet3->getColumnDimension($columnID)->setAutoSize(true);}



        // -------------------------------------------------------------------
        // FEUILLE 4 - RIVIERES
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet4 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Rivieres'; 
        $sheet4->setTitle($nomFeuil);
                    
        // En-tête de colonne
        $sheet4->setCellValue('A1', 'Ident');
        $sheet4->setCellValue('B1', 'Nom Rivière');   
        $sheet4->setCellValue('C1', 'Description');   
        $sheet4->setCellValue('D1', 'Ident Region Hydro'); 
        $sheet4->setCellValue('E1', 'Nom Region Hydro');      
        
        $num_ligne = 2;
        
        // TABLE RIVIERE
        $sql_riviere = "SELECT DISTINCT id, nom, description, id_regionhydro 
                        FROM ".TABLE_RIVIERE." 
                        WHERE id_territoire=".$territoire_id." 
                        ORDER BY LOWER(nom) ASC";
        $riviere_query = tep_db_query($sql_link,$sql_riviere);
        while ($riviere_tab = tep_db_fetch_array($riviere_query))
        {
            $sheet4->setCellValue('A'.$num_ligne, $riviere_tab['id']);
            $sheet4->setCellValue('B'.$num_ligne, $riviere_tab['nom']);  
            $sheet4->setCellValue('C'.$num_ligne, $riviere_tab['description']);  
            $sheet4->setCellValue('D'.$num_ligne, $riviere_tab['id_regionhydro']);  
            $sheet4->setCellValue('E'.$num_ligne, $regionhydro_array[$riviere_tab['id_regionhydro']]);  

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet4->getStyle('A1:E1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','E') as $columnID){$sheet4->getColumnDimension($columnID)->setAutoSize(true);}

    }


    if(in_array('typechron',$paramArray)) 
    {
        // -------------------------------------------------------------------
        // FEUILLE 5 - TYPES CHRONIQUES
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet5 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Types Chroniques'; 
        $sheet5->setTitle($nomFeuil);
        
                
        // En-tête de colonne
        $sheet5->setCellValue('A1', 'Ident');
        $sheet5->setCellValue('B1', 'Initiales');        
        $sheet5->setCellValue('C1', 'Nom');   
        $sheet5->setCellValue('D1', 'Unite');        
        $sheet5->setCellValue('E1', 'Ident Type Donnees');   
        $sheet5->setCellValue('F1', 'Nom Type Donnees');   
        
        $num_ligne = 2;


        // TABLE DATA_CHRON (TYPE CHRON - CI,PI, CIE, ...)
        $sql_type_chron = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, unite
                            FROM ".TABLE_TYPE_DATA." 
                            ORDER BY id_eq_type_data, init_type_data ASC";
        $type_chron_query = tep_db_query($sql_link,$sql_type_chron);	
        while ($type_chron_tab = tep_db_fetch_array($type_chron_query))
        {
            $chron_idtypedata = '';
            $chron_typedata = '';
            if(isset($eq_type_array[$type_chron_tab['id_eq_type_data']]))
            {
                $chron_idtypedata = $type_chron_tab['id_eq_type_data'];
                $chron_typedata = $eq_type_array[$type_chron_tab['id_eq_type_data']];
            }

            $sheet5->setCellValue('A'.$num_ligne, $type_chron_tab['id_data_type']);
            $sheet5->setCellValue('B'.$num_ligne, $type_chron_tab['init_type_data']);  
            $sheet5->setCellValue('C'.$num_ligne, $type_chron_tab['nom_type_data']);  
            $sheet5->setCellValue('D'.$num_ligne, $type_chron_tab['unite']);   
            $sheet5->setCellValue('E'.$num_ligne, $chron_idtypedata);   
            $sheet5->setCellValue('F'.$num_ligne, $chron_typedata);   

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet5->getStyle('A1:F1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','F') as $columnID){$sheet5->getColumnDimension($columnID)->setAutoSize(true);}
    
    }


    if(in_array('st_nature',$paramArray)) 
    {
        // -------------------------------------------------------------------
        // FEUILLE 6 - NATURE DES STATIONS
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet6 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Natures Stations'; 
        $sheet6->setTitle($nomFeuil);
        
                
        // En-tête de colonne
        $sheet6->setCellValue('A1', 'Ident');
        $sheet6->setCellValue('B1', 'Libelle');          
        
        $num_ligne = 2;

        // TABLE NATURE (PIEZO)
        $sql_nature = "SELECT DISTINCT id, libelle 
                        FROM ".TABLE_STATION_NATURE."                        
                        ORDER BY libelle ASC";
        $nature_query = tep_db_query($sql_link,$sql_nature);
        while ($nature_tab = tep_db_fetch_array($nature_query))
        {
            $sheet6->setCellValue('A'.$num_ligne, $nature_tab['id']);
            $sheet6->setCellValue('B'.$num_ligne, $nature_tab['libelle']);  

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet6->getStyle('A1:B1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','B') as $columnID){$sheet6->getColumnDimension($columnID)->setAutoSize(true);}
    
    }


    if(in_array('codequal',$paramArray)) 
    {
        // -------------------------------------------------------------------
        // FEUILLE 7 - CODE QUALITE
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet7 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Codes Qualite'; 
        $sheet7->setTitle($nomFeuil);
        
                
        // En-tête de colonne
        $sheet7->setCellValue('A1', 'Ident');
        $sheet7->setCellValue('B1', 'Initiales'); 
        $sheet7->setCellValue('C1', 'Nom');
        $sheet7->setCellValue('D1', 'Informations'); 
        $sheet7->setCellValue('E1', 'Ident Type Donnees');   
        $sheet7->setCellValue('F1', 'Nom Type Donnees');          
        
        $num_ligne = 2;

        // TABLE NATURE (PIEZO)
        $sql_quality = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data, info_qualite_data, id_eq_type 
                        FROM ".TABLE_DATA_QUALITE."
                        WHERE init_qualite_data<>'' 
                        ORDER BY id_eq_type ASC, init_qualite_data ASC";
        $quality_query = tep_db_query($sql_link,$sql_quality);
        while ($quality_tab = tep_db_fetch_array($quality_query))
        {
            $codequal_idtypedata = '';
            $codequal_typedata = '';
            if(isset($eq_type_array[$quality_tab['id_eq_type']]))
            {
                $codequal_idtypedata = $quality_tab['id_eq_type'];
                $codequal_typedata = $eq_type_array[$quality_tab['id_eq_type']];
            }


            $sheet7->setCellValue('A'.$num_ligne, $quality_tab['id_data_qualite']);
            $sheet7->setCellValue('B'.$num_ligne, $quality_tab['init_qualite_data']); 
            $sheet7->setCellValue('C'.$num_ligne, $quality_tab['nom_qualite_data']); 
            $sheet7->setCellValue('D'.$num_ligne, $quality_tab['info_qualite_data']); 
            $sheet7->setCellValue('E'.$num_ligne, $codequal_idtypedata); 
            $sheet7->setCellValue('F'.$num_ligne, $codequal_typedata);  

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet7->getStyle('A1:F1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','F') as $columnID){$sheet7->getColumnDimension($columnID)->setAutoSize(true);}
    
    }


    if(in_array('eqjge',$paramArray)) 
    {
        // -------------------------------------------------------------------
        // FEUILLE 8 - JGE - HELICE
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet8 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Helices'; 
        $sheet8->setTitle($nomFeuil);
        
                
        // En-tête de colonne
        $sheet8->setCellValue('A1', 'Ident');
        $sheet8->setCellValue('B1', 'Numero'); 
        $sheet8->setCellValue('C1', 'Diametre');
        $sheet8->setCellValue('D1', 'Pas'); 
        $sheet8->setCellValue('E1', 'l1');   
        $sheet8->setCellValue('F1', 'a1');  
        $sheet8->setCellValue('G1', 'b1');      
        $sheet8->setCellValue('H1', 'l2');   
        $sheet8->setCellValue('I1', 'a2');  
        $sheet8->setCellValue('J1', 'b2');    
        $sheet8->setCellValue('K1', 'a3');  
        $sheet8->setCellValue('L1', 'b3');    
        $sheet8->setCellValue('M1', 'Fabricant');
        $sheet8->setCellValue('N1', 'Observation');
        
        $num_ligne = 2;

        // TABLE HELICE - HYDRO
        $sql_helice = "SELECT DISTINCT id, num, diametre, pas, l1, a1, b1, l2, a2, b2, a3, b3, fabricant, obs
                        FROM ".TABLE_HELICE."
                        ORDER BY num ASC";
        $helice_query = tep_db_query($sql_link,$sql_helice);
        while ($helice_tab = tep_db_fetch_array($helice_query))
        {
            $diametre = '';
            if($helice_tab['diametre'] > 0){$diametre = $helice_tab['diametre'];}
            $pas = '';
            if($helice_tab['pas'] > 0){$pas = $helice_tab['pas'];}
            $l1 = '';
            if($helice_tab['l1'] > 0){$l1 = $helice_tab['l1'];}
            $a1 = '';
            if($helice_tab['a1'] > 0){$a1 = $helice_tab['a1'];}
            $b1 = '';
            if($helice_tab['b1'] > 0){$b1 = $helice_tab['b1'];}
            $l2 = '';
            if($helice_tab['l2'] > 0){$l2 = $helice_tab['l2'];}
            $a2 = '';
            if($helice_tab['a2'] > 0){$a2 = $helice_tab['a2'];}
            $b2 = '';
            if($helice_tab['b2'] > 0){$b2 = $helice_tab['b2'];}
            $a3 = '';
            if($helice_tab['a3'] > 0){$a3 = $helice_tab['a3'];}
            $b3 = '';
            if($helice_tab['b3'] > 0){$b3 = $helice_tab['b3'];}

            $sheet8->setCellValue('A'.$num_ligne, $helice_tab['id']);
            $sheet8->setCellValue('B'.$num_ligne, $helice_tab['num']); 
            $sheet8->setCellValue('C'.$num_ligne, $diametre); 
            $sheet8->setCellValue('D'.$num_ligne, $pas); 
            $sheet8->setCellValue('E'.$num_ligne, $l1); 
            $sheet8->setCellValue('F'.$num_ligne, $a1); 
            $sheet8->setCellValue('G'.$num_ligne, $b1);  
            $sheet8->setCellValue('H'.$num_ligne, $l2); 
            $sheet8->setCellValue('I'.$num_ligne, $a2); 
            $sheet8->setCellValue('J'.$num_ligne, $b2);  
            $sheet8->setCellValue('K'.$num_ligne, $a3); 
            $sheet8->setCellValue('L'.$num_ligne, $b3);  
            $sheet8->setCellValue('M'.$num_ligne, $helice_tab['fabricant']); 
            $sheet8->setCellValue('N'.$num_ligne, $helice_tab['obs']); 

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet8->getStyle('A1:N1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','N') as $columnID){$sheet8->getColumnDimension($columnID)->setAutoSize(true);}



        // -------------------------------------------------------------------
        // FEUILLE 9 - JGE - MOULINET
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet9 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Moulinets'; 
        $sheet9->setTitle($nomFeuil);
        
                
        // En-tête de colonne
        $sheet9->setCellValue('A1', 'Ident');
        $sheet9->setCellValue('B1', 'Numero');  
        $sheet9->setCellValue('C1', 'Fabricant');  
        $sheet9->setCellValue('D1', 'Observation');          
        
        $num_ligne = 2;

        // TABLE MOULINET - HYDRO
        $sql_moulinet = "SELECT DISTINCT id, num, fabricant, obs  FROM ".TABLE_MOULINET;
        $moulinet_query = tep_db_query($sql_link,$sql_moulinet);
        while ($moulinet_tab = tep_db_fetch_array($moulinet_query))
        {
            $sheet9->setCellValue('A'.$num_ligne, $moulinet_tab['id']);
            $sheet9->setCellValue('B'.$num_ligne, $moulinet_tab['num']);  
            $sheet9->setCellValue('C'.$num_ligne, $moulinet_tab['fabricant']);
            $sheet9->setCellValue('D'.$num_ligne, $moulinet_tab['obs']);  

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet9->getStyle('A1:D1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','D') as $columnID){$sheet9->getColumnDimension($columnID)->setAutoSize(true);}



        // -------------------------------------------------------------------
        // FEUILLE 10 - JGE - SAUMON
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet10 = $spreadsheet->getActiveSheet();
        $nomFeuil = 'Saumons'; 
        $sheet10->setTitle($nomFeuil);
        
                
        // En-tête de colonne
        $sheet10->setCellValue('A1', 'Ident');
        $sheet10->setCellValue('B1', 'Numero');  
        $sheet10->setCellValue('C1', 'Titre');  
        $sheet10->setCellValue('D1', 'Points');    
        $sheet10->setCellValue('E1', 'Distance axe');  
        $sheet10->setCellValue('F1', 'T air');    
        $sheet10->setCellValue('G1', 'R distance');   
        $sheet10->setCellValue('H1', 'Fabricant');    
        $sheet10->setCellValue('I1', 'Observation');         
        
        $num_ligne = 2;

        // TABLE MOULINET - HYDRO
        $sql_saumon = "SELECT DISTINCT id, num, titre, poids, distance_axe, t_air, r_dist, fabricant, obs FROM ".TABLE_SAUMON."
				        ORDER BY num ASC";
        $saumon_query = tep_db_query($sql_link,$sql_saumon);
        while ($saumon_tab = tep_db_fetch_array($saumon_query))
        {
            $sheet10->setCellValue('A'.$num_ligne, $saumon_tab['id']);
            $sheet10->setCellValue('B'.$num_ligne, $saumon_tab['num']);  
            $sheet10->setCellValue('C'.$num_ligne, $saumon_tab['titre']);
            $sheet10->setCellValue('D'.$num_ligne, $saumon_tab['poids']);  
            $sheet10->setCellValue('E'.$num_ligne, $saumon_tab['distance_axe']);
            $sheet10->setCellValue('F'.$num_ligne, $saumon_tab['t_air']);  
            $sheet10->setCellValue('G'.$num_ligne, $saumon_tab['r_dist']);
            $sheet10->setCellValue('H'.$num_ligne, $saumon_tab['fabricant']);  
            $sheet10->setCellValue('I'.$num_ligne, $saumon_tab['obs']);  

            $num_ligne++;
        }

        // Mise en forme des cellules 
        // Appliquer le style gras à la première ligne
        
        $sheet10->getStyle('A1:I1')->getFont()->setBold(true);

        // Ajuster la largeur des colonnes en fonction du contenu
        foreach(range('A','I') as $columnID){$sheet10->getColumnDimension($columnID)->setAutoSize(true);}
    
    }


    
    // -------------------------------------------------------------------
    // -------------------------------------------------------------------
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

    $Filename = 'HP_Parametres_'.$today_formatted.'.xlsx'; 
    $xlsFilename = $chemin_folder_process.'/'.$Filename;

    // Écrire le fichier Excel sur le système de fichiers
    $writer->save($xlsFilename);    
    
$endTime = microtime(true); // Temps à la fin du script


// Calcul de la durée d'exécution en secondes
//$executionTime = number_format($endTime - $startTime,1);
$executionTime = 22;


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