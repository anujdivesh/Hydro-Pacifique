<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Paramétrage général de la Plateforme
Ce code ce charge sur chaque page
----------------------------------------
*/

// Chargmeent du fichier de configuration (Lien et Connexion à la BDD)
require('include/config.php');

// Initialisation des class
require(DIR_WS_CLASS . 'navigation.php');
require(DIR_WS_CLASS . 'type_image.php');		
require(DIR_WS_CLASS . 'type_fichier.php');
require(DIR_WS_CLASS . 'evalmath.class.php');
//require(DIR_WS_CLASS . 'xlsxwriter.class.php'); // Class Excel xlswriter
require(DIR_WS_CLASS . 'csv.php');

// Initialisation fonctions
require(DIR_WS_FUNCTION . 'database.php');	
require(DIR_WS_FUNCTION . 'html_output.php');

require(DIR_WS_FUNCTION . 'general.php');
require(DIR_WS_FUNCTION . 'date.php');
require(DIR_WS_FUNCTION . 'math.php');
require(DIR_WS_FUNCTION . 'stats.php');
require(DIR_WS_FUNCTION . 'gestion_erreur.php');
require(DIR_WS_FUNCTION . 'rubrique.php');
require(DIR_WS_FUNCTION . 'pagination.php');
require(DIR_WS_FUNCTION . 'search.php');
require(DIR_WS_FUNCTION . 'images.php');
require(DIR_WS_FUNCTION . 'pdf.php');
require(DIR_WS_FUNCTION . 'importation_fichier.php');
require(DIR_WS_FUNCTION . 'form_valid.php');
require(DIR_WS_FUNCTION . 'form_multilingue_content.php');
require(DIR_WS_FUNCTION . 'envoi_mail.php');
require(DIR_WS_FUNCTION . 'html_affichage.php');
require(DIR_WS_FUNCTION . 'barre_progression.php');

require(DIR_WS_FUNCTION . 'password.php');
require(DIR_WS_FUNCTION . 'sessions.php');
require(DIR_WS_FUNCTION . 'ip_controle.php');

require(DIR_WS_FUNCTION . 'sql_function.php');	

// Liste des tables 
require(DIR_WS_INCLUDE . 'database_tables.php');


// Type caractère UTF-8
header('Content-Type: text/html; charset=utf-8');

// Connexion à la base de données
global $sql_link;
$sql_link = mysqli_connect(DB_SERVER,DB_SERVER_USERNAME,DB_SERVER_PASSWORD,DB_DATABASE) or die('Impossible de se connecter à la base de données!');
mysqli_query($sql_link,"SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
mysqli_query($sql_link,'SET NAMES UTF8');

// Nettoyage de la table session
clean_connexion($sql_link); 

// Pour éviter le numéro de session dans l'url
ini_set('url_rewriter.tags','');

// Debut de session
session_start();

// ip qui se connecte, est interdit
if(ip_out($sql_link))
{
	$message = "";
	$message_info = "";
	$message_info .= "!!! Tentative d'accès d'un Ip prohibé !!!<br><br>";	
	$message .= $message_info . $message_content;	
	
	mail_simple($expediteur,$adresse_reponse,$destinataire,$sujet,$copie_mail,$message);
	
	tep_redirect('error.html');
	tep_db_close($sql_link);
	die();	
}


// Permet d'afficher une feuille pour les impressions (Pas sûr que ce sera utilisé)
$print=false;
if(isset($_GET['print']) && tep_not_null($_GET['print']) && $_GET['print']=='ok'){$print=true;}

// Variable globale pour remplacer un String=Null et faire fonctionner la fonction html_entity_decode()
$default_string = '';

// Verif autorisation
$autorisation = false;
$id_user = 0;
$file_encours = basename($_SERVER['PHP_SELF']);

// Territoire Utilisateur - Permet de définir automatiquement dans quel Territoire on se trouve (NC, PF, WF) et de définir les Vairables globales liées
$sql_territoire = "SELECT DISTINCT t.id_territoire, t.init_territoire, t.nom_territoire, t.theme_region, 
								t.region_default, t.service_hydro, t.color_service, t.timezone_php, t.lang,
								t.mapLong, t.mapLat, t.mapZoom, t.mapMinZoom
					FROM ".TABLE_TERRITOIRE." t 
					WHERE t.init_territoire='".INIT_T."'";
$territoire_query = tep_db_query($sql_link,$sql_territoire);
$territoire = tep_db_fetch_array($territoire_query);

$territoire_id = $territoire['id_territoire'];
$territoire_init = $territoire['init_territoire'];
$territoire_nom = htmlaccent($territoire['nom_territoire']);
$territoire_region = htmlaccent($territoire['theme_region']);
$region_default = htmlaccent($territoire['region_default']);
$service_hydro = htmlaccent($territoire['service_hydro']);
$color_service = htmlaccent($territoire['color_service']);
$timezone_php = htmlaccent($territoire['timezone_php']);
$territoire_lang = $territoire['lang'];
$territoire_mapLong = $territoire['mapLong'];
$territoire_mapLat = $territoire['mapLat'];
$territoire_mapZoom = $territoire['mapZoom'];
$territoire_mapMinZoom = $territoire['mapMinZoom'];

$lang = $territoire_lang;


// RECUPERATION DU TEXT - POUR LA TRADUCTION
require(DIR_WS_INCLUDE . 'text_content_'.$lang.'.php');

// On regarde si on est sur la page de Log ou pas. Cela permet d'identifier les variables globales et de sécuriser la navigation
if($file_encours != 'login.php')
{
	if(suiviSession($sql_link)) // Cette fonction permet de vérifier que la session n'a pas changer. Sécurité face au "vol" de Session
	{
		if(basename($_SERVER['PHP_SELF']) != 'logout.php'){$autorisation = true;}

		
		// Info Utilisateur liée à la Session
		$tab_session = getAdminInfo($sql_link);
		$id_user = $tab_session['admin_id'];
		$nom_user = htmlaccent(post_secure($sql_link,$tab_session['nom']));
		$prenom_user = htmlaccent(post_secure($sql_link,$tab_session['prenom']));
		$info_user = htmlaccent(post_secure($sql_link,$tab_session['info']));

		// -----------------------------------
		
		// Gestion du temps en fonction du territoire
		date_default_timezone_set($timezone_php); 
		$today = new DateTime(); // Crée un objet DateTime pour la date actuelle
		$today_formatted = $today->format('Y-m-d');  // Formatage de la date (uniquement la partie 'Y-m-d') et stockage dans une variable ou affichage
		$today_fr_formatted = $today->format('d-m-Y');  // Formatage de la date (uniquement la partie 'Y-m-d') et stockage dans une variable ou affichage
		
		$today_time = new DateTime(); // Crée un objet DateTime pour la date actuelle
		$today_time_formatted = $today_time->format('Y-m-d H:i:s'); // Formatage de la date et de l'heure (format complet 'Y-m-d H:i:s') et stockage ou affichage
		$today_time_fr_formatted = $today_time->format('d-m-Y H:i:s');
		
		// Récupération des droits d'accès de l'utilisateur qui se connecte
		$sql_acces = "SELECT DISTINCT gestion_data, parametre, config FROM ".TABLE_USER_ACCES;
		$where_acces = " WHERE id=".$id_user;
		$acces_query = tep_db_query($sql_link,$sql_acces.$where_acces);
		$acces = tep_db_fetch_array($acces_query);
	
		// Permet de gérer l'accès aux différentes partie de la plateforme en fonction des droits d'accès
		$visual_data = 1;
		$gestion_data = post_secure($sql_link,$acces['gestion_data']);
		$parametre = post_secure($sql_link,$acces['parametre']);
		$config = post_secure($sql_link,$acces['config']);
	
		// Récupération des autorisations d'accès de la page / Permet de palier à une éventuel attaque par la lisaison à un fichier non autorisé
		$sql_ctrl_acces = "SELECT DISTINCT file, var FROM ".TABLE_AUTORISATION." WHERE file='".$file_encours."'";
		$ctrl_acces_query = tep_db_query($sql_link,$sql_ctrl_acces);
		$ctrl_acces = tep_db_fetch_array($ctrl_acces_query);
		
		
		// Contrôle des accès à une page en fonction des droits d'autorisation de l'utilisateur
		if(isset($ctrl_acces) && tep_not_null($ctrl_acces['file']))
		{
			if(isset(${$ctrl_acces['var']}))
			{	
				if(${$ctrl_acces['var']}==0)
				{
					tep_redirect('noaccess.html');
					tep_db_close($sql_link);
					die();
				}
			}
			else
			{
				tep_redirect('noaccess.html');
				tep_db_close($sql_link);
				die();
			}
		}
		else
		{
			tep_redirect('noaccess.html');
			tep_db_close($sql_link);
			die();
		}
		
		
		// Nettoyer la table Export et surtout les données si elles ont plus d'un mois
		$sql_cleanexport = "SELECT DISTINCT id, id_user, type_action, info, dateheure, file_export
						FROM ".TABLE_ACTIONS."
						WHERE type_action=36
						AND dateheure < DATE_SUB(NOW(), INTERVAL 1 MONTH)";

		$cleanexport_query = tep_db_query($sql_link,$sql_cleanexport);
		while ($cleanexport_tab = tep_db_fetch_array($cleanexport_query))
		{
			$file_export = DIR_WS_DATA_EXPORT.$cleanexport_tab['file_export'];
			
			// Vérifier si le dossier principal existe et est un dossier
			if(file_exists($file_export))
			{
				unlink($file_export);	// Supprimer le fichier
				//echo $file_export;
			}     
		}
	
	}
	else
	{
		// Si la session n'est pas conforme l'application se déconnecte automatiquement.
		session_destroy();
		tep_redirect('login.php');
		die();	
	}
}


?>