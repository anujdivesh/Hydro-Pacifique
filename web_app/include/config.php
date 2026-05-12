<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
*/

/* Fichier de configuration du site */


// Variable globale de description
define('AD_SITE', ''); // adresse de la plateforme
define('NOM_SITE', 'HydroPacifique - SCP'); // nom de la plateforme
define('MIN_SITE', 'HydroPacifique - SCP'); // initiales de la plateforme
define('TITRE_SITE', 'HP - SCP'); // titre de la plateforme
define('TITRE_SITE_ADMIN', 'Hydro Pacifique - Traitement et Analyse des données hydrologiques'); // Site d'administation
define('TITRE_T', 'Pacific'); // territoire par défaut pour la démo importée
define('INIT_T', 'Pacific'); // doit correspondre à geo_territoire.init_territoire

define('TITLE_SERVICE', 'Davar - Service De l\'Eau (DSE)'); // Variable Globale pour le service en charge de la plateforme

// Valeur pour connexions au Serveur
$http_scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$http_host = $_SERVER['HTTP_HOST'] ?? '';
define('HTTP_SERVER', $http_host !== '' ? $http_scheme . '://' . $http_host . '/' : 'http://hydropacifique-nc/'); // adresse exacte du site 
//define('HTTP_SERVER', 'http://hydropacifique-pf/'); // adresse exacte du site 
define('DIR_WS_ADMIN', ''); // 
define('SESSION_TIMEOUT', 10000); // durée d'une session sans activité


//----------------

// Valeur pour connexions à la Base de Donnée 
// WAMP - LOCAL 


define('DB_SERVER', 'localhost'); // eg, localhost - should not be empty for productive servers
define('DB_SERVER_USERNAME', 'root'); // utilisateur de la base de données
define('DB_SERVER_PASSWORD', ''); 
define('DB_DATABASE', 'hp-data-demo-spc'); // nom de la base de données

//----------------



// Prefixes des tables
define('DB_TABLE_PREFIX', ''); //administration

define('DB_TABLE_PREFIX_ADMIN',DB_TABLE_PREFIX.'ad_'); //administration
define('DB_TABLE_PREFIX_CTRL',DB_TABLE_PREFIX.'ctrl_'); //tables de con


// Chemins des principaux dossiers des fichiers de codage

define('DIR_WS_INCLUDE', 'include/');

define('DIR_WS_STRUCTURE', DIR_WS_INCLUDE . 'structure/');

define('DIR_WS_STATION', DIR_WS_STRUCTURE . 'station/');

define('DIR_WS_FILTRE', DIR_WS_STRUCTURE . 'filtre/');

define('DIR_WS_IMPORT', DIR_WS_STRUCTURE . 'import/');
define('DIR_WS_EXPORT', DIR_WS_STRUCTURE . 'export/');

define('DIR_WS_CSV', DIR_WS_EXPORT . 'csv/');

define('DIR_WS_INDEX', DIR_WS_STRUCTURE . 'index/');
define('DIR_WS_GRAPH', DIR_WS_STRUCTURE . 'graph/');
define('DIR_WS_TYPEDATA', DIR_WS_STRUCTURE . 'typedata/');
define('DIR_WS_QUALITYDATA', DIR_WS_STRUCTURE . 'qualitydata/');
define('DIR_WS_EQJGE', DIR_WS_STRUCTURE . 'eq_jge/');
define('DIR_WS_JAUGEAGE', DIR_WS_STRUCTURE . 'jaugeage/');
define('DIR_WS_ETL', DIR_WS_STRUCTURE . 'etl/');
define('DIR_WS_CALCUL', DIR_WS_STRUCTURE . 'calcul/');
define('DIR_WS_MODCALCUL', DIR_WS_STRUCTURE . 'mod_calcul/');
define('DIR_WS_RA', DIR_WS_STRUCTURE . 'ra/');
define('DIR_WS_DIAG', DIR_WS_STRUCTURE . 'diag/');
define('DIR_WS_AGENT', DIR_WS_STRUCTURE . 'agent/');
define('DIR_WS_GEO', DIR_WS_STRUCTURE . 'geographie/');

/*
define('DIR_WS_STATS', DIR_WS_STRUCTURE . 'stats/');
define('DIR_WS_STATS_PLU', DIR_WS_STATS . 'plu/');
define('DIR_WS_STATS_LIMNI', DIR_WS_STATS . 'limni/');
define('DIR_WS_STATS_DEBIT', DIR_WS_STATS . 'debit/');

define('DIR_WS_JAUGEAGE', DIR_WS_STRUCTURE . 'jaugeage/');
define('DIR_WS_DEBIT_TARAGE', DIR_WS_STRUCTURE . 'debit_tarage/');
*/

define('DIR_WS_FORMULAIRE', DIR_WS_INCLUDE . 'ctrl_form/');
define('DIR_WS_SUPPRIMER', DIR_WS_INCLUDE . 'suppression/');
define('DIR_WS_BOX', DIR_WS_STRUCTURE . 'box/');
define('DIR_WS_FUNCTION', 'function/');
define('DIR_WS_CLASS', 'class/');

// Chemin des fichiers de fichiers DATA

define('DIR_WS_DATA', 'data/');
define('DIR_WS_DATA_EXPORT', DIR_WS_DATA . 'export/');
define('DIR_WS_DATA_IMPORT', DIR_WS_DATA . 'uploads/');
define('DIR_WS_DATA_CORRECTIONS', DIR_WS_DATA . 'corrections/');
define('DIR_WS_DATA_PHOTOS', DIR_WS_DATA . 'photos_station/');

// Dossier devant contenir les pdf créé à la volé. 
define('DIR_WS_PDF', 'pdf/');

// Chemin des fichiers TXT
define('DIR_WS_TXT', DIR_WS_DATA . 'txt/');

// Chemin des fichiers images

define('DIR_WS_IMG', 'image/');
define('DIR_WS_IMG_ICO', DIR_WS_IMG . 'icones/');
define('DIR_WS_IMG_PDF', DIR_WS_IMG . 'pdf/');


// Variables globales complémentaires
define('VERSION_HP', 'v0.9.6'); // version de la mise à jour
define('DATE_VERSION_HP', '18/10/2024'); // version de la mise à jour
define('NB_LIGNE_PAGE', 50); // nombre de lignes s'affichant sur une page (ex : liste des stations)


//----------------------------------
// Gestions des mails

define('MAIL_WEBMASTER', 'Hydro Pacifique - Contact <contact@hydropacifique.com>');



?>