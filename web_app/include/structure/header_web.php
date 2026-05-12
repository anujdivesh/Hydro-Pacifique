<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
En-tête HTML présent sur chaque page générée
----------------------------------------
*/

header('Pragma: no-cache');
header('Cache-Control: no-cache');

//<!DOCTYPE html>
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Style-Type" content="text/css" />

<?php echo "<title>".TITRE_SITE_ADMIN."</title>"; ?>


<meta name="DC.Creator" content="vai-natura.com"/>
<meta http-equiv="X-UA-Compatible" content="IE=8" />
<meta name="DC.Date.created" scheme="W3CDTF" content="2023"/>

<link rel="shortcut icon" type="images/x-icon" href="image/ico.png" />
<link rel="stylesheet" type="text/css" href="css/general.css" media="print, screen" />


<!-- Chargement de fichier JS contenant toutes les fonctions nécessaire et les librairies -->

<script type="text/javascript" src="include/javascript/ctrl_supp.js"></script>
<script type="text/javascript" src="include/javascript/quality_pass.js"></script>
<script type="text/javascript" src="include/javascript/calendar.js"></script>

<script type="text/javascript" src="include/javascript/jquery.js"></script>
<!-- librairie graph Plotly -->
<script type="text/javascript" src="include/javascript/plotly-2.20.0.min.js"></script>

	
<script type="text/javascript" src="include/javascript/select.js"></script> <!-- -->
<script type="text/javascript" src="include/javascript/divers.js"></script> <!-- -->
<script type="text/javascript" src="include/javascript/formlink.js"></script> <!-- Appel du script pourTransmettre des données à une page par proctocole FORM -->
<!-- <script type="text/javascript" src="include/javascript/js_ra.js"></script> --> <!-- Appel du script de Fonctions liées aux RA -->
<script type="text/javascript" src="include/javascript/js_jge.js"></script> <!-- Appel du script de Fonctions liées aux JGE -->
<script type="text/javascript" src="include/javascript/js_jge_simple.js"></script> <!-- Appel du script de Fonctions liées aux JGE simple (saisie hauteur débit) -->
<script type="text/javascript" src="include/javascript/onglets-dym.js"></script>


<!-- AJAX -->

<script type="text/javascript" src="include/javascript/ajax/pass.js"></script>
<script type="text/javascript" src="include/javascript/ajax/auto_select.js"></script>



<script type="text/javascript">
		
    /*    
	document.addEventListener("DOMContentLoaded", function() 
	{
        // Sélectionner tous les liens avec target="_blank"
        const links = document.querySelectorAll('a[target="_blank"]');

        // Parcourir chaque lien et supprimer l'attribut target="_blank"
        links.forEach(function(link) {
            link.removeAttribute('target');
        });
    });
    */
	
</script>



</head>
