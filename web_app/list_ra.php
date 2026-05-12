<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Affichage de la Liste des RA avec options de sélection
- Listes des RA
- On charge toutes les données du RA qui sont stocker sur la page à travers des champs formulaire cachés (hidden)
- Ces données seront récupérées au fur et à masure que l'onaffiche les fiches RA.
- Un fiche RA est par construction vide
- Les champs de la fiche sont remplis automatiquement à travers un script JS
- !!! c'est sans doute l'une des page de code les plus complexe !!!
----------------------------------------
*/

// Appel de la fonction de configuration générale
require('include/application_top.php');


// Initialisation des variables
$modif_ra = 0;
$id_ra_modif = 0;

$message_info = '';
$description_popup = '';
$row = 0;
$indice = 0;

$nb_ra = 0;
$nb_ra_valid = 0;


// Supprimer les RA


//---------------------------------------------------------------
// Récupération des champs de formulaires pour sélection

// Implémentation des variables transmises par formulaire 

// LIMIT DU NB DE LIGNES A AFFICHER
$limit_encours = 50;
$limit_ra = " LIMIT 0, ".$limit_encours;
if(isset($_POST['select_limit']))
{
	$limit_encours = $_POST['select_limit'];
	if($limit_encours > 0){$limit_ra = " LIMIT 0, ".$limit_encours;}
	else{$limit_ra = '';}
}

// SELECT POUR LE TRI
// TRI (Nom station, Code Station, Commune, Type_DATA)
$tri_encours = 1;
$tri = "ra.date_heure_ra"; // tri par défault est lié à la date du dernier passage
if(isset($_POST['select_tri']))
{
	$tri_encours = $_POST['select_tri'];
	if($_POST['select_tri'] == 1){$tri = "ra.date_heure_ra";} // tri nom station
    if($_POST['select_tri'] == 2){$tri = "s.nom_station";} // tri nom station
	if($_POST['select_tri'] == 3){$tri = "s.code_station";} // tri code station
	if($_POST['select_tri'] == 4){$tri = "s.station_type";} // tri type data (Pluie, Hydro, Piezo)
}

$tri_order_encours = 2;
$tri_order = " DESC,";
if(isset($_POST['order_tri']))
{
	$tri_order_encours = $_POST['order_tri'];
	if($_POST['order_tri'] == 1){$tri_order = " ASC,";} // tri croissant
	if($_POST['order_tri'] == 2){$tri_order = " DESC,";} // tri décroissant
}


// Initialisation des var. pour les filtres les plus commun
$affiche_select_type = true;
$affiche_select_tournee = true;
$affiche_search = true;
$affiche_select_riviere = false;
$affiche_select_station = true;
$affiche_select_statut_station = false;
require(DIR_WS_FILTRE . 'filtre_stations_var.php');

if(isset($_POST['search_station']))
{
	$search_station = post_secure($sql_link,$_POST['search_station']);
	$where_search = search_station($search_station,'');
}

// Période de données
$select_periode_encours = 60; // à changer en fonction de ce que l'on veut
if(isset($_POST['select_periode'])){$select_periode_encours = $_POST['select_periode'];}

$where_and_periode = " AND ra.date_heure_ra >= CURDATE() - INTERVAL ".$select_periode_encours." MONTH";		
if($select_periode_encours==0){$where_and_periode = '';}


//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

// TABLE AGENT - Appel pour stocker l'info dans un tableau
$sql_agent = "SELECT DISTINCT id, nom, prenom 
                FROM ".TABLE_AGENT." 
                WHERE terrain=1 
				ORDER BY nom ASC";
$agent_query = tep_db_query($sql_link,$sql_agent);
while($agent = tep_db_fetch_array($agent_query))
{
	$nom_agent =  strtoupper(html_entity_decode($agent['nom'] ?? $default_string));
	$prenom_agent =  htmlaccent(html_entity_decode($agent['prenom'] ?? $default_string));

	$agent_array[$agent['id']] = $prenom_agent." ".$nom_agent;
}

// --------------------------------------------------
// Pour l'enregitrement ou la suppression de RA 


// Enregistrer les RA
//if(isset($_POST['save_ra'])){require(DIR_WS_FORMULAIRE . 'ctrl_ra.php');}

// On récupère la clause WHERE de la recherche des RA et la clause ORDER
$where_ra = 's.id_territoire='.$territoire_id.
				$where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_type.$where_and_station.
				$where_and_tournee.
				$where_and_active.$where_and_suivi.$where_and_armee.$where_and_periode;
$order_ra = $tri.$tri_order;

// Pour récupérer le tri station pour affiner la liste des stations dans le bloc RA. 
$where_station = $where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_station;


// ---------------------------------------------------

// EDITION HTML

// Indication d'affichage de la page en HTML
require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

	echo "<div id='contenu_info' style='display:none;'></div>";

	require(DIR_WS_STRUCTURE . 'block_wait.php'); // Block d'attente pendant les interrogations au serveur
	require(DIR_WS_RA . 'block_ra_delete.php'); // Block pour permettre une confirmation de la suppression d'un RA
	require(DIR_WS_RA . 'block_ra.php'); // Block pour affichage d'une fiche RA en premier plan
	
	require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
	include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

	echo "<div id='contour_general'>";

		//echo "<div id='contour_affichage'>"; // ce cadre est le fond blanc lors de l'affiche de la box avec les RA

			echo "<div id='contenu_centre'>";
				
				//FORMULAIRE DE RECHERCHE
				
				echo "<div id='contenu_box2'>";
				
					// TITRE de la PAGE					
					echo "<h1>\n";
						echo "<span>".htmlaccent('Liste des Rapports d\'Activités - RA')."</span>\n";
					echo "</h1>\n";
					
		
					// ----------------------------------------------------------------------------------------
					// FORMULAIRE DE SELECTION - Cadre en-tête de la page
					// Ce bloc contient les champs formulaire en liste qui permettent de sélectionner les RA en fonction de différents critères

					// On met les formulaire ici pour enblober les bouton de recherche et les champs des RA dans le popup
					
					$lien_form = tep_href_link('list_ra.php');	
					$name_form = 'form_ra';					
					echo "<form name='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";

						echo "<div id='cadre_graph' style='float:left;width:258px;margin-right:15px;height:80vh;overflow-y: auto;'>\n"; 

							echo "<div id='boxpopup' class='select-top' style='width:92%;padding:10px 3%;margin-bottom:10px;'>\n";
							
								// Boutton pour Saisir de nouveaux RA
								$num_button = 0;
								if($select_type_encours > 0)
								{
									if($select_type_encours == 1)
									{
										echo "<div id='button_titre' style='margin-left:7%;' onClick='loadRA(0,1)'>";	// Pluvio
										echo htmlaccent('Nouveau RA - Pluvio');
										echo "</div>\n";	

										$num_button++;
									}
									if($select_type_encours == 11)
									{
										$marginbutton='';
										if($num_button>0){$marginbutton='margin-top:10px;';}

										echo "<div id='button_titre' style='margin-left:7%;".$marginbutton."' onClick='loadRA(0,11)'>";	// Hydro
											echo htmlaccent('Nouveau RA - Hydro');
										echo "</div>\n";	

										$num_button++;
									}
									if($select_type_encours == 5)
									{
										$marginbutton='';
										if($num_button>0){$marginbutton='margin-top:10px;';}

										echo "<div id='button_titre' style='margin-left:7%;".$marginbutton."' onClick='loadRA(0,5)'>";	// Hydro
											echo htmlaccent('Nouveau RA - Piézo');
										echo "</div>\n";	

										$num_button++;
									}	
								}
								else
								{
									if(isset($eq_type_array[1]))
									{
										echo "<div id='button_titre' style='margin-left:7%;' onClick='loadRA(0,1)'>";	// Pluvio
											echo htmlaccent('Nouveau RA - Pluvio');
										echo "</div>\n";

										$num_button++;
									}

									if(isset($eq_type_array[11]))
									{
										$marginbutton='';
										if($num_button>0){$marginbutton='margin-top:10px;';}

										echo "<div id='button_titre' style='margin-left:7%;".$marginbutton."' onClick='loadRA(0,11)'>";	// Hydro
											echo htmlaccent('Nouveau RA - Hydro');
										echo "</div>\n";

										$num_button++;
									}

									if(isset($eq_type_array[5]))
									{
										$marginbutton='';
										if($num_button>0){$marginbutton='margin-top:10px;';}

										echo "<div id='button_titre' style='margin-left:7%;".$marginbutton."' onClick='loadRA(0,5)'>";	// Hydro
											echo htmlaccent('Nouveau RA - Piézo');
										echo "</div>\n";

										$num_button++;
									}
								}
								
							echo "</div>";
							
							echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;'>\n";
						
								echo "<p style='float:left;width:26%;margin-top:15px;padding-top:5px;color: #609966;'>".htmlaccent('Période')."</p>";

								echo "<select name='select_periode' id='select_periode' onchange='".$name_form.".submit();' style='float:right;width:130px;margin-top:15px;'>";
									
									if($select_periode_encours==1){$selected="selected";}	
									else{$selected = '';}									
									echo "<option value='1' ".$selected.">".htmlaccent('1 mois')."</option>";
									
									if($select_periode_encours==3){$selected="selected";}	
									else{$selected = '';}									
									echo "<option value='3' ".$selected.">".htmlaccent('3 mois')."</option>";
									
									if($select_periode_encours==6){$selected="selected";}	
									else{$selected = '';}									
									echo "<option value='6' ".$selected.">".htmlaccent('6 mois')."</option>";
									
									if($select_periode_encours==12){$selected="selected";}	
									else{$selected = '';}									
									echo "<option value='12' ".$selected.">".htmlaccent('1 ans')."</option>";									
									
									if($select_periode_encours==24){$selected="selected";}	
									else{$selected = '';}	
									echo "<option value='24' ".$selected.">".htmlaccent('2 ans')."</option>";									
									
									if($select_periode_encours==60){$selected="selected";}	
									else{$selected = '';}									
									echo "<option value='60' ".$selected.">".htmlaccent('5 ans')."</option>";
										
									if($select_periode_encours==120){$selected="selected";}	
									else{$selected = '';}									
									echo "<option value='120' ".$selected.">".htmlaccent('10 ans')."</option>";

									if($select_periode_encours==0){$selected="selected";}	
									else{$selected = '';}									
									echo "<option value='0' ".$selected.">".htmlaccent('Toutes les données')."</option>";
																		
								echo "</select>";

								echo "<hr>\n";

								require(DIR_WS_FILTRE . 'filtre_stations_html.php');
								
								echo "<hr>";

								// TRI DE LA TABLE
								echo "<div style='width:100%;border-bottom:2px solid #176B87;margin-top:15px;'></div>";


								echo "<p style='float:left;width:auto;padding-top:5px;color:#186F65;margin-top:15px;'>".htmlaccent('TRIER PAR')."</p>";

								echo "<select name='select_tri' id='select_tri' onchange='".$name_form.".submit();' style='float:right;width:130px;margin-top:15px;'>";
									
									$selected = ($tri_encours == 1) ? "selected" : "";
									echo "<option value='1' ".$selected.">".htmlaccent('Date du dernier passage')."</option>";
									$selected = ($tri_encours == 2) ? "selected" : "";
									echo "<option value='2' ".$selected.">".htmlaccent('Nom de la station')."</option>";
									$selected = ($tri_encours == 3) ? "selected" : "";
									echo "<option value='3' ".$selected.">".htmlaccent('Code de la station')."</option>";
									$selected = ($tri_encours == 4) ? "selected" : "";
									echo "<option value='4' ".$selected.">".htmlaccent('Type de données')."</option>";
										
								echo "</select>";

								echo "<hr>";

								echo "<div style='float:right;'>";

									// Déterminer la valeur de l'attribut "checked" en fonction de $tri_order_encours
									$asc_checked = ($tri_order_encours == 1) ? "checked" : "";
									$desc_checked = ($tri_order_encours == 2) ? "checked" : "";

									echo "<p style='float:left;width:55px;padding-top:3px;'>".htmlaccent('Croissant')."</p>";
									echo "<input type='radio' id='asc' name='order_tri' value='1' style='float:left;' ".$asc_checked." onchange='".$name_form.".submit();' >";

									echo "<p style='float:left;width:65px;margin-left:10px;padding-top:3px;'>".htmlaccent('Décroissant')."</p>";
									echo "<input type='radio' id='desc' name='order_tri' value='2' style='float:left;' ".$desc_checked." onchange='".$name_form.".submit();' >";

								echo "</div>";

								echo "<hr>";

								// LIMITE DU NOMBRE DE LIGNE
								echo "<p style='float:left;width:auto;padding-top:5px;color:#186F65;margin-top:15px;'>".htmlaccent('NB DE LIGNES')."</p>";
								
									echo "<select name='select_limit' id='select_limit' onchange='".$name_form.".submit();' style='float:right;width:130px;margin-top:15px;'>";
											
										$selected = ($limit_encours == 50) ? "selected" : "";
										echo "<option value='50' ".$selected.">".htmlaccent('50 lignes')."</option>";
										$selected = ($limit_encours == 100) ? "selected" : "";
										echo "<option value='100' ".$selected.">".htmlaccent('100 lignes')."</option>";
										$selected = ($limit_encours == 200) ? "selected" : "";
										echo "<option value='200' ".$selected.">".htmlaccent('200 lignes')."</option>";
										$selected = ($limit_encours == 300) ? "selected" : "";
										echo "<option value='300' ".$selected.">".htmlaccent('300 lignes')."</option>";
										$selected = ($limit_encours == 0) ? "selected" : "";
										echo "<option value='0' ".$selected.">".htmlaccent('Toutes les lignes')."</option>";
											
									echo "</select>";
								
								// Affichage nombre de stations ; nbre stations activse ; nbre stations suivies - Cadre jaune
								echo "<div id='contenu_infos' >";
														
									echo "<p>";

										echo "<span style='font-size:12px;'>".htmlaccent('Nombre de RA à valider : ')."</span>";
										echo "<input type='text' id='nb_valid_ra_input' value='' readonly style='float:right;width:50px;padding:0;font-size:12px;background:none;border:none;'>";
										echo "<br><br>";
										echo "<span style='font-size:12px;'>".htmlaccent('Nombre total de RA : ')."</span>";
										echo "<input type='text' id='nb_ra_input' value='' readonly style='float:right;width:50px;padding:0;font-size:12px;background:none;border:none;'>";
										
									echo "</p>";

								echo "</div>";

								echo "<hr>";
									
							echo "</div>";	
						
						echo "</div>";
					
					echo "</form>";
					

					// FIN FORMULAIRE SELECTION	
					echo "<input type='hidden' value='".$modif_ra."' name='info_modif_ra' id='info_modif_ra'>"; // Premet de disposer des info sur la possibilité de modifier les RA
		
					// ----------------------------------------------------------------------------------------		
					// TABLEAU GENERAL RA - Permet d'afficher la liste des RA

					echo "<div id='result_listRa' class='table-container' style='float:none;width:auto;height:80vh;'>";

						echo "<div style='width:95%;height:78vh;overflow-y: auto;'>";
							echo "<table id='table_tri' cellspacing='0'>";
						
								// En-tête du tableau
								echo "<thead>";
									echo "<tr class='header-row'>";		
													
										echo "<th style='text-align:center;width:50px;'>".htmlaccent('Etat')."</th>";			
										echo "<th style='width:120px;padding-left:20px;'>".htmlaccent('Date Heure')."</th>";					
										echo "<th style='width:150px;'>".htmlaccent('Type de données')."</th>";	
										echo "<th style='width:120px;'>".htmlaccent('Code station')."</th>";							
										echo "<th style='width:280px;'>".htmlaccent('Nom de la station')."</th>";
										echo "<th style='width:120px;'>".htmlaccent('Commune')."</th>";
										echo "<th style='width:230px;'>".htmlaccent('Agents')."</th>";	
										echo "<th style='width:40px;text-align:center;'></th>";	
										
									echo "</tr>";

									echo "<tr>";
										echo "<td colspan='8' style='height:15px;'>&nbsp;</td>";
									echo "</tr>";
								echo "</thead>";		
								
								echo "<tbody>";
								echo "</tbody>";
						
							echo "</table>";
						echo "</div>";

					echo "</div>";

					echo "<div id='wait' style='width:100%;height:65px;margin-top:30px;text-align:center;'>";
						echo "<img src='".DIR_WS_IMG."hp100.gif' style='width:150px;' title='".htmlaccent('Chargement en cours ...')."'>";
						echo "<p style='text-align:center;color:#000;'>".htmlaccent('Chargement en cours ...')."</p>";
						echo "<p style='text-align:center;'>".htmlaccent('- Veuillez patienter -')."</p>";
					echo "</div>\n";  
					
				echo "<hr>";
				echo "</div>";
			
			echo "<hr>";
			echo "</div>";
			
		//echo "</div>";

	echo "<hr>";
	echo "</div>";

	// Pour afficher message
	// On le met à cet endroit pour que le message s'affiche au-dessus du bloc de saisie RA
	//if(tep_not_null($message_info)){echo "<div id='contenu_info'>".$message_info."</div><hr>";}


	// Pied de page				
	require('include/application_bottom.php'); 

echo "</body>";

echo "</html>";


?>	

<script>
	
	// Initialisation des variables
	var id_user = '<?php echo $id_user; ?>';

	var territoire_id = '<?php echo $territoire_id; ?>';
	var timezone_php = '<?php echo $timezone_php; ?>';
	var where_ra = '<?php echo $where_ra; ?>';
	var order_ra = '<?php echo $order_ra; ?>';
	var limit_ra = '<?php echo $limit_ra; ?>';
	
	var where_station = '<?php echo $where_station; ?>';

	var blockListRA = document.getElementById('result_listRa');
	var nbRa = document.getElementById('nb_ra_input');
	var nbValidRa = document.getElementById('nb_valid_ra_input');

	var boxWait = document.getElementById('box_wait'); // Attente lors des opérations sur les RA, occupe l'ensemble de la page
	var wait = document.getElementById('wait'); // Attente pour le cahrgement du tableau des RA, occupe uniquement le tableau de la liste des RA

	var box_ra = document.getElementById('box_ra');
	var box_ra_piezoprofil = document.getElementById('box_ra_piezoprofil');
	var tbody_info = document.querySelector("#table_tri tbody"); // Pour récupérer le contenu du tableau d'affichage des corrections en cours

	var boxDelRa = document.getElementById('box_del_ra');

	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info

	var ra_nav_json = null; // On initialise une variable générale pour récupérer ensuite le contenu du tableau dans loadRATab()


	// Fonction pour afficher la liste des RA sélectionnés par un appel vers le serveur
    function loadRATab()
    {
		return new Promise((resolve, reject) => {

			contenuInfo.style.display = 'none';
			blockListRA.style.display = 'none';
			wait.style.display = 'block';

			// Créer un objet JavaScript contenant les données à envoyer
			var dataToSend = {
								territoire_id: territoire_id,
								where_ra: where_ra,
								order_ra: order_ra,
								limit_ra: limit_ra
							};
			
			
			// Effectuer une requête AJAX asynchrone
			var xhr = new XMLHttpRequest();
			xhr.open("POST", "include/structure/ra/process_ra_tab.php", true);
			xhr.setRequestHeader("Content-Type", "application/json");

			xhr.onreadystatechange = function() 
			{
				if (xhr.readyState === 4 && xhr.status === 200) 
				{
					// Analyser la réponse JSON
					var jsonResponse = JSON.parse(xhr.responseText);

					nb_ra_valid = jsonResponse['nb_ra_valid'];
					nb_ra = jsonResponse['nb_ra'];

					nbValidRa.value = (nb_ra - nb_ra_valid);
					nbRa.value = nb_ra;

					tab_html = jsonResponse['tab_html'];				
					tbody_info.innerHTML = tab_html; // Ajoute la ligne dans le tableau des fichiers importables

					ra_nav_json = jsonResponse['ra_nav_json'];

					blockListRA.style.display = 'block';
					wait.style.display = 'none';
					
                    resolve(); // Résoudre la promesse après que tout soit fait (nécessaire pour lancer le processus de façon asynchron et s'assurer que les donneés sont chargées)
				}
			};

			// Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
			xhr.send(JSON.stringify(dataToSend));

		});
    }
	

	// ------------------------------------------

    loadRATab(); // On lance le chargement de la table RA
    // ------------------------------------------


	// Fonction pour afficher la liste des RA sélectionnés par un appel vers le serveur
	function loadRA(id_ra,id_type_ra)
    {
		contenuInfo.style.display = 'none';
		boxWait.style.display = 'block';

		var info_modif_ra = document.getElementById('info_modif_ra').value; // Information sur la possibilité d'enregistrer un RA

		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							territoire_id: territoire_id,
							timezone_php: timezone_php,
							id_user: id_user,
							id_ra: id_ra,
							where_station: where_station,
							check_modif: info_modif_ra,
							ra_nav_json: ra_nav_json
						};
		
        
        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        if(id_type_ra == 1){xhr.open("POST", "include/structure/ra/process_ra_plu_affiche.php", true);} // Pluvio
		if(id_type_ra == 5){xhr.open("POST", "include/structure/ra/process_ra_piezo_affiche.php", true);} // Piezo
		if(id_type_ra == 11){xhr.open("POST", "include/structure/ra/process_ra_hydro_affiche.php", true);} // Hydro
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
				// Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

                tab_html = jsonResponse['tab_html'];
                box_ra.innerHTML = tab_html; // On met à jour les champs du block RA

				// Ce script permet de mettre à jour en temps réel le graphique des profils en profondeur
				// On doit le mettre là pour que les inputs soient générés depuis le serveur
				if(id_type_ra == 5)
				{
					var inputs = document.querySelectorAll("input[name^='piezo_profil_prof_'], input[name^='piezo_profil_conduct_']");

					// Ajout de l'écouteur d'événement à tous les inputs sélectionnés pour mettre à jour instantannément le graphique des profils
					inputs.forEach(function(input) 
					{
						input.addEventListener('input', f_editgraph_profil);
					});
				}

				box_ra.style.display = 'block';
				boxWait.style.display = 'none';

				// Pour afficher ou non les bouttons d'enregistrement des RA
				// Ajouter un écouteur d'événements sur la checkbox
				document.getElementById('check_modif_ra').addEventListener('change', function () 
				{
					// Sélectionner l'élément avec la classe `.modif_ok`
					const popupNav = document.querySelector('.modif_ok');

					// Vérifier si la checkbox est cochée
					if (this.checked) {
						popupNav.style.display = 'block'; // Afficher le bloc
						document.getElementById('info_modif_ra').value = 1;
					} else {
						popupNav.style.display = 'none'; // Masquer le bloc
						document.getElementById('info_modif_ra').value = 0;
					}
				});

                				
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
    }

	// Fonction pour enregistrer un Ra
	function saveRA(event)
	{
		boxWait.style.display = 'block';

		event.preventDefault(); // Empêche la soumission par défaut du formulaire

		var form = document.getElementById('formRA'); // Récupérer l'élément du formulaire		
		var formData = new FormData(form); // Créer un objet FormData à partir du formulaire

		// Ajouter des données supplémentaires à envoyer
		formData.append('territoire_id', territoire_id);
		formData.append('id_user_agent', id_user);

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/ra/process_ra_save.php", true);

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				erreur = jsonResponse['erreur'];
				new_ra = jsonResponse['new_ra'];
				id_ra = jsonResponse['id_ra'];
				type_data = jsonResponse['type_data'];
				msg_info = jsonResponse['msg_info'];
				
				if(!erreur)
				{
					
					// Permet de s'assurer que le chargement de la fiche RA se fait après le rechargement de la table
					loadRATab().then(() => {
							loadRA(id_ra,type_data);
							
							contenuInfo.innerHTML = msg_info;							
							contenuInfo.style.display = 'block';

							contenuInfo.style.border = '4px solid #09886d'; // bordure en vert

					})
					/*
					if(new_ra)
					{
						// Permet de s'assurer que le chargement de la fiche RA se fait après le rechargement de la table
						loadRATab().then(() => {
							loadRA(id_ra,type_data);							
						})
					}
					else // pas besoin de recharger tous les Ra avant de lancer l'affichage du résultat de la correction
					{
						loadRA(id_ra,type_data);
					}
					*/
					
				}
				else
				{
					contenuInfo.innerHTML = msg_info;							
					contenuInfo.style.display = 'block';

					contenuInfo.style.border = '4px solid #930000'; // bordure en rouge

					boxWait.style.display = 'none';
				}

				//boxWait.style.display = 'none';
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(formData);
	}

	// Fonction pour affiche un popup de controle avec la suppression d'un RA
	function verifDelRA(id_ra)
	{
		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							id_ra: id_ra
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/ra/process_ra_verifdelete.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				tab_html = jsonResponse['tab_html'];

				boxDelRa.innerHTML = tab_html; // On met à jour les champs du block DelRA
				boxDelRa.style.display = 'block';								
            }
        };

		// Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
		xhr.send(JSON.stringify(dataToSend));
	}

	// Fonction pour supprimer un RA
	function delRA(id_ra)
	{
		// Créer un objet JavaScript contenant les données à envoyer
        var dataToSend = {
							id_ra: id_ra,
							id_user_agent: id_user
						};

		// Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/ra/process_ra_delete.php", true);
		xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
				// Permet de s'assurer que le chargement de la fiche RA se fait après le rechargement de la table
				loadRATab().then(() => {
							// Analyser la réponse JSON
							var jsonResponse = JSON.parse(xhr.responseText);

							boxDelRa.style.display = 'none';

							msg_info = jsonResponse['msg_info'];
							del = jsonResponse['del'];
								
							contenuInfo.innerHTML = msg_info;						
							contenuInfo.style.display = 'block';

							if(del){contenuInfo.style.border = '4px solid #09886d';}
							else{contenuInfo.style.border = '4px solid #930000';}

					})
             
				
            }
        };

        // Convertir l'objet JavaScript en format JSON et l'envoyer au serveur
        xhr.send(JSON.stringify(dataToSend));
	}


	// Fonction pour afficher la box profil et l'ancer l'édition du graphique
	function affiche_RA_piezoprofil()
	{
		document.getElementById('box_ra_piezoprofil').style.display='block';
		document.getElementById('box_ra_piezoprofil').style.zIndex='1700';

		//id_ra = document.getElementById('id_ra').value;

		f_editgraph_profil();
	}

	// Fonction pour édition interactive des graphs Piezo Profil dans RA
	function f_editgraph_profil(update = false)
	{
		var xData = [];
		var yData = [];
			
		for (let i = 1; i <= 15; i++) 
		{
			var profElement = document.getElementById('piezo_profil_prof_'+i);
			var conductElement = document.getElementById('piezo_profil_conduct_'+i);

			var profValue = 0;
			var conductValue = 0;

			if (profElement && profElement.value !== '') {
				profValue = (-1)*parseFloat(profElement.value);
			}

			if (conductElement && conductElement.value !== '') {
				conductValue = parseFloat(conductElement.value);
			}

			if((profValue !== 0) && (conductValue !== 0))
			{
				xData.push(conductValue);
				yData.push(profValue);
			}
		}

		var Xmax = Math.max(...xData);
		var Ymin = Math.min(...yData);
		
		var data_profil = 
		{ 
			x: xData,
			y: yData,    

			mode: 'markers+lines', // type de trace (scatter plot)
			type: 'scatter', // type de graphique
			marker: { size: 8}, // taille des marqueurs   
		};  

		// Pour l'édition du graphique
		var config = 
		{
			responsive: true,
			doubleClickDelay: 1000, //Delay du zoom
			
			displayModeBar: true, // Affichage constant du menu de la figure
			scrollZoom: false, // Zoom avec la roulette de la souris

			modeBarButtonsToRemove: ['select2d','lasso2d','autoScale2d','zoomIn2d','zoomOut2d'],
			modeBarOrientation: 'v',

			displaylogo: false
		};

		var layout_profil = 
		{
			xaxis: 
			{
				title: {
					text: 'Conductivité [&mu;S/cm]',
					standoff: 0 // Ajuster la distance entre le titre et l'axe
				},                
				tickfont: {size: 11}, // Taille des caractères des graduations
				titlefont: {family: 'roboto, arial, helvetica',
					size: 14,
					bold: true,
					color: '#000000'},
					
				tickangle: 0,
				ticklen: 5,
				showline: true,
				linewidth: 1,
				automargin: true,  
				//autorange: true, // Ajustement automatique de l'échelle de l'axe x			
				range: [0, (Xmax*1.1)], // Définir la plage de l'axe x
				side: 'top' // Placer l'axe x en haut du graphique
			},

			yaxis:
			{
				title: {
					text: 'Profondeur [m]',
					standoff: 0 // Ajuster la distance entre le titre et l'axe
				},
				tickfont: {size: 11}, // Taille des caractères des graduations
				titlefont: {family: 'roboto, arial, helvetica',
						size: 14,
						bold: true,
						color: '#000000'},
				tickformat: ',.1f',
				ticklen: 5,
				showline: true,
				linewidth: 1,
				automargin: true,
				//autorange: true, // Ajustement automatique de l'échelle de l'axe y
				range: [(Ymin*1.1),0], // Définir la plage de l'axe y
			},

			// Cette ligne permet de rapprocher le graphique de la ligne d'option en haut
			margin: {l: 80, r: 30, t: 65, b: 0}, // Par défault : l: 60, r: 60, t: 80, b: 60 
            
		};
		
		Plotly.newPlot('plot_profil', [data_profil], layout_profil, config);

	}

	// Fonction pour aider le calcul automatique de Hechelle - Hsonde
	function hydro_calcDiff()
	{
		let hydro_h_echelle_1 = document.getElementById('hydro_h_echelle_1');
		let hydro_h_sonde = document.getElementById('hydro_h_sonde');
		let hech_hsonde = document.getElementById('hech_hsonde');

		// Convertir les valeurs en nombres
		let valeurEchelle = parseFloat(hydro_h_echelle_1.value) || 0;
		let valeurSonde = parseFloat(hydro_h_sonde.value) || 0;

		// Vérifier que les valeurs sont bien des nombres
		if (isNaN(valeurEchelle) || isNaN(valeurSonde)) 
		{
			hech_hsonde.value = '';
			return;
		}
		hech_hsonde.value = valeurEchelle - valeurSonde;
	}




</script>