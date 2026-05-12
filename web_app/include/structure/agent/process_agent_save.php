<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Enregistrement d'une fiche Agent (Modification ou Création)
- post_secure : est une fonction accessible dans include/function/ permettant de contrôler et de corriger les entrées depuis des formulaires. Fonction de Sécurité contre insertion JS et PHP
- Pour la vérification des champs valeur numéric, date ou heure, les valeurs vide sont valides. Il faut se référer aux fonctions de contrôle validDate(), validTime(), valid

Processus asynchrone AJAX coté serveur
----------------------------------------
*/

// ----------------------------------------------
// nécessaire pour la configuration du script

require('../../config.php');
require('../../database_tables.php');

require('../../function/date.php');	
require('../../function/gestion_erreur.php');	
require('../../function/database.php');	
require('../../function/html_output.php');
require('../../function/general.php');

// pour solutionner les pb d'accents
header('Content-Type: text/html; charset=utf-8');

// connexion à la base de données	
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query ($sql_link,'SET NAMES UTF8');



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


// -------------------------------------------------------------


// Initialisation Variables Globales
$msg_info_send = '';
$msg_info = '';
$erreur = false;
$tab_html = '';
$newAgent = false;
$row = 0;

// Vérifier si la requête envoyé depuis le client est bien une requête POST
if($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $id_user_agent = isset($_POST['id_user_agent']) ? $_POST['id_user_agent'] : '';
    $territoire_id = isset($_POST['territoire_id']) ? $_POST['territoire_id'] : '';

    $id_agent = isset($_POST['id_agent_fiche']) ? $_POST['id_agent_fiche'] : '';
    
    $check_terrain = 0;
    if(isset($_POST['check_terrain'])){$check_terrain=1;}   

    $check_service_hydro = 0;
    if(isset($_POST['check_service_hydro'])){$check_service_hydro=1;}   


    // Récupération des données du formulaire soumis qui correspondentt à une fiche agent
    $nom = isset($_POST['nom']) ? $_POST['nom'] : '';
    $nom = post_secure($sql_link,$nom);
    $nom_marital = isset($_POST['nom_marital']) ? $_POST['nom_marital'] : '';
    $nom_marital = post_secure($sql_link,$nom_marital);
    $prenom = isset($_POST['prenom']) ? $_POST['prenom'] : '';
    $prenom = post_secure($sql_link,$prenom);

    if(!tep_not_null($nom)) // Si le champs nom est vide
    {
        $erreur = true;
        $msg_info  .= "Le nom de l'agent est un champs obligatoire.<br>";
	}

    // Vérification d'un agent portant déjà le même nom et le même prénom 
    $sql_verif_agent = "SELECT EXISTS (
                                        SELECT 1 FROM ".TABLE_AGENT." WHERE nom='".$nom."' AND nom='".$prenom."' LIMIT 1
                                    ) AS agent_exists";
    $verif_agent_query = tep_db_query($sql_link,$sql_verif_agent);	
    $verif_agent_array = tep_db_fetch_array($verif_agent_query);

    if($verif_agent_array['agent_exists'] == 1)	// Si l'Agent (Nom et Prénom identiques) existe déjà
	{
        $erreur = true;
        $msg_info  .= "Un Agent avec les mêmes Nom et Prénom - ".$nom." ".$prenom." - existe déjà. Il ne peut pas être ajouté une seconde fois.<br>";
	}

    $raisonsociale = isset($_POST['raisonsociale']) ? $_POST['raisonsociale'] : '';
    $raisonsociale = post_secure($sql_link,$raisonsociale);
    $numinscription = isset($_POST['numinscription']) ? $_POST['numinscription'] : '';
    $numinscription = post_secure($sql_link,$numinscription);
    $fonction = isset($_POST['fonction']) ? $_POST['fonction'] : '';
    $fonction = post_secure($sql_link,$fonction);

    $tel = isset($_POST['tel']) ? $_POST['tel'] : '';
    $tel = post_secure($sql_link,$tel);
    $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
    $mobile = post_secure($sql_link,$mobile);
    $fax = isset($_POST['fax']) ? $_POST['fax'] : '';
    $fax = post_secure($sql_link,$fax);
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $email = post_secure($sql_link,$email);
    $siteweb = isset($_POST['siteweb']) ? $_POST['siteweb'] : '';
    $siteweb = post_secure($sql_link,$siteweb);

    $adresse = isset($_POST['adresse']) ? $_POST['adresse'] : '';
    $adresse = post_secure($sql_link,$adresse);
    $lieudit = isset($_lieudit) ? $_POST['lieudit'] : '';
    $lieudit = post_secure($sql_link,$lieudit);
    $bp = isset($_POST['bp']) ? $_POST['bp'] : '';
    $bp = post_secure($sql_link,$bp);
    $codepostal = isset($_POST['codepostal']) ? $_POST['codepostal'] : '';
    $codepostal = post_secure($sql_link,$codepostal);

    $select_commune ='';
    if(isset($_POST['select_commune'])){$select_commune = post_secure($sql_link,$_POST['select_commune']);}




    // --------------------------------------------------------------------------
    // Engregistrement des données dans la base

    if(!$erreur)
    {
        $type_action = 13; // Action Paramétrage
        $dateheure_action =  date("Y-m-d H:i:s");

        if($id_agent < 1) // Si on enregistre un nouvel Agent id_agent=0
        {
            $query = "INSERT INTO ".TABLE_AGENT." (nom, prenom) 
                                            VALUES ('".$nom."','".$prenom."')";	

            // Execution de la requête
            tep_db_query($sql_link, $query);

            // Récupérer le nouvel identifiant
            $id_agent = mysqli_insert_id($sql_link); 
            $newAgent = true;     

            $msg_info_send .= "<span style='font-size:16px;'>";
                $msg_info_send .= "La fiche Agent a bien été crée";
            $msg_info_send .= "</span>";
            $msg_info_send .= "<br><br>";

            $msg_info .= "Agent : ".$nom." - ".$prenom;


            // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
            
            $info_action = "Création d'une nouvelle Fiche Agent <br>";
            $info_action .= $msg_info;
            $info_action = post_secure($sql_link,$info_action); 

            $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$dateheure_action."')";
            tep_db_query($sql_link,$query);
        }


        // Mise à jour de l'agent (Nouveau ou Modifié)
        $query = "UPDATE ".TABLE_AGENT." SET  nom='".$nom."',
                                            nom_marital='".$nom_marital."',
                                            prenom='".$prenom."',
                                            raisonsociale='".$raisonsociale."',
                                            numinscription='".$numinscription."',
                                            fonction='".$fonction."',
                                            tel='".$tel."',
                                            mobile='".$mobile."',
                                            fax='".$fax."',
                                            email='".$email."',
                                            siteweb='".$siteweb."',
                                            adresse='".$adresse."',
                                            lieudit='".$lieudit."',
                                            bp='".$bp."',
                                            codepostal='".$codepostal."',
                                            id_commune='".$select_commune."',
                                            terrain='".$check_terrain."',
                                            niveau='".$check_service_hydro."' 									
                                            WHERE id=".$id_agent;

        // Execution de la requête
        tep_db_query($sql_link, $query);


        // Message pour la mise à jour si ce n'est pas une nouvelle fiche                                  
        if(!$newAgent)
        {
            $msg_info_send .= "<span style='font-size:16px;'>";
                $msg_info_send .= "La fiche Agent a bien été modifiée";
            $msg_info_send .= "</span>";
            $msg_info_send .= "<br><br>";

            $msg_info .= "Agent : ".$nom." - ".$prenom;

            // Enregistrement de l'action dans la table ACTION qui suit toutes les actions de la plateforme
            
            $info_action = "Modification Fiche Agent <br>";
            $info_action .= $msg_info;                 
            $info_action = post_secure($sql_link,$info_action); 

            $query = "INSERT INTO ".TABLE_ACTIONS." (id_user, type_action, info, dateheure) VALUES (".$id_user_agent.",'".$type_action."','".$info_action."','".$dateheure_action."')";
            tep_db_query($sql_link,$query);
        }
    }
    else
    {
        $msg_info_send .= "<span style='font-size:16px;'>";
        $msg_info_send .= "Erreur : la fiche Agent n'a pu être enregistrée";
        $msg_info_send .= "</span>";
        $msg_info_send .= "<br><br>";
    }
}
else
{
    $msg_info_send .= "<span style='font-size:16px;'>";
    $msg_info_send .= "Une erreur est survenue lors de l'envoi des données sur le serveur.";
    $msg_info_send .= "</span>";
    $msg_info_send .= "<br><br>";
}

$msg_info_send .= $msg_info; 

// Remplissage du tableau de retour

$responseData = array(
    'erreur' => $erreur,
    'id_agent' => $id_agent,    
    'msg_info' => $msg_info_send
);

// Encodage du tableau associatif en JSON
$jsonResponse = json_encode($responseData);

// Envoi des données coté Client
echo $jsonResponse;

?>