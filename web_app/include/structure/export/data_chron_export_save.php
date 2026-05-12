<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export au format xlsx
----------------------------------------
*/

// -------------------------------------
// Librairy PhpSpreadsheet
require 'php-excel/vendor/autoload.php';

// Appels à la librairie phpspreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// -------------------------------------

//ini_set('max_execution_time', 0);
// Pour éviter le pb de limite de temp accorder par le serveur 'max_execution_time' on fait un traitement par lots

// Initialisation de la taille du lot (batch)
$batchSize = 100; // Définissez la taille de lot souhaitée
$batchData = []; // Tableau pour stocker les données du lot


// --------------------------------------
// INIT VAR
$nbTotData = 0;
$nb_feuil = 0;
$today_dateTime = date('YmdHi');

// créer un objet ZipArchive
$zip = new ZipArchive();


// nom du fichier ZIP à créer
$zipFilename =  'data/'.$today_dateTime . '_export_hydropacifique.zip';
if (!file_exists($zipFilename)) {
    // create an empty file with the specified name
    file_put_contents($zipFilename, '');
}


// ouvrir le fichier ZIP en mode création
if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { //OVERWRITE permet d'éviter que d'anciens fichiers EXCEL générés soit tjrs dans le fichier zip en sortie.
    die('Erreur : impossible d\'ouvrir le fichier ZIP');
}


// --------------------------------------
// CREATION DES FICHIERS

// Si un seul fichier pour toutes les stations
if(!isset($_POST['multi_file'])){$spreadsheet = new Spreadsheet();}

// Définir le style pour les cellules A1, B1 et C1 des fichiers EXCEL
$styleArray = [
    'font' => [
        'bold' => true,
    ]
];

// Parcours de toutes les chroniques par station
foreach($station_chron_array as $cle_station => $typedata_array) 
{  
    // Si un fichier pas station
    if(isset($_POST['multi_file']))
    {
        // Créer une feuille de calcul
        $spreadsheet = new Spreadsheet();
    }

    // Pour chaque type de données
    foreach($typedata_array as $typedata_chron => $sql_chron) 
    { 
        // Génération de la classe Excel
        $spreadsheet->createSheet();
        $nb_feuil = $spreadsheet->getIndex($spreadsheet->getSheetByName("Worksheet")); // Obtient l'index de la feuille de calcul par défaut
        $spreadsheet->setActiveSheetIndex($nb_feuil);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($station_all_array[$cle_station]['code_station'].'_'.$type_chron_array[$typedata_chron]['init_type_data'].'_1');
        $sheet->getColumnDimension('A')->setWidth(25);           

        $row_excel = 1;

        if(isset($_POST['entete_col']))
        {
            $sheet->getStyle('A'.$row_excel.':C'.$row_excel)->applyFromArray($styleArray);

            $sheet->setCellValue('A'.$row_excel, 'Date heure');
            $sheet->setCellValue('B'.$row_excel, 'Valeur');
            $sheet->setCellValue('C'.$row_excel, 'Qualité');

            $row_excel++;
        } 
        
        // On va chercher les données de chroniques dans la table Data_All
        $data_chron_query = tep_db_query($sql_link,$sql_chron);
        while($data_chron_tab = tep_db_fetch_array($data_chron_query))
        {             
            // Créez un objet DateTime à partir de la chaîne de date
            $dateTime = new DateTime($data_chron_tab['dateheure'], new DateTimeZone('UTC')); // Spécifiez le fuseau horaire, par exemple, 'UTC'
            $excelDate = ($dateTime->getTimestamp() / 86400) + 25569; // Conversion en format Excel

            // Tableau contenant les données à afficher
            $batchData[] = [
                $excelDate,
                $data_chron_tab['valeur'],
                $data_chron_tab['init_qualite_data']
            ];

            if(count($batchData) >= $batchSize) 
            {
                // Écrivez le lot de données dans le fichier Excel
                foreach ($batchData as $data) 
                {
                    $sheet->fromArray([$data], null, 'A' . $row_excel);
                    $sheet->getStyle('A' . $row_excel)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');
                    $row_excel++;
                }
                // Réinitialisez le tableau pour le prochain lot
                $batchData = [];
            }
        }   
        
        // Après la fin de la boucle, vérifiez s'il reste des données dans $batchData
        if (count($batchData) > 0) 
        {
            // Écrivez le lot restant de données dans le fichier Excel
            foreach ($batchData as $data) 
            {
                $sheet->fromArray([$data], null, 'A' . $row_excel);
                $sheet->getStyle('A' . $row_excel)->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm:ss');
                $row_excel++;
            }
            // Réinitialisez le tableau
            $batchData = [];
        }
    }
    
    // Si un fichier pas station
    if(isset($_POST['multi_file']))
    {
        // Supprimez le classeur spécifique en utilisant son nom
        $nomClasseurSuppr = 'Worksheet 1'; 
        $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName($nomClasseurSuppr));
        $spreadsheet->removeSheetByIndex($sheetIndex);
    
        // nom du fichier Excel à créer
        //$excelFilename = $station_all_array[$cle_station]['code_station'].'_'.$date_1.'_'.$date_2.'.xlsx';
        $excelFilename = $station_all_array[$cle_station]['code_station'].'_'.$today_dateTime.'.xlsx';

        // créer un gestionnaire d'écriture pour le fichier Excel
        $writer = new Xlsx($spreadsheet);

        // écrire le fichier Excel dans une variable
        gc_collect_cycles();
        unset($excelData);
        ob_start();
        $writer->save('php://output');
        $excelData = ob_get_contents();
        ob_end_clean();

        // ajouter le fichier Excel dans le fichier ZIP
        $zip->addFromString($excelFilename, $excelData);
    }
    
}

// Si un seule fichier pour tout l'export
if(!isset($_POST['multi_file']))
{
    // Supprimez le classeur spécifique en utilisant son nom
    $nomClasseurSuppr = 'Worksheet 1'; 
    $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName($nomClasseurSuppr));
    $spreadsheet->removeSheetByIndex($sheetIndex);

    // nom du fichier Excel à créer
    $excelFilename = MIN_SITE.'_Export_MultiStation_'.$date_1.'_'.$date_2.'.xlsx';

    // créer un gestionnaire d'écriture pour le fichier Excel
    $writer = new Xlsx($spreadsheet);

    // écrire le fichier Excel dans une variable
    gc_collect_cycles();
    unset($excelData);
    ob_start();
    $writer->save('php://output');
    $excelData = ob_get_contents();
    ob_end_clean();

    // ajouter le fichier Excel dans le fichier ZIP
    $zip->addFromString($excelFilename, $excelData);
}


// fermer le fichier ZIP
$zip->close();


// proposer le téléchargement du fichier ZIP
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
header('Content-Length: ' . filesize($zipFilename));
readfile($zipFilename);      


if (file_exists($zipFilename)) 
{
    unlink($zipFilename);
}

?>