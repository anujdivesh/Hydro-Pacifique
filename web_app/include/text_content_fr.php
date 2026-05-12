<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Configuration des variables pour tous les aspects de la plateforme et la rendre multilingue
Text FR
----------------------------------------
*/

// POPUP TEXT

    define('TEXT_POPUP_NOCONNEXION','Vous n êtes pas connecté à Internet. \n Certaines fonctionnalités pourraient ne pas être disponibles. \n Les fonds de cartes ne pourront pas s afficher.');

// TOP
    
    define('TEXT_TOP_FIRST','Accueil');
    define('TEXT_TOP_DATE_HP','Date');
    define('TEXT_TOP_VERSION_HP','Version');

    define('TEXT_TOP_COUNTRY','Territoire');

    define('TEXT_TOP_LOG','Compte');
    define('TEXT_TOP_LOG_QUAL','Qualité');
    define('TEXT_TOP_ADMIN','Administration');
    define('TEXT_TOP_PASS','Modifier mon mot de passe');
    define('TEXT_TOP_CLOSE','Se déconnecter');


// ---------------------------------------------------------------------

// MENU

    // DATA
    define('TEXT_MENU_DATA','Données');

    define('TEXT_MENU_DATA_CHRON','Data / Chroniques');
    define('TEXT_MENU_DATA_TRACKCONNECT','Suivi des Corrections');
    define('TEXT_MENU_DATA_ACTREPORT','Rapport d\'Activité (RA)');
    define('TEXT_MENU_DATA_IMPORT','Importation');
    define('TEXT_MENU_DATA_EXPORT','Exportation');

    // MODULES
    define('TEXT_MENU_MOD','Modules');

    define('TEXT_MENU_MOD_STATION','Stations de mesure');
    define('TEXT_MENU_MOD_JGE','Jaugeages (Débits)');
    define('TEXT_MENU_MOD_ETL','Etalonnages (Débits)');
    define('TEXT_MENU_MOD_DIAG','Diagraphie (Piézo)');
    define('TEXT_MENU_MOD_AGENTS','Agents');

    // ROUND
    define('TEXT_MENU_ROUND','Tournées');

    define('TEXT_MENU_ROUND_TRACK','Suivi des tournées');
    define('TEXT_MENU_ROUND_MANAGE','Gestion des tournées');


    // SETTINGS
    define('TEXT_MENU_SET','Paramètres');

    define('TEXT_MENU_SET_GEO','Zones Géographiques');
    define('TEXT_MENU_SET_TYPEC','Type de Chroniques');
    define('TEXT_MENU_SET_QUAL','Codes Qualité');
    define('TEXT_MENU_SET_EQJGE','Equipements Jaugeages');
    define('TEXT_MENU_SET_OPTION','Options');
    define('TEXT_MENU_SET_TRANSF','Export / Import');

    // ACTIONS
    define('TEXT_MENU_HP','Actions HP');

    define('TEXT_MENU_HP_TRACKIMPORT','Suivi des Imports');
    define('TEXT_MENU_HP_TRACKEXPORT','Suivi des Exports');
    define('TEXT_MENU_HP_ACTIONS','Toutes les Actions');

    // RESSOURCES
    define('TEXT_MENU_RESSOURCE','Ressources');

    define('TEXT_MENU_RESSOURCE_FIRST','Accueil');
    define('TEXT_MENU_RESSOURCE_HELP','Aide');
    define('TEXT_MENU_RESSOURCE_CONDITION','Conditions d\'utilisation');
    define('TEXT_MENU_RESSOURCE_CONTACT','Contact');


// ---------------------------------------------------------------------

// MAP
    
    define('TEXT_MAP_TITLE','Carte Interactive');

    define('TEXT_MAP_BACK','Revenir à l\'échelle du '.TEXT_TOP_COUNTRY);
    define('TEXT_MAP_ZOOM','Zoom');
    define('TEXT_MAP_LONG','Long.');   
    define('TEXT_MAP_LAT','Lat.');    

    define('TEXT_MAP_FULLSCREEN','Carte en plein écran'); 
    define('TEXT_MAP_WINDOWED','Quitter le plein écran');  
    
    define('TEXT_MAP_SAVEIMG','Capturer la carte en image'); 
    define('TEXT_MAP_DLIMG','Télécharger l\'image');   
    
    define('TEXT_MAP_LEGEND_TITLE','Légende Station'); 

    
// FILTRES STATIONS

    define('TEXT_FILTER_ALL','* Tous');

    define('TEXT_FILTER_SEARCH','Rechercher');
    define('TEXT_FILTER_TYPE','Type de mesures');
    define('TEXT_FILTER_BV','Région hydro. <br> Bassin Versant');
    define('TEXT_FILTER_RIVER','Rivière');
    define('TEXT_FILTER_CITY','Commune');
    define('TEXT_FILTER_ROUND','Tournées');
    define('TEXT_FILTER_STATION','Station');
    
    define('TEXT_FILTER_STATUT','Statut stations');
    define('TEXT_FILTER_SUIVI','Suivi stations');
    define('TEXT_FILTER_ETATEQ','Etat des éqiupements');

    define('TEXT_FILTER_NBSTATION','NB stations');
    define('TEXT_FILTER_STATUTACTIVE','Active');
    define('TEXT_FILTER_STATUTHISTORIQUE','Historique');
    define('TEXT_FILTER_SUIVICONTINU','Mesures continues');
    define('TEXT_FILTER_SUIVIPONCTUEL','Mesures ponctuelles');
    define('TEXT_FILTER_ETATFONCTIONNEMENT','En fonctionnement');
    define('TEXT_FILTER_ETATPANNE','En panne');

// DESCRIPTION STATIONS

    define('TEXT_STATION_NOM','Nom station');
    define('TEXT_STATION_CODE','Code station');
    define('TEXT_STATION_DATE_INSTALL','Date installation');
    define('TEXT_STATION_DATE_LASTGO','Date du dernier passage');
    define('TEXT_STATION_DELAY_LASTGO','Délais depuis le dernier passage');

    define('TEXT_STATION_STATUT','Statut');
    define('TEXT_STATION_SUIVI','Suivi');
    define('TEXT_STATION_ETATEQ','Etat des éqiupements');

    define('TEXT_STATION_LINK_FICHE','>> Fiche station');
    define('TEXT_STATION_LINK_DATA','>> Données de la station');
    define('TEXT_STATION_LINK_LAST_RA','>> Derniers Rapports d\'Activité');

// BOUTON INDEX

    define('TEXT_BUTTON_RA','Rapport d\'activité');
    define('TEXT_BUTTON_IMPORT','Données importées');


// CHRONIQUE

    define('TEXT_CHRON_RA','Fait marquant');
    define('TEXT_CHRON_RA_HEIGHT','Relevé manuel de la hauteur');
    define('TEXT_CHRON_JGE','Jaugeage');    


?>