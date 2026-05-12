<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Toutes les fonctions liées au traitement des calculs statistiques
*/





// YEAR

// Edit table des statistiques
function statsEditYear($sql_link,$id_station,$type_chron_years) 
{
    $results_lastyear = stats_lastyear($sql_link,$id_station,$type_chron_years);
    $results_10years = stats_10years($sql_link,$id_station,$type_chron_years);
    $results_Allyears = stats_Allyears($sql_link,$id_station,$type_chron_years);

    $textedit_Tab = "";

        $textedit_Tab .= "<table style='width:100%;' >";
            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td>&nbsp;</td>";
                $textedit_Tab .= "<td style='width:150px;'><span>".htmlaccent('Dernière année')."</span></td>";
                $textedit_Tab .= "<td style='width:150px;'><span>".htmlaccent('10 dernière années')."</span></td>";
                $textedit_Tab .= "<td style='width:150px;'><span>".htmlaccent('Tous le temps')."</span></td>";
            $textedit_Tab .= "</tr>";

            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td><span>".htmlaccent('Moy.')."</span></td>";
                $textedit_Tab .= "<td>".$results_lastyear['moy']."</td>";
                $textedit_Tab .= "<td>".$results_10years['moy']."</td>";
                $textedit_Tab .= "<td>".$results_Allyears['moy']."</td>";
            $textedit_Tab .= "</tr>";

            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td><span>".htmlaccent('Max.')."</span></td>";
                $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>".$results_10years['max']."</td>";
                $textedit_Tab .= "<td>".$results_Allyears['max']."</td>";
            $textedit_Tab .= "</tr>";

            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td><span>".htmlaccent('Min.')."</span></td>";
                $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>".$results_10years['min']."</td>";
                $textedit_Tab .= "<td>".$results_Allyears['min']."</td>";
            $textedit_Tab .= "</tr>";

            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td><span>".htmlaccent('Période')."</span></td>";
                $textedit_Tab .= "<td>".$results_lastyear['year']."</td>";
                $textedit_Tab .= "<td>".$results_10years['annee_min']." - ".$results_10years['annee_max']."</td>";
                $textedit_Tab .= "<td>".$results_Allyears['annee_min']." - ".$results_Allyears['annee_max']."</td>";
            $textedit_Tab .= "</tr>";

        $textedit_Tab .= "</table>";


    return $textedit_Tab;
}

// Statistiques sur la dernière année
function stats_lastyear($sql_link,$id_station,$type_chron) 
{
    $results_lastyear = array(); // Initialisation du tableau des résultats

    $results_lastyear['moy'] = '-';   
    $results_lastyear['min'] = '-';  
    $results_lastyear['max'] = '-';  
    $results_lastyear['year'] = '-';  
    
    // Requête pour les statistiques des 10 dernières années
    $sql_stats_lastyear = "
    SELECT 
        YEAR(MAX(da.dateheure)) AS year,
        AVG(da.valeur) AS moy,  -- Calcule la moyenne des valeurs
        MIN(da.valeur) AS min,
        MAX(da.valeur) AS max
    FROM 
        ".TABLE_DATA_ALL." da
    JOIN 
        ".TABLE_DATA_META." dm ON da.id_meta=dm.id
    WHERE 
        dm.id_typedata = ".$type_chron."
        AND dm.id_station = ".$id_station."
        AND da.valeur > 0 
        AND da.valeur <= 99999 -- pour ne pas prendre en compte les lacunes
    GROUP BY YEAR(da.dateheure) 
    ORDER BY da.dateheure DESC
    ";

    $stats_lastyear_query = tep_db_query($sql_link,$sql_stats_lastyear);
    $stats_lastyear_tab = tep_db_fetch_array($stats_lastyear_query);
    
    if(isset($stats_lastyear_tab) && isset($stats_lastyear_tab['moy']))
    {
        $results_lastyear['moy'] = number_format((float)$stats_lastyear_tab['moy'], 3, '.', ' ');   
        $results_lastyear['min'] = number_format((float)$stats_lastyear_tab['min'], 3, '.', ' ');  
        $results_lastyear['max'] = number_format((float)$stats_lastyear_tab['max'], 3, '.', ' ');  
        $results_lastyear['year'] = $stats_lastyear_tab['year'];
    }

    return $results_lastyear;
}

// Statistiques sur les 10 dernières années par rapport à l"année d"aujourd"hui
function stats_10years($sql_link,$id_station,$type_chron) 
{
    
    $results_10years = array(); // Initialisation du tableau des résultats

    // Requête pour les statistiques des 10 dernières années
    $sql_stats_10years = "
    SELECT
        AVG(da.valeur) AS moy,
        MIN(da.valeur) AS min,
        MAX(da.valeur) AS max,
        COUNT(da.valeur) AS nb_data,
        MIN(YEAR(da.dateheure)) AS annee_min,
        MAX(YEAR(da.dateheure)) AS annee_max
    FROM 
        ".TABLE_DATA_ALL." da
    JOIN 
        ".TABLE_DATA_META." dm ON da.id_meta=dm.id
    WHERE 
        dm.id_typedata = ".$type_chron."
        AND dm.id_station = ".$id_station."
        AND da.valeur > 0
        AND da.valeur <= 99999 -- pour ne pas prendre en compte les lacunes
        AND YEAR(da.dateheure) >= YEAR(CURRENT_DATE) - 10
    ORDER BY 
        da.dateheure DESC;
    ";


    $stats_10years_query = tep_db_query($sql_link,$sql_stats_10years);
    $stats_10years_tab = tep_db_fetch_array($stats_10years_query);

    if(isset($stats_10years_tab))
    {
        $results_10years['moy'] = number_format((float)$stats_10years_tab['moy'], 3, '.', ' ');
        $results_10years['max'] = number_format((float)$stats_10years_tab['max'], 3, '.', ' '); 
        $results_10years['min'] = number_format((float)$stats_10years_tab['min'], 3, '.', ' ');   
        $results_10years['annee_min'] = $stats_10years_tab['annee_min'];  
        $results_10years['annee_max'] = $stats_10years_tab['annee_max'];  
        $results_10years['nb_data'] = number_format((float)$stats_10years_tab['nb_data'], 0, '.', ' ');    
    }

    return $results_10years;

}

// Statistiques sur toutes les années
function stats_Allyears($sql_link,$id_station,$type_chron) 
{
    
    $results_Allyears = array(); // Initialisation du tableau des résultats

    // Requête pour les statistiques des 10 dernières années
    $sql_stats_Allyears = "
    SELECT
        AVG(da.valeur) AS moy,
        MIN(da.valeur) AS min,
        MAX(da.valeur) AS max,
        COUNT(da.valeur) AS nb_data,
        MIN(YEAR(da.dateheure)) AS annee_min,
        MAX(YEAR(da.dateheure)) AS annee_max
    FROM 
        ".TABLE_DATA_ALL." da
    JOIN 
        ".TABLE_DATA_META." dm ON da.id_meta=dm.id
    WHERE 
        dm.id_typedata = ".$type_chron."
        AND dm.id_station = ".$id_station."
        AND da.valeur > 0
        AND da.valeur <= 99999 -- pour ne pas prendre en compte les lacunes
    ORDER BY 
        da.dateheure DESC;
    ";

    $stats_Allyears_query = tep_db_query($sql_link,$sql_stats_Allyears);
    $stats_Allyears_tab = tep_db_fetch_array($stats_Allyears_query);

    if(isset($stats_Allyears_tab))
    {
        $results_Allyears['moy'] = number_format((float)$stats_Allyears_tab['moy'], 3, '.', ' ');
        $results_Allyears['max'] = number_format((float)$stats_Allyears_tab['max'], 3, '.', ' '); 
        $results_Allyears['min'] = number_format((float)$stats_Allyears_tab['min'], 3, '.', ' ');   
        $results_Allyears['annee_min'] = $stats_Allyears_tab['annee_min'];  
        $results_Allyears['annee_max'] = $stats_Allyears_tab['annee_max'];  
        $results_Allyears['nb_data'] = number_format((float)$stats_Allyears_tab['nb_data'], 0, '.', ' ');  
    }

    return $results_Allyears;

}


// MONTH

// Edit table des statistiques
function statsEditMonth($sql_link,$id_station,$type_chron_years) {

    //$results_lastyear = stats_lastyearMonth($sql_link,$id_station,$type_chron_years);
    /*
    $results_10years = stats_10years($sql_link,$id_station,$type_chron_years);
    $results_Allyears = stats_Allyears($sql_link,$id_station,$type_chron_years);
    */

    $textedit_Tab = "";

        $textedit_Tab .= "<table style=\"width:80%;\" id=\"tab_stat_map\">";
            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td>&nbsp;</td>";
                $textedit_Tab .= "<td><span>".htmlaccent("Last Year")."</span></td>";
                $textedit_Tab .= "<td><span>".htmlaccent("10 ans")."</span></td>";
                $textedit_Tab .= "<td><span>".htmlaccent("All")."</span></td>";
            $textedit_Tab .= "</tr>";

            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td><span>".htmlaccent("Moy.")."</span></td>";
                //$textedit_Tab .= "<td>".$results_lastyear["moy"]."</td>";
                $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>-</td>";
            $textedit_Tab .= "</tr>";

            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td><span>".htmlaccent("Max.")."</span></td>";
                $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>-</td>";
            $textedit_Tab .= "</tr>";

            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td><span>".htmlaccent("Min.")."</span></td>";
                $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>-</td>";
            $textedit_Tab .= "</tr>";

            $textedit_Tab .= "<tr>";
                $textedit_Tab .= "<td><span>".htmlaccent("Info.")."</span></td>";
                //$textedit_Tab .= "<td>".$results_lastyear["last_year"]." - ".$results_lastyear["nb_data"]." mois</td>";
                $textedit_Tab .= "<td>-</td>";
                    $textedit_Tab .= "<td>-</td>";
                $textedit_Tab .= "<td>-</td>";
            $textedit_Tab .= "</tr>";

        $textedit_Tab .= "</table>";


    return $textedit_Tab;
}


// Statistiques sur la dernière année
function stats_lastyearMonth($sql_link,$id_station,$type_chron) {
    
    $results_lastyear = array(); // Initialisation du tableau des résultats

    // Requête pour les statistiques des 10 dernières années
    $sql_stats_lastyear = "
    SELECT 
        MAX(YEAR(da.dateheure)) AS last_year,
        AVG(da.valeur) AS moy,
        COUNT(da.valeur) AS nb_data
    FROM 
        ".TABLE_DATA_ALL." da
    JOIN 
        ".TABLE_DATA_META." dm ON da.id_meta=dm.id
    WHERE 
        dm.id_typedata = ".$type_chron."
        AND dm.id_station = ".$id_station."
        AND da.valeur > 0
        AND YEAR(da.dateheure) = (
                                    SELECT MAX(YEAR(da_inner.dateheure))
                                    FROM ".TABLE_DATA_ALL." da_inner
                                    JOIN ".TABLE_DATA_META." dm_inner ON da_inner.id_meta = dm_inner.id
                                    WHERE dm_inner.id_typedata = dm.id_typedata
                                    AND dm_inner.id_station = dm.id_station
                                )
    ";

    $stats_lastyear_query = tep_db_query($sql_link,$sql_stats_lastyear);
    $stats_lastyear_tab = tep_db_fetch_array($stats_lastyear_query);

    if(isset($stats_lastyear_tab))
    {
        $results_lastyear["moy"] = number_format((float)$stats_lastyear_tab["moy"], 3, ".", "");   
       //$results_lastyear["month"] = $stats_lastyear_tab["month"];
        $results_lastyear["last_year"] = $stats_lastyear_tab["last_year"];
        $results_lastyear["nb_data"] = $stats_lastyear_tab["nb_data"];
    }

    return $results_lastyear;
}

?>
