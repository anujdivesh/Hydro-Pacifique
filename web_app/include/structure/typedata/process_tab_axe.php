<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Processus permettant de supprimer une région géographique
Appelé depuis gestion_geo.php -> pform_geo_regiongeo.php
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
$jsonDataInfo = file_get_contents('php://input');

// Initialisation Variables
$tab_axedata = true;
$message_info = '';

// Extraction données de la BDD

// Requête sur DATA TYPE AXE 
$sql_data_type_axe = "SELECT DISTINCT id, axe, unite FROM ".TABLE_DATA_TYPE_AXE." ORDER BY LOWER(axe) ASC" ;
$data_type_axe_query = tep_db_query($sql_link,$sql_data_type_axe);
while ($data_type_axe = tep_db_fetch_array($data_type_axe_query))
{				
	$axe_nom = $data_type_axe['axe'];
	$unite = $data_type_axe['unite'];

    $del_axe = true;
    $sql_verif_chron = "SELECT COUNT(*) as nb_axe FROM ".TABLE_TYPE_DATA."
                        WHERE axe_data=".$data_type_axe['id']."
                        LIMIT 1";
    $verif_chron_query = tep_db_query($sql_link,$sql_verif_chron);  
    $verif_chron = tep_db_fetch_array($verif_chron_query);
    if($verif_chron['nb_axe']>0){$del_axe = false;}         
	
	$data_type_axe_array[$data_type_axe['id']] = array('axe' => $axe_nom,
														'unite' => $unite,
														'del_axe' => $del_axe
														);
} 





// Construction des données
$row = 0;
$htmlcode = '';

$htmlcode .= "<table id='table_tri' cellspacing='0' style=''>";

    $htmlcode .= "<thead>";
        $htmlcode .= "<tr class='header-row' style='background-color: #eef3f8;'>";
            $htmlcode .= "<th style='width:200px;'>".htmlaccent('Nom de l\'axe')."</th>";				
            $htmlcode .= "<th style='width:80px;'>".htmlaccent('Unité')."</th>";
            $htmlcode .= "<th style='width:60px;text-align:center;'>&nbsp;</td>";
        $htmlcode .= "</tr>";
    $htmlcode .= "</thead>";	

    // Nouvelle Entrée
    $htmlcode .= "<tr><td colspan='3' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter un axe')."</td></tr>\n";

    $htmlcode .= "<tr>";
    
        $htmlcode .= "<td><input type='text' name='axe_nom_0' style='width:180px;border:2px solid #609966;'></td>";
        $htmlcode .= "<td><input type='text' name='axe_unite_0' style='width:60px;border:2px solid #609966;'></td>";
        
        $htmlcode .= "<td>&nbsp;</td>";
        
    $htmlcode .= "<tr>";

    $htmlcode .= "<tr><td colspan='3' class='lignevide'>&nbsp;</td></tr>";

    if(isset($data_type_axe_array))
    {
        foreach ($data_type_axe_array as $id => $data)
        {
            $axe = $data['axe'];
            $unite = $data['unite'];
            
            if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
            else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 

            $htmlcode .= "<tr ".$row_l." id='row_axe_".$id."'>";
                    
                // Nom Axe
                $htmlcode .= "<td>";
                    $htmlcode .= "<input type='text' style='width:180px;' name='axe_nom_".$id."' value='".$axe."'>\n";
                $htmlcode .= "</td>";
                
                // Unite
                $htmlcode .= "<td>";
                    $htmlcode .= "<input type='text' style='width:60px;' name='axe_unite_".$id."' value='".$unite."'>\n";
                $htmlcode .= "</td>";
                
                // Supprimer
                // Supprimer
                $htmlcode .= "<td style='text-align:center;'>";
                        
                    if($data['del_axe'])
                    {
                        $htmlcode .= "<span class='del' title='".htmlaccent('Supprimer')."' onClick=\"delete_axe('".$id."');\">";
                            $htmlcode .= "X";
                        $htmlcode .= "</span>";
                    }
                    else{$htmlcode .= "<span>-</span>";}

                $htmlcode .= "</td>\n";
            
            $htmlcode .= "</tr>";					
        }
    }
    else
    {
        $tab_axedata = false;
        $message_info .= "Aucune donnée n'a été trouvée";
    }

$htmlcode .= "</table>";


// Remplissage du tableau de retour

$responseData = array(
    'tab_axedata' => $tab_axedata,
    'htmlcode' => $htmlcode,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>