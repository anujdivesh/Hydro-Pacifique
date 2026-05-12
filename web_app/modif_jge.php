<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Saisie d'un jaugeage et calcul du débit
*/

require('include/application_top.php');

$message_info = '';
$message_suprr_liaison = '';
$row = 0;
$reference = '';
$libelle = '';
$today = date('d-m-Y'); 
$today_us = date('Y-m-d'); 
$date_format = 'd-m-Y';
$current_time = date('H:i:s');

$id_region = $region_default;
$id_commune = 0;
$id_station_old = '';

$nb_bras_tab = 0; 

$modif=false;
$error_jge = false;

//---------------------------------------------------------------
// SQL - Récupérer les données pour les mettre dans des tableaux

// TABLE AGENT - Appel pour stocker l'info dans un tableau
$sql_agent = "SELECT DISTINCT id, nom, prenom 
            FROM ".TABLE_AGENT." 
            WHERE terrain=1 
            ORDER BY nom ASC";
$agent_query = tep_db_query($sql_link,$sql_agent);
while($agent = tep_db_fetch_array($agent_query))
{
    // Nettoyage des noms et prénoms
    $nom_agent = ucwords(strtolower(noaccent(html_entity_decode($agent['nom'] ?? $default_string))));
    $prenom_agent = noaccent(html_entity_decode($agent['prenom'] ?? $default_string));

    $prenom_initial = strtoupper(substr($prenom_agent, 0, 1)) . '.'; // Extrait la première lettre et ajoute un point
    $agent_array[$agent['id']] = $prenom_initial . " " . $nom_agent;
}


// TABLE STATION
$sql_station = "SELECT DISTINCT s.id_station, s.nom_station, s.code_station, s.active_station, s.id_region
				FROM ".TABLE_STATION." s 
				JOIN ".TABLE_REGION." r ON s.id_region=r.id_region
				WHERE s.station_type=11 AND r.id_territoire=".$territoire_id." 
				ORDER BY s.nom_station";

$station_query = tep_db_query($sql_link,$sql_station);
while ($station = tep_db_fetch_array($station_query))
{	
	$nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));
	$code_station =  htmlaccent(html_entity_decode($station['code_station'] ?? $default_string));
	
	$act_station = false;
	if($station['active_station'] == 1){$act_station = true;}
		
		
	$station_array[] = array('id' => $station['id_station'],							 
							 'act_station' => $act_station,
							 'nom_station' => $nom_station,
						   	 'code_station' => $code_station);
	
}

// DATA SITE JGE
$sql_data_jge_site = "SELECT DISTINCT id, titre, obs FROM ".TABLE_DATA_JGE_SITE;
$data_jge_site_query = tep_db_query($sql_link,$sql_data_jge_site);
while ($data_jge_site = tep_db_fetch_array($data_jge_site_query))
{				
	$data_jge_site_array[$data_jge_site['id']] = array('titre' => htmlaccent(html_entity_decode($data_jge_site['titre'] ?? $default_string)),
                                                        'obs' => htmlaccent(html_entity_decode($data_jge_site['obs'] ?? $default_string))
                                                        );
} 

// DATA METHODE JGE
$sql_data_jge_methode = "SELECT DISTINCT id, titre, obs FROM ".TABLE_DATA_JGE_METHODE;
$data_jge_methode_query = tep_db_query($sql_link,$sql_data_jge_methode);
while ($data_jge_methode = tep_db_fetch_array($data_jge_methode_query))
{				
	$data_jge_methode_array[$data_jge_methode['id']] = array('titre' => htmlaccent(html_entity_decode($data_jge_methode['titre'] ?? $default_string)),
                                                            'obs' => htmlaccent(html_entity_decode($data_jge_methode['obs'] ?? $default_string))
                                                            );
} 


// DATA JGE FOND LIT
$sql_data_jge_fondlit = "SELECT DISTINCT id, titre, obs FROM ".TABLE_DATA_JGE_FONDLIT;
$data_jge_fondlit_query = tep_db_query($sql_link,$sql_data_jge_fondlit);
while ($data_jge_fondlit = tep_db_fetch_array($data_jge_fondlit_query))
{				
	$data_jge_fondlit_array[$data_jge_fondlit['id']] = array('titre' => htmlaccent(html_entity_decode($data_jge_fondlit['titre'] ?? $default_string)),
                                                            'obs' => htmlaccent(html_entity_decode($data_jge_fondlit['obs'] ?? $default_string))
                                                            );
} 

// DATA TYPE JGE
$sql_data_jge_type = "SELECT DISTINCT id, titre, obs FROM ".TABLE_DATA_JGE_TYPE;
$data_jge_type_query = tep_db_query($sql_link,$sql_data_jge_type);
while ($data_jge_type = tep_db_fetch_array($data_jge_type_query))
{				
	$data_jge_type_array[$data_jge_type['id']] = array('titre' => htmlaccent(html_entity_decode($data_jge_type['titre'] ?? $default_string)),
                                                            'obs' => htmlaccent(html_entity_decode($data_jge_type['obs'] ?? $default_string))
                                                            );
} 



// DATA MOULINET
$sql_moulinet = "SELECT DISTINCT id, num, fabricant, obs  FROM ".TABLE_MOULINET;
$moulinet_query = tep_db_query($sql_link,$sql_moulinet);
while ($moulinet = tep_db_fetch_array($moulinet_query))
{				
	$moulinet_array[$moulinet['id']] = array('num' => htmlaccent(html_entity_decode($moulinet['num'] ?? $default_string)),
                                            'fabricant' => htmlaccent(html_entity_decode($moulinet['fabricant'] ?? $default_string)),
                                            'obs' => htmlaccent(html_entity_decode($moulinet['obs'] ?? $default_string))
                                            );
} 

// DATA HELICE
// On récupère d'abord les données hélice dans la BDD
// On utilise les paramètres pour faire le calcul de convertion du nombre de tour d'hélice en vitesse
$data_helice_hidden = '';

$sql_helice = "SELECT DISTINCT id, num, diametre, pas, l1, a1, b1, l2, a2, b2, a3, b3, fabricant, obs
                FROM ".TABLE_HELICE."
                ORDER BY num ASC";
$helice_query = tep_db_query($sql_link,$sql_helice);
while ($helice = tep_db_fetch_array($helice_query))
{	
    $id_helice = $helice['id'];
    $num = htmlaccent(html_entity_decode($helice['num'] ?? $default_string));
    $diametre = floatval($helice['diametre']);
    $pas = floatval($helice['pas']);
    $l1 = floatval($helice['l1']);
    $a1 = floatval($helice['a1']);
    $b1 = floatval($helice['b1']);
    $l2 = floatval($helice['l2']);
    $a2 = floatval($helice['a2']);
    $b2 = floatval($helice['b2']);
    $a3 = floatval($helice['a3']);    
    $b3 = floatval($helice['b3']);
    $fabricant = htmlaccent(html_entity_decode($helice['fabricant'] ?? $default_string));                                            
    $obs = htmlaccent(html_entity_decode($helice['obs'] ?? $default_string));
    
    // On met en champs cacher les infos des hélices afin de pouvoir les récupérer en JS et générer les calculs de jaugeage de façon instantanné
    $data_helice_hidden .= " 
                            <input type='hidden' name='l1_".$id_helice."' id='l1_".$id_helice."' value='".$l1."'>
                            <input type='hidden' name='a1_".$id_helice."' id='a1_".$id_helice."' value='".$a1."'>
                            <input type='hidden' name='b1_".$id_helice."' id='b1_".$id_helice."' value='".$b1."'>
                            <input type='hidden' name='l2_".$id_helice."' id='l2_".$id_helice."' value='".$l2."'>
                            <input type='hidden' name='a2_".$id_helice."' id='a2_".$id_helice."' value='".$a2."'>
                            <input type='hidden' name='b2_".$id_helice."' id='b2_".$id_helice."' value='".$b2."'>
                            <input type='hidden' name='a3_".$id_helice."' id='a3_".$id_helice."' value='".$a3."'>
                            <input type='hidden' name='b3_".$id_helice."' id='b3_".$id_helice."' value='".$b3."'>
                            ";

    // On remplit aussi un tableau pour la gestion des hélice en PHP (Remplissage du formulaire au chargement de la page)
	$helice_array[$helice['id']] = array('num' => $num,
                                            'diametre' => $diametre,
                                            'pas' => $pas,
                                            'l1' => $l1,
                                            'a1' => $a1,
                                            'b1' => $b1,
                                            'l2' => $l2,
                                            'a2' => $a2,
                                            'b2' => $b2,
                                            'a3' => $a3,
                                            'b3' => $b3,
                                            'fabricant' => $fabricant,                                            
                                            'obs' => $obs
                                            );
} 


// DATA CODE QUALITE
$sql_code_qual = "SELECT DISTINCT id_data_qualite, init_qualite_data, nom_qualite_data 
                FROM ".TABLE_DATA_QUALITE." 
                WHERE (id_eq_type=0 OR id_eq_type=11) 
                ORDER BY id_eq_type DESC, init_qualite_data";
$code_qual_query = tep_db_query($sql_link,$sql_code_qual);
while ($code_qual = tep_db_fetch_array($code_qual_query))
{				
	$code_qual_array[$code_qual['id_data_qualite']] = array('init_qualite_data' => htmlaccent(html_entity_decode($code_qual['init_qualite_data'] ?? $default_string)),
                                                            'nom_qualite_data' => htmlaccent(html_entity_decode($code_qual['nom_qualite_data'] ?? $default_string))
                                                            );
} 


//---------------------------------------------------------------
// En fonction du choix de l'utilisateur plusieurs action peuvent être exécuter : ModifEnCours, Delete, Save Data

if(isset($_GET['ref'])){$ref_id = mysqli_real_escape_string($sql_link,trim(addslashes($_GET['ref'])));$modif=true;}
//if($modif && isset($_GET['del'])){require(DIR_WS_SUPPRIMER . 'suppr_jge.php');}
//if(isset($_POST['button_jge'])){require(DIR_WS_FORMULAIRE . 'ctrl_jge.php');}

//---------------------------------------------------------------
// Récupération des données jaugages si ce n'est pas une première saisie.

// initialisation des variables si nouveau jaugeage  
$nbb = 1;
$id_jge = 0;        
$id_station = 0;
$code_station = '';
$nom_station = '';

$date_jge = $today;
$heure_jge = $current_time;
$date_heure_jge = $today.' '.$current_time;

$x_gps = '';
$y_gps = '';
        
$dist_site = '';
$id_site = 0;
$id_sitejge = 0; //Info emplacement est dans une table séparé, attendre la mise à jour des bases        

$id_methode = 0;
$id_typejge = 0;

$depouil_hmoy = '';
$depouil_q = '';
$depouil_sect = '';
$depouil_vmoy = '';
$depouil_vsurf = '';
$depouil_rh = '';
$depouil_profmoy = '';
$depouil_nbvert = 0;

$code_qualite = 0;       

$obs_jge = '';
$fichier_lien = '';


if($modif)
{	
	// requête sql pour récupérer les données articles
	$sql_jge = "SELECT DISTINCT jge.id, jge.id_station, s.code_station, s.nom_station, jge.datetime, jge.x_gps, jge.y_gps,
                                jge.nb_bras, jge.dist_site, jge.id_site, jge.id_methode, jge.id_typejge,
                                jge.depouil_hmoy, jge.depouil_q, jge.depouil_sect, jge.depouil_vmoy, jge.depouil_vsurf, 
                                jge.depouil_rh, jge.depouil_profmoy, jge.depouil_nbvert, 
                                jge.code_qualite, jge.obs, jge.fichier, jge.agents
				FROM ".TABLE_DATA_JGE." jge
				JOIN ".TABLE_STATION." s ON jge.id_station=s.id_station
				JOIN ".TABLE_REGION." r ON s.id_region=r.id_region	 
				WHERE jge.id=".$ref_id;
	
	$jge_query = tep_db_query($sql_link,$sql_jge);
	$jge = tep_db_fetch_array($jge_query);

	if(isset($jge))
	{	
		$id_jge = html_entity_decode($jge['id'] ?? $default_string);
        
        $id_station = html_entity_decode($jge['id_station'] ?? $default_string);
        $code_station = htmlaccent(html_entity_decode($jge['code_station'] ?? $default_string));
        $nom_station = htmlaccent(html_entity_decode($jge['nom_station'] ?? $default_string));
		
        $tab_date_heure_jge = explode(" ",$jge['datetime']);
        $date_jge = dateus_fr($tab_date_heure_jge[0]);
        $heure_jge = $tab_date_heure_jge[1];
        $date_heure_jge = $date_jge.' '.$heure_jge;
        
        $x_gps = htmlaccent(html_entity_decode($jge['x_gps'] ?? $default_string));
        $y_gps = htmlaccent(html_entity_decode($jge['y_gps'] ?? $default_string));
                
        $dist_site = floatval($jge['dist_site']);
        $id_site = htmlaccent(html_entity_decode($jge['id_site'] ?? $default_string));
        $id_sitejge = 0; //Info emplacement est dans une table séparé, attendre la mise à jour des bases        

        $id_methode = html_entity_decode($jge['id_methode'] ?? $default_string);
        $id_typejge = html_entity_decode($jge['id_typejge'] ?? $default_string);

        $depouil_hmoy = round(floatval($jge['depouil_hmoy']),3);
        $depouil_q = round(floatval($jge['depouil_q']),3);
        $depouil_sect = round(floatval($jge['depouil_hmoy']),3);
        $depouil_vmoy = round(floatval($jge['depouil_q']),3);
        $depouil_vsurf = round(floatval($jge['depouil_hmoy']),3);
        $depouil_rh = round(floatval($jge['depouil_q']),3);
        $depouil_profmoy = round(floatval($jge['depouil_hmoy']),3);
        $depouil_nbvert = $jge['depouil_q'];

        $code_qualite = $jge['code_qualite'];       
        
        $obs_jge = htmlaccent(html_entity_decode($jge['obs'] ?? $default_string));
        $fichier_lien = htmlaccent(html_entity_decode($jge['fichier'] ?? $default_string));
        $agents_text = htmlaccent(html_entity_decode($jge['agents'] ?? $default_string));

        // Récupération des infos sur les bras composant le jaugeage    

        $nb_bras = html_entity_decode($jge['nb_bras'] ?? $default_string); // valeur contenu dans la table JGE
        
        
        // On vérifie dans la table JGE_BRAS si il n'y a pas de bras oublié, surtout effectif pour les anciennes données
        // Et on récupère toutes les informations Bras
                
        $sql_jge_bras = "SELECT DISTINCT b.id, b.id_moulinet, b.id_helice, b.id_saumon, b.perche_diam, 
                                        b.berge_depart, b.heure_first, b.h_ech_first, b.heure_end, b.h_ech_end,
                                        b.fond_text,
                                        b.depouil_hmoy, b.depouil_nbvert, b.depouil_profmoy, b.depouil_distmax, 
                                        b.depouil_vmoy, b.depouil_vsurf, b.depouil_surfmouil, b.depouil_perimouil,
                                        b.depouil_rh, b.depouil_q, b.obs
                        FROM ".TABLE_DATA_JGE_BRAS." b
                        WHERE id_jge=".$id_jge;
        
                        
        $jge_bras_query = tep_db_query($sql_link,$sql_jge_bras);
        while ($jge_bras = tep_db_fetch_array($jge_bras_query))
        {
            $id_bras =  html_entity_decode($jge_bras['id'] ?? $default_string);

            $id_moulinet =  html_entity_decode($jge_bras['id_moulinet'] ?? $default_string); 
            $id_helice =  html_entity_decode($jge_bras['id_helice'] ?? $default_string); 
            $id_saumon =  html_entity_decode($jge_bras['id_saumon'] ?? $default_string); 
            $perche_diam =  round(floatval($jge_bras['perche_diam']),3);
 
            $berge_depart =  html_entity_decode($jge_bras['berge_depart'] ?? $default_string);

            $heure_first =  html_entity_decode($jge_bras['heure_first'] ?? $default_string);
            $h_ech_first =  round(floatval($jge_bras['h_ech_first']),3);
            $heure_end =  html_entity_decode($jge_bras['heure_end'] ?? $default_string);
            $h_ech_end =  round(floatval($jge_bras['h_ech_end']),3);

            $fond_text = htmlaccent(html_entity_decode($jge_bras['fond_text'] ?? $default_string));

            //$id_fondlit = html_entity_decode($jge['id_fondlit'] ?? $default_string); // N'existe pas dans la base pour le moment
            $id_fondlit = 0;    

            $depouil_bras_hmoy =  round(floatval($jge_bras['depouil_hmoy']),3);
            $depouil_bras_nbvert =  round(floatval($jge_bras['depouil_nbvert']),3);
            $depouil_bras_profmoy =  round(floatval($jge_bras['depouil_profmoy']),3);
            $depouil_bras_distmax =  round(floatval($jge_bras['depouil_distmax']),3);
            $depouil_bras_vmoy =  round(floatval($jge_bras['depouil_vmoy']),3);
            $depouil_bras_vsurf =  round(floatval($jge_bras['depouil_vsurf']),3);
            $depouil_bras_surfmouil =  round(floatval($jge_bras['depouil_surfmouil']),3);
            $depouil_bras_perimouil =  round(floatval($jge_bras['depouil_perimouil']),3);
            $depouil_bras_rh =  round(floatval($jge_bras['depouil_rh']),3);
            $depouil_bras_q =  round(floatval($jge_bras['depouil_q']),3);

            $bras_obs = htmlaccent(html_entity_decode($jge_bras['obs'] ?? $default_string));

            $jge_bras_array[$nbb] = array('id_bras' => $id_bras,
                                        'id_moulinet' => $id_moulinet,
                                        'id_helice' => $id_helice,
                                        'id_saumon' => $id_saumon,
                                        'perche_diam' => $perche_diam,
                                        'berge_depart' => $berge_depart,
                                        'heure_first' => $heure_first,
                                        'h_ech_first' => $h_ech_first,
                                        'heure_end' => $heure_end,
                                        'h_ech_end' => $h_ech_end,
                                        'fond_text' => $fond_text,
                                        'depouil_bras_hmoy' => $depouil_bras_hmoy,
                                        'depouil_bras_nbvert' => $depouil_bras_nbvert,
                                        'depouil_bras_profmoy' => $depouil_bras_profmoy,
                                        'depouil_bras_distmax' => $depouil_bras_distmax,
                                        'depouil_bras_vmoy' => $depouil_bras_vmoy,
                                        'depouil_bras_vsurf' => $depouil_bras_vsurf,
                                        'depouil_bras_surfmouil' => $depouil_bras_surfmouil,
                                        'depouil_bras_perimouil' => $depouil_bras_perimouil,
                                        'depouil_bras_rh' => $depouil_bras_rh,
                                        'depouil_bras_q' => $depouil_bras_q,
                                        'bras_obs' => $bras_obs);

            $nbb++;
        }
        if(isset($jge_bras_array)){$nb_bras_tab = sizeof($jge_bras_array);}
        
    }
    else
    {
        $error_jge = true;
    }
}


//---------------------------------------------------------------
// Edition HTML

require(DIR_WS_STRUCTURE . 'header_web.php');


echo "<body>";

    echo "<div id='contenu_info' style='display:none;'></div>";

    require(DIR_WS_JAUGEAGE . 'block_jge_affiche_etl.php'); // Block pour affichage d'une fiche RA en premier plan
    require(DIR_WS_JAUGEAGE . 'block_jge_obs.php'); // Block pour affichage d'un observation sur un pt de JGE
    require(DIR_WS_STRUCTURE . 'block_wait.php'); // Block d'attente pendant les interrogations au serveur

    require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
    include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

    echo "<div id='contour_general'>";

        if(tep_not_null($message_info)){echo "<div id='contenu_info'>".$message_info."</div>";}
        
        echo "<div id='contenu_centre'>";
            
            echo "<div id='contenu_box2'>";
            
                if(!$error_jge)
                {
                    echo "<form id='formJGE'>";
                    
                        echo $data_helice_hidden; // Champs caché avec les infos sur les hélices

                        echo "<input type='hidden' value='".$id_user."' name='id_user_agent'>";
                        echo "<input type='hidden' value='".$territoire_id."' name='territoire_id'>";

                        echo "<input type='hidden' value='".$id_jge."' name='id_jge'>";

                        echo "<h1>";

                            if($modif)
                            {                        
                                echo "<span>".htmlaccent('Jaugeage')."</span>";
                                echo "<span style='color:#000;'>".htmlaccent(' : '.$code_station.' - '.$nom_station)."</span>";
                            }
                            else{echo "<span>".htmlaccent('Nouveau Jaugeage')."</span>";}

                            echo "<input type='submit' class='button' name='save_JGE' id='save_JGE' style='float:right;' value='Enregistrer'  onclick='saveJGE(event);' />";
                        
                        echo "</h1>";
                        
                        // Colonne de Gauche permettant d'avoir accès aux infos général du JGE              
                        
                        echo "<div id='cadre_graph' style='float:left;width:14%;height:80vh;overflow-y: auto;'>\n";

                            echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 5px 10px;padding-right:0;'>\n";

                                // Hauteur
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;margin-bottom:5px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #930000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Hauteur moy. [cm]');
                                    echo "</p>";
                                
                                    $value = '';
                                    if($modif){$value = $depouil_hmoy;}
                                    echo "<input type='text' style='float:right;width:40px;border:0px;background-color:#FFFFDD;' id='jge_hmoy' name='jge_hmoy' value='".$value."' >\n";

                                echo "</div>";

                                // Débit
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #930000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Débit [m<sup>3</sup>/s]');
                                    echo "</p>";

                                    $value = '';
                                    if($modif){$value = $depouil_q;}
                                    echo "<input type='text' style='float:right;width:40px;border:0px;background-color:#FFFFDD;' id='jge_q' name='jge_q' value='".$value."' >\n";

                                echo "</div>";

                                echo "<div id='boite_small' style='width:95%;text-align:center;margin-right:0px;'>\n";

                                    echo "<p style='float:left;width:100%;font-weight: bold;color: #36802d;font-size: 11px;margin:5px 0;'>";

                                        echo "<span style='cursor:pointer;' 
                                                    title='".htmlaccent('Afficher la courbe d\'étalonnage')."' 
                                                    onMouseOver='this.style.color=\"red\";'
                                                    onMouseOut='this.style.color=\"\";'
                                                    onClick='afficheETL();'
                                                >";
                                            echo htmlaccent('- Voir la courbe d\'étalonnage -');
                                        echo "</span>";

                                    echo "</p>";

                                echo "</div>";



                            echo "</div>";  

                            echo "<div id='boxpopup' class='select-top' style='width:92%;margin-top:5px;padding: 5px 10px;padding-right:0;'>\n";

                                echo "<div id='boite_small' style='width:95%;margin-right:0px;margin-bottom:5px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Date JGE');
                                    echo "</p>";

                                    $value = $today;
                                    if($modif){$value = $date_jge;}
                                    echo "<input type='text' style='float:right;width:65px;' id='date_jge' name='date_jge' value='".$value."' >\n";

                                echo "</div>";  

                                echo "<div id='boite_small' style='width:95%;margin-right:0px;margin-bottom:5px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Heure JGE');
                                    echo "</p>";

                                    $value = $current_time;
                                    if($modif){$value = $heure_jge;}
                                    echo "<input type='text' style='float:right;width:60px;' id='heure_jge' name='heure_jge' value='".$value."' >\n";

                                echo "</div>";

                                // Liste des stations hydrométriques
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;'>\n";
                                                        
                                    echo "<p style='float:left;width:100%;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;margin-right:10px;'>";
                                        echo htmlaccent('Station hydrométrique');
                                    echo "</p>";

                                    echo "<select name='select_station' id='select_station' style='float:left;width:100%;' >";// onchange='form_jge.submit();' >";
                                                    
                                        echo "<option value='0'>-</option>";
                                            
                                        $selected = '';									
                                        if(isset($station_array))
                                        {
                                            for($c=0;$c<sizeof($station_array);$c++)
                                            {
                                                if($station_array[$c]['id'] == $id_station){$selected="selected";}	
                                                else{$selected = '';}											
                                                echo "<option value='".$station_array[$c]['id']."' ".$selected." >".$station_array[$c]['code_station']." - ".$station_array[$c]['nom_station']."</option>";
                                            }
                                        }  
                                    
                                    echo "</select>";

                                echo "</div>";  

                                echo "<hr>\n";
                                
                                // Code qualité
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;'>\n";
                                                        
                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Code Qualité');
                                    echo "</p>";

                                    echo "<select name='select_code_qual' id='select_code_qual' style='float:right;width:90px;' >";// onchange='form_jge.submit();' >";
                                                    
                                        echo "<option value='0'>-</option>";
                                            
                                        $selected = '';									
                                        
                                        if(isset($code_qual_array))
                                        {
                                            foreach ($code_qual_array as $key => $value)
                                            {
                                                if($key == $code_qualite){$selected="selected";}	
                                                else{$selected = '';}											
                                                echo "<option value='".$key."' ".$selected." title='".$code_qual_array[$key]['nom_qualite_data']."'>".$code_qual_array[$key]['init_qualite_data']."</option>";
                                            }
                                        }
                                    
                                    echo "</select>";

                                echo "</div>";  

                            echo "</div>";

                            echo "<div id='boxpopup' class='select-top' style='width:92%;margin-top:5px;padding: 5px 10px;padding-right:0;'>\n";

                                // Distance du site (de la sonde ou de l'échelle Limnimétrique)
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;margin-bottom:5px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Distance Site [m]');
                                    echo "</p>";

                                    $value = '';
                                    if($modif){$value = $dist_site;}
                                    echo "<input type='text' style='float:right;width:40px;' id='dist_site' name='dist_site' value='".$value."' >\n";

                                echo "</div>";

                                // Site (Amont, Aval, Pont, Station)
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;margin-bottom:5px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Site - Précision');
                                    echo "</p>";

                                    echo "<select name='select_site_jge' id='select_site_jge' style='float:right;width:90px;' >";// onchange='form_jge.submit();' >";
                                                    
                                        echo "<option value='0'>-</option>";
                                            
                                        $selected = '';									
                                        if(isset($data_jge_site_array))
                                        {
                                            if(isset($data_jge_site_array))
                                            {
                                                foreach ($data_jge_site_array as $key => $value)
                                                {
                                                    if($key == $id_sitejge){$selected="selected";}	
                                                    else{$selected = '';}											
                                                    echo "<option value='".$key."' ".$selected." title='".$data_jge_site_array[$key]['obs']."'>".$data_jge_site_array[$key]['titre']."</option>";
                                                }
                                            }
                                        }  
                                    
                                    echo "</select>";

                                echo "</div>";

                                // GPS - X
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;margin-bottom:5px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Coord. X (GPS)');
                                    echo "</p>";

                                    $value = '';
                                    if($modif){$value = $x_gps;}
                                    echo "<input type='text' style='float:right;width:80px;' id='x_gps' name='x_gps' value='".$value."' >\n";

                                echo "</div>";

                                // GPS - Y
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;margin-bottom:5px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Coord. Y (GPS)');
                                    echo "</p>";

                                    $value = '';
                                    if($modif){$value = $y_gps;}
                                    echo "<input type='text' style='float:right;width:80px;' id='y_gps' name='y_gps' value='".$value."' >\n";

                                echo "</div>";

                            echo "</div>";

                            echo "<div id='boxpopup' class='select-top' style='width:92%;margin-top:5px;padding: 5px 10px;padding-right:0;'>\n";
                            
                                // Type de prise de mesure
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;margin-bottom:5px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Prise de mesure');
                                    echo "</p>";

                                    echo "<select name='select_type_jge' id='select_type_jge' style='float:right;width:90px;' >";// onchange='form_jge.submit();' >";
                                                    
                                        echo "<option value='0'>-</option>";
                                            
                                        $selected = '';									
                                        if(isset($data_jge_type_array))
                                        {
                                            if(isset($data_jge_type_array))
                                            {
                                                foreach ($data_jge_type_array as $key => $value)
                                                {
                                                    if($key == $id_typejge){$selected="selected";}	
                                                    else{$selected = '';}											
                                                    echo "<option value='".$key."' ".$selected." title='".$data_jge_type_array[$key]['obs']."'>".$data_jge_type_array[$key]['titre']."</option>";
                                                }
                                            }
                                        }
                
                                    echo "</select>";

                                echo "</div>";

                                // Type de prise de mesure
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;'>\n";

                                    echo "<p style='float:left;ont-weight: bold;color: #000;font-size: 11px;margin-top:5px;'>";
                                        echo htmlaccent('Méthode');
                                    echo "</p>";

                                    echo "<select name='select_methode_jge' id='select_methode_jge' style='float:right;width:90px;' >";// onchange='form_jge.submit();' >";
                                                
                                        echo "<option value='0'>-</option>";
                                            
                                        $selected = '';									
                                        if(isset($data_jge_methode_array))
                                        {
                                            foreach ($data_jge_methode_array as $key => $value)
                                            {
                                                if($key == $id_methode){$selected="selected";}	
                                                else{$selected = '';}											
                                                echo "<option value='".$key."' ".$selected." title='".$data_jge_methode_array[$key]['obs']."'>".$data_jge_methode_array[$key]['titre']."</option>";
                                            }
                                        }  
                                    
                                    echo "</select>";

                                echo "</div>";

                            echo "</div>";

                            echo "<div id='boxpopup' class='select-top' style='width:92%;margin-top:5px;padding: 5px 10px;padding-right:0;'>\n";
                            
                                // Observation
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;'>\n";

                                    echo "<p style='float:left;font-weight: bold;color: #000;font-size: 11px;margin-top:0px;'>";
                                        echo htmlaccent('Observations');
                                    echo "</p>";

                                    $value = '';
                                    if($modif){$value = $obs_jge;}
                                    
                                    echo "<textarea id='obs' name='obs' style='width:95%;height:80px;'>".$value."</textarea>\n";

                                echo "</div>";

                            echo "</div>";

                            // Agents
                            echo "<div id='boxpopup' class='select-top' style='width:92%;margin-top:5px;padding: 5px 10px;padding-right:0;'>\n";
                            
                                // Lien fichier
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;'>\n";

                                    echo "<p style='float:left;width:100%;font-weight: bold;color: #000;font-size: 11px;margin-top:0px;'>";
                                        echo htmlaccent('Agents ayant participé');
                                    echo "</p>";

                                    if (!isset($agents_text) || is_null($agents_text)) 
                                    {
                                        $agents_text = '';
                                    }
        
                                    if(isset($agent_array))
                                    {
                                        foreach($agent_array as $key => $value)
                                        {
                                            $checked = (strpos($agents_text, $value) !== false) ? 'checked' : '';
        
                                            echo "
                                                <div style='float:left;'>\n
                                                    <input class='input_texte' style='width:25px;padding:0;' name='check_agent_".$key."' id='check_agent_".$key."' type='checkbox' data-value='".$value."' onchange='updateSelectedAgents();' ".$checked.">	
                                                    <span style='float:left;margin-right:5px;font-size:10px;padding-top:3px;'>".$value."</span>
                                                </div>\n
                                                ";
                                        }
                                    }                                    
                                    echo "<input type='text' style='float:right;width:95%;' id='agents_text' name='agents_text' value='".$agents_text."' >\n";

                                echo "</div>";
                            echo "</div>";


                            echo "<div id='boxpopup' class='select-top' style='width:92%;margin-top:5px;padding: 5px 10px;padding-right:0;'>\n";
                            
                                // Lien fichier
                                echo "<div id='boite_small' style='width:95%;margin-right:0px;'>\n";

                                    echo "<p style='float:left;width:100%;font-weight: bold;color: #000;font-size: 11px;margin-top:0px;'>";
                                        echo htmlaccent('Lien fichier');
                                    echo "</p>";

                                    $value = '';
                                    if($modif){$value = $fichier_lien;}
                                    
                                    echo "<input type='text' style='float:right;width:95%;' id='file_link' name='file_link' value='".$value."' >\n";

                                echo "</div>";

                            echo "</div>";

                        echo "</div>";
                        

                        // Bloc qui contient les onglets - Chaque onglet correspond à un bras de JGE  

                        echo "<div style='float:left;width:85%;margin:0;margin-left:0.5%;'>\n";
                        
                            // Préparation des onglets
                                            
                            echo "<div id='onglet'>";

                                echo "<ul id='menu_onglet'>";

                                    echo "<input type='hidden' value='".$nb_bras_tab."' id='nb_bras' name='nb_bras'>";
                                
                                    if($nb_bras_tab > 0) // En fonction du nombre de bras jaugés
                                    {                                   
                                        for($nbb=1;$nbb<=$nb_bras_tab;$nbb++)
                                        {
                                            $class = '';
                                            if($nbb == 1 ){$class = 'actif';}

                                            echo "<li onClick=\"javascript:ChangeOnglet_2(".$nbb.", ".($nb_bras_tab+1).", 'onglet-', 'contenu-');\" id='onglet-".$nbb."' class='".$class."' >".htmlaccent('JGE - Bras ').$nbb."</li>\n";
                                        }
                                        
                                        echo "<li onClick=\"javascript:ChangeOnglet_2(".$nbb.", ".($nb_bras_tab+1).", 'onglet-', 'contenu-');\" id='onglet-".$nbb."' 
                                                    style='width:20px;font-size:22px;font-weight:bold;padding-top:2px;padding-bottom:8px;' title='".htmlaccent('Nouveau Bras')."'>".htmlaccent('+')."</li>\n";                            					
                                    
                                    }
                                    else
                                    {
                                        $nbb=1;
                                        echo "<li onClick=\"javascript:ChangeOnglet_2(1,1, 'onglet-', 'contenu-');\" id='onglet-1' 
                                                    style='width:20px;font-size:22px;font-weight:bold;padding-top:2px;padding-bottom:8px;' title='".htmlaccent('Nouveau Bras')."'>".htmlaccent('+')."</li>\n";
                                    }                        
                                                    
                                echo "</ul>";

                                if($nb_bras_tab > 0)  // En fonction du nombre de bras jaugés
                                {                        
                                    for($nbb=1;$nbb<=$nb_bras_tab;$nbb++)
                                    {
                                        $display = 'display:none;';
                                        if($nbb == 1){$display = 'display:block;';}

                                        echo "<div id='contenu-".($nbb)."' class='contenu' style='".$display."backgound:none;width:100%;'>";
                                            require(DIR_WS_JAUGEAGE . 'form_jge_bras.php');
                                        echo "</div>";
                                    }  

                                    $display = 'display:none;';
                                    echo "<div id='contenu-".$nbb."' class='contenu' style='".$display."backgound:none;width:100%;'>";
                                        require(DIR_WS_JAUGEAGE . 'form_jge_bras.php');
                                    echo "</div>";  
                                }
                                else
                                {
                                    $display = 'display:block;';
                                    echo "<div id='contenu-1' class='contenu' style='".$display."backgound:none;width:100%;'>";
                                        require(DIR_WS_JAUGEAGE . 'form_jge_bras.php');
                                    echo "</div>";  
                                }
                            
                            echo "</div>";


                        echo "</div>";
                        
                    echo "</form>\n";
                }
				else
				{
					echo "<h1>";
						echo "<span>".htmlaccent('Jaugeage')."</span>";
					echo "</h1>";

					echo "<div id='boxpopup' style='margin-left: 1%;'>\n";
						echo "<p class='alert'>".htmlaccent('Aucun - Jaugeage - n\'a été trouvé')."</p>";
					echo "</div>";
				}
        
            echo "<hr>";
            echo "</div>";
            
        
        echo "<hr>";
        echo "</div>";
            
        
    echo "<hr>";
    echo "</div>";
	
	require('include/application_bottom.php'); 
	
echo "</body>";

echo "</html>";

?>	

<script>

	// Initialisation des variables
	var boxWait = document.getElementById('box_wait'); // Attente lors des opérations, cet élément occupe l'ensemble de la page
	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info

    var boxAfficheEtl = document.getElementById('box_jge_affiche_etl'); // popup d'affichage du Graph ETL
    var boxGraphEtl = document.getElementById('plot_etl'); // popup d'affichage du Graph ETL
    var boxEtlGraphWait = document.getElementById('wait_graph');
    var infoEtl = document.getElementById('info_etl');
        
    var idStation = <?php echo $id_station; ?> // Id Station
    var jgeHmoy = document.getElementById('jge_hmoy'); // Hauteur JGE
    var jgeQ = document.getElementById('jge_q'); // Q (Débit) JGE
    var jgeDate = document.getElementById('date_jge'); // Date JGE
    var jgeHeure = document.getElementById('heure_jge'); // Heure JGE

    function saveJGE(event)
	{        
		boxWait.style.display = 'block';

		event.preventDefault(); // Empêche la soumission par défaut du formulaire

		var form = document.getElementById('formJGE'); // Récupérer l'élément du formulaire		
		var formData = new FormData(form); // Créer un objet FormData à partir du formulaire
        
		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/jaugeage/process_jge_save.php", true);

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				erreur = jsonResponse['erreur'];
				id_station = jsonResponse['id_station'];
				id_jge = jsonResponse['id_jge'];
				msg_info = jsonResponse['msg_info'];

                
				if(!erreur)
				{
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

                     // Temporisation de 2 secondes avant de rediriger
                     /*
                     setTimeout(function(){
                        // Rediriger vers modif_jge.php avec le paramètre ref=id
                        window.location.href = 'modif_jge.php?ref=' + id_jge;
                    }, 1000);
                    */
                    
				}
				else
				{
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
				}

				boxWait.style.display = 'none';
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(formData);

	}


    function afficheETL()
    {
        contenuInfo.style.display = 'none';
        
        if(!isValidDate(jgeDate.value))
        {
            contenuInfo.innerHTML = "La date du Jaugeage (Date JGE) n'est pas au bon format (jj-mm-aaaa)";
            contenuInfo.style.border = '4px solid #930000'; 
            contenuInfo.style.display = 'block';
            return;
        }
        
        jgeHmoyValue = parseFloat(jgeHmoy.value);
        jgeQValue = parseFloat(jgeQ.value);
        if(isNaN(jgeHmoyValue) || isNaN(jgeQValue)) 
        {
            contenuInfo.innerText = "Les valeurs de Hauteur moy. et de Débit doivent être des nombres"
            contenuInfo.style.border = '4px solid #930000'; 
            contenuInfo.style.display = 'block';
            return;
        }

        boxAfficheEtl.style.display = 'block';
        boxGraphEtl.style.display = 'none';
        boxEtlGraphWait.style.display = 'block';

        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer
        var dataToSend = {
            idStation: idStation,
            jgeHmoy: jgeHmoy.value,
            jgeQ: jgeQ.value,
            jgeDate: jgeDate.value,
            jgeHeure: jgeHeure.value
        };

        // Convertir l'objet en JSON
        var jsonDataGraph = JSON.stringify(dataToSend);

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/jaugeage/process_jge_etlgraph.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                boxGraphEtl.style.display = 'block';
                boxEtlGraphWait.style.display = 'none';
                
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                // Accéder aux données récupéré coté serveur

                edit_graph = jsonResponse['edit_graph'];
                text_info = jsonResponse['js_text']; // Affichage du texte d'information

                infoEtl.innerHTML = text_info;	

                // Affichage du graph si des données existes
                if(edit_graph)
                {   
                    eval(jsonResponse['js_graph']); 
                }
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataGraph);
    }


    // Fonctions permettant de gérer la saisie des agents présents
	function updateSelectedAgents() 
    {
        // Récupère toutes les cases à cocher correspondant au sélecteur donné
        var checkboxes = Array.from(document.querySelectorAll('input[type="checkbox"][name^="check_agent_"]'));

        // Récupère les valeurs des cases à cocher sélectionnées
        var selectedValues = checkboxes
            .filter(function(checkbox) {
                return checkbox.checked;
            })
            .map(function(checkbox) {
                return checkbox.getAttribute('data-value').trim();
            });

        // Récupère et filtre le texte manuel, en excluant les doublons et les valeurs déjà cochées
        var currentText = document.getElementById('agents_text').value;
        var manualText = currentText
            .split(' / ')
            .map(function(value) {
                return value.trim();
            })
            .filter(function(value) {
                return value !== '' &&
                    !selectedValues.includes(value) &&
                    !checkboxes.some(function(chk) {
                        return chk.getAttribute('data-value').trim() === value;
                    });
            });

        // Combine le texte manuel filtré et les valeurs cochées, puis met à jour le champ d'entrée
        var combinedText = manualText.concat(selectedValues).join(' / ');
        document.getElementById('agents_text').value = combinedText;
    }


    // Fonction pour valider une date réelle
    function isValidDate(dateString) 
    {
        // Vérifier le format avec une regex
        const dateRegex = /^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[0-2])-(\d{4})$/;
        if (!dateRegex.test(dateString)) 
        {
            return false; // Format invalide
        }

        // Découper la date
        const [day, month, year] = dateString.split("-").map(Number);

        // Créer une date JavaScript et vérifier sa validité
        const date = new Date(year, month - 1, day); // Mois commence à 0 en JS
        return (
            date.getFullYear() === year &&
            date.getMonth() === month - 1 &&
            date.getDate() === day
        );
    }

    // Fonction pour convertir une date (format valide) en objet Date
    function parseDate(dateString) 
    {
        [day, month, year] = dateString.split("-").map(Number);
        return new Date(year, month - 1, day);
    }

</script>