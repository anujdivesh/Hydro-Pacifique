<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
- Ce script permet de créer un nouvelle ETL
à partir d'une équation. 
- La régression se fait toute seule. 
----------------------------------------
*/

// ----------------------------------------------
// nécessaire pour la configuration du script

require('../../config.php');
require('../../database_tables.php');

require('../../function/math.php');	
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
$jsonDataModif = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataJson = json_decode($jsonDataModif, true);

// Accéder aux données du tableau récupérer
$id_user = $dataJson['idUser'];
$todayTimeFormatted = $dataJson['todayTimeFormatted'];
$date1 = $dataJson['date1'];
$date2 = $dataJson['date2'];
$heure1 = $dataJson['heure1'];
$heure2 = $dataJson['heure2'];
$origineH0 = $dataJson['origineH0'];
$bornesTab = $dataJson['bornesTab'];
$id_station = $dataJson['idStation'];

$datetime1 = datefr_us($date1).' '.$heure1;
$datetime2 = datefr_us($date2).' '.$heure2;

for($i=0;$i<4;$i++)
{
    if(isset($bornesTab[$i]))
    {
        ${"inf_".($i+1)} = $bornesTab[$i]['inf'];
        ${"sup_".($i+1)} = $bornesTab[$i]['sup'];
        ${"interv_".($i+1)} = $bornesTab[$i]['interv'];
    }
}

$import_result = '';
$valid_process = false;

// ----------------------------------------------
// Récupération de données dans la base

// TABLE STATION
$sql_station_all = "SELECT DISTINCT id_station, nom_station, code_station, station_type, active_station
					FROM ".TABLE_STATION;
$station_all_query = tep_db_query($sql_link,$sql_station_all);
while ($station_all = tep_db_fetch_array($station_all_query))
{	
	$station_all_array[$station_all['id_station']] = array('code_station' => $station_all['code_station'],
															'nom_station' => $station_all['nom_station'],
															'station_type' => $station_all['station_type'],
															);
}


// ----------------------------------------------
// Début du processus de mise à jour

        
$sql_ETL_valid = "SELECT COUNT(*) as nb
                FROM ".TABLE_DATA_ETL." etl
                WHERE id_station=$id_station
                AND NOT (datetime_end <= '$datetime1' OR datetime_first >= '$datetime2')";

$ETL_valid_query = tep_db_query($sql_link,$sql_ETL_valid);
$ETL_data_tab = tep_db_fetch_array($ETL_valid_query);

if($ETL_data_tab['nb'] < 1)
{
    // Démarrer une transaction
    //mysqli_begin_transaction($sql_link, MYSQLI_TRANS_START_READ_WRITE);

    try{
        // Maintenant on récupère les pts de JGE correspondant (même station, même période)
        $sql_jge = "SELECT DISTINCT jge.id, jge.datetime, jge.depouil_hmoy, jge.depouil_q
                    FROM ".TABLE_DATA_JGE." jge
                    WHERE jge.id_station = ".$id_station." 
                    AND jge.datetime >= '".$datetime1."'  
                    AND jge.datetime <= '".$datetime2."'                 
                    AND jge.depouil_hmoy REGEXP '^-?[0-9]+(\.[0-9]+)?$'  -- Vérifie si c'est un nombre
                    AND jge.depouil_q REGEXP '^-?[0-9]+(\.[0-9]+)?$'       -- Vérifie si c'est un nombre
                    ORDER BY jge.depouil_hmoy ASC";
        $jge_query = tep_db_query($sql_link,$sql_jge);

        $num_jge = 0;
        $h0=0.01;
        if($origineH0 > 0){$h0 = $origineH0;}
        while ($jge_tab = tep_db_fetch_array($jge_query))
        {
            $h = abs($jge_tab['depouil_hmoy']);
            $q = abs($jge_tab['depouil_q']);
            
            $num_jge++;
            
            $tab_h[$num_jge] = $h;
            $tab_q[$num_jge] = $q;

            $tab_X[$num_jge] = log10($h-$h0); // log10(h-h0)
            $tab_XX[$num_jge] = pow($tab_X[$num_jge],2); //(log10(h - h0))^2

            $tab_Y[$num_jge] = log10($q); // log10(q)

            $tab_XY[$num_jge] = $tab_Y[$num_jge] * $tab_X[$num_jge]; // log(q) * log(h - h0)
        }
        //print_r($tab_XY);
        

        if($num_jge > 1) // Il faut plus d'un point pour faire une courbe de tendance
        {
            $moy_XY = mean($tab_XY); // moy (XY)
            $moy_X = mean($tab_X); // moy (X)             
            $moy_XX = mean($tab_XX); // moy (X^2)      
            $moy_Y = mean($tab_Y); // moy (Y)

            $var_X = variance($tab_X); // VAR (X)
            $var_Y = variance($tab_Y); // VAR (Y)
            $cov_XY = covariance($tab_X,$tab_Y); // COV(X,Y)

            $param_a = ($moy_XY-($moy_X*$moy_Y))/($moy_XX-pow($moy_X,2));
            $param_b = $moy_Y-($param_a*$moy_X);

            $eq_text = "Equation : Q = (10<sup>".round($param_b,3)."</sup>) * H<sup>".round($param_a,3)."</sup>\n";

            $r2 = $cov_XY/(sqrt($var_X)*sqrt($var_Y));
            $r2_text = " Qualité de représentation : R<sup>2</sup> = ".round($r2,2);


            // Y calcul - 4 densité de points possibles
            
           
            for ($i = 1; $i <= 4; $i++) 
            {
                for ($h_eq = ${"inf_$i"}; $h_eq <= ${"sup_$i"}; $h_eq += ${"interv_$i"}) 
                {
                    $tab_h_calc[] = $h_eq;
                    $tab_q_calc[] = pow(10, $param_b) * pow($h_eq, $param_a);
                }
            }

            
            // Créer un nouvel ETL
            $sql_new_ETL = "INSERT INTO ".TABLE_DATA_ETL." (id_station, datetime_first, datetime_end)
                        VALUES ($id_station, '$datetime1', '$datetime2')";
            $new_ETL_query = tep_db_query($sql_link,$sql_new_ETL);

            if ($new_ETL_query) 
            {
                // Récupérer le dernier ID inséré
                $new_id_ETL = mysqli_insert_id($sql_link);

                $insert_etl_data = "INSERT INTO " . TABLE_DATA_ETL_DATA . "
                                    (`id_etl`, `hauteur`, `debit`) 
                                    VALUES (?, ?, ?)";
                $stmt_etl_data = mysqli_prepare($sql_link, $insert_etl_data);

                foreach ($tab_h_calc as $index => $h_eq) 
                {
                    mysqli_stmt_bind_param(
                        $stmt_etl_data,
                        "idd",
                        $new_id_ETL,
                        $h_eq,
                        $tab_q_calc[$index]
                    );
                    mysqli_stmt_execute($stmt_etl_data);            
                }
                mysqli_stmt_close($stmt_etl_data);
            }

            // Enregistrement de l'action Export dans la base action
            $type_action = 33;
            $info_action = "Mise à jour des données ETL - Station : ".$station_all_array[$id_station]['nom_station']." - ETL : ".$date1." ".$heure1." → ".$date2." ".$heure2;

            $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) 
                                            VALUES (".$id_user.",'".$type_action."','".$info_action."','".$todayTimeFormatted."')";
            tep_db_query($sql_link,$query);

            // Commit de la transaction si tout est correct
            mysqli_commit($sql_link);
            

            $import_result .= "La nouvelle relation d'étalonnage 'ETL : ".$date1." ".$heure1." → ".$date2." ".$heure2." a bien été crée.<br>";
            
            $import_result .= "-<br>";
            $import_result .= $eq_text."<br>";
            $import_result .= $r2_text."<br>";

            $valid_process = true;
        }
        else{$import_result .= "Il faut plus d'un point de JGE pour générer une courbe de tendance.\n";}
        

    } catch (Exception $e) 
    {
        // Annuler la transaction en cas d'erreur
        mysqli_rollback($sql_link);
    
        // Afficher un message d'erreur
        $import_result .= "Erreur lors de l'exécution de la transaction : " . $e->getMessage();
    } 
}
else
{
    $import_result .= "La période choisie est déjà couverte par une autre relation d'étalonnage : ".$date1." ".$heure1." → ".$date2." ".$heure2."\n";
}

$responseData = array(
    'js_text' => $import_result,
    'valid_process' => $valid_process
);


// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>