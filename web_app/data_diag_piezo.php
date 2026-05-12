<?php
/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
ETL - List (ETL : relation d'Etalonnage pour convertir les hauteurs d'eau en débit)
Cette page permet d'afficher la liste des stations Hydrométriques 
et de proposer un accès aux courbes d'ETL.

----------------------------------------
*/

require('include/application_top.php');

//$message_suprr_station = '';
$row = 0;

// -----------------------------
// Initialisation des Var.

$where_and_type = " AND s.station_type=5"; // Uniquement des stations piézométrique


// SELECT POUR LE TRI
// TRI (Nom station, Code Station, Commune, Type_DATA)
$tri_encours = 0;
$tri = "last_date_heure_ra";
if(isset($_POST['select_tri']))
{
	$tri_encours = $_POST['select_tri'];
	if($tri_encours == 1){$tri = "s.nom_station";} // tri nom station
	if($tri_encours == 2){$tri = "s.code_station";} // tri code station
}

$tri_order_encours = 2;
$tri_order = " DESC,";
if(isset($_POST['order_tri']))
{
	$tri_order_encours = $_POST['order_tri'];
	if($tri_order_encours == 1){$tri_order = " ASC,";} // tri décroissant
	if($tri_order_encours == 2){$tri_order = " DESC,";} // tri décroissant
}


// Initialisation des var. pour les filtres les plus commun
$affiche_select_type = false;
$affiche_select_tournee = true;
$affiche_search = true;
$affiche_select_riviere = false;
$affiche_select_station = false;
$affiche_select_statut_station = true;
require(DIR_WS_FILTRE . 'filtre_stations_var.php');

//---------------------------------------------------------------
// TABLE SQL - Recupération DATA

/* REQUETE SQL - DATA STATION */
$station_array = [];
$nb_station = 0;
$nb_station_active = 0;
$nb_station_suivi = 0;
$nb_station_armee = 0;

$sql_station = "SELECT s.id_station, s.id_commune, s.nom_station, s.code_station,
						s.active_station, s.suivi, s.armee,
						c.nom_commune,
						COUNT(DISTINCT pp.id_ra) AS nb_diag,
						MAX(r.date_heure_ra) AS last_date_heure_ra
				FROM
					".TABLE_STATION." s
				JOIN
					".TABLE_COMMUNE." c ON s.id_commune = c.id_commune
				JOIN
					".TABLE_DATA_RA." r ON r.id_station = s.id_station
				JOIN
					".TABLE_DATA_RA_PIEZO_PROFIL." pp ON pp.id_ra = r.id_ra
				WHERE s.id_territoire=".$territoire_id.
					$where_search.$where_and_regionhydro.$where_and_region.$where_and_commune.$where_and_riviere.$where_and_type.$where_and_tournee.
					$where_and_active.$where_and_suivi.$where_and_armee." 					
				GROUP BY
					s.id_station, s.id_commune, s.nom_station, s.code_station, s.active_station, s.suivi, s.armee, c.nom_commune
				ORDER BY 
					".$tri.$tri_order." s.active_station ASC, s.suivi DESC, s.armee ASC";

$station_query = tep_db_query($sql_link,$sql_station);
while ($station = tep_db_fetch_array($station_query))
{	
	$code_station =  htmlaccent(html_entity_decode($station['code_station'] ?? $default_string));
	$nom_station =  htmlaccent(html_entity_decode($station['nom_station'] ?? $default_string));
	$nom_commune =  html_entity_decode($station['nom_commune'] ?? $default_string);	
	$nb_diag = $station['nb_diag'];

	$last_date_heure_ra = $station['last_date_heure_ra'];
	$last_date = DateTime::createFromFormat('Y-m-d H:i:s', $last_date_heure_ra);
	$formatted_last_date = $last_date->format('d-m-Y');

	$active_station = 0;
	if($station['active_station'] == 1)
	{
		$active_station = 1;
		$nb_station_active++;
	}

	$suivi_station = 0;
	if($station['suivi'] == 1)
	{
		$suivi_station = 1;
		$nb_station_suivi++;
	}
	
	$armee_station = 0;
	if($station['armee'] == 1)
	{
		$armee_station = 1;
		$nb_station_armee++;
	}
		
	$station_array[$station['id_station']] = array('code_station' => $code_station,
													'nom_station' => $nom_station,
													'nom_commune' => $nom_commune,
													'nb_diag' => $nb_diag,
													'formatted_last_date' => $formatted_last_date,
													'active_station' => $active_station,							 
													'suivi_station' => $suivi_station,                             						 
													'armee_station' => $armee_station,
													);
}
$nb_stations = sizeof($station_array);	



//---------------------------------------------------------------
// EDITION HTML

require(DIR_WS_STRUCTURE . 'header_web.php');

echo "<body>";

	echo "<div id='contenu_info' style='display:none;'></div>";

	require(DIR_WS_DIAG . 'block_diag.php'); // Block pour affichage d'une fiche RA en premier plan	

	require(DIR_WS_STRUCTURE . 'header.php'); // Bando Haut
	include(DIR_WS_BOX . 'nav_accueil.php'); // Menu

	echo "<div id='contour_general'>";	

		echo "<div id='contenu_centre'>";
			
			echo "<div id='contenu_box2'>";
			
				echo "<h1>";
					echo "<span>".htmlaccent('Diagraphies comparées - Stations Piézométriques')."</span>";
				echo "</h1>";

				$lien_form = tep_href_link('data_diag_piezo.php');
				$name_form = 'form_select_diag';			
				echo "<form name='".$name_form."' id='".$name_form."' action='".$lien_form."' method='post' enctype='multipart/form-data'>";
					
					echo "<div id='cadre_graph' style='float:left;width:250px;margin-right:1%;height:75vh;overflow-y: auto;'>\n"; 

						echo "<div id='boxpopup' class='select-top' style='width:92%;padding:10px 3%;margin-bottom:10px;'>\n";

							echo "<div id='button_titre' style='margin-left:4%;' onclick='load_data_diag();'>\n";
								echo htmlaccent('Afficher les diagraphies');
							echo "</div>\n";

						echo "</div>";

					
						echo "<div id='boxpopup' class='select-top' style='width:92%;margin:0px;padding: 0 3%;padding-top:10px;'>\n";

							require(DIR_WS_FILTRE . 'filtre_stations_html.php');

							echo "<hr>";

							// TRI DE LA TABLE		
							echo "<div style='width:100%;border-bottom:2px solid #176B87;margin-top:15px;'></div>";
											
							echo "<p style='float:left;padding-top:5px;color:#186F65;margin-top:15px;'>".htmlaccent('TRIER PAR')."</p>";

							echo "<select name='select_tri' id='select_tri' onchange='".$name_form.".submit();' style='float:right;width:140px;margin-top:15px;'>";
								
								$selected = ($tri_encours == 1) ? "selected" : "";
								echo "<option value='1' ".$selected.">".htmlaccent('Nom de la station')."</option>";
								$selected = ($tri_encours == 2) ? "selected" : "";
								echo "<option value='2' ".$selected.">".htmlaccent('Code de la station')."</option>";
									
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

		
							echo "<div id='contenu_infos' style='width:auto;'>";
										
								echo "<p>";
									echo "<span>".htmlaccent('Nombre de stations : ').number_format($nb_stations,0,'.',' ')."</span>";
								echo "</p>";
							
							echo "</div>";

							echo "<hr>";
				
						echo "</div>";	
					
					echo "</div>";
				
				echo "</form>"; // Fin du formulaire
				
				
				// ----------------------------------------------------------------------------------------		
				// TABLEAU GENERAL STATIONS - Permet d'afficher la liste des Stations
				if(isset($station_array) && ($nb_stations>0))
				{
					echo "<div class='table-container' style='float:none;width:auto;height:75vh;'>";

						echo "<div style='width:95%;height:75vh;overflow-y: auto;'>";

							echo "<table id='table_tri' cellspacing='0'>";
						
								echo "<thead>";
									echo "<tr class='header-row'>";
																		
										echo "<th style='width:60px;text-align:center;' title=\"".htmlaccent('Active ou Historique (Fermée)')."\">".htmlaccent('Statut')."</th>";
										echo "<th style='width:60px;text-align:center;' title=\"".htmlaccent('Mesures en continue ou Mesures Ponctuelles')."\">".htmlaccent('Suivi')."</th>";	
										//echo "<th style='width:60px;text-align:center;padding-left:5px;' title=".htmlaccent('En fonctionnement ou en panne').">".htmlaccent('Etat')."</th>";	
										echo "<th style='width:80px;padding-left:15px;'>".htmlaccent('Code station')."</th>";
										echo "<th style='width:220px;padding-left:15px;'>".htmlaccent('Nom station')."</th>";		
										echo "<th style='width:120px;padding-left:15px;'>".htmlaccent('Commune')."</th>";							
										echo "<th style='width:80px;text-align:center;' title=\"".htmlaccent('Nombre de diagraphies')."\">".htmlaccent('Nb Diag.')."</th>";								
										echo "<th style='width:100px;text-align:center;' title=\"".htmlaccent('Date de la dernière diagraphie')."\">".htmlaccent('Dernière Diag.')."</th>";
										echo "<th style='width:80px;text-align:center;cursor:pointer;' 
												title=\"".htmlaccent('Sélectionner toutes les diagraphies')."\"
												onclick='toggleCheckboxes();'>";
											echo "<span class='selectAll'>".htmlaccent('Select +/-')."</span>";
										echo "</th>";

									echo "</tr>";
								echo "</thead>";
						
								//ligne vide dans le tableau		
									
								echo "<tr>";
									echo "<td colspan='6' style='height:10px;'>&nbsp;</td>";
								echo "</tr>";	
									
								foreach($station_array as $key => $value)
								{								
								
									if(fmod($row,2)==0){$row_l="class='row1' onmouseover=\"this.className='row1hover';\" onmouseout=\"this.className='row1';\" ";} 
									else{$row_l="class='row2' onmouseover=\"this.className='row2hover';\" onmouseout=\"this.className='row2';\" ";} 
																
									echo "<tr ".$row_l." >";
									
										//Statut Station
										echo "<td style='text-align:center;'>";
												
											if($value['active_station'] === 1){
												echo "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('En activité')."'>";
											}
											else{
												echo "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title='".htmlaccent('Historique (Fermée)')."'>";
											}

										echo "</td>\n";
										
										// Suivi Station
										echo "<td style='text-align:center;' >";

											if($value['suivi_station'] === 1){
												echo "<img src='".DIR_WS_IMG_ICO."puce_verte.png' style='width:12px;' title='".htmlaccent('Mesures en continu')."'>";									
											}
											else{
												echo "<img src='".DIR_WS_IMG_ICO."puce_rouge.png' style='width:12px;' title='".htmlaccent('Mesures ponctuelles')."'>";	
											}

										echo "</td>\n";
										
										echo "<td style='padding-left:15px;'>";
											echo $value['code_station'];
										echo "</td>\n";		
										
										echo "<td style='padding-left:15px;'>";
											echo affichelettres($value['nom_station'],50);
										echo "</td>\n";	
																		
										echo "<td style='padding-left:15px;' >".$value['nom_commune']."</td>\n";

										echo "<td style='text-align:center;' >".$value['nb_diag']."</td>\n";

										echo "<td style='text-align:center;' >".$value['formatted_last_date']."</td>\n";

										// Sélection des stations pour les diagraphies comparées
										echo "<td style='text-align:center;'>";

											echo "<input type='checkbox' name='check_station_diag[]' value='".$key."' >";

										echo "</td>\n";

									echo "</tr>\n";

									$row++;
								}

							echo "</table>";

						echo "</div>";

					echo "</div>";
				}
				else
				{
					echo "<div id='boxpopup' >\n";
							echo "<p class='alert'>".htmlaccent('Aucune Station n\'a été trouvée')."</p>";
					echo "</div>";
				}
				
			
				
			
			echo "<hr>";
			echo "</div>";
			
			
		echo "<hr>";
		echo "</div>";
		
		
		echo "<hr>";
	echo "</div>";

	require('include/application_bottom.php'); 

echo "</body>";

echo "</html>";

?>	


<script>

	// Paramétrage général
	idUser = <?php echo $id_user;?>;

	// Bouton des popups    
	contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info
	boxTabWait = document.getElementById('wait_tab');
	boxTab = document.getElementById('cadre_data_station_lgt');
	boxGraphWait = document.getElementById('wait_graph');
	boxPlot = document.getElementById('plot');
	boxDiag = document.getElementById('box_diag');

	//boxDiag.style.display = 'block';

	// Actions liées à l'affichage du tableau des diagraphies à afficher et du graphique des diagraphies
    
    // Lancement de la génération de tableau des diagraphies à sélectionner
    function load_data_diag()
    {
		// ETAPE 1 : Récupérer tous les checkboxes sélectionnés
		checkboxes_station_diag = document.querySelectorAll('input[name="check_station_diag[]"]');

		// Initialisez un tableau pour stocker les valeurs des cases cochées
		selectedStations = [];

		// Parcourez les cases à cocher
		checkboxes_station_diag.forEach(function(checkbox) 
		{
			// Vérifiez si la case à cocher est cochée
			if (checkbox.checked) 
			{
				// Ajoutez la valeur de la case cochée au tableau
				selectedStations.push(checkbox.value);
			}
		});

		if(selectedStations.length < 1)
		{
			contenuInfo.innerHTML  = "Aucune Diagraphie n'a été sélectionnée, le graphique ne peut être généré.";
			contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
			contenuInfo.style.display = 'block';
			return;
		}

		boxDiag.style.display = 'block';
		
		boxTabWait.style.display = 'block';
		boxTab.style.display = 'none';


		// Convertissez le tableau en une chaîne séparée par des virgules
		selectedStationString = selectedStations.join(',');

        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer

        var dataToSend = {
                            listStation: selectedStationString
                        };

        // Convertir l'objet en JSON
        var jsonDataTab = JSON.stringify(dataToSend);

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/diag/process_diag_tab.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {
                
                
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);
                
                // Accéder aux données récupéré coté serveur
                boxTab.innerHTML = jsonResponse['html_text'];

				
                boxTabWait.style.display = 'none';
				boxTab.style.display = 'block';
                
                load_graph_diag(); // On lance l'édition du graph quand la liste des ETL est affichée


            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataTab);  
    }

	// Lancement de la génération du graphique
    function load_graph_diag()
    {
		//Récupérer tous les checkboxes sélectionnés
		checkboxes_diag = document.querySelectorAll('input[name="check_diag[]"]');

		// Initialisez un tableau pour stocker les valeurs des cases cochées
		selectedDiag = [];

		// Parcourez les cases à cocher
		checkboxes_diag.forEach(function(checkbox) 
		{
			// Vérifiez si la case à cocher est cochée
			if (checkbox.checked) 
			{
				// Ajoutez la valeur de la case cochée au tableau
				nameCheckBox = checkbox.value;
				checkInfoDiag = nameCheckBox.split('_')[1];
				selectedDiag.push(checkInfoDiag);
			}
		});

		if(selectedDiag.length < 1)
		{
			contenuInfo.innerHTML  = "Aucune Diagraphie n'a été sélectionnée, le graphique ne peut être généré.";
			contenuInfo.style.border = '4px solid #930000'; // bordure en rouge
			contenuInfo.style.display = 'block';
			return;
		}

		boxPlot.style.display = 'none';
        boxGraphWait.style.display = 'block';


		// Convertissez le tableau en une chaîne séparée par des virgules
		selectedDiagString = selectedDiag.join(',');

        // Mise au format JSON des données
        // Créer un objet contenant les données à envoyer

        var dataToSend = {
                            listDiag: selectedDiagString
                        };

        // Convertir l'objet en JSON
        var jsonDataGraph = JSON.stringify(dataToSend);

        // Effectuer une requête AJAX asynchrone
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "include/structure/diag/process_diag_graph.php", true);
        xhr.setRequestHeader("Content-Type", "application/json");

        xhr.onreadystatechange = function() 
        {
            if (xhr.readyState === 4 && xhr.status === 200) 
            {                
                // Analyser la réponse JSON
                var jsonResponse = JSON.parse(xhr.responseText);

				boxPlot.style.display = 'block';
                boxGraphWait.style.display = 'none';

				eval(jsonResponse['js_graph']); // on récupère le script généré coté serveur pour afficher les graphiques

				Plotly.relayout('plot',{});

				//console.log(jsonResponse['js_graph']);
                
            }
        };

        // Envoyer les données JSON au serveur
        xhr.send(jsonDataGraph);  



	}

	// Dans le tableau initial, fonction permettant de selectionner toutes les stations contenant des diagraphies dans la liste proposée
	function toggleCheckboxes() 
	{
		// Sélectionnez toutes les cases à cocher avec le nom 'check_station_diag[]'
		var checkboxes = document.querySelectorAll("input[name='check_station_diag[]']");

		// Vérifiez si toutes les cases à cocher sont déjà cochées
		var allChecked = Array.from(checkboxes).every(function(checkbox) 
		{
			return checkbox.checked;
		});

		// Définissez toutes les cases à cocher sur le même état : toutes cochées ou toutes décochées
		checkboxes.forEach(function(checkbox) 
		{
			checkbox.checked = !allChecked;
		});
	}

	// ---------------------------------------------------------
	// ---------------------------------------------------------
	// Codes pour la partie d'affichage du graphique et du tableau associé

	// Code pour la sélection des diagraphies par station
	function checkboxDiagSelect() 
	{
		// Récupérer la case à cocher check_station_diac[] qui a été cliquée
		const stationCheckbox = event.target;
		const id_station = stationCheckbox.value;
		const isChecked = stationCheckbox.checked;

		// Sélectionner toutes les cases à cocher check_diag[] dont la valeur commence par id_station
		const relatedCheckboxes = document.querySelectorAll(`input[name='check_diag[]'][value^='${id_station}_']`);

		// Mettre à jour l'état de toutes les cases à cocher associées
		relatedCheckboxes.forEach(function(cb) {
			cb.checked = isChecked;
		});
	}

	// Code pour la dynamique des listes des stations et des diagraphies
	$(document).ready(function() 
	{
		$(document).on('click', '.toggle-diag', function()
		{
			// Trouver le contenu associé à ce titre		
			const id_user = <?php echo json_encode($id_user); ?>;
			const navdiag = $(this).nextAll('.navdiag').first();
			const menuId = $(this).data('menu-diag'); // Récupérer l'identifiant du menu
			const isOpen = navdiag.is(':visible');
			

			navdiag.slideToggle('slow', function() 
			{
				// Changer l'icône de la flèche
				const arrow = $(this).prevAll('.toggle-diag').find('.arrow');
				
				if (navdiag.is(':visible')) 
				{
					arrow.html('&#9650;'); // Flèche vers le haut
				} else 
				{
					arrow.html('&#9660;'); // Flèche vers le bas
				}			

			});
		});
	});




</script>	
