<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export au format csv 
- Ce script permet de générer les fichiers csv directement sur le serveur en tâche caché.
----------------------------------------
*/

// ----------------------------------------------
// nécessaire pour la configuration du script

require('../../config.php');
require('../../database_tables.php');

require('../../function/database.php');	
require('../../function/html_output.php');
require('../../function/general.php');



// Appels à la librairie phpspreadsheet

// Librairy PhpSpreadsheet
/*
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
*/


// Appel à la Librairy SPOUT
// Librairy Spout
/*
require_once '../../../vendor/autoload.php'; // Chargez l'autoloader de Composer

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
*/

// Librairy Php-Excel-Writer (Ellumilel)
require_once '../../../php-excel-writer/vendor/autoload.php';

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

// Accès aux données individuelles
$Filename = $data['Filename'];
$folder_download = $data['folder_download'];
$chemin_folder = $data['chemin_folder'];
$id_station = $data['id_station'];
$code_station = $data['code_station'];
$nom_station = $data['nom_station'];
$typedata_array = json_decode($data['typedata_array'], true); // Décodez le JSON en tableau associatif
$type_chron_array = json_decode($data['type_chron_array'], true); // Décodez le JSON en tableau associatif
$nbdata_chron_array = json_decode($data['nbdata_chron_array'], true); // Décodez le JSON en tableau associatif
$quality_array = json_decode($data['quality_array'], true); // Décodez le JSON en tableau associatif
$multi_file = $data['multi_file'];
$entete_col = $data['entete_col'];


// --------------------------------------
// Initialisation de variables locales

$todayTime = new DateTime(); // Crée un objet DateTime pour la date actuelle
$today_formatted = $todayTime->format('dmYHi');

// Spécifiez le nom du dossier à créer
$chemin_folder_process = '../../../' . $chemin_folder;

// Vérifiez si le dossier n'existe pas déjà
if (!is_dir($chemin_folder_process)) 
{
    // Créez le dossier avec les permissions appropriées (par exemple, 0755)
    mkdir($chemin_folder_process, 0755, true); // Le troisième paramètre true crée les dossiers parents si nécessaire
} 


// --------------------------------------
// CREATION FICHIER OU MODIFICATION FICHIER EXISTANT

$total_data = 0;
$nb_chron = 0;
$content = '';

// Chemin vers le fichier Excel
$xlsFilename = $chemin_folder_process.'/'.$Filename;

// Spreadsheet
/*
// On crée un object Spreadsheet
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

$spreadsheet->createSheet(); // Génération de la classe Excel
*/

// SPOUT
/*
// Création d'un nouvel objet Writer
$writer = WriterEntityFactory::createXLSXWriter();  
// Créer un nouveau fichier Excel
$writer->openToFile($xlsFilename);
*/

// Librairy Php-Excel-Writer (Ellumilel)
$writer = new Ellumilel\ExcelWriter(); 


// Temps au début du script
$startTime = microtime(true);

    // Pour chaque type de données
    foreach($typedata_array as $typedata_chron => $sql_chron) 
    { 
        $nb_chron++;
        $total_data += $nbdata_chron_array[$id_station][$typedata_chron];

        $nomFeuil = $code_station.'_'.$type_chron_array[$typedata_chron]['init_type_data'].'_1'; 
            
        
        /*
        // Spreadsheet
        // Génération de la classe Excel
            $spreadsheet->createSheet();
            $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
            $spreadsheet->setActiveSheetIndex($nb_feuil);

            $sheet = $spreadsheet->getActiveSheet();
            //$sheet->setCalculateFormulas(false);

            $sheet->setTitle($code_station.'_'.$type_chron_array[$typedata_chron]['init_type_data'].'_1');
            $sheet->getColumnDimension('A')->setWidth(25);           

            $row_excel = 1;

            if($entete_col)
            {
                $sheet->getStyle('A'.$row_excel.':C'.$row_excel)->applyFromArray($styleArray);

                $sheet->setCellValue('A'.$row_excel, 'Date heure');
                $sheet->setCellValue('B'.$row_excel, 'Valeur');
                $sheet->setCellValue('C'.$row_excel, 'Qualité');

                $row_excel++;
            }
            
            // Définir le style à l'extérieur de la boucle
            $style = $sheet->getStyle('A:A');
            $style->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');
       

        // --------------------------------------
        // Préparation de la requête SQL
        $dataRows = [];


        $batchSize = 300; // Définissez la taille du lot
         */

        // 

        // SPOUT
        /*
        $sheet = $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName($code_station.'_'.$type_chron_array[$typedata_chron]['init_type_data'].'_1'); 
        */


        $data_chron_query = tep_db_query($sql_link, $sql_chron);
        while ($data_chron_tab = tep_db_fetch_array($data_chron_query)) 
        {
            // SpreadSheet

                // Pour savoir quel est le niveau de la progression 
                //$current_iteration_all++;
            /*
                // Créez un objet DateTime à partir de la chaîne de date
                $dateTime = new DateTime($data_chron_tab['dateheure'], new DateTimeZone('UTC')); // Spécifiez le fuseau horaire, par exemple, 'UTC'
                $excelDate = ($dateTime->getTimestamp() / 86400) + 25569; // Conversion en format Excel$dateTime_formatted = $data_chron_tab['dateheure'];

            
                $dataRows[] = [
                                $excelDate,
                                $data_chron_tab['valeur'],
                                $quality_array[$data_chron_tab['id_typedata']]['init_qualite_data']
                            ];
                        
                if(count($dataRows) >= $batchSize) 
                {
                    $sheet->fromArray($dataRows, null, 'A' . $row_excel);
                    $row_excel += count($dataRows);
                    // Réinitialisez le tableau pour le prochain lot
                    $dataRows = [];
                }
            */


            // SPOUT
            /*
            $rowData = WriterEntityFactory::createRowFromArray($data_chron_tab);
            $writer->addRow($rowData);
            */


            // Librairy Php-Excel-Writer (Ellumilel)
            $dataRows = array($data_chron_tab['dateheure'], $data_chron_tab['valeur'], $quality_array[$data_chron_tab['id_typedata']]['init_qualite_data']);
            $writer->writeSheetRow($nomFeuil, $dataRows);
        }

        // Après la boucle while pour les dernières données si $dataRows < $batchSize
        // SpreadSHEET
        /*
        if (!empty($dataRows)) 
        {                    
            $sheet->fromArray($dataRows, null, 'A' . $row_excel);
        }
        */

        // Libération des ressources de résultat
        mysqli_free_result($data_chron_query);      
    }

    
    // Librairy Php-Excel-Writer (Ellumilel)
    // Sauvegarde du fichier Excel
    $writer->writeToFile($xlsFilename);

    // SPOUT Enregistrement du fichier Excel
   //$writer->close();

    // SpreadSheet    
    /*
    // Supprimez le classeur spécifique en utilisant son nom
    $nomClasseurSuppr = 'Worksheet 1'; 
    $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName($nomClasseurSuppr));
    $spreadsheet->removeSheetByIndex($sheetIndex);

    // créer un gestionnaire d'écriture pour le fichier Excel
    $writer = new Xlsx($spreadsheet);

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->setPreCalculateFormulas(false);

    $Filename = $code_station.'_'.$today_formatted.'.xlsx';    
    $xlsFilename = $chemin_folder_process.'/'.$Filename;

    // Écrire le fichier Excel sur le système de fichiers
    $writer->save($xlsFilename);    
    */
    


$endTime = microtime(true); // Temps à la fin du script


// Calcul de la durée d'exécution en secondes
$executionTime = number_format($endTime - $startTime,1);

$data_info = array(
                    'total_data' => $total_data,                    
                    'nb_chron' => $nb_chron,
                    'station_time' => $executionTime                    
                    );


echo json_encode($data_info, JSON_UNESCAPED_UNICODE);
    


?>