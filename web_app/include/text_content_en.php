<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Configuration des variables pour tous les aspects de la plateforme et la rendre multilingue
Text EN
----------------------------------------
*/

// POPUP TEXT

define('TEXT_POPUP_NOCONNEXION','You are not connected to the Internet. \n Some features may not be available. \n Map backgrounds will not be displayed.');

// TOP

    define('TEXT_TOP_FIRST','Home');
    define('TEXT_TOP_DATE_HP','Date');
    define('TEXT_TOP_VERSION_HP','Version');

    define('TEXT_TOP_COUNTRY','Territory');

    define('TEXT_TOP_LOG','Account');
    define('TEXT_TOP_LOG_QUAL','Quality');
    define('TEXT_TOP_ADMIN','Administration');
    define('TEXT_TOP_PASS','Change my password');
    define('TEXT_TOP_CLOSE','Log out');


// ---------------------------------------------------------------------

// MENU

    // DATA
    define('TEXT_MENU_DATA','Data');

    define('TEXT_MENU_DATA_CHRON','Time-Series Data');
    define('TEXT_MENU_DATA_TRACKCONNECT','Tracking Corrections');
    define('TEXT_MENU_DATA_ACTREPORT','Activity Report (AR)');
    define('TEXT_MENU_DATA_IMPORT','Import');
    define('TEXT_MENU_DATA_EXPORT','Export');

    // MODULES
    define('TEXT_MENU_MOD','Modules');

    define('TEXT_MENU_MOD_STATION','Measuring Stations');
    define('TEXT_MENU_MOD_JGE','Gaugings (Flow Rates)');
    define('TEXT_MENU_MOD_ETL','Calibrations (Flow Rates)');
    define('TEXT_MENU_MOD_DIAG','Diagraphy (Piezo)');
    define('TEXT_MENU_MOD_AGENTS','Agents');

    // ROUND
    define('TEXT_MENU_ROUND','Rounds');

    define('TEXT_MENU_ROUND_TRACK','Round Tracking');
    define('TEXT_MENU_ROUND_MANAGE','Round Management');

    // SETTINGS
    define('TEXT_MENU_SET','Settings');

    define('TEXT_MENU_SET_GEO','Geographical Zones');
    define('TEXT_MENU_SET_TYPEC','Time-Series type');
    define('TEXT_MENU_SET_QUAL','Quality Codes');
    define('TEXT_MENU_SET_EQJGE','Gauging Equipment');
    define('TEXT_MENU_SET_OPTION','Options');
    define('TEXT_MENU_SET_TRANSF','Export / Import');


    // ACTIONS
    define('TEXT_MENU_HP','HP Actions');

    define('TEXT_MENU_HP_TRACKIMPORT','Import Tracking');
    define('TEXT_MENU_HP_TRACKEXPORT','Export Tracking');
    define('TEXT_MENU_HP_ACTIONS','All Actions');

    // RESOURCES
    define('TEXT_MENU_RESSOURCE','Resources');

    define('TEXT_MENU_RESSOURCE_FIRST','Home');
    define('TEXT_MENU_RESSOURCE_HELP','Help');
    define('TEXT_MENU_RESSOURCE_CONDITION','Terms of Use');
    define('TEXT_MENU_RESSOURCE_CONTACT','Contact');


// ---------------------------------------------------------------------

// MAP

    define('TEXT_MAP_TITLE','Interactive Map');

    define('TEXT_MAP_BACK','Return to '.TEXT_TOP_COUNTRY.' scale');
    define('TEXT_MAP_ZOOM','Zoom');
    define('TEXT_MAP_LONG','Long.');
    define('TEXT_MAP_LAT','Lat.');

    define('TEXT_MAP_FULLSCREEN','Fullscreen Map');
    define('TEXT_MAP_WINDOWED','Exit Fullscreen');

    define('TEXT_MAP_SAVEIMG','Capture map as image');
    define('TEXT_MAP_DLIMG','Download image');

    define('TEXT_MAP_LEGEND_TITLE','Station Legend');

// STATION FILTERS

    define('TEXT_FILTER_ALL','* All');

    define('TEXT_FILTER_SEARCH','Search');
    define('TEXT_FILTER_TYPE','Measurement Type');
    define('TEXT_FILTER_BV',' Watershed');
    define('TEXT_FILTER_RIVER','River');
    define('TEXT_FILTER_CITY','Municipality');
    define('TEXT_FILTER_ROUND','Rounds');
    define('TEXT_FILTER_STATION','Station');

    define('TEXT_FILTER_STATUT','Station Status');
    define('TEXT_FILTER_SUIVI','Station Tracking');
    define('TEXT_FILTER_ETATEQ','Equipment Status');

    define('TEXT_FILTER_NBSTATION','Number of Stations');
    define('TEXT_FILTER_STATUTACTIVE','Active');
    define('TEXT_FILTER_STATUTHISTORIQUE','Historical');
    define('TEXT_FILTER_SUIVICONTINU','Continuous Measurements');
    define('TEXT_FILTER_SUIVIPONCTUEL','Spot Measurements');
    define('TEXT_FILTER_ETATFONCTIONNEMENT','Operational');
    define('TEXT_FILTER_ETATPANNE','Out of Order');

// STATION DESCRIPTIONS

    define('TEXT_STATION_NOM','Station Name');
    define('TEXT_STATION_CODE','Station Code');
    define('TEXT_STATION_DATE_INSTALL','Installation Date');
    define('TEXT_STATION_DATE_LASTGO','Date of Last Visit');
    define('TEXT_STATION_DELAY_LASTGO','Time Since Last Visit');

    define('TEXT_STATION_STATUT','Status');
    define('TEXT_STATION_SUIVI','Tracking');
    define('TEXT_STATION_ETATEQ','Equipment Status');

    define('TEXT_STATION_LINK_FICHE','>> Station Details');
    define('TEXT_STATION_LINK_DATA','>> Station Data');
    define('TEXT_STATION_LINK_LAST_RA','>> Latest Activity Reports');

// INDEX BUTTON

    define('TEXT_BUTTON_RA','Activity Report');
    define('TEXT_BUTTON_IMPORT','Imported Data');

// CHRONIQUE

    define('TEXT_CHRON_RA','Field Visit - Important info');
    define('TEXT_CHRON_RA_HEIGHT','Manual height');
    define('TEXT_CHRON_JGE','Flow Gaugings');

?>