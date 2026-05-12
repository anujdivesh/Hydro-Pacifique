<?php
/*  
----------------------------------------
Copyright (c) 2011 - Vai-Natura
----------------------------------------
*/
$f_y = 0; 
$l_y = 0;

$sql_an = "SELECT DISTINCT YEAR(date_first) as year FROM ".TABLE_IMPORT." UNION SELECT YEAR(date_end) FROM ".TABLE_IMPORT." WHERE id_station='".$select_station."' ORDER BY year DESC";
$annee_query = tep_db_query($sql_link,$sql_an);
while ($annee_t = tep_db_fetch_array($annee_query))
{
	if($f_y == 0){$f_y = $annee_t['year'];}
	$l_y = $annee_t['year'];
}
for($i=$f_y;$i>=$l_y;$i--){$tab_annee[]=$i;}


$intervalle_type = 'd';

if(isset($_GET['date_p1']) && isset($_GET['date_p2']))
{
	$date_p1 = $_GET['date_p1'];
	$date_p2 = $_GET['date_p2'];
	
	switch($periode)
	{
		case 1 :
		  $titre = htmlaccent('Journalière');
		  $intervalle_type = 'h';
		  break;
	  case 2 :
		  $titre = htmlaccent('Mensuelle');
		  break;
	  case 3 :
		  $titre = htmlaccent('Annuelle');
		  $year_tab = explode('-',$date_p1);
		  $year_p1 = $year_tab[0];
		  break;
	  case 4 :
		  $titre = htmlaccent('Personnalisée');
		  break;
	}
}
else
{
	switch($periode)
	{
	  case 1 :
		  $titre = htmlaccent('Journalière');
		  
		  $day_p1 = $_POST['day_stats'];
		  if($day_p1<10){$day_p1='0'.$day_p1;}
		  $month_p1 = $_POST['month_day'];
		  $year_p1 = $_POST['annee_d'];
		  $date_p1 = $year_p1.'-'.$month_p1.'-'.$day_p1;
		  $date_p2 = $date_p1;
		  
		  $intervalle_type = 'h';
		  break;
	  case 2 :
		  $titre = htmlaccent('Mensuelle');
		  
		  $month_p1 = $_POST['month_stats'];
		  $year_p1 = $_POST['annee_m'];
		  $date_p1 = $year_p1.'-'.$month_p1.'-01';
		  $date_p2 = $year_p1.'-'.$month_p1.'-31';
		  
		  break;
	  case 3 :
		  $titre = htmlaccent('Annuelle');
		  
		  $year_p1 = $_POST['select_year'];
		  
		  $date_p1 = $year_p1.'-01-01';
		  $date_p2 = $year_p1.'-12-31';
		  
		  break;
	  case 4 :
		  $titre = htmlaccent('Personnalisée');
		  
		  $day_p1 = $_POST['day_p1'];
		  if($day_p1<10){$day_p1='0'.$day_p1;}
		  $month_p1 = $_POST['month_p1'];
		  $year_p1 = $_POST['year_p1'];
		  $date_p1 = $year_p1.'-'.$month_p1.'-'.$day_p1;
		  
		  $day_p2 = $_POST['day_p2'];
		  if($day_p2<10){$day_p2='0'.$day_p2;}
		  $month_p2 = $_POST['month_p2'];
		  $year_p2 = $_POST['year_p2'];
		  $date_p2 = $year_p2.'-'.$month_p2.'-'.$day_p2;
		  
		  break;
	}
}


if($intervalle_type=='d')
{
	//$data_limni = getDatesBetween($date_p1, $date_p2,$type_data);
	$groupby = 'date_mesure';
}
if($intervalle_type=='h')
{
	for($h=0;$h<=24;$h++)
	{
		if($h<10){$h='0'.$h;}
		$date_day = $date_p1." ".$h.":00:00";
		$data_limni[$date_day] = array('heure_m' => $h.':00:00','hauteur' => 0,'hauteur_lac' => 0);	
		$groupby = 'HOUR(heure_mesure)';
	}
}

// initialisation des variables
$min_limni = 0;
$max_limni = 0;
$date_max = '';
$date_min = '';
$nb_data = 0;
$tot_limni_all = 0;
$moy_limni_all = 0;
$string_graph_1 = '';
$string_html_limni = '';
$edition = '';

//$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, HOUR(heure_mesure) as heure_m, date_heure_mesure, AVG(qte) as hauteur, AVG(qte_lacune) as hauteur_lac, lacune FROM ".TABLE_DATA_LIMNI. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 24:00:00' GROUP BY ".$groupby;

$sql_data = "SELECT DISTINCT date_mesure, heure_mesure, qte as hauteur, qte_lacune as hauteur_lac FROM ".TABLE_DATA_LIMNI. " WHERE id_station='".$select_station."' AND date_heure_mesure>='".$date_p1." 00:00:00' AND date_heure_mesure<='".$date_p2." 24:00:00'";
$data_query = tep_db_query($sql_link,$sql_data);	
while($data = tep_db_fetch_array($data_query))
{
	$date_mesure = $data['date_mesure'];
	$heure_mesure = $data['heure_mesure'];
	//$heure_m = $data['heure_m'];
	//if($heure_m<10){$heure_m='0'.$heure_m;}
	//$date_heure_mesure = $data['date_mesure'].' '.$heure_m.':00:00';
	$date_heure_mesure = $data['date_mesure'].' '.$heure_mesure;
	
	//if($intervalle_type=='d'){$data_limni[$date_mesure] = array('heure_m' => '00:00:00','hauteur' => $data['hauteur'],'hauteur_lac' => $data['hauteur_lac']);}
	//if($intervalle_type=='d'){$data_limni[$date_heure_mesure] = array('heure_m' => '00:00:00','hauteur' => $data['hauteur'],'hauteur_lac' => $data['hauteur_lac']);}
	//if($intervalle_type=='h'){$data_limni[$date_heure_mesure] = array('heure_m' => $heure_m.':00:00','hauteur' => $data['hauteur'],'hauteur_lac' => $data['hauteur_lac']);}
	
	$data_limni[$date_heure_mesure] = array('hauteur' => $data['hauteur'],'hauteur_lac' => $data['hauteur_lac']);
	
	
	if($data['hauteur_lac'] > $max_limni){$max_limni = $data['hauteur_lac'];$date_max = dateus_fr($date_mesure);}
	if($nb_data==0){$min_limni=$max_limni;$date_min = dateus_fr($date_mesure);}
	
	if($data['hauteur_lac'] < $min_limni){$min_limni = $data['hauteur_lac'];$date_min = dateus_fr($date_mesure);}

	$tot_limni_all += $data['hauteur_lac']; 
	
	$nb_data++;
	
}


if($nb_data>0)
{
	$moy_limni_all = $tot_limni_all/$nb_data; 
	$round_limni = 0;
	$round_limni_lac = 0;
	
	foreach($data_limni as $cle => $element)
	{
		if(round($element['hauteur'],0)!=0){$round_limni = round($element['hauteur'],0);}
		if(round($element['hauteur_lac'],0)!=0){$round_limni_lac = round($element['hauteur_lac'],0);}
		
		$cle_time = strtotime($cle)*1000;
		$string_graph_1 .= "[".$cle_time.",".$round_limni_lac."],";
		
		// data limni
		$string_html_limni .= "<tr>";
		//if($intervalle_type=='d'){$string_html_limni .= "<td>".dateus_fr($cle)."</td>";}
		//if($intervalle_type=='h'){$string_html_limni .= "<td>".$element['heure_m']."</td>";}
		$string_html_limni .= "<td>".dateus_fr($cle)."</td>";
		$string_html_limni .= "<td>".$round_limni."</td>";
		//$string_html_limni .= "<td>".$round_limni_lac."</td>";
		$string_html_limni .= "</tr>";
	}
	$string_graph_1 = substr($string_graph_1, 0, -1);
	
}	

$min_date = $date_p1.' 00:00:00';
$max_date = $date_p2.' 24:00:00';


$lacune_load=false;


?>		

<script type="text/javascript" src="include/javascript/flotr/lib/prototype-1.6.0.3.js"></script>
<!--[if IE]>
	<script type="text/javascript" src="include/javascript/flotr/lib/excanvas.js"></script>
	<script type="text/javascript" src="include/javascript/flotr/lib/base64.js"></script>
<![endif]-->
<script type="text/javascript" src="include/javascript/flotr/lib/canvas2image.js"></script>
<script type="text/javascript" src="include/javascript/flotr/lib/canvastext.js"></script>
<script type="text/javascript" src="include/javascript/flotr/flotr.js"></script>
		
				

<?php

echo "<form name='stats_select' action='stats_result.php' method='post' enctype='multipart/form-data'>";

	echo "<input type='hidden' name='button_stats' id='button_stats' value='1'>";
	
	
	echo "<input type='hidden' name='select_region' id='select_region' value='".$select_region."'>";
	echo "<input type='hidden' name='select_station' id='select_station' value='".$select_station."'>";
	echo "<input type='hidden' name='select_periode' id='select_periode' value='".$periode."'>";
	echo "<input type='hidden' name='select_type_eq' id='select_type_eq' value='2'>";
	//echo "<input type='hidden' name='select_year' id='select_year' value='".$year_stats."'>";
	

 
//echo "<h1 style='background-color:#eaf7ea;'>";
echo "<h1>";
				
	$titre = htmlaccent('Tableau de bord - Limnimètre - Synthèse ').$titre;
	$titre_p = htmlaccent($titre)." - Station : ".$nom_station;
	
	if($print){echo "<span class='print'>".$titre_p."</span>";}
	else{echo "<span>".$titre."</span>";}
	
	if($nb_data > 0  && !$print)
	{
		echo button_pdf('export_pdf.php?type=stats&ty=limni&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		echo button_xls('export_excel.php?imp=2&ty=limni&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&date_p1='.$date_p1.'&date_p2='.$date_p2);
		echo button_print('print_stats.php?imp=2&ty=limni&print=ok&bs=1&il='.$select_region.'&st='.$select_station.'&periode='.$periode.'&eq=2&date_p1='.$date_p1.'&date_p2='.$date_p2,'Tableau');
	}
	
echo "</h1>";
echo "<hr>";	
	

echo "<div id='box_graph_all'>";

	echo "<div id='box_graph' class='lgt'>";
		
		require(DIR_WS_STATS . 'stats_box_info.php');
	
	echo "</div>";
	
	echo "<div id='box_graph' class='lgt_r'>";
	
		echo "<h8>".htmlaccent('Résumé')."</h8>";
		
		echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr class='grey'>";
				echo "<td class='bold'>".htmlaccent('Période')."</td>";
				echo "<td style='text-align:right;'>du ".dateus_fr($date_p1)." au ".dateus_fr($date_p2)."</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td class='bold'>".htmlaccent('Hauteur d\'eau moyenne (en cm)')."</td>";
				echo "<td style='text-align:right;'>".round($moy_limni_all,1)."</td>";
			echo "</tr>";
			
			echo "<tr class='grey'>";
				if($periode==1){echo "<td class='bold'>".htmlaccent('Nombre d\'heures d\'enregistrement')."</td>";}
				else{echo "<td class='bold'>".htmlaccent('Nombre de jours d\'enregistrement')."</td>";}
				echo "<td style='text-align:right;'>".$nb_data."</td>";
			echo "</tr>";
			
			echo "<tr>";
				echo "<td class='bold'>".htmlaccent('Hauteur d\'eau moyenne max (en cm)')." - ".$date_max."</td>";
				echo "<td style='text-align:right;'>".round($max_limni,1)."</td>";
			echo "</tr>";
			
			echo "<tr class='grey'>";
				echo "<td class='bold'>".htmlaccent('Hauteur d\'eau moyenne min (en cm)')." - ".$date_min."</td>";
				echo "<td style='text-align:right;'>".round($min_limni,1)."</td>";
			echo "</tr>";
				
		echo "</table>";
		
	echo "</div>";
	
	if(!$print){require(DIR_WS_STATS . 'stats_box_form.php');}
	
		
echo "</div>";



echo "<div id='box_graph_all'>";

	if($nb_data > 0)
	{
		echo "<div id='box_graph' class='gd'>";
			
			echo "<h8>".htmlaccent('Limnimètre (hauteur moyenne journalière en cm)')."</h8>";
							
			echo "<hr><hr><hr>";
			
				echo "<div id='graph_onglet'>";
						
					echo "<div id='contenu-0' class='contenu'>";
						//echo "<div  class='jqPlot' id='chart1' style='height:100%;width:95%;margin-left:3%;'></div>";
						echo "<div id='container' style='width:95%;height:400px;margin-left:3%;'></div>";
					echo "</div>";
				
					if(!$print)
					{
						echo "<div id='contenu-1' class='contenu' >";
							echo affiche_plu_data($string_html_limni);
						echo "</div>";
						
						
						echo "<ul id='graph_menu_onglet'>";
						
							echo "<li id='onglet-0' class='none'>".htmlaccent('Graph')."</li>\n";
							echo "<li id='onglet-1' class='none'>".htmlaccent('Data')."</li>\n";
											
						echo "</ul>";
					
					
						echo "<div class='zoom'>";
							
							 echo "<p id='zoommoins'>";	
								echo "<img src='".DIR_WS_IMG_ICO."zoommoins.png' title='".htmlaccent('zoom -')."'>\n"; 
								echo "<span>".htmlaccent('Revenir au graphique complet')."</span>";
							echo "</p>";
								
							echo "</br>";
							
							echo "<form name='image-download' action='' onsubmit='return false'>";
								echo "<p onclick='f.saveImage(getV(this.form.format))'>";	
									echo "<img src='".DIR_WS_IMG_ICO."download_png.png' title='".htmlaccent('Download PNG')."'>\n"; 
									echo "<button name='download' onclick='f1.saveImage(getV(this.form.format))'>".htmlaccent('Enregistrer le graphique')."</button>";
								echo "</p>";
							echo "</form>";	
							
						echo "</div>";	
					}
					
				echo "</div>";		
		
		echo "</div>";
		
		
		//require(DIR_WS_STRUCTURE . 'stats_limni_year_tab.php');
		//if($periode == 3){require(DIR_WS_STATS_LIMNI . 'stats_limni_tab.php');echo $edition;}
		
		//require(DIR_WS_STRUCTURE . 'box_list_lacunes.php');
		
	}
	else
	{
		echo "<div id='box_graph'>";
			
			echo "<h6>".htmlaccent('Aucune donnée n\'a été trouvée pour cette station, pour cette période')."</h6>";
							
			
		echo "</div>";
	}

echo "</div>";

echo "</form >\n";

?>


<script id="source" type="text/javascript">

function getV(nl) 
{
	var v = null;
	$A(nl).each(function(e) {
	  if (e.checked) {
	    v = e.value;
	      return;
	    }
	});
	return v;
}



var f;
			
document.observe('dom:loaded', 

function()
{
	// DATA
	<?php echo "var d1 = [".$string_graph_1."];";?>
				
	// OPTIONS			
	var options = {
					 'shadowSize': 0, 
					 'HtmlText': false, 
					 'fontSize': 12,
					 'legend': {'show': false}, 
					 'xaxis': {'title': 'Date', 'showLabels': true, 'mode': 'time', 'timeFormat': '%d-%m-%y','noTicks': 5},
					 'yaxis': {'showLabels': true, 'trackDecimals': 0, 'min': 0, 'max': <?php echo $max_limni+10; ?>}, 
					 'lines': {'show': true, 'lineWidth': 1, 'fill': true, 'fillOpacity': 0.2}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'tickColor': '#cccccc', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 0}, 
					 'selection': {'mode': 'xy', 'color': '#865fb9'}, 
					 'crosshair': {'mode': 'x', 'color': '#FF0000', 'hideCursor': true}, 
					 'mouse': {'track': true, 'trackAll': true, 'position': 'nw', 'relative': true, 'margin': 20,'trackDecimals': 0,'trackFormatter':function dateTracker(obj){
																																									  var dateToDisplay = new Date(parseInt(obj.x)); 
																																									  var fullYear = dateToDisplay.getFullYear();
																																									  var month = dateToDisplay.getMonth()+1;
																																									  if(month<10){month='0'+month;}
																																									  var date = dateToDisplay.getDate();
																																									  if(date<10){date='0'+date;}
																																									  var heures = dateToDisplay.getHours();
																																									  if(heures<10){heures='0'+heures;}
																																									  var minutes = dateToDisplay.getMinutes();
																																									  if(minutes<10){minutes='0'+minutes;}
																																									  var sec = dateToDisplay.getSeconds();
																																									  if(sec<10){sec='0'+sec;}
																																									  return 'Hauteur d‘eau (en cm): ' + obj.y + ' cm<br>Date: '+date+'-'+month+'-'+fullYear+' '+heures+':'+minutes+':'+sec;  
																																								  }
					 
								} 
					
				  
					}
  
	
	f = Flotr.draw($('container'),[{data:d1, color:'#007300'}],Object.extend(Object.clone(options)));
	 
	 
	function drawGraph(opts){
		
		var o = Object.extend(Object.clone(options), opts || {});
		
		return f = Flotr.draw(
			$('container'), 
			[{data:d1, color:'#007300'}],
			o
		);
	}	
	
	
	// ZOOM
	$('container').observe('flotr:select', function(evt){
	
		var area = evt.memo[0];
		
		f = drawGraph({
			xaxis: {min:area.x1, max:area.x2,'noTicks': 5,'mode': 'time', 'timeFormat': '%d-%m-%y'},
			yaxis: {min:area.y1, max:area.y2+10,'trackDecimals': 0}
		});
	});
	
	
	
	$('zoommoins').observe('click', function(){drawGraph()});
	
	if (Prototype.Browser.IE) 
	{
		var form = $(document.forms['image-download']);
		form.disable().insert({top: "Your browser doesn't allow you to use this feature, sorry :(<br />"});
	}
	
});



  
</script>
