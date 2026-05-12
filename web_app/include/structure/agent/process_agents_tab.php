<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Procédure pour afficher dans un tableau les RA en cours dans la page RA.
Processus asynchrone AJX coté serveur
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
$territoire_id = $dataInfo['territoire_id'];
$where_agents = $dataInfo['where_agents'];
//$order_ra = $dataInfo['order_ra'];

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// TABLE USER
$sql_user_list = "SELECT DISTINCT id, id_statut, login, nom, prenom FROM ".TABLE_USER;
$user_list_query = tep_db_query($sql_link,$sql_user_list);
while ($user_list = tep_db_fetch_array($user_list_query))
{
    $id = $user_list['id'];
    $id_statut = $user_list['id_statut'];
	$login = htmlaccent(html_entity_decode($user_list['login'] ?? $default_string));
	$nom = ucfirst(strtolower(htmlaccent(html_entity_decode($user_list['nom'] ?? $default_string))));
	$prenom = ucfirst(strtolower(htmlaccent(html_entity_decode($user_list['prenom'] ?? $default_string))));

	$user_list_array[$id] = array('id_statut' => $id_statut,
                                    'login' => $login,
                                    'nom' => $nom,
                                    'prenom' => $prenom
                                    );
}

// TABLE COMMUNE
$sql_commune = "SELECT DISTINCT c.id_commune, c.nom_commune 
				FROM ".TABLE_COMMUNE." c
				JOIN ".TABLE_REGION." r ON c.id_region=r.id_region
				WHERE r.id_territoire=".$territoire_id."
				ORDER BY c.nom_commune ASC";

$commune_query = tep_db_query($sql_link,$sql_commune);
while ($commune = tep_db_fetch_array($commune_query))
{
	$commune_array[$commune['id_commune']] = $commune['nom_commune'];
}

// -------------------------------------------------------------


// Initialisation Variables
$tab_html = '';
$hidden_html_agent = '';
$nb_agents = 0;
$nb_agents_service = 0;
$nb_agents_terrain = 0;

$row = 0;




// Requête d'accès aux RA
$sql_agents = "SELECT DISTINCT a.id, a.nom, a.nom_marital, a.prenom, a.raisonsociale, a.numinscription, a.fonction, a.adresse, a.lieudit, 
                a.bp, a.codepostal, a.id_commune, a.tel, a.mobile, a.fax, a.email, a.siteweb, a.type, a.terrain, a.niveau  
				FROM ".TABLE_AGENT." a ".$where_agents." 
				ORDER BY a.niveau DESC, a.terrain DESC, a.nom ASC";
        

$agents_query = tep_db_query($sql_link,$sql_agents);
if($agents_query)
{
    while($agents_tab = tep_db_fetch_array($agents_query))
    {        
        $nb_agents++;
        $id_agent =  $agents_tab['id'];

        // nettoyer_et_echapper() est une fonction php créer dans function/general.php permettent d'éviter les bugs du à des caractères spéciaux : ',",(,),...
        
        $nom =  nettoyer_et_echapper($agents_tab['nom']);
        $nom_marital =  nettoyer_et_echapper($agents_tab['nom_marital']);        
        $prenom =  nettoyer_et_echapper($agents_tab['prenom']);
        
        $raisonsociale =  nettoyer_et_echapper($agents_tab['raisonsociale']);
        $numinscription =  nettoyer_et_echapper($agents_tab['numinscription']);
        $fonction =  nettoyer_et_echapper($agents_tab['fonction']);
        $adresse =  nettoyer_et_echapper($agents_tab['adresse']);
        $lieudit =  nettoyer_et_echapper($agents_tab['lieudit']);
        $bp =  nettoyer_et_echapper($agents_tab['bp']);
        $codepostal =  nettoyer_et_echapper($agents_tab['codepostal']);
        
        $id_commune = $agents_tab['id_commune'];
        $nom_commune = '';
        if(isset($commune_array[$id_commune])){$nom_commune = $commune_array[$id_commune];}

        $tel =  nettoyer_et_echapper($agents_tab['tel']);
        $mobile =  nettoyer_et_echapper($agents_tab['mobile']);        
        $fax =  nettoyer_et_echapper($agents_tab['fax']);
        $email =  nettoyer_et_echapper($agents_tab['email']);
        $siteweb =  nettoyer_et_echapper($agents_tab['siteweb']);
        $fonction =  nettoyer_et_echapper($agents_tab['fonction']);
        $type =  nettoyer_et_echapper($agents_tab['type']);
        $terrain =  $agents_tab['terrain']; // 1 ou 0
        $niveau =  $agents_tab['niveau']; // 1 ou 0 - Agent du Service territorial

        // On saisie des champs cachés

        $hidden_html_agent .= "<input type='hidden' id='nom_".$id_agent."' value=\"".$nom."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='nom_marital_".$id_agent."' value=\"".$nom_marital."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='prenom_".$id_agent."' value=\"".$prenom."\" />\n";
        
        $hidden_html_agent .= "<input type='hidden' id='raisonsociale_".$id_agent."' value=\"".$raisonsociale."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='numinscription_".$id_agent."' value=\"".$numinscription."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='fonction_".$id_agent."' value=\"".$fonction."\" />\n";
        
        $hidden_html_agent .= "<input type='hidden' id='adresse_".$id_agent."' value=\"".$adresse."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='lieudit_".$id_agent."' value=\"".$lieudit."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='bp_".$id_agent."' value=\"".$bp."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='codepostal_".$id_agent."' value=\"".$codepostal."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='id_commune_".$id_agent."' value=\"".$id_commune."\" />\n";

        $hidden_html_agent .= "<input type='hidden' id='tel_".$id_agent."' value=\"".$tel."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='mobile_".$id_agent."' value=\"".$mobile."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='fax_".$id_agent."' value=\"".$fax."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='email_".$id_agent."' value=\"".$email."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='siteweb_".$id_agent."' value=\"".$siteweb."\" />\n";

        
        $hidden_html_agent .= "<input type='hidden' id='terrain_".$id_agent."' value=\"".$terrain."\" />\n";
        $hidden_html_agent .= "<input type='hidden' id='service_hydro_".$id_agent."' value=\"".$niveau."\" />\n";
        

        // -------------------------------------------------------------
        // Remplissage d'un tableau pour la avoir la liste des RA
        
        // Coloration d'une ligne au survol de la souris
        
        $row++;
        if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
        else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
        
        
        $tab_html .= "<tr ".$row_l." >";
            
            // Nom
            $tab_html .= "<td style='cursor:pointer;' onClick='loadFicheAgent(".$id_agent.");'>";	
                $tab_html .= $nom;
            $tab_html .= "</td>\n";

            // Prenom
            $tab_html .= "<td style='cursor:pointer;' onClick='loadFicheAgent(".$id_agent.");'>";	
                $tab_html .= $prenom;
            $tab_html .= "</td>\n";
                    
            // Email
            $tab_html .= "<td>";	
                $tab_html .= "<a href='mailto:".$email."'>";
                    $tab_html .= $email;
                $tab_html .= "</a>";
            $tab_html .= "</td>\n";

            // Telephone
            $tab_html .= "<td style='cursor:pointer;' onClick='loadFicheAgent(".$id_agent.");'>";	
                $tab_html .= $tel;
            $tab_html .= "</td>\n";

            // Institution
            $tab_html .= "<td style='cursor:pointer;' onClick='loadFicheAgent(".$id_agent.");'>";	
                $tab_html .= $raisonsociale;
            $tab_html .= "</td>\n";

            // Fonction
            $tab_html .= "<td style='cursor:pointer;' onClick='loadFicheAgent(".$id_agent.");'>";	
                $tab_html .= $fonction;
            $tab_html .= "</td>\n";

            // Agent du service hydrologie
            $puce_agent_service = "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' >";
            if($niveau > 0)
            {
                $nb_agents_service++;
                $puce_agent_service = "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('Agent du service')."'>";
            }

            $tab_html .=  "<td class='t_cont_m' style='text-align:center;'>";
                $tab_html .=  $puce_agent_service;
            $tab_html .=  "</td>\n";

            // Agent de Terrain
            $puce_agent_terrain = "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' >";
            if($terrain > 0)
            {
                $nb_agents_terrain++;
                $puce_agent_terrain = "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('Agent de terrain')."'>";
            }

            $tab_html .=  "<td class='t_cont_m' style='text-align:center;'>";
                $tab_html .=  $puce_agent_terrain;
            $tab_html .=  "</td>\n";
            
            // Lien pour la suppression du RA
            $tab_html .= "<td  style='text-align:center;'>";	
                $tab_html .= "
                    <a style='font-size:12px;font-weight:bold;' id='del_".$id_agent."' onClick='verifDelAgent(".$id_agent.");' title='".htmlaccent('Supprimer l\'agent')."'>
                    X
                    </a>";
            $tab_html .= "</td>\n";
            
        $tab_html .=  "</tr>";	
 
    } 
    
    // Convertir le tableau PHP en JSON - Pour envoyer dans la fonction js loadRA() définie dans list_ra   
    //$ra_nav_json = json_encode($ra_nav_array);           
    //$ra_nav_json = json_encode($agents_nav_array);           
}
else
{
    $tab_html .= "<div id='boxpopup' style='margin-left: 1%;'>\n";
        $tab_html .= "<p class='alert'>".htmlaccent('Aucun - Agent - n\'a été trouvé')."</p>";
    $tab_html .= "</div>";
}
 


// Remplissage du tableau de retour


$responseData = array(
    'nb_agents' => $nb_agents,
    'hidden_html_agent' => $hidden_html_agent,
    'tab_html' => $tab_html,
//    'ra_nav_json' => $ra_nav_json
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>