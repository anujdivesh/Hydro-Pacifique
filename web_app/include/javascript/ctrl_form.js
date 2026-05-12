/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/

var text_info='';



function testDateValide(saisie) 
{
	if (saisie == "") return false;
	
	saisie = (saisie).split("/");
	
	if ((saisie.length != 3) || isNaN(parseInt(saisie[0])) || isNaN(parseInt(saisie[1])) || isNaN(parseInt(saisie[2]))) return false;
	
	var laDate = new Date(eval(saisie[2]),eval(saisie[1])-1,eval(saisie[0]));
	var annee = laDate.getYear();
	
	if ((Math.abs(annee)+"").length < 4) annee = annee + 1900;
	
	return ((laDate.getDate() == eval(saisie[0])) && (laDate.getMonth() == eval(saisie[1])-1) && (annee == eval(saisie[2])));
}
