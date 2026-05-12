<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Calcul Chronique
- Ce script permet de construire de nouvelles chroniques à partir d'une chronique existe
- Calcul loi Ynew = aX+b
- Correction de la ligne de temps
- Création de Chroniques temporelle : x minutes, Jours, Mois, Année
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
$jsonDataCalcul = file_get_contents('php://input');

// Décoder les données JSON en un tableau associatif PHP
$dataCalcul = json_decode($jsonDataCalcul, true);

// Accéder aux données du tableau récupérer
$id_user = $dataCalcul['id_user'];
$id_correction = $dataCalcul['id_correction'];
$id_station = $dataCalcul['id_station'];
$id_chron = $dataCalcul['id_chron'];
$type_correction = $dataCalcul['type_correction'];
$calcul_correction = $dataCalcul['calcul_correction'];
$axe_correction = $dataCalcul['axe_correction'];
$pastemps = $dataCalcul['pastemps'];
$modecalcul = $dataCalcul['modecalcul'];
$to_periode_encours = $dataCalcul['to_periode_encours'];
$id_create_chron_encours = $dataCalcul['id_create_chron_encours'];

$datetime_first = $dataCalcul['datetime_first'];
$datetime_first_tab = explode(' ',$datetime_first);
$datetime_first_formated = datefr_us($datetime_first_tab[0]).' '.$datetime_first_tab[1];

$datetime_end = $dataCalcul['datetime_end'];
$datetime_end_tab = explode(' ',$datetime_end);
$datetime_end_formated = datefr_us($datetime_end_tab[0]).' '.$datetime_end_tab[1];

// Autre initialisation de variables
$datetime_now_us = date('Y-m-d H:i:s'); // Crée un objet DateTime pour la date actuelle format us pour inclusion dans les tables
$datetime_now_fr = date('d-m-Y H:i:s'); // Crée un objet DateTime pour la date actuelle format fr pour texte

$min_y = 0;
$max_y = 0;

$msg_newCorrection = '';


// Chargement de tables nécessaires au traitement de l'algorithme

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


// ------------------------------------------ MODULE DE CORRECTION ----------------------------------------------------------

// Récupération des données de la chronique à corriger

// Création de la ligne de correction de données dans la table data_correction

if($id_correction == 0)
{
    $sql_insert_newcorrection = "INSERT INTO ".TABLE_DATA_CORRECTION." (id_user, datetime_correction, id_station, id_chron_init, datetime_first, datetime_end) 
                                VALUES (".$id_user.", '".$datetime_now_us."',".$id_station.",".$id_chron.",'".$datetime_first_formated."','".$datetime_end_formated."');";
    tep_db_query($sql_link,$sql_insert_newcorrection);
    $id_correction = mysqli_insert_id($sql_link); // Obtenir l'ID généré par le champ AUTO_INCREMENT
}


$source_info = "Correction";
// Création des données dans la table temporaire data_all_correction
$sql_calcul = 'da.valeur';
$sql_date_decalage = 'da.dateheure';
$info_correction = '';
if($type_correction == 'calcul')
{
    $sql_calcul = str_replace('Y', '*da.valeur', $calcul_correction);
    $info_correction = $calcul_correction;
}
if($type_correction == 'decalage_date')
{
    $sql_date_decalage = "DATE_ADD(da.dateheure, INTERVAL ".$calcul_correction." SECOND)";
    $info_correction = $calcul_correction." seconde(s)";
}
if($type_correction == 'lissage')
{
   $info_correction = "Lissage - seuil : ".$calcul_correction." %";
}
if($type_correction == 'lacune')
{
    $info_correction = "Lacune";
}
if($type_correction == 'calcul_pastemps')
{
    $info_correction = "New chron. <br> pas de temps : ".$pastemps." min";
}
if($type_correction == 'calcul_chron_new')
{
    $info_correction = "New chron. : ".$type_chron_array[$id_create_chron_encours]['init_type_data']." ".$type_chron_array[$id_create_chron_encours]['nom_type_data'];
}

$obs = $info_correction;



// Création d'une nouvelle metadonnees temporaire dans la table data_meta_correction
$sql_insert_newmeta = "INSERT INTO ".TABLE_DATA_META_CORRECTION." (id_station, id_typedata, id_user, source, obs, id_correction, type_correction, info_correction, axe_correction, datetime_first,datetime_end) 
                        VALUES (".$id_station.",
                                ".$id_chron.",
                                ".$id_user.",
                                '".$source_info."',
                                '".$obs."',
                                ".$id_correction.",
                                '".$type_correction."',
                                '".$info_correction."',
                                '".$axe_correction."',
                                '".$datetime_first_formated."',
                                '".$datetime_end_formated."'
                                );";
tep_db_query($sql_link,$sql_insert_newmeta);
$id_meta = mysqli_insert_id($sql_link); // Obtenir l'ID généré par le champ AUTO_INCREMENT



if(($type_correction == 'calcul') || ($type_correction == 'decalage_date'))
{

    $sql_insert_newdata = "INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                            SELECT 
                                ".$sql_date_decalage." AS nouvelle_dateheure,
                                CASE 
                                WHEN da.valeur IN (-8888, -9999, -88888, -99999) THEN -99999
                                    ELSE ".$sql_calcul."
                                END AS nouvelle_valeur,
                                '".$id_meta."' AS nouvelle_id_meta
                            FROM 
                                ".TABLE_DATA_ALL." da
                            JOIN 
                                ".TABLE_DATA_META." dm ON da.id_meta = dm.id
                            WHERE 
                                dm.id_typedata = ".$id_chron."
                                AND dm.id_station = ".$id_station."
                                AND da.dateheure >= '".$datetime_first_formated."'
                                AND da.dateheure <= '".$datetime_end_formated."'
                            ORDER BY 
                                da.dateheure ASC;";
                                
    tep_db_query($sql_link,$sql_insert_newdata);

}

if($type_correction == 'lissage')
{
    $sql_data_brutes = "
                        SELECT da.dateheure, da.valeur, dm.id_station, dm.id, dm.id_typedata, dm.id_codequal
								FROM ".TABLE_DATA_ALL." da
								JOIN ".TABLE_DATA_META." dm ON da.id_meta=dm.id
								WHERE dm.id_typedata = ".$id_chron."
								AND dm.id_station = ".$id_station."
								AND da.dateheure >= '".$datetime_first_formated."'
								AND da.dateheure <= '".$datetime_end_formated."'
								ORDER BY da.dateheure ASC;
                        ";
    $data_brutes_query = tep_db_query($sql_link,$sql_data_brutes);
    
    $seuil = $calcul_correction/100; // 1% de variation
    $data_lissees = []; // Tableau des données filtrées
    $insert_values = []; // Accumulateur pour les valeurs à insérer

    // Variables pour suivre les points
    $precedent = null;
    $courant = null;

    // Parcourir les données
    while ($data_brutes_tab = tep_db_fetch_array($data_brutes_query)) 
    {
        // Charger le point courant
        $suivant = [
            'dateheure' => $data_brutes_tab['dateheure'],
            'valeur' => floatval($data_brutes_tab['valeur']), // Conversion en float
        ];

        // Si c'est le premier point, on l'ajoute directement
        if (is_null($precedent)) 
        {
            $data_lissees[] = $suivant;
            $precedent = $suivant;
            $courant = $suivant;
            continue;
        }

        // Vérifier si on conserve le point courant
        if (!is_null($courant)) 
        {
            // Calculer la variation par rapport au précédent et au suivant
            if ($precedent['valeur'] != 0) {
                $var_precedent = abs(($courant['valeur'] - $precedent['valeur']) / $precedent['valeur']);
            } else {
                // Si le précédent est 0, définir la variation à 0 ou gérer différemment
                $var_precedent = 0; // Vous pouvez aussi choisir une autre valeur ou logique
            }

            if ($suivant['valeur'] != 0) {
                $var_suivant = abs(($suivant['valeur'] - $courant['valeur']) / $suivant['valeur']);
            } else {
                // Si le courant est 0, définir la variation à 0 ou gérer différemment
                $var_suivant = 0; // Vous pouvez aussi choisir une autre valeur ou logique
            }

            // Conserver le point courant si la variation dépasse le seuil
            if ($var_precedent < $seuil && $var_suivant < $seuil) 
            {
                // Ignorer le point courant (ne pas l'ajouter à la liste)
                $precedent = $courant; // Mettre à jour le précédent sans changer la liste
                continue;
            }
        }

        // Ajouter le point courant dans la liste lissée
        $data_lissees[] = $courant;
        $precedent = $courant; // Mettre à jour le précédent pour la prochaine itération

        // Avancer les points
        $courant = $suivant;
    }

    // Ajouter le dernier point (toujours conservé)
    if (!is_null($courant)) 
    {
        $data_lissees[] = $courant;
    }

    // Préparer les valeurs pour une seule requête INSERT
    foreach ($data_lissees as $data) 
    {
        $insert_values[] = "('".$data['dateheure']."', ".$data['valeur'].", ".$id_meta.")";
    }

    // Construire et exécuter la requête INSERT unique
    if (!empty($insert_values)) 
    {
        $sql_insert_lissage = "INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta) VALUES " . implode(',', $insert_values) . ";";
        tep_db_query($sql_link, $sql_insert_lissage);
    }
}


if($type_correction == 'lacune')
{

    $sql_insert_lacune = "INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                            VALUES 
                            ('".$datetime_first_formated."', -99999, ".$id_meta."),
                            ('".$datetime_end_formated."', -99999, ".$id_meta.");";
    tep_db_query($sql_link,$sql_insert_lacune);
}

if($type_correction == 'calcul_pastemps')
{
    // L'opération doit être soit un cumul (PLuie), soit une moyenne (Autre type de données)
    $agregation = 'AVG'; // Moyenne
    $type_data = $type_chron_array[$id_chron]['id_eq_type_data']; // On retrouve le bon type de donnée (Pluie, Hydro, Piézo, ...)
    if($eq_type_array[$type_data]['valeur_data_type'] == 2){$agregation = 'SUM';} // Somme (cumul)

    if($modecalcul == 'moy'){ $agregation = 'AVG';}
    if($modecalcul == 'cumul'){ $agregation = 'SUM';}

    $sql_insert_pastemps = "INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                                    SELECT 
                                        DATE_FORMAT(
                                                        FROM_UNIXTIME(
                                                            FLOOR(UNIX_TIMESTAMP(da.dateheure) / (".$pastemps." * 60)) * (".$pastemps." * 60)
                                                        ), 
                                                        '%Y-%m-%d %H:%i:00'
                                                    ) AS nouvelle_dateheure,  -- Date arrondie au début de la tranche de 5 minutes
                                        ".$agregation."(da.valeur) AS nouvelle_valeur,  -- Moyenne ou Cumul des valeurs valides
                                        '".$id_meta."' AS id_meta                                      -- ID méta à définir ou à récupérer dynamiquement
                                    FROM 
                                        ".TABLE_DATA_ALL." da
                                    JOIN 
                                        ".TABLE_DATA_META." dm ON da.id_meta = dm.id
                                    WHERE 
                                        valeur BETWEEN -8888 AND 99999  -- Exclure les valeurs spéciales qui sont des notifications de lacunes
                                        AND dm.id_typedata = ".$id_chron."
                                        AND dm.id_station = ".$id_station."
                                        AND da.dateheure >= '".$datetime_first_formated."'
                                        AND da.dateheure <= '".$datetime_end_formated."'                       
                                    GROUP BY 
                                        UNIX_TIMESTAMP(dateheure) DIV (".$pastemps." * 60)                               
                                    ORDER BY 
                                        nouvelle_dateheure ASC;";

    tep_db_query($sql_link,$sql_insert_pastemps);
}

if($type_correction == 'calcul_chron_new')
{
    // L'opération doit être soit un cumul (PLuie), soit une moyenne (Autre type de données)
    $agregation = 'AVG'; // Moyenne
    $type_data = $type_chron_array[$id_chron]['id_eq_type_data']; // On retrouve le bon type de donnée (Pluie, Hydro, Piézo, ...)
    if($eq_type_array[$type_data]['valeur_data_type'] == 2){$agregation = 'SUM';} // Somme (cumul)

    if($to_periode_encours == 1) // by day 
    {
        $sql_insert_chron_new = "INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                                        SELECT 
                                            DATE(da.dateheure) AS nouvelle_dateheure,  -- Date au début du jour
                                            ".$agregation."(da.valeur) AS nouvelle_valeur,        -- Moyenne des valeurs valides
                                            '".$id_meta."' AS id_meta                            -- ID méta à définir ou à récupérer dynamiquement
                                        FROM 
                                            ".TABLE_DATA_ALL." da
                                        JOIN 
                                            ".TABLE_DATA_META." dm ON da.id_meta = dm.id
                                        WHERE 
                                            da.valeur NOT IN (-8888, -9999, -88888, -99999)  -- Exclure les valeurs spéciales                                            
                                            AND dm.id_typedata = ".$id_chron."
                                            AND dm.id_station = ".$id_station."
                                            AND da.dateheure >= '".$datetime_first_formated."'
                                            AND da.dateheure <= '".$datetime_end_formated."'                       
                                        GROUP BY 
                                            DATE(da.dateheure)                            -- Grouper par jour
                                        ORDER BY 
                                            nouvelle_dateheure ASC;";
    }

    if($to_periode_encours == 2) // by month 
    {
        $sql_insert_chron_new = "INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                                        SELECT 
                                            DATE_FORMAT(da.dateheure, '%Y-%m-01') AS nouvelle_dateheure,  -- Date au début du jour
                                            ".$agregation."(da.valeur) AS nouvelle_valeur,        -- Moyenne des valeurs valides
                                            '".$id_meta."' AS id_meta                            -- ID méta à définir ou à récupérer dynamiquement
                                        FROM 
                                            ".TABLE_DATA_ALL." da
                                        JOIN 
                                            ".TABLE_DATA_META." dm ON da.id_meta = dm.id
                                        WHERE 
                                            da.valeur NOT IN (-8888, -9999, -88888, -99999)  -- Exclure les valeurs spéciales                                            
                                            AND dm.id_typedata = ".$id_chron."
                                            AND dm.id_station = ".$id_station."
                                            AND da.dateheure >= '".$datetime_first_formated."'
                                            AND da.dateheure <= '".$datetime_end_formated."'                       
                                        GROUP BY 
                                            DATE_FORMAT(da.dateheure, '%Y-%m-01')                            -- Grouper par mois
                                        ORDER BY 
                                            nouvelle_dateheure ASC;";
    }

    if($to_periode_encours == 3) // by year 
    {
        $sql_insert_chron_new = "INSERT INTO ".TABLE_DATA_ALL_CORRECTION." (dateheure, valeur, id_meta)
                                        SELECT 
                                            DATE_FORMAT(da.dateheure, '%Y-01-01') AS nouvelle_dateheure,  -- Date au début du jour
                                            ".$agregation."(da.valeur) AS nouvelle_valeur,        -- Moyenne des valeurs valides
                                            '".$id_meta."' AS id_meta                            -- ID méta à définir ou à récupérer dynamiquement
                                        FROM 
                                            ".TABLE_DATA_ALL." da
                                        JOIN 
                                            ".TABLE_DATA_META." dm ON da.id_meta = dm.id
                                        WHERE 
                                            da.valeur NOT IN (-8888, -9999, -88888, -99999)  -- Exclure les valeurs spéciales                                            
                                            AND dm.id_typedata = ".$id_chron."
                                            AND dm.id_station = ".$id_station."
                                            AND da.dateheure >= '".$datetime_first_formated."'
                                            AND da.dateheure <= '".$datetime_end_formated."'                       
                                        GROUP BY 
                                            DATE_FORMAT(da.dateheure, '%Y-01-01')                            -- Grouper par année
                                        ORDER BY 
                                            nouvelle_dateheure ASC;";
    }
    
   tep_db_query($sql_link,$sql_insert_chron_new);
}




$msg_newCorrection .= " 
                        <div style='font-weight:normal;'>
                            <span style='font-size:16px;font-weight:bold;'>
                                La correction a bien été générée
                            </span>
                            <br><br>
                                <span style='font-weight:bold;'>"."Type"."</span> : ".$obs."
                            <br>
                                <span style='font-weight:bold;'>"."Période"."</span> : du ".$datetime_first." au ".$datetime_end."
                        </div>
                        ";


// Remplissage du tableau de retour

$responseData = array(
    'id_correction' => $id_correction,
    'msg_newCorrection' => $msg_newCorrection  
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;


?>