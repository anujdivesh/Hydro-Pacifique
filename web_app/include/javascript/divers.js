/*  
----------------------------------------
Copyright (c) 2024 - Vai-Natura
----------------------------------------
*/


function chemin_file_split()
{			
	file_info = document.getElementById('file_data_import').value; 
	file_info_array = file_info.split('\\'); 
	
	document.getElementById('file_info').value = file_info_array[file_info_array.length-1];
}


function check_all(id_first,field)
{		
    nb_id = id_first+field;
	
	for (id=id_first; id<=(nb_id); id++)
	{
		if(document.getElementById('check_del_'+id)){document.getElementById('check_del_'+id).checked=true;}
	}
}



function check_none(id_first,field)
{		
    nb_id = id_first+field;
	
	for (id=id_first; id<=(nb_id); id++)
	{
		if(document.getElementById('check_del_'+id)){document.getElementById('check_del_'+id).checked=false;}
	}
}

// Fonction pour évaluer une expression et renvoyer une valeur si l'expression est nulle
function evalInt(valeurChamp, valeurReturn) 
{
	if (valeurChamp === null || isNaN(valeurChamp) || valeurChamp === '') 
	{
		return valeurReturn; // Assigner 1 si la valeur est nulle, non numérique ou une chaîne vide
	} else {
		const parsedValue = parseInt(valeurChamp);
		return !isNaN(parsedValue) ? parsedValue : valeurReturn; // Affecter la valeur convertie, sinon affecter 1
	}
}

// Fonction pour évaluer une expression et renvoyer une valeur si l'expression est nulle
function evalFloat(valeurChamp, valeurReturn) 
{
	if (valeurChamp == null || isNaN(valeurChamp) || valeurChamp == '') 
	{
		return valeurReturn; // Assigner 1 si la valeur est nulle, non numérique ou une chaîne vide
	} else {
		const parsedValue = parseFloat(valeurChamp);
		return !isNaN(parsedValue) ? parsedValue : valeurReturn; // Affecter la valeur convertie, sinon affecter 1
	}
}

// Fonction pour intégré un séparateur de millier pour les nombre
function formatNumberThousandsSeparator(number) 
{
    // Convertir le nombre en chaîne de caractères
    var numberString = number.toString();
    
    // Séparer les parties entières et décimales
    var parts = numberString.split('.');
    var integerPart = parts[0];
    var decimalPart = parts.length > 1 ? '.' + parts[1] : '';

    // Insérer les séparateurs de milliers dans la partie entière
    var formattedIntegerPart = '';
    for (var i = integerPart.length - 1, j = 0; i >= 0; i--, j++) 
	{
        formattedIntegerPart = integerPart.charAt(i) + formattedIntegerPart;
        if (j % 3 === 2 && i > 0) 
		{
            formattedIntegerPart = ' ' + formattedIntegerPart;
        }
    }

    // Concaténer la partie entière et décimale
    return formattedIntegerPart + decimalPart;
}



// Fonction pour la régression linéaire en JS (équivalente de Trend dans VB)
function linearTrend(knownYs, knownXs, newXs) 
{
    const n = knownYs.length;

    // Calcul des sommes nécessaires
    const sumX = knownXs.reduce((sum, value) => sum + value, 0);
    const sumY = knownYs.reduce((sum, value) => sum + value, 0);
    const sumXY = knownXs.reduce((sum, value, index) => sum + value * knownYs[index], 0);
    const sumX2 = knownXs.reduce((sum, value) => sum + value * value, 0);

    // Calcul de la pente (m) et de l'ordonnée à l'origine (b)
    const a = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
    const b = (sumY - a * sumX) / n;

    // Prévisions pour les nouvelles valeurs de x
	newY = a * newXs + b;
    return newY;
}