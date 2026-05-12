-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: hp-data-demo-spc
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `ad_actions`
--

DROP TABLE IF EXISTS `ad_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_actions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `type_action` int DEFAULT NULL,
  `info` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `dateheure` datetime DEFAULT NULL,
  `file_export` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `id_import` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2643 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_actions_type`
--

DROP TABLE IF EXISTS `ad_actions_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_actions_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_autorisation`
--

DROP TABLE IF EXISTS `ad_autorisation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_autorisation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `var` varchar(60) NOT NULL,
  `file` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=148 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_session`
--

DROP TABLE IF EXISTS `ad_session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_session` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sid` text NOT NULL,
  `admin_id` int NOT NULL DEFAULT '0',
  `date_connect` date NOT NULL DEFAULT '0000-00-00',
  `heure_connect` time NOT NULL DEFAULT '00:00:00',
  `last_access` int NOT NULL DEFAULT '0',
  `ip` varchar(40) DEFAULT NULL,
  `browser` text,
  `map_zoom` float DEFAULT NULL,
  `map_long` float DEFAULT NULL,
  `map_lat` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=898 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_user`
--

DROP TABLE IF EXISTS `ad_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin` int NOT NULL DEFAULT '0',
  `id_statut` int NOT NULL,
  `login` varchar(150) NOT NULL,
  `nom` varchar(150) NOT NULL DEFAULT '',
  `prenom` varchar(150) DEFAULT NULL,
  `email` varchar(40) NOT NULL DEFAULT '',
  `info` varchar(150) NOT NULL,
  `password` varchar(40) NOT NULL DEFAULT '',
  `group_id` int NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date_modif` datetime DEFAULT '0000-00-00 00:00:00',
  `active` int NOT NULL DEFAULT '0',
  `last_log` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `nb_log` int NOT NULL DEFAULT '0',
  `debut_annee_hydro` int DEFAULT NULL,
  `debut_journee_hydro` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_user_acces`
--

DROP TABLE IF EXISTS `ad_user_acces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_user_acces` (
  `id` int NOT NULL AUTO_INCREMENT,
  `gestion_data` int NOT NULL DEFAULT '1',
  `parametre` int NOT NULL DEFAULT '1',
  `config` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_user_coord`
--

DROP TABLE IF EXISTS `ad_user_coord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_user_coord` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `map_zoom` float NOT NULL,
  `map_long` float NOT NULL,
  `map_lat` float NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_user_menu`
--

DROP TABLE IF EXISTS `ad_user_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_user_menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `menu_id` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `is_open` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_user` (`id_user`,`menu_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ad_user_to_territoire`
--

DROP TABLE IF EXISTS `ad_user_to_territoire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ad_user_to_territoire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_territoire` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `agent`
--

DROP TABLE IF EXISTS `agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agent` (
  `id` int NOT NULL AUTO_INCREMENT,
  `niveau` int NOT NULL,
  `id_user` int NOT NULL,
  `nom` varchar(150) NOT NULL,
  `nom_marital` varchar(150) NOT NULL,
  `prenom` varchar(150) NOT NULL,
  `raisonsociale` varchar(150) NOT NULL,
  `numinscription` varchar(50) NOT NULL,
  `fonction` varchar(100) NOT NULL,
  `adresse` varchar(200) NOT NULL,
  `lieudit` varchar(100) NOT NULL,
  `bp` varchar(50) NOT NULL,
  `codepostal` varchar(50) NOT NULL,
  `commune` varchar(50) NOT NULL,
  `id_commune` int DEFAULT NULL,
  `tel` varchar(50) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `fax` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `siteweb` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,
  `terrain` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=246 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ctrl_ip_aspirateur`
--

DROP TABLE IF EXISTS `ctrl_ip_aspirateur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ctrl_ip_aspirateur` (
  `ip` varchar(40) NOT NULL DEFAULT '',
  `date_connect` varchar(40) NOT NULL DEFAULT '',
  `nb_pages` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ctrl_ip_login`
--

DROP TABLE IF EXISTS `ctrl_ip_login`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ctrl_ip_login` (
  `ip` varchar(40) NOT NULL DEFAULT '',
  `date_connect` varchar(40) NOT NULL DEFAULT '',
  `nb_tentatives` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ctrl_ip_out`
--

DROP TABLE IF EXISTS `ctrl_ip_out`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ctrl_ip_out` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ctrl_ip_suspect`
--

DROP TABLE IF EXISTS `ctrl_ip_suspect`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ctrl_ip_suspect` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL DEFAULT '',
  `dns` varchar(80) DEFAULT NULL,
  `browser` text,
  `type` text,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `heure` time NOT NULL DEFAULT '00:00:00',
  `last_access` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_all`
--

DROP TABLE IF EXISTS `data_all`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_all` (
  `dateheure` datetime DEFAULT NULL,
  `valeur` float DEFAULT NULL,
  `id_meta` int DEFAULT NULL,
  KEY `id_meta` (`id_meta`),
  KEY `date_heure` (`dateheure`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_all_correction`
--

DROP TABLE IF EXISTS `data_all_correction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_all_correction` (
  `dateheure` datetime DEFAULT NULL,
  `valeur` float DEFAULT NULL,
  `id_meta` int DEFAULT NULL,
  KEY `id_meta` (`id_meta`),
  KEY `date_heure` (`dateheure`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_all_correction_test`
--

DROP TABLE IF EXISTS `data_all_correction_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_all_correction_test` (
  `dateheure` datetime DEFAULT NULL,
  `valeur` float DEFAULT NULL,
  `id_meta` int DEFAULT NULL,
  `valeurQ` float DEFAULT NULL,
  `a` float DEFAULT NULL,
  `b` float DEFAULT NULL,
  KEY `id_meta` (`id_meta`),
  KEY `date_heure` (`dateheure`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_correction`
--

DROP TABLE IF EXISTS `data_correction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_correction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int DEFAULT NULL,
  `datetime_correction` datetime DEFAULT NULL,
  `id_station` int DEFAULT NULL,
  `id_chron_init` int DEFAULT NULL,
  `id_chron_modif` int DEFAULT NULL,
  `datetime_first` datetime DEFAULT NULL,
  `datetime_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=376 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_etl`
--

DROP TABLE IF EXISTS `data_etl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_etl` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `datetime_first` datetime DEFAULT NULL,
  `datetime_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1450 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_etl_correction`
--

DROP TABLE IF EXISTS `data_etl_correction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_etl_correction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int NOT NULL,
  `id_typedata` int NOT NULL,
  `datetime_first` datetime NOT NULL,
  `datetime_end` datetime NOT NULL,
  `h1` float NOT NULL,
  `h2` float NOT NULL,
  `a` float NOT NULL,
  `b` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id_station` (`id_station`),
  KEY `idx_id_typedata` (`id_typedata`),
  KEY `idx_datetime_first` (`datetime_first`),
  KEY `idx_datetime_end` (`datetime_end`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_etl_data`
--

DROP TABLE IF EXISTS `data_etl_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_etl_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_etl` int DEFAULT NULL,
  `hauteur` float DEFAULT NULL,
  `debit` float DEFAULT NULL,
  `code_qualite` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=67740 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_jge`
--

DROP TABLE IF EXISTS `data_jge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_jge` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `x_gps` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `y_gps` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `datetime` datetime DEFAULT NULL,
  `nb_bras` int DEFAULT NULL,
  `dist_site` float DEFAULT NULL,
  `id_site` int DEFAULT NULL,
  `id_methode` int DEFAULT NULL,
  `id_typejge` int DEFAULT NULL,
  `depouil_hmoy` float DEFAULT NULL,
  `depouil_q` float DEFAULT NULL,
  `depouil_sect` float DEFAULT NULL,
  `depouil_vmoy` float DEFAULT NULL,
  `depouil_vsurf` float DEFAULT NULL,
  `depouil_rh` float DEFAULT NULL,
  `depouil_profmoy` float DEFAULT NULL,
  `depouil_nbvert` int DEFAULT NULL,
  `code_qualite` int DEFAULT NULL,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `fichier` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `agents` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32845 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_jge_bras`
--

DROP TABLE IF EXISTS `data_jge_bras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_jge_bras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_jge` int DEFAULT NULL,
  `num_bras` int DEFAULT NULL,
  `id_moulinet` int DEFAULT NULL,
  `id_helice` int DEFAULT NULL,
  `id_saumon` int DEFAULT NULL,
  `perche_diam` int DEFAULT NULL,
  `id_fondlit` int DEFAULT NULL,
  `berge_depart` int DEFAULT NULL,
  `heure_first` time DEFAULT NULL,
  `h_ech_first` float DEFAULT NULL,
  `heure_end` time DEFAULT NULL,
  `h_ech_end` float DEFAULT NULL,
  `fond_text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `depouil_hmoy` float DEFAULT NULL,
  `depouil_nbvert` int DEFAULT NULL,
  `depouil_profmoy` float DEFAULT NULL,
  `depouil_distmax` float DEFAULT NULL,
  `depouil_vmoy` float DEFAULT NULL,
  `depouil_vsurf` float DEFAULT NULL,
  `depouil_surfmouil` float DEFAULT NULL,
  `depouil_perimouil` float DEFAULT NULL,
  `depouil_rh` float DEFAULT NULL,
  `depouil_q` float DEFAULT NULL,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `id_jge` (`id_jge`)
) ENGINE=InnoDB AUTO_INCREMENT=33121 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_jge_fondlit`
--

DROP TABLE IF EXISTS `data_jge_fondlit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_jge_fondlit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_jge_methode`
--

DROP TABLE IF EXISTS `data_jge_methode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_jge_methode` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_jge_points`
--

DROP TABLE IF EXISTS `data_jge_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_jge_points` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_bras` int DEFAULT NULL,
  `num_vert` int DEFAULT NULL,
  `dist_depart` float DEFAULT NULL,
  `prof_max` float DEFAULT NULL,
  `prof_pts` float DEFAULT NULL,
  `nb_tours` int DEFAULT NULL,
  `tps_pts` int DEFAULT NULL,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `dist_calc` float DEFAULT NULL,
  `debit_lam` float DEFAULT NULL,
  `prof_calc` float DEFAULT NULL,
  `vitesse_calc` float DEFAULT NULL,
  `vitesse_surf` float DEFAULT NULL,
  `vitesse_fond` float DEFAULT NULL,
  `vitesse_moy` float DEFAULT NULL,
  `identique` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23149 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_jge_site`
--

DROP TABLE IF EXISTS `data_jge_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_jge_site` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_jge_to_agent`
--

DROP TABLE IF EXISTS `data_jge_to_agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_jge_to_agent` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_jge` int DEFAULT NULL,
  `id_agent` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_jge_type`
--

DROP TABLE IF EXISTS `data_jge_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_jge_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titre` text,
  `obs` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_lab`
--

DROP TABLE IF EXISTS `data_lab`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_lab` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `date_heure` datetime NOT NULL,
  `cumul` int DEFAULT NULL,
  `total` int DEFAULT NULL,
  `id_data_qualite` int DEFAULT NULL,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  KEY `id_station` (`id_station`)
) ENGINE=InnoDB AUTO_INCREMENT=11263661 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_meta`
--

DROP TABLE IF EXISTS `data_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_meta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `id_typedata` int DEFAULT NULL,
  `id_codequal` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `source` varchar(250) DEFAULT NULL,
  `file` varchar(250) DEFAULT NULL,
  `obs` text,
  PRIMARY KEY (`id`),
  KEY `idx_data_meta_id_station_id_typedata` (`id_station`,`id_typedata`),
  KEY `id_station` (`id_station`),
  KEY `id_meta` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=87717 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_meta_correction`
--

DROP TABLE IF EXISTS `data_meta_correction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_meta_correction` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `id_typedata` int DEFAULT NULL,
  `id_codequal` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `obs_user` text,
  `source` varchar(250) DEFAULT NULL,
  `file` varchar(250) DEFAULT NULL,
  `obs` text,
  `datetime_correction` datetime DEFAULT NULL,
  `id_correction` int DEFAULT NULL,
  `type_correction` varchar(45) DEFAULT NULL,
  `info_correction` text,
  `axe_correction` text,
  `datetime_first` datetime DEFAULT NULL,
  `datetime_end` datetime DEFAULT NULL,
  `valid` int DEFAULT '0',
  `id_chron_modif` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_data_meta_id_station_id_typedata` (`id_station`,`id_typedata`),
  KEY `id_station` (`id_station`),
  KEY `id_meta` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=812 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_qualite`
--

DROP TABLE IF EXISTS `data_qualite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_qualite` (
  `id_data_qualite` int NOT NULL AUTO_INCREMENT,
  `init_qualite_data` varchar(100) DEFAULT NULL,
  `nom_qualite_data` varchar(250) DEFAULT NULL,
  `info_qualite_data` text,
  `id_eq_type` int DEFAULT '0',
  PRIMARY KEY (`id_data_qualite`)
) ENGINE=MyISAM AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_ra`
--

DROP TABLE IF EXISTS `data_ra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_ra` (
  `id_ra` int NOT NULL AUTO_INCREMENT,
  `id_ra_old` varchar(10) DEFAULT NULL,
  `datetime_saisie` datetime DEFAULT NULL,
  `id_agent_user` int DEFAULT NULL,
  `id_station` int NOT NULL,
  `date_heure_ra` datetime NOT NULL,
  `id_eq_type` int DEFAULT NULL,
  `id_eq_ra` int DEFAULT NULL,
  `type_appareil` varchar(200) DEFAULT NULL,
  `num_appareil` varchar(200) DEFAULT NULL,
  `heure_appareil` time DEFAULT NULL,
  `plu_taille_auget` int DEFAULT NULL,
  `etat_ra` int DEFAULT '0',
  `hydro_heure_cote` time DEFAULT NULL,
  `hydro_h_sonde` float DEFAULT NULL,
  `hydro_h_echelle_1` float DEFAULT NULL,
  `hydro_h_echelle_2` float DEFAULT NULL,
  `hydro_num_sonde` varchar(200) DEFAULT NULL,
  `plu_tot_type` varchar(45) DEFAULT NULL,
  `plu_tot_first` int DEFAULT NULL,
  `plu_tot_last` int DEFAULT NULL,
  `plu_tot_heure_basc` time DEFAULT NULL,
  `plu_cumul_tot` int DEFAULT NULL,
  `plu_cumul_plu` int DEFAULT NULL,
  `plu_diff_tot_plu` int DEFAULT NULL,
  `plu_recalage_heure_plu` time DEFAULT NULL,
  `plu_test_auget` varchar(20) DEFAULT NULL,
  `plu_nb_basculement` int DEFAULT NULL,
  `nb_octet` varchar(200) DEFAULT NULL,
  `num_batterie` varchar(200) DEFAULT NULL,
  `tension_batterie` text,
  `num_cassette` varchar(200) DEFAULT NULL,
  `heure_init_cassette` time DEFAULT NULL,
  `hydro_h_sonde_cassette` float DEFAULT NULL,
  `plu_heure_bascul1_cassette` time DEFAULT NULL,
  `hydro_recalage_sonde` varchar(20) DEFAULT NULL,
  `hydro_recalage_heure_sonde` time DEFAULT NULL,
  `hydro_purge_sonde` int DEFAULT NULL,
  `hydro_ra_jaugeage` int DEFAULT NULL,
  `plu_ra_bouchage` int DEFAULT NULL,
  `plu_ra_huile_tot` int DEFAULT NULL,
  `ra_debroussaillage` int DEFAULT NULL,
  `ra_eau_batterie` int DEFAULT NULL,
  `ra_transfert_data` int DEFAULT NULL,
  `ra_delete_memory` int DEFAULT NULL,
  `ra_obs` text,
  `ra_futur` text,
  `name_file_data` varchar(200) DEFAULT NULL,
  `obs_file_data` text,
  `pre_marquant` int DEFAULT NULL,
  `fait_marquant` int DEFAULT NULL,
  `agents_complement` text,
  `piezo_toitnappesonde` float DEFAULT NULL,
  `piezo_conductivite` float DEFAULT NULL,
  `piezo_temperature` float DEFAULT NULL,
  `piezo_recalage_diff` float DEFAULT NULL,
  `piezo_recalage_sonde` varchar(45) DEFAULT NULL,
  `piezo_recalage_heure_sonde` time DEFAULT NULL,
  `piezo_nature_repere` text,
  `piezo_instrument` text,
  `piezo_num_instrument` text,
  `piezo_prof_toitnappe` float DEFAULT NULL,
  `piezo_prof_totale` float DEFAULT NULL,
  `piezo_x_terrain` float DEFAULT NULL,
  `piezo_y_terrain` float DEFAULT NULL,
  `piezo_gps_precision` text,
  `piezo_systeme_coord` text,
  `piezo_pompage_encours` int DEFAULT NULL,
  `piezo_pompage_proche` int DEFAULT NULL,
  `piezo_pluie_crue` int DEFAULT NULL,
  `piezo_temps_sec` int DEFAULT NULL,
  `piezo_photos` int DEFAULT NULL,
  PRIMARY KEY (`id_ra`),
  KEY `id_ra` (`id_ra`),
  KEY `id_station` (`id_station`)
) ENGINE=MyISAM AUTO_INCREMENT=55211 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_ra_piezo_profil`
--

DROP TABLE IF EXISTS `data_ra_piezo_profil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_ra_piezo_profil` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_ra` int DEFAULT NULL,
  `profondeur` float DEFAULT NULL,
  `conductivite` float DEFAULT NULL,
  `temperature` float DEFAULT NULL,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37567 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_ra_to_agent`
--

DROP TABLE IF EXISTS `data_ra_to_agent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_ra_to_agent` (
  `id_rta` int NOT NULL AUTO_INCREMENT,
  `id_ra` int NOT NULL,
  `id_agent` int NOT NULL,
  PRIMARY KEY (`id_rta`)
) ENGINE=MyISAM AUTO_INCREMENT=33938 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_tot`
--

DROP TABLE IF EXISTS `data_tot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_tot` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `date_heure` datetime NOT NULL,
  `valeurDebut` int DEFAULT NULL,
  `valeurFin` int DEFAULT NULL,
  `ecartPrecedent` int DEFAULT NULL,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `id_data_qualite` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_station` (`id_station`)
) ENGINE=InnoDB AUTO_INCREMENT=43322 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_type`
--

DROP TABLE IF EXISTS `data_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_type` (
  `id_data_type` int NOT NULL AUTO_INCREMENT,
  `init_type_data` varchar(100) DEFAULT NULL,
  `nom_type_data` varchar(250) DEFAULT NULL,
  `id_eq_type_data` int DEFAULT NULL,
  `axe_data` int DEFAULT NULL,
  `unite` varchar(45) DEFAULT NULL,
  `to_periode` int DEFAULT NULL,
  `id_chon_periode` int DEFAULT NULL,
  `traitement` int DEFAULT NULL,
  `type_graph` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_data_type`)
) ENGINE=MyISAM AUTO_INCREMENT=90 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `data_type_axe`
--

DROP TABLE IF EXISTS `data_type_axe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `data_type_axe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `axe` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `unite` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eq_helice`
--

DROP TABLE IF EXISTS `eq_helice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_helice` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `diametre` int DEFAULT NULL,
  `pas` float DEFAULT NULL,
  `l1` float DEFAULT NULL,
  `a1` float DEFAULT NULL,
  `b1` float DEFAULT NULL,
  `l2` float DEFAULT NULL,
  `a2` float DEFAULT NULL,
  `b2` float DEFAULT NULL,
  `a3` float DEFAULT NULL,
  `b3` float DEFAULT NULL,
  `fabricant` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eq_moulinet`
--

DROP TABLE IF EXISTS `eq_moulinet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_moulinet` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num` varchar(50) NOT NULL,
  `fabricant` text NOT NULL,
  `obs` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=279 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eq_saumon`
--

DROP TABLE IF EXISTS `eq_saumon`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_saumon` (
  `id` int NOT NULL AUTO_INCREMENT,
  `num` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `titre` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `poids` int DEFAULT NULL,
  `distance_axe` int DEFAULT NULL,
  `t_air` int DEFAULT NULL,
  `r_dist` int DEFAULT NULL,
  `fabricant` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eq_type`
--

DROP TABLE IF EXISTS `eq_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eq_type` (
  `id_eq_type` int NOT NULL AUTO_INCREMENT,
  `init_eq_type` varchar(45) DEFAULT NULL,
  `nom_eq_type` varchar(150) DEFAULT NULL,
  `unite_eq_type` varchar(10) DEFAULT NULL,
  `interval_eq_type` int DEFAULT '0',
  `valeur_data_type` int DEFAULT '1',
  `active_eq_type` int NOT NULL DEFAULT '0',
  `order_eq_type` int DEFAULT NULL,
  `type_color_border` varchar(30) DEFAULT NULL,
  `type_color_background` varchar(30) DEFAULT NULL,
  `type_graph` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id_eq_type`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `export_interval`
--

DROP TABLE IF EXISTS `export_interval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `export_interval` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` varchar(150) NOT NULL,
  `min` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_aquifere`
--

DROP TABLE IF EXISTS `geo_aquifere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_aquifere` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text,
  `description` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_commune`
--

DROP TABLE IF EXISTS `geo_commune`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_commune` (
  `id_commune` int NOT NULL AUTO_INCREMENT,
  `id_region` int NOT NULL,
  `nom_commune` varchar(70) NOT NULL,
  `id_territoire` int DEFAULT NULL,
  PRIMARY KEY (`id_commune`)
) ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_region`
--

DROP TABLE IF EXISTS `geo_region`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_region` (
  `id_region` int NOT NULL AUTO_INCREMENT,
  `nom_region` varchar(200) NOT NULL,
  `id_territoire` int NOT NULL,
  PRIMARY KEY (`id_region`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_regionhydro`
--

DROP TABLE IF EXISTS `geo_regionhydro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_regionhydro` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(250) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `id_territoire` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_riviere`
--

DROP TABLE IF EXISTS `geo_riviere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_riviere` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `id_regionhydro` int DEFAULT NULL,
  `id_territoire` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_territoire`
--

DROP TABLE IF EXISTS `geo_territoire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_territoire` (
  `id_territoire` int NOT NULL AUTO_INCREMENT,
  `nom_territoire` varchar(50) NOT NULL,
  `init_territoire` varchar(10) NOT NULL,
  `theme_region` varchar(30) NOT NULL,
  `region_default` int NOT NULL,
  `service_hydro` varchar(250) DEFAULT NULL,
  `color_service` varchar(45) DEFAULT NULL,
  `timezone_php` varchar(150) DEFAULT NULL,
  `lang` varchar(45) DEFAULT NULL,
  `mapLong` float DEFAULT NULL,
  `mapLat` float DEFAULT NULL,
  `mapZoom` int DEFAULT NULL,
  `mapMinZoom` int DEFAULT NULL,
  PRIMARY KEY (`id_territoire`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `geo_tournee`
--

DROP TABLE IF EXISTS `geo_tournee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `geo_tournee` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `id_territoire` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `import_files`
--

DROP TABLE IF EXISTS `import_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_files` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name_ext` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `multi_feuil` int DEFAULT NULL,
  `separateur` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `algo` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `valid` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `import_suivi`
--

DROP TABLE IF EXISTS `import_suivi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `import_suivi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_import` text,
  `file_import` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `file_ext` varchar(45) DEFAULT NULL,
  `dateheure` datetime DEFAULT NULL,
  `id_station` int DEFAULT NULL,
  `id_chron` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `nb_data` int DEFAULT NULL,
  `datetime_first` datetime DEFAULT NULL,
  `datetime_end` datetime DEFAULT NULL,
  `import` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2349 DEFAULT CHARSET=utf16;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `option_pastemps`
--

DROP TABLE IF EXISTS `option_pastemps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `option_pastemps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `interval_min` int DEFAULT NULL,
  `info` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station`
--

DROP TABLE IF EXISTS `station`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station` (
  `id_station` int NOT NULL AUTO_INCREMENT,
  `id_station_old` varchar(20) NOT NULL,
  `station_type` int DEFAULT NULL,
  `active_station` int NOT NULL,
  `suivi` int DEFAULT NULL,
  `armee` int DEFAULT NULL,
  `nom_station` varchar(150) NOT NULL,
  `nom_court` varchar(50) DEFAULT NULL,
  `code_station` varchar(50) NOT NULL,
  `num_irh` varchar(50) DEFAULT NULL,
  `id_territoire` int NOT NULL DEFAULT '0',
  `id_region` int DEFAULT '0',
  `id_commune` int DEFAULT '0',
  `id_regionhydro` int DEFAULT '0',
  `id_riviere` int DEFAULT '0',
  `id_aquifere` int DEFAULT NULL,
  `id_tournee` int DEFAULT '0',
  `site_station` varchar(200) DEFAULT NULL,
  `vallee_station` varchar(150) DEFAULT NULL,
  `riviere_station` varchar(50) DEFAULT NULL,
  `altitude_station` varchar(50) DEFAULT NULL,
  `orientation_station` varchar(40) NOT NULL,
  `latitude_station` varchar(50) NOT NULL,
  `longitude_station` varchar(50) NOT NULL,
  `utm_station_x` varchar(50) NOT NULL,
  `utm_station_y` varchar(50) NOT NULL,
  `ign_station_x` varchar(20) NOT NULL,
  `ign_station_y` varchar(20) NOT NULL,
  `lamb_station_x` varchar(20) NOT NULL,
  `lamb_station_y` varchar(20) NOT NULL,
  `date_installation_station` date DEFAULT NULL,
  `date_fermeture_station` date DEFAULT NULL,
  `description_station` text NOT NULL,
  `source_info` varchar(50) DEFAULT NULL,
  `proprio_station` varchar(200) NOT NULL,
  `transmission_station` varchar(10) DEFAULT NULL,
  `correct_station` int NOT NULL DEFAULT '0',
  `piezo_id_nature` int DEFAULT NULL,
  `piezo_sonde` int DEFAULT NULL,
  `piezo_precision` varchar(45) DEFAULT NULL,
  `piezo_maitre_ouvrage` text,
  `piezo_date_realisation` datetime DEFAULT NULL,
  `z_sol` float DEFAULT NULL,
  PRIMARY KEY (`id_station`),
  KEY `id_station` (`id_station`,`code_station`)
) ENGINE=MyISAM AUTO_INCREMENT=2240 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_access`
--

DROP TABLE IF EXISTS `station_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_access` (
  `id_station` int NOT NULL,
  `proprietaire` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `contact_nom` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `contact_phone` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `contact_mail` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `contact_adresse` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `contact_bp` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `contact_cp` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `contact_commune` int DEFAULT NULL,
  `info_access` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `pedestre_access` int DEFAULT NULL,
  `time_access` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `difficulty_access` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `remarque_access` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id_station`),
  UNIQUE KEY `id_station_UNIQUE` (`id_station`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_nature`
--

DROP TABLE IF EXISTS `station_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_nature` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_photos`
--

DROP TABLE IF EXISTS `station_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_photos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `date_photo` date DEFAULT NULL,
  `description_photo` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `file_photo` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_piezo_caracteristique`
--

DROP TABLE IF EXISTS `station_piezo_caracteristique`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_piezo_caracteristique` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `date` date DEFAULT NULL,
  `prof` float DEFAULT NULL,
  `materiaux_tete` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `dim_tete_ext` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `materiaux_tub_inter` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `diam_tub_inter` int DEFAULT NULL,
  `materiaux_dalle` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `dim_dalle` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `dist_capto_tube` float DEFAULT NULL,
  `dist_tube_dalle` float DEFAULT NULL,
  `dist_dalle_sol` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `presence_capot` int DEFAULT NULL,
  `etat` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `activite` int DEFAULT NULL,
  `utilisation` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `equipement_exploitation` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `schema_tete` int DEFAULT NULL,
  `schema_protect` int DEFAULT NULL,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1402 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_piezo_repere`
--

DROP TABLE IF EXISTS `station_piezo_repere`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_piezo_repere` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `nature_repere` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `code_repere` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `z_repere` float DEFAULT NULL,
  `precision_repere` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `date_debut_valid` date DEFAULT NULL,
  `date_fin_valid` date DEFAULT NULL,
  `nature_repere_1` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `z_repere_g1` float DEFAULT NULL,
  `nature_repere_2` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `z_repere_g2` float DEFAULT NULL,
  `id_schema` int DEFAULT NULL,
  `obs` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1455 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_piezo_schema`
--

DROP TABLE IF EXISTS `station_piezo_schema`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_piezo_schema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_schema` text,
  `img` text,
  `id_nature` int DEFAULT NULL,
  `capot` int DEFAULT '0',
  `dist_ct` text,
  `dist_td` text,
  `dist_ds` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=231 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_piezo_schema_to_nature`
--

DROP TABLE IF EXISTS `station_piezo_schema_to_nature`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_piezo_schema_to_nature` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_data_piezo_schema` int NOT NULL,
  `id_nature` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_proprietaire`
--

DROP TABLE IF EXISTS `station_proprietaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_proprietaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `libelle` text,
  `contact` varchar(105) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `station_to_tournee`
--

DROP TABLE IF EXISTS `station_to_tournee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `station_to_tournee` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_station` int DEFAULT NULL,
  `id_tournee` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tournee_periode`
--

DROP TABLE IF EXISTS `tournee_periode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tournee_periode` (
  `id` int NOT NULL AUTO_INCREMENT,
  `periode` text CHARACTER SET utf8mb3 COLLATE utf8mb3_bin,
  `nb_days` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-12 12:14:39
