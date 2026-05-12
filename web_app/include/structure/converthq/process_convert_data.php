<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Export 
- Ce script permet de convertir les données d'une station hydrométrique de hauteur d'eau en débit
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
require('../../function/stats.php');	

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');


// DATA TYPE AXE 
$sql_data_type_axe = "SELECT DISTINCT id, axe, unite FROM ".TABLE_DATA_TYPE_AXE;
$data_type_axe_query = tep_db_query($sql_link,$sql_data_type_axe);
while ($data_type_axe = tep_db_fetch_array($data_type_axe_query))
{				
	$data_type_axe_array[$data_type_axe['id']] = array('axe' => $data_type_axe['axe'],
														'unite' => $data_type_axe['unite']
														);
} 

// Récupération des données JSON envoyées depuis la requête AJAX
$jsonDataGraph = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataJson = json_decode($jsonDataGraph, true);

// Accéder aux données du tableau récupérer
$timezone_php = $dataJson['timezone_php'];
$offSet = $dataJson['offSet'];

$typedata_chron_h = $dataJson['typedataChronH'];
$typedata_chron_q = $dataJson['typedataChronQ'];
$date_1 = $dataJson['xDateMin'];
$datetime_1 = datefr_us($date_1)." 00:00:00";
$date_2 = $dataJson['xDateMax'];
$datetime_2 = datefr_us($date_2)." 23:59:59";

$station_chron = $dataJson['idStation'];
$id_user = $dataJson['id_user']; 
$id_meta_new = $dataJson['id_meta_correction']; 

if($offSet < 1)
{
    $etl_id = null; // id_encours
    $etl_h1 = null; // Valeur précédente
    $etl_q1 = null; // Valeur actuelle
    $new_etl = true;
    $tab_etl = []; 

    $sql_etl_data = "SELECT DISTINCT etl.id, etl.datetime_first, etl.datetime_end, ed.hauteur,ed.debit
                    FROM ".TABLE_DATA_ETL." etl
                    JOIN ".TABLE_DATA_ETL_DATA." ed ON ed.id_etl=etl.id
                    WHERE etl.id_station=".$station_chron."
                    AND etl.datetime_first <= '".$datetime_2."'
                    AND etl.datetime_end >= '".$datetime_1."'
                    ORDER BY datetime_first ASC, hauteur ASC";            
    $etl_data_query = tep_db_query($sql_link,$sql_etl_data);
    while($etl_data_tab = tep_db_fetch_array($etl_data_query))
    {
        $new_etl = ($etl_id !== $etl_data_tab['id']); // Vérifie si on change de ETL
        $etl_id = $etl_data_tab['id']; // Met à jour l'ID courant
        
        $etl_h2 = $etl_data_tab['hauteur']; // Affectez la hauteur de la ligne en cours à $etl_h2
        $etl_q2 = $etl_data_tab['debit']; // Affectez la hauteur de la ligne en cours à $etl_q2
        
        if(!$new_etl && $etl_h1 !== null && $etl_q1 !== null)
        {
            // Calcul des coefficients de régression
            // Vérifie que h2 et h1 sont différents pour éviter division par zéro
            if ($etl_h2 !== $etl_h1) 
            {
                $coef_a = round(($etl_q2 - $etl_q1) / ($etl_h2 - $etl_h1),2);
                $coef_b = round($etl_q1 - $coef_a * $etl_h1,2);
            } else {
                $coef_a = 0; // Valeur par défaut
                $coef_b = $etl_q1; // Valeur constante
            }

            // Conversion des dates en objets DateTime
            $datetimeFirst = new DateTime($etl_data_tab['datetime_first']);
            $datetimeEnd = new DateTime($etl_data_tab['datetime_end']);
            $date1Object = new DateTime($datetime_1);
            $date2Object = new DateTime($datetime_2);

            // Comparaison des dates
            if ($datetimeFirst < $date1Object) {
                $dateFirst = $datetime_1;
            } else {
                $dateFirst = $datetimeFirst->format('Y-m-d H:i:s'); // Conversion en chaîne
            }

            if ($datetimeEnd > $date2Object) {
                $dateEnd = $datetime_2;
            } else {
                $dateEnd = $datetimeEnd->format('Y-m-d H:i:s'); // Conversion en chaîne
            }


            $tab_etl[] = array('dateFirst' => $dateFirst,
                            'dateEnd' => $dateEnd,
                            'h1' => $etl_h1,
                            'h2' => $etl_h2,
                            'q1' => $etl_q1,
                            'q2' => $etl_q2,
                            'a' => $coef_a,
                            'b' => $coef_b
                                );
        }

        // Met à jour h1/q1 pour la prochaine itération
        $etl_h1 = $etl_h2;
        $etl_q1 = $etl_q2;
    }


    // Création d'une nouvelle metadonnees temporaire dans la table data_meta_correction
    if($id_meta_new == 0)
    {
        $result = '';
        $source_info = "Conversion";
        $info_correction = "Conversion Côte(C) -> Débit(Q)";
        $obs = '';
        $id_correction = 0; // pour le moment 0 // à changer dans l'optimisation du code


        $sql_insert_newmeta = "INSERT INTO ".TABLE_DATA_META_CORRECTION." 
                                (id_station, id_typedata, id_user, source, obs, id_correction, info_correction, datetime_first,datetime_end) 
                                VALUES (".$station_chron.",
                                        ".$typedata_chron_q.",
                                        ".$id_user.",
                                        '".$source_info."',
                                        '".$obs."',
                                        ".$id_correction.",
                                        '".$info_correction."',
                                        '".$dateFirst."',
                                        '".$dateEnd."'
                                        );";
                                    
        tep_db_query($sql_link,$sql_insert_newmeta);
        $id_meta_new = mysqli_insert_id($sql_link); // Obtenir l'ID généré par le champ AUTO_INCREMENT
    }
    

    // Étape 1 : On vide d'abord la table data_etl_correction qui doit contenir les informations pour la conversion des H en Q
    $sql_create_temp_table = "TRUNCATE TABLE ".TABLE_DATA_ETL_CORRECTION.";";
    tep_db_query($sql_link, $sql_create_temp_table);

    // Étape 2 : Insérer les coefficients dans la table data_etl_correction
    foreach($tab_etl as $key => $value)
    {    
        $sql_insert_etl_convert = "INSERT INTO ".TABLE_DATA_ETL_CORRECTION." (
                                            id_station, id_typedata, datetime_first, datetime_end, h1, h2, a, b
                                            ) VALUES (
                                            " . $station_chron . ",
                                            " . $typedata_chron_h . ",
                                            '" . $value['dateFirst'] . "',
                                            '" . $value['dateEnd'] . "',
                                            " . $value['h1'] . ",
                                            " . $value['h2'] . ",
                                            " . $value['a'] . ",
                                            " . $value['b'] . "
                                            );";

        tep_db_query($sql_link, $sql_insert_etl_convert);
    }

    // On supprime d'abord d'éventuelle données avec un id_meta identique dans la table all_correction
    tep_db_query($sql_link, "DELETE FROM " . TABLE_DATA_ALL_CORRECTION . " WHERE id_meta=" . $id_meta_new);
}
          
    // Étape 3 : Exécuter la requête principale
    
    $batch_size = 1000; // Taille des lots pour traitement récursif

    // 1 - on s'occupe des données à corriger                            
    $sql_insert_correction = "
                                INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                                SELECT
                                    da.dateheure,
                                    ROUND((ec.a * da.valeur + ec.b), 3) AS valeur,
                                    ".$id_meta_new." AS id_meta
                                FROM ".TABLE_DATA_ALL." da
                                JOIN ".TABLE_DATA_META." dm
                                    ON da.id_meta = dm.id
                                    AND dm.id_station = ".$station_chron."
                                    AND dm.id_typedata = ".$typedata_chron_h."
                                JOIN ".TABLE_DATA_ETL_CORRECTION." ec
                                    ON ec.id_station = ".$station_chron."
                                    AND ec.id_typedata = ".$typedata_chron_h."
                                WHERE da.valeur > -8888
                                    AND da.valeur >= ec.h1
                                    AND da.valeur < ec.h2
                                    AND da.dateheure >= ec.datetime_first
                                    AND da.dateheure < ec.datetime_end
                                ORDER BY da.dateheure ASC, da.valeur DESC
                                LIMIT $offSet, $batch_size;
                            ";
    tep_db_query($sql_link, $sql_insert_correction);

    // 1 - on ne s'occupe que des lacunes
    $sql_insert_lacunes = "INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                                SELECT
                                    da.dateheure,
                                    -99999,
                                    ".$id_meta_new." AS id_meta
                                FROM ".TABLE_DATA_ALL." da
                                JOIN ".TABLE_DATA_META." dm
                                    ON da.id_meta = dm.id
                                    AND dm.id_station = ".$station_chron."
                                    AND dm.id_typedata = ".$typedata_chron_h."
                                WHERE da.valeur <= -8888
                                    AND da.dateheure >= '".$datetime_1."'
                                    AND da.dateheure < '".$datetime_2."'
                                ORDER BY da.dateheure ASC, da.valeur DESC
                                LIMIT $offSet, $batch_size;
                            ";
    
    /*
    "
                                INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                                SELECT
                                    da.dateheure,
                                    -99999,
                                    ".$id_meta_new." AS id_meta
                                FROM ".TABLE_DATA_ALL." da
                                JOIN ".TABLE_DATA_META." dm
                                    ON da.id_meta = dm.id
                                    AND dm.id_station = ".$station_chron."
                                    AND dm.id_typedata = ".$typedata_chron_h."
                                JOIN ".TABLE_DATA_ETL_CORRECTION." ec
                                    ON ec.id_station = ".$station_chron."
                                    AND ec.id_typedata = ".$typedata_chron_h."
                                WHERE da.valeur <= -8888
                                    AND da.dateheure >= ec.datetime_first
                                    AND da.dateheure < ec.datetime_end
                                ORDER BY da.dateheure ASC, da.valeur DESC
                                LIMIT $offSet, $batch_size;
                            ";
*/                            
    tep_db_query($sql_link, $sql_insert_lacunes);

    // Vérifions si d'autres données restent à insérer
    $count_query = "
            SELECT EXISTS (
                SELECT 1
                FROM ".TABLE_DATA_ALL." da
                JOIN ".TABLE_DATA_META." dm
                    ON da.id_meta = dm.id
                    AND dm.id_station = ".$station_chron."
                    AND dm.id_typedata = ".$typedata_chron_h."
                JOIN ".TABLE_DATA_ETL_CORRECTION." ec
                    ON ec.id_station = ".$station_chron."
                    AND ec.id_typedata = ".$typedata_chron_h."
                WHERE da.dateheure >= ec.datetime_first
                    AND da.dateheure < ec.datetime_end
                    AND da.valeur >= ec.h1
                    AND da.valeur < ec.h2
                LIMIT 1 OFFSET ".($offSet + $batch_size)."
            ) AS remaining;
    ";

    $result_count = tep_db_query($sql_link, $count_query);
    $remaining = $result_count ? (int)tep_db_fetch_array($result_count)['remaining'] : 0;


$responseData = array(
    'remaining' => $remaining > 0,
    'nextOffset' => ($offSet + $batch_size),
    'id_meta_correction' =>  $id_meta_new
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>