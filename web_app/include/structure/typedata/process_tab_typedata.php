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

// Décoder les données JSON en un tableau associatif PHP
$dataInfo = json_decode($jsonDataInfo, true);

// Accéder aux données du tableau récupérer
$id_typedata = $dataInfo['idTypeData'];

$where_typedata = '';
if($id_typedata > 0)
{$where_typedata = 'WHERE id_eq_type_data='.$id_typedata;}


// Initialisation Variables
$tab_typedata = true;
$message_info = '';

// Extraction données de la BDD

// Requête sur TYPE DE MESURE (Hydrométrie, Pluviométrie, Piézométrie, ...)
$sql_eq_type = "SELECT DISTINCT id_eq_type, nom_eq_type FROM ".TABLE_EQ_TYPE." 
                WHERE active_eq_type=1
                ORDER BY order_eq_type ASC";
$eq_type_query = tep_db_query($sql_link,$sql_eq_type);
while ($eq_type = tep_db_fetch_array($eq_type_query))
{				
	$eq_type_array[$eq_type['id_eq_type']] = htmlaccent(html_entity_decode($eq_type['nom_eq_type'] ?? $default_string));
} 

// Requête sur DATA TYPE AXE 
$sql_data_type_axe = "SELECT DISTINCT id, axe, unite FROM ".TABLE_DATA_TYPE_AXE." ORDER BY LOWER(axe) ASC" ;
$data_type_axe_query = tep_db_query($sql_link,$sql_data_type_axe);
while ($data_type_axe = tep_db_fetch_array($data_type_axe_query))
{				
	$axe_nom = htmlaccent(html_entity_decode($data_type_axe['axe'] ?? $default_string));
	$unite = htmlaccent(html_entity_decode($data_type_axe['unite'] ?? $default_string));
	
	$data_type_axe_array[$data_type_axe['id']] = array('axe' => $axe_nom,
														'unite' => $unite
														);
} 

// Requête sur Chroniques de données
$sql_chronique = "SELECT DISTINCT id_data_type, init_type_data, nom_type_data, id_eq_type_data, axe_data, unite, 
								to_periode, id_chon_periode, traitement, type_graph
				FROM ".TABLE_TYPE_DATA." td
                ".$where_typedata."
				ORDER BY LOWER(td.init_type_data) ASC";
$chronique_query = tep_db_query($sql_link,$sql_chronique);
while($chronique_data = tep_db_fetch_array($chronique_query)) 
{
	$init = htmlaccent(html_entity_decode($chronique_data['init_type_data'] ?? $default_string));
	$nom_chron = htmlaccent(html_entity_decode($chronique_data['nom_type_data'] ?? $default_string));
	$id_eq_type = $chronique_data['id_eq_type_data'];
	$axe_id = $chronique_data['axe_data'];
	$unite = $chronique_data['unite'];
	$to_periode = $chronique_data['to_periode'];
	$id_chon_periode = $chronique_data['id_chon_periode'];
	$traitement = $chronique_data['traitement'];
	$typegraph = $chronique_data['type_graph'];

    $del_chron = true;
    $sql_verif_meta = "SELECT COUNT(*) as nb_meta FROM ".TABLE_DATA_META."
                        WHERE id_typedata=".$chronique_data['id_data_type']."
                        LIMIT 1";
    $verif_meta_query = tep_db_query($sql_link,$sql_verif_meta);  
    $verif_meta = tep_db_fetch_array($verif_meta_query);
    if($verif_meta['nb_meta']>0){$del_chron = false;}                      


	$chronique_array[$chronique_data['id_data_type']] = array('init' => $init,
															'nom_chron' => $nom_chron,
															'id_eq_type' => $id_eq_type,
															'axe_id' => $axe_id,
															'unite' => $unite,
															'to_periode' => $to_periode,
															'id_chon_periode' => $id_chon_periode,
															'traitement' => $traitement,
															'typegraph' => $typegraph,
															'del_chron' => $del_chron,
															);
}



$periode_transf[1] = 'DAY';
$periode_transf[2] = 'MONTH';
$periode_transf[3] = 'YEAR';




// Construction des données
$row = 0;
$htmlcode = '';

$htmlcode .= "<table id='table_tri' cellspacing='0' style=''>";

    $htmlcode .= "<thead>";
        $htmlcode .= "<tr class='header-row' style='background-color: #eef3f8;'>";
            $htmlcode .= "<th style='width:90px;'>".htmlaccent('Acronyme')."</th>";				
            $htmlcode .= "<th style='width:280px;'>".htmlaccent('Intitulé')."</th>";
            $htmlcode .= "<th style='width:140px;'>".htmlaccent('Type de données')."</th>";			
            $htmlcode .= "<th style='width:140px;'>".htmlaccent('Axe')."</th>";		
            $htmlcode .= "<th style='width:80px;'>".htmlaccent('Unité')."</th>";	
            $htmlcode .= "<th style='width:100px;'>".htmlaccent('Traitement')."</th>";							
            $htmlcode .= "<th style='width:100px;'>".htmlaccent('Type Graph')."</th>";					
            $htmlcode .= "<th style='width:120px;'>".htmlaccent('Transf. Période')."</th>";
            $htmlcode .= "<th style='width:160px;'>".htmlaccent('Transf. Chronique')."</th>";
            $htmlcode .= "<th style='width:60px;text-align:center;'>&nbsp;</td>";
        $htmlcode .= "</tr>";
    $htmlcode .= "</thead>";	

    // Nouvelle Entrée
    $htmlcode .= "<tr><td colspan='10' style='color:#000;font-size:14px;font-weight:bold;'>".htmlaccent('Ajouter un type de Chronique')."</td></tr>\n";

    $htmlcode .= "<tr>";
    
        $htmlcode .= "<td><input type='text' style='width:60px;border:2px solid #609966;' name='chron_init_0' ></td>";
        $htmlcode .= "<td><input type='text' style='width:250px;border:2px solid #609966;' name='chron_nom_0' ></td>";
        
        $htmlcode .= "<td>";
            
            $htmlcode .= "<select name='chron_select_type_mesure_0' id='chron_select_type_mesure_0' style='width:120px;border:2px solid #609966;' >";
                            
                $htmlcode .= "<option value='0'>-</option>";

                $selected = '';		
                if(isset($eq_type_array))
                {
                    foreach($eq_type_array as $key => $value)
                    {																			
                        $htmlcode .= "<option value='".$key."' ".$selected." >".$value."</option>";
                    }
                }
                
            $htmlcode .= "</select>";
            
        $htmlcode .= "</td>";

        $htmlcode .= "<td>";
            
            $htmlcode .= "<select name='chron_select_axe_0' id='chron_select_axe_0' style='width:120px;border:2px solid #609966;' >";
                            
                $htmlcode .= "<option value='0'>-</option>";
                
                $selected = '';		
                if(isset($data_type_axe_array))
                {
                    foreach($data_type_axe_array as $key => $value)
                    {																			
                        $htmlcode .= "<option value='".$key."' ".$selected." >".$value['axe']."</option>";
                    }
                }
                
            $htmlcode .= "</select>";
            
        $htmlcode .= "</td>";	
        
        $htmlcode .= "<td><input type='text' id='chron_unite_0' name='chron_unite_0' style='width:50px;border:2px solid #609966;' ></td>";

        // Traitement de la données pour affichage : valeur (lecture directe ou cumul (pluie basculement))
        $htmlcode .= "<td>";
            
            $htmlcode .= "<select name='chron_select_traitement_0' id='chron_select_traitement_0' style='width:80px;border:2px solid #609966;' onchange=''>";
                            
                $selected = '';		
                $htmlcode .= "<option value='0' ".$selected." >".htmlaccent('valeur')."</option>";
                $htmlcode .= "<option value='1' ".$selected." >".htmlaccent('cumul')."</option>";
                
            $htmlcode .= "</select>";
            
        $htmlcode .= "</td>";

        // Type de graphique liée : Ligne, Bar, Points, ...
        $htmlcode .= "<td>";
            
            $htmlcode .= "<select name='chron_select_typegraph_0' id='chron_select_typegraph_0' style='width:80px;border:2px solid #609966;'>";
                            
                $selected = '';		
                $htmlcode .= "<option value='lines' ".$selected." >".htmlaccent('linéaire')."</option>";
                $htmlcode .= "<option value='bar' ".$selected." >".htmlaccent('à barres')."</option>";
                
            $htmlcode .= "</select>";
            
        $htmlcode .= "</td>";
            

        // Transformation période possible (DAY, MONTH, YEAR)
        $htmlcode .= "<td>";
            
            $htmlcode .= "<select name='chron_select_to_periode_0' id='chron_select_to_periode_0' style='width:100px;border:2px solid #609966;'>";
                            
                $htmlcode .= "<option value='0'>-</option>";
                
                $selected = '';		
                for($p=1;$p<=3;$p++)
                {
                    $htmlcode .= "<option value='".$p."' ".$selected." >by ".$periode_transf[$p]."</option>";
                }
                
            $htmlcode .= "</select>";
            
        $htmlcode .= "</td>";


        // Identification de la Chronique par transformation 
        $htmlcode .= "<td>";
        
            $htmlcode .= "<select name='chron_select_chron_periode_0' id='chron_select_chron_periode_0' style='width:180px;border:2px solid #609966;'>";
                            
                $htmlcode .= "<option value='0'>-</option>";
            
                $selected = '';		
                foreach($chronique_array as $key => $value)
                {
                    $htmlcode .= "<option value='".$key."' ".$selected." >".$value['init']." - ".$value['nom_chron']."</option>";
                }
                
            $htmlcode .= "</select>";
            
        $htmlcode .= "</td>";
        
        $htmlcode .= "<td>&nbsp;</td>";
        
    $htmlcode .= "<tr>";

    //ligne vide dans le tableau
    $htmlcode .= "<tr><td colspan='10' class='lignevide'>&nbsp;</td></tr>";

    if(isset($chronique_array))
    {
        
        // Affichage dans le formulaire
        foreach ($chronique_array as $id => $data) 
        {
            if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
            else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 

            $htmlcode .= "<tr ".$row_l." id='row_chron_".$id."'>";
                    
                // Acronyme Chronique
                $htmlcode .= "<td>";
                    $htmlcode .= "<input type='text' style='width:60px;' name='chron_init_".$id."' value='".$data['init']."'>\n";
                $htmlcode .= "</td>";
                
                // Nom Chronique
                $htmlcode .= "<td>";
                    $htmlcode .= "<input type='text' style='width:250px;' name='chron_nom_".$id."' value='".$data['nom_chron']."'>\n";
                $htmlcode .= "</td>";
                
                // Type de données liée Hydro, Plu, Piezo
                $htmlcode .= "<td>";
                
                    $htmlcode .= "<select name='chron_select_type_".$id."' id='chron_select_type_".$id."' style='width:120px;'>";
                                    
                        $htmlcode .= "<option value='0'>-</option>";
                        
                        $selected = '';		
                        if(isset($eq_type_array))
                        {
                            foreach($eq_type_array as $key => $value)
                            {
                                if($data['id_eq_type'] == $key){$selected="selected";}	
                                else{$selected = '';}											
                                $htmlcode .= "<option value='".$key."' ".$selected." >".$value."</option>";
                            }
                        }
                        
                    $htmlcode .= "</select>";
                    
                $htmlcode .= "</td>";

                // Axe lié
                $htmlcode .= "<td>";
                
                    $htmlcode .= "<select name='chron_select_axe_".$id."' id='chron_select_axe_".$id."'  style='width:120px;'>";
                                    
                        $htmlcode .= "<option value='0'>-</option>";
                    
                        $selected = '';		
                        if(isset($data_type_axe_array))
                        {
                            foreach($data_type_axe_array as $key => $value)
                            {
                                if($data['axe_id'] == $key){$selected="selected";}	
                                else{$selected = '';}											
                                $htmlcode .= "<option value='".$key."' ".$selected." >".$value['axe']."</option>";
                            }
                        }

                    $htmlcode .= "</select>";
                    
                $htmlcode .= "</td>";

                // Unite
                $htmlcode .= "<td>";
                    $htmlcode .= "<input type='text' style='width:50px;' name='chron_unite_".$id."' value='".$data['unite']."'>\n";
                $htmlcode .= "</td>"; 

                // Traitement de la données pour affichage : valeur (lecture directe ou cumul (pluie basculement))
                $htmlcode .= "<td>";
                        
                    $htmlcode .= "<select name='chron_select_traitement_".$id."' id='chron_select_traitement_".$id."' style='width:80px;'>";
                                    
                        $selected = '';		
                        if($data['traitement'] == 0){$selected="selected";}
                        $htmlcode .= "<option value='0' ".$selected." >".htmlaccent('valeur')."</option>";
                        $selected = '';		
                        if($data['traitement'] == 1){$selected="selected";}
                        $htmlcode .= "<option value='1' ".$selected." >".htmlaccent('cumul')."</option>";
                        
                    $htmlcode .= "</select>";
                    
                $htmlcode .= "</td>";

                $htmlcode .= "<td>";
                
                    $htmlcode .= "<select name='chron_select_typegraph_".$id."' id='chron_select_typegraph_".$id."' style='width:80px;'>";
                                    
                        $selected = '';		
                        if($data['typegraph'] == 'lines'){$selected="selected";}
                        $htmlcode .= "<option value='lines' ".$selected." >".htmlaccent('linéaire')."</option>";
                        $selected = '';		
                        if($data['typegraph'] == 'bar'){$selected="selected";}
                        $htmlcode .= "<option value='bar' ".$selected." >".htmlaccent('à barres')."</option>";
                        
                    $htmlcode .= "</select>";
                    
                $htmlcode .= "</td>";


                // Transformation période possible (DAY, MONTH, YEAR)
                $htmlcode .= "<td>";
                
                    $htmlcode .= "<select name='chron_select_to_periode_".$id."' id='chron_select_to_periode_".$id."' style='width:100px;'>";
                                    
                        $htmlcode .= "<option value='0'>-</option>";
                    
                        $selected = '';		
                        for($p=1;$p<=3;$p++)
                        {
                            if($data['to_periode'] == $p){$selected="selected";}	
                            else{$selected = '';}											
                            $htmlcode .= "<option value='".$p."' ".$selected." >by ".$periode_transf[$p]."</option>";
                        }
                        
                    $htmlcode .= "</select>";
                    
                $htmlcode .= "</td>";


                // Identification de la Chronique par transformation 
                $htmlcode .= "<td>";
                
                    $htmlcode .= "<select name='chron_select_chron_periode_".$id."' id='chron_select_chron_periode_".$id."' style='width:180px;'>";
                                    
                        $htmlcode .= "<option value='0'>-</option>";
                    
                        $selected = '';		
                        foreach($chronique_array as $key => $value)
                        {
                            if($data['id_chon_periode'] == $key){$selected="selected";}	
                            else{$selected = '';}											
                            $htmlcode .= "<option value='".$key."' ".$selected." >".$value['init']." - ".$value['nom_chron']."</option>";
                        }
                        
                    $htmlcode .= "</select>";
                    
                $htmlcode .= "</td>";
                
                // Supprimer
                $htmlcode .= "<td style='text-align:center;'>";
                    
                    if($data['del_chron'])
                    {
                        $htmlcode .= "<span class='del' title='".htmlaccent('Supprimer')."' onClick=\"delete_typedata('".$id."');\">";
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
        $tab_typedata = false;
        $message_info .= "Aucune donnée n'a été trouvée";
    }

$htmlcode .= "</table>";


// Remplissage du tableau de retour

$responseData = array(
    'tab_typedata' => $tab_typedata,
    'htmlcode' => $htmlcode,
    'message_info' => $message_info
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>