/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
Fonctions JS pour Remplir sans recharger la page les fiches RA, Agents. 
Il y a égaleemnt la fonction pour les formulaires des dates
*/

function calcul_jge(bras)
{
	var contenuInfo = document.getElementById('contenu_info'); // popup d'affichage d'info
	var nb_bras = document.getElementById('nb_bras');
	var champs_saisi = document.getElementById('select_saisie_' + bras);
	
	nettoyage_pts(bras);

	// Si il faut calculer les vitesses
	if(champs_saisi.value < 3){calc_vitesse_pts(bras);}

	calc_q(bras,nb_bras.value);
	f_editgraph_jge(bras);
}


// Fonction liée à l'édition des paramètres de l'équation liée aux hélices
function helice_eq(bras)
{
    // Récupération de la valeur du Select Helice après changement
    var selectElement = document.getElementById('select_helice_' + bras);
    var selectValue = selectElement.value;
    
    var l1Elt = document.getElementById('l1_' + selectValue);
    var a1Elt = document.getElementById('a1_' + selectValue);
    var b1Elt = document.getElementById('b1_' + selectValue);
    var l2Elt = document.getElementById('l2_' + selectValue);
    var a2Elt = document.getElementById('a2_' + selectValue);
    var b2Elt = document.getElementById('b2_' + selectValue);
    var a3Elt = document.getElementById('a3_' + selectValue);
    var b3Elt = document.getElementById('b3_' + selectValue);
    
    document.getElementById('l1_bras_' + bras).value = l1Elt.value;
    document.getElementById('l1_inf_bras_' + bras).value = l1Elt.value;
    document.getElementById('a1_bras_' + bras).value = a1Elt.value;
    document.getElementById('b1_bras_' + bras).value = b1Elt.value;    
    document.getElementById('l2_bras_' + bras).value = l2Elt.value;
    document.getElementById('l2_inf_bras_' + bras).value = l2Elt.value;     
    document.getElementById('a2_bras_' + bras).value = a2Elt.value;
    document.getElementById('b2_bras_' + bras).value = b2Elt.value;
    document.getElementById('a3_bras_' + bras).value = a3Elt.value;
    document.getElementById('b3_bras_' + bras).value = b3Elt.value;

    

    if((l2Elt.value > 0) && (l2Elt.value < 99.99)) 
		{
        $('.hidden_helice').css('visibility', 'visible');		
       document.getElementById('lsign_' + bras).value = '< n <=';
    }
    else
    {
        $('.hidden_helice').css('visibility', 'hidden');    
        document.getElementById('lsign_' + bras).value = '< n';        
        document.getElementById('l2_bras_' + bras).value = '';
    }
}


// Fonction pour nettoyer la saisie du JGE et trier les champs saisis pour que ce soit cohérent
function nettoyage_pts(bras)
{
	let numVert = 0;
	let distance = 0;
	let profMax = 0;

	let data = []; // Tableau pour stocker les données

	// Correction des saisies
	for (i = 0; i < 150; i++) 
	{
		// Récupération des éléments DOM avec vérification
		let numVertElement = document.getElementById('jge_bra_vert_'+ bras + '_' + i);
		let distElement = document.getElementById('jge_bra_dist_'+ bras + '_' + i);
		let profMaxElement = document.getElementById('jge_bra_profmax_'+ bras + '_' + i);
		let profMesureElement = document.getElementById('jge_bra_profmesure_'+ bras + '_' + i);
		let nbtourElement = document.getElementById('jge_bra_nbtour_'+ bras + '_' + i);
		let tpsElement = document.getElementById('jge_bra_tps_'+ bras + '_' + i);
		let vitesseElement = document.getElementById('jge_bra_vitesse_'+ bras + '_' + i);

		if (profMesureElement && profMesureElement.value !== '' && !isNaN(parseFloat(profMesureElement.value)))
		{
			let distElt = evalFloat(distElement.value, distance); // Distance par rapport à la berge
			if(distElt != distance)
			{
				numVert++;
				distance = distElt;				
			}	
			
			numVertElt = evalInt(numVertElement.value,numVert);
			numVert = numVertElt;
			
			profMesureElt = evalFloat(profMesureElement.value,0);

			profMaxElt = evalFloat(profMaxElement.value,profMax);
			profMax = profMaxElt;

			nbtourElt = evalInt(nbtourElement.value,'');
			tpsElt = evalInt(tpsElement.value,30);

			vitesseElt = evalFloat(vitesseElement.value,'');

			// Création d'un objet pour stocker les données de cette itération
            var rowData = 
			{
                numVert: numVertElt,
                distance: distElt,
                profMax: profMaxElt,
                profMesure: profMesureElt,
                nbTour: nbtourElt,
                tps: tpsElt,
				vitesse: vitesseElt
            };

            // Ajout de cet objet au tableau de données
            data.push(rowData);
		}
	}


	// Tri du tableau par distance puis par ProfMesure
	
    data.sort(function(a, b) 
	{
		let distanceA = parseFloat(a.distance); // Assure que 'distance' est bien un nombre
		let distanceB = parseFloat(b.distance);
		
		if (distanceA === distanceB) {
			let profMesureA = parseFloat(a.profMesure); // Assure que 'profMesure' est bien un nombre
			let profMesureB = parseFloat(b.profMesure);
			return profMesureB - profMesureA; // Tri décroissant par profMesure si les distances sont égales
		}
		return distanceA - distanceB; // Tri croissant par distance
	});


	// Affichage des saisies corrigées
	// Mettre à jour les éléments HTML avec les données triées	

    for(i = 0; i < data.length; i++) 
	{
		var numVertElement = document.getElementById('jge_bra_vert_'+ bras + '_' + i);
		var distElement = document.getElementById('jge_bra_dist_'+ bras + '_' + i);
		var profMaxElement = document.getElementById('jge_bra_profmax_'+ bras + '_' + i);
		var profMesureElement = document.getElementById('jge_bra_profmesure_'+ bras + '_' + i);
		var nbtourElement = document.getElementById('jge_bra_nbtour_'+ bras + '_' + i);
		var tpsElement = document.getElementById('jge_bra_tps_'+ bras + '_' + i);
		var vitesseElement = document.getElementById('jge_bra_vitesse_'+ bras + '_' + i);

		if(numVertElement)
		{
			numVertElement.value = data[i].numVert;
			distElement.value = data[i].distance;
			profMaxElement.value = data[i].profMax;
			profMesureElement.value = data[i].profMesure;
			nbtourElement.value = evalInt(data[i].nbTour,'');
			tpsElement.value = evalInt(data[i].tps,'');
			vitesseElement.value = evalFloat(data[i].vitesse,0);
		}
	}
}



// Fonction pour le calcul des vitesses à partir du nombre de tour et de l'équation de l'hélice
function calc_vitesse_pts(bras)
{		
	var champs_saisi = document.getElementById('select_saisie_' + bras);

	var numVert = 0;
	var distance = 0;
	var profMax = 0;

	var l1=0;var a1=0;var b1=0;var l2=0;var a2=0;var b2=0;var a3=0;var b3=0;
	var coefA=0;var coefB=0;
	
	// On récupère les paramètres d'équation liée à l'hélice en cours
	if(document.getElementById('l1_bras_' + bras).value != ''){l1 = parseFloat(document.getElementById('l1_bras_' + bras).value);}
	if(document.getElementById('a1_bras_' + bras).value != ''){a1 = parseFloat(document.getElementById('a1_bras_' + bras).value);}
	if(document.getElementById('b1_bras_' + bras).value != ''){b1 = parseFloat(document.getElementById('b1_bras_' + bras).value);}
	if(document.getElementById('l2_bras_' + bras).value != ''){l2 = parseFloat(document.getElementById('l2_bras_' + bras).value);}
	if(document.getElementById('a2_bras_' + bras).value != ''){a2 = parseFloat(document.getElementById('a2_bras_' + bras).value);}
	if(document.getElementById('b2_bras_' + bras).value != ''){b2 = parseFloat(document.getElementById('b2_bras_' + bras).value);}
	if(document.getElementById('a3_bras_' + bras).value != ''){a3 = parseFloat(document.getElementById('a3_bras_' + bras).value);}
	if(document.getElementById('b3_bras_' + bras).value != ''){b3 = parseFloat(document.getElementById('b3_bras_' + bras).value);}

	for (let i = 0; i < 150; i++) 
	{
		// Récupération des éléments DOM avec vérification
		let nbtourElement = document.getElementById('jge_bra_nbtour_'+ bras + '_' + i);
		let tpsElement = document.getElementById('jge_bra_tps_'+ bras + '_' + i);		
		let tourssecElement = document.getElementById('jge_bra_tourssec_'+ bras + '_' + i);
		let vitesseElement = document.getElementById('jge_bra_vitesse_'+ bras + '_' + i);
	
		let calcul = false;

		if(champs_saisi.value == 1) // Si l'utilisateur a choisi de rentrer le nb de tour d'hélice (et le temps)
		{					
			if(!isNaN(parseInt(nbtourElement.value)) && !isNaN(parseInt(tpsElement.value)))
			{
				//vitesseElt = evalFloat(vitesseElement.value,'');
				nbtourElt = evalFloat(nbtourElement.value,'');
				tpsElt = evalFloat(tpsElement.value,'');

				n = 0;
				if(nbtourElt > 0)
				{
					n = nbtourElt / tpsElt; // Nbre de tour par seconde					
					tourssecElement.value = n.toFixed(3);
				} 			
				calcul = true;
			}
		}

		if(champs_saisi.value == 2)
		{
			tourssecElt = evalFloat(tourssecElement.value,'');

			if(!isNaN(tourssecElement.value))
			{
				n = tourssecElt;
				calcul = true;
			}
		}
		if(calcul == true)
		{
			// -----------------------------------
			// Calcul de vitesse par points à partir de l'équation de l'hélice
			if(n > 0)
			{	
				if((l1 > 0) && (l1 < 99.99) && (n <= l1)) // Si eq 1
				{
					coefA = a1;
					coefB = b1;
				}
				else if ((l1 > 0) && (l1 < 99.99) && (l2 === 0 || (l2 > 0 && l2 < 99.99)) && (l1 < n))  // Si eq 2, même si l2 = 0
				{
					coefA = a2;
					coefB = b2;
				}
				else if((l2 > 0) && (l2 < 99.99) && (l2 < n)) // Si eq 3
				{
					coefA = a3;
					coefB = b3;
				}
				
				vitesseElt = (coefA * n + coefB).toFixed(3);	
			}
			else {vitesseElt = 0;}


			vitesseElement.value = vitesseElt
		}
		else
		{
			vitesseElement.value = '';
		}
	}
}

function calc_q(bras,nb_bras)
{
	let numVert = -1;
	let verticalTab = [];  // Tableau pour stocker les valeurs
	let ptsByVert = {};    // Tableau multidimensionnel pour stocker les points par numVert
	let count = 0;  
	let numPtsVert = 0;  // Variable count pour suivre l'index

	for (let i = 0; i < 150; i++) 
	{
		// Récupération des éléments DOM avec vérification
		let numVertElement = document.getElementById('jge_bra_vert_'+ bras + '_' + i);
		let distElement = document.getElementById('jge_bra_dist_'+ bras + '_' + i);
		let profMaxElement = document.getElementById('jge_bra_profmax_'+ bras + '_' + i);
		let profMesureElement = document.getElementById('jge_bra_profmesure_'+ bras + '_' + i);
		let vitesseElement = document.getElementById('jge_bra_vitesse_'+ bras + '_' + i);

		if(numVertElement)
		{	
			let currentNumVert = parseInt(numVertElement.value); // Conversion en nombre
			
			if(!isNaN(currentNumVert))
			{				
				if(currentNumVert != numVert)
				{
					numVert = currentNumVert;
					// Si numVert est rencontré pour la première fois, on l'initialise
					if (!ptsByVert[numVert]) 
					{
						ptsByVert[numVert] = [];  // Initialise un tableau vide pour ce numVert
					}
				
					verticalTab[numVert] = 
					{
						distance: parseFloat(distElement.value) || 0,  // Valeur par défaut si vide
						profMax: parseFloat(profMaxElement.value) || 0, // Valeur par défaut si vide
						count: 0
					};
				}

				// Ajoute les points mesurés pour ce numVert dans ptsByVert[numVert]
				ptsByVert[numVert].push(
				{
					profMesure: parseFloat(profMesureElement.value) || 0,  // Valeur par défaut si vide
					vitesse: parseFloat(vitesseElement.value) || 0  // Valeur par défaut si vide
				});
		
				// Mets à jour le nombre de points dans verticalTab[numVert]
				verticalTab[numVert].count = ptsByVert[numVert].length;				
			}
		}
	}

	// On parcours les verticales et les points dans chaque

	if(verticalTab.length > 0)
	{
		// Initialisation des variables
		let vSurfTot = 0; // Var. pour la vitesse moyenne en surface
		let vSurfPrec = 0;
		let profMoy = 0; // Var. pour la profondeur moyenne
		let profMaxPrec = 0;
		let surfaceMouillee = 0; // Var. Surface Mouillée
		let perimetreMouillee = 0; // Var. Surface Mouillée
		let largeurProfil = verticalTab[(verticalTab.length - 1)].distance; // largeur totale du profil 
		let distancePrec = 0;
		

		let debitTotal = 0; // Var. pour le débit
		let debitPrec = 0;

		for (let vert in verticalTab) 
		{		
			let vPts = new Array(2); // Initialiser les tableaux vPts et pPts
			let pPts = new Array(2);

			let aire = 0; //Variable qui défini l'aire sous la courbe de vitesse
			let vSurf = 0;
			let vFond = 0;
			let profMaxVert = verticalTab[vert].profMax; // profondeur max pour la verticale
			let distanceVert = verticalTab[vert].distance; // distance du points de départ pour la verticale

			let distanceCalc = distanceVert - distancePrec;
			distancePrec = distanceVert;

			
			
			// Tri du tableau par ordre décroissant de profMesure
			ptsByVert[numVert].sort(function(a, b) 
			{
				return b.profMesure - a.profMesure; // Tri décroissant
			});

			for (let i = 0; i < verticalTab[vert].count; i++) 
			{
				//console.log('vert :'+vert);		
				vCalc = ptsByVert[vert][i].vitesse;
				profCalc = profMaxVert - ptsByVert[vert][i].profMesure;

				//console.log('profMesure : '+ptsByVert[vert][i].profMesure);
				//console.log('profCalc : '+profCalc);

				if(verticalTab[vert].count == 1) // 1 seul point sur la vertical
				{
					
					vAvant = vCalc * 0.95; // coef constant pour calculer la vitesse de surface
					vActuel = vCalc;
					pAvant = 0;
					pActuel = profCalc;
					aire = (pActuel - pAvant) * (vActuel + vAvant) / 2;

					vAvant = vCalc;
					vActuel = vCalc * 0.7; // coef constant pour calculer la vitesse de fond
					pAvant = pActuel;
					pActuel = profMaxVert;
					
					aire = aire + (pActuel - pAvant) * ((vActuel + vAvant) / 2);

					vSurf = vCalc * 0.95;
					vfond = vCalc * 0.7;
				}

				if(verticalTab[vert].count > 1) // + de 1 point sur la verticale
				{
					// Gestion des 2 premiers points, incrémentation d'un tableau pour utiliser ensuite la fonction de regression linéaire
					if(i <= 1)
					{
						vPts[i] = vCalc;
						pPts[i] = profCalc; 
					}
					else
					{
						vPts[0] = vPts[1];
						pPts[0] = pPts[1]; 
						vPts[1] = vCalc;
						pPts[1] = profCalc; 
					}

					if(i == 1) // Second point
					{
						vTrend = linearTrend(vPts, pPts, 0); // Calcul par régression linéaire de la vitesse en surface
						vAvant = vTrend * 0.99;
						pAvant = 0;
						vActuel = vPts[0];
						pActuel = pPts[0];
						aire = aire + (pActuel - pAvant) * (vActuel + vAvant) / 2;

						vSurf = vTrend * 0.99;
					}

					// à partir du second points sauf si c'est le dernier
					if(i >= 1 && !(i == 2 && verticalTab[vert].count == 2))
					{
						vAvant = vActuel;
						vActuel = vPts[1];
						pAvant = pActuel;
						pActuel = pPts[1];
						aire = aire + (pActuel - pAvant) * (vActuel + vAvant) / 2;
					} 

					// Dernier points
					if(i == (verticalTab[vert].count - 1))
					{
						vAvant = vActuel;
						vTrend = linearTrend(vPts, pPts, profMaxVert); // Calcul par régression linéaire de la vitesse au fond
						vActuel = vTrend * 0.7;
						if(vActuel < 0){vActuel = 0;}
						pAvant = pActuel;
						pActuel = profMaxVert;
						aire = aire + (pActuel - pAvant) * (vActuel + vAvant) / 2;

						vfond = vTrend * 0.7;
					}
				}
					
				//console.log('aire :'+aire);			
			}

			// Calcul de la vitesse moyenne en surface
			vSurfTot = vSurfTot + distanceCalc * (vSurf + vSurfPrec) / 2;

			// Calcul de la profondeur moyenne
			profMoy = profMoy + distanceCalc * (profMaxVert + profMaxPrec) / 2;
			
			// Calcul de la surface mouillée (j'ai un doute)
			//surfaceMouillee = surfaceMouillee + distanceCalc * (profMaxVert + profMaxPrec) / 2;
			surfaceMouillee = surfaceMouillee + distanceCalc * profMaxVert;

			// Calcul du périmètre mouillée
			perimetreMouillee = perimetreMouillee + Math.sqrt(Math.pow(distanceCalc, 2) + Math.pow((profMaxVert - profMaxPrec), 2)); 
			

			// Calcul du débit
			debitTotal = debitTotal + distanceCalc * (aire + debitPrec) / 2;

			vSurfPrec = vSurf;
			profMaxPrec = profMaxVert;
			debitPrec = aire;
		}
		
		vSurfTot = vSurfTot / largeurProfil;
		profMoy = profMoy / largeurProfil;
		debitTotal = debitTotal * 1.02;
		vitesseMoy = debitTotal / surfaceMouillee;
		let rh = surfaceMouillee / perimetreMouillee;

		let h_ech_first = evalFloat(document.getElementById('h_ech_first_'+ bras).value, 0);
		let h_ech_end = evalFloat(document.getElementById('h_ech_end_'+ bras).value, 0);
		let hMoy_terrain = evalFloat((h_ech_first + h_ech_end) / 2, 0);
		

		document.getElementById('depouil_bras_q_'+ bras).value = debitTotal.toFixed(3);
		var sum_q = parseFloat(document.getElementById('depouil_bras_q_'+ bras).value);
		document.getElementById('depouil_bras_hmoy_'+ bras).value = hMoy_terrain.toFixed(0);
		if(bras == 1){document.getElementById('jge_hmoy').value = hMoy_terrain.toFixed(0);}
		
		document.getElementById('depouil_bras_vmoy_'+ bras).value = vitesseMoy.toFixed(3);
		document.getElementById('depouil_bras_vsurf_'+ bras).value = vSurfTot.toFixed(3);
		document.getElementById('depouil_bras_rh_'+ bras).value = rh.toFixed(3);

		document.getElementById('depouil_bras_surfmouil_'+ bras).value = surfaceMouillee.toFixed(3);
		document.getElementById('depouil_bras_perimouil_'+ bras).value = perimetreMouillee.toFixed(3);

		document.getElementById('depouil_bras_profmoy_'+ bras).value = profMoy.toFixed(3);
		document.getElementById('depouil_bras_distmax_'+ bras).value = largeurProfil.toFixed(2);

		// Calcul du débit global et de la hauteur globale
		
		for(b=1;b<=nb_bras;b++)
		{
			if(b != bras)
			{
				sum_q = sum_q + parseFloat(document.getElementById('depouil_bras_q_'+ b).value);
			}
		}
		document.getElementById('jge_q').value = sum_q.toFixed(3);
	}
	else
	{
		msg_info = "Erreur !!!";
		msg_info += "<br>Le calcul du Jaugeage n'a pas pû s'exécuter.";
		msg_info += "<br>Aucune donnée n'a été saisie.";

		contenuInfo.innerHTML = msg_info;							
		contenuInfo.style.display = 'block';
		contenuInfo.style.border = '4px solid #930000'; // bordure en rouge

	}
}



// Fonction pour édition interactive des graphs JGE
function f_editgraph_jge(bras=0)
{
	var xData = [0];
    var yData = [0];
    var vData = [0];
	var xDataT = [0];
    var yDataT = [0];

	var distValue = 0;
	var profMaxValue = 0;
		
	for (let i = 0; i < 150; i++) 
	{
		var distElement = document.getElementById('jge_bra_dist_'+ bras + '_' + i);
		var profmaxElement = document.getElementById('jge_bra_profmax_'+ bras + '_' + i);
		var profmesureElement = document.getElementById('jge_bra_profmesure_'+ bras + '_' + i);
		var nbtourElement = document.getElementById('jge_bra_nbtour_'+ bras + '_' + i);
		var vitesseElement = document.getElementById('jge_bra_vitesse_'+ bras + '_' + i);
		
		var profMesureValue = 0;

		// Distance du point de départ / Distance de la verticale
		if (distElement && distElement.value !== '') {
			distValue = parseFloat(distElement.value);
		}

		// Profondeur max de la verticale 
		if (profmaxElement && profmaxElement.value !== '') {
			profMaxValue = (-1)*parseFloat(profmaxElement.value);
		}

		// Profondeur de la mesure en cours
		if (profmesureElement && profmesureElement.value !== '') {
			profMesureValue = (-1)*(parseFloat(profmaxElement.value) - parseFloat(profmesureElement.value));
			vitesseElementValue = vitesseElement.value;
		}

		// Remplir le tableau pour le contour du jaugeage
		if((distValue !== 0)) // && (profMaxValue !== 0))
		{
			xDataT.push(distValue);
        	yDataT.push(profMaxValue);
		}

		// Remplir le tableau pour les points de jaugeages
		if((distValue !== 0)) // && (profMesureValue !== 0))
		{
			xData.push(distValue);
        	yData.push(profMesureValue);
        	vData.push(vitesseElementValue);
		}
	}

	var Xmax = Math.max(...xData);
	var Ymin = Math.min(...yData);
	var YminT = Math.min(...yDataT);

	var YminEch = Ymin;
	if(YminT < Ymin){YminEch = YminT;}
	
	// Tous les points de jaugeage
	var data_profil = 
	{ 
		hovermode: 'closest',
		x: xData,
		y: yData,    
		customdata: vData,

		name: 'Points du JGE',

		// Format d'étiquette des données au survol
		hovertemplate:  'Distance : %{x:.2f} m<br>' +  // Format pour x 
						'Profondeur : %{y:.2f} m<br>' +   // Format pour y 
						'Vitesse : %{customdata} m/s' , // Date

		mode: 'markers', // type de trace (scatter plot)
		type: 'scatter', // type de graphique
		marker: { size: 8}, // taille des marqueurs   
	};  

	// Définition de la deuxième série de données, les limites du jaugeage avec des points rouges
	var data_profil_2 = 
	{
		hovermode: 'closest',
		x: xData,
		y: yDataT,

		name: 'Profil du lit',

		// Format d'étiquette des données au survol
		hovertemplate:  'Dist. : %{x:.2f} m<br>' +  // Format pour x 
						'Prof. : %{y:.2f} m' ,   // Format pour y 

		mode: 'markers+lines',
		type: 'scatter',
		marker: { 
			size: 8,
			color: 'red' // Couleur des points rouges
		}
	};

	// Pour l'édition du graphique
    var config = 
    {
        responsive: true,
        doubleClickDelay: 1000, //Delay du zoom
        
        displaylogo: false,
		displayModeBar: true, // Affichage constant du menu de la figure
        scrollZoom: true, // Zoom avec la roulette de la souris
		modeBarOrientation: 'v',
    
        // Organisation personnalisée des boutons
		modeBarButtons: [
			[
				{
					name: 'Export SVG',
					icon: Plotly.Icons.disk,
					click: function(gd) {
						Plotly.downloadImage(gd, {format: 'svg', filename: 'mon_grap'});
					}
				},            
				'toImage',
				'zoom2d',
				'pan2d',
				'resetScale2d'
			]
		],

		modeBarButtonsToRemove: ['select2d', 'lasso2d', 'autoScale2d', 'zoomIn2d', 'zoomOut2d']
    };

    var layout_profil = 
    {
        xaxis: 
        {
            title: {
                text: 'Distance [m]',
                standoff: 5 // Ajuster la distance entre le titre et l'axe
            },
                                           
            tickfont: {size: 11}, // Taille des caractères des graduations
            titlefont: {family: 'roboto, arial, helvetica',
                size: 14,
                bold: true,
                color: '#000000'},
			
			tickformat: ',.2f',    
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
                standoff: 10 // Ajuster la distance entre le titre et l'axe
            },
            tickfont: {size: 11}, // Taille des caractères des graduations
			titlefont: {family: 'roboto, arial, helvetica',
                    size: 14,
                    bold: true,
                    color: '#000000'},
            
			tickformat: '.2f',
            ticklen: 5,
            showline: true,
            linewidth: 1,
			automargin: true,
			//autorange: true, // Ajustement automatique de l'échelle de l'axe y
			range: [(YminEch*1.3),0], // Définir la plage de l'axe y
        },

		hovermode: '',
		hoverlabel: { bgcolor: '#fff', font: { size: 12, color: '#000' } },
		cursor: 'pointer',
		margin: {l: 50, r: 10, t: 0, b: 40}, // Par défault : l: 60, r: 60, t: 80, b: 60
		showlegend: true,
		legend: 
		{
			x: 0,
			y: 0,
			orientation: 'v',
		},
    };

    Plotly.react('plot_jge_bras_'+bras, [data_profil,data_profil_2], layout_profil, config);
}