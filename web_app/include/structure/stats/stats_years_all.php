<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/
$tab_color = array('#8B0000','#FF8C00','#006400','#6495ED','#CD5C5C','#9932CC','#008B8B','#2E8B57','#008080','#B0C4DE','#FF8C00','#20B2AA');




$sql_an = "SELECT DISTINCT YEAR(date_first) as year FROM ".TABLE_IMPORT." UNION SELECT YEAR(date_end) FROM ".TABLE_IMPORT." ORDER BY year DESC";

$annee_query = tep_db_query($sql_link,$sql_an);
while ($annee_t = tep_db_fetch_array($annee_query))
{
	$tab_annee[] = $annee_t['year'];
}




$cumul_pluvio = 0;
$cumul_pluvio_tot = 0;
$max_pluvio = 0;
$nb_dayofrain = 0;
$year_first = 0;
$year_last = 0;
$year_encours = 0;
$month_encours = 0;




$sql_data = "SELECT DISTINCT YEAR(date_mesure) as year, MONTH(date_mesure) as month, SUM(qte_lacune) as qte_plu FROM ".TABLE_DATA_PLUVIO. " WHERE id_station='".$_POST['select_station']."' GROUP BY YEAR(date_mesure), MONTH(date_mesure)";
$data_query = tep_db_query($sql_link,$sql_data);	
while($data = tep_db_fetch_array($data_query))
{
	if($year_encours == 0){$year_first = $data['year'];$year_encours = $data['year'];$month_encours = $data['month'];}
	
	
	
	if($month_encours != $data['month'])
	{
		if($cumul_pluvio>$max_pluvio){$max_pluvio = $cumul_pluvio;}
		
		$data_array[$year_encours.'-'.$month_encours] = array('cumul_pluvio' => $cumul_pluvio,
							 								'nb_days' => $nb_dayofrain);
							 
		$cumul_pluvio = round($data['qte_plu'],1);
		$nb_dayofrain = 1;
		
		if($year_encours != $data['year']){$year_encours = $data['year'];}	
		$month_encours = $data['month'];			 
	}
	else
	{
		$cumul_pluvio += round($data['qte_plu'],1);
		$nb_dayofrain++;
	}
	
	$cumul_pluvio_tot += round($data['qte_plu'],1);
}
//pour enregistrer le dernier mois de la derrnière année
if($nb_dayofrain>0)
{
	$data_array[$year_encours.'-'.$month_encours] = array('cumul_pluvio' => $cumul_pluvio,
							 								'nb_days' => $nb_dayofrain);
}


//$max_cumul_pluvio = round($cumul_pluvio, -3);
$max_cumul_pluvio = $cumul_pluvio_tot;
$year_last = $year_encours;



// année pour graph comparatif
$yt=0;
for($yc=$year_first;$yc<=$year_last;$yc++)
{
	if(isset($_POST['check_year_'.$yc])){$tab_years_check[$yc] = 'checked';$yt++;}
	else{$tab_years_check[$yc] = '';}
}

if($yt==0)
{
	for($yc=$year_first;$yc<($year_last-2);$yc++){$tab_years_check[$yc] = '';}
	for($yt=($year_last-2);$yt<=$year_last;$yt++){$tab_years_check[$yt] = 'checked';}
}
				
?>		

<!--[if IE]><script type="text/javascript" src="../excanvas.min.js"></script><![endif]-->
<!-- BEGIN: load jquery -->
<script type="text/javascript" src="include/javascript/plot/jquery-1.4.2.min.js"></script>

<!-- END: load jquery -->

<!-- BEGIN: load jqplot -->
<script type="text/javascript" src="include/javascript/plot/jquery.jqplot.js"></script>
<script type="text/javascript" src="include/javascript/plot/plugins/jqplot.canvasAxisTickRenderer.js"></script>
<script type="text/javascript" src="include/javascript/plot/plugins/jqplot.dateAxisRenderer.js"></script>

<script type="text/javascript" src="include/javascript/plot/plugins/jqplot.cursor.js"></script>
<script type="text/javascript" src="include/javascript/plot/plugins/jqplot.highlighter.js"></script>	
		
				

<?php	
				
				
				
echo "<form name='stats_years_select' action='stats_result_years.php' method='post' enctype='multipart/form-data'>";

	echo "<input type='hidden' name='button_stats' id='button_stats' value='1'>";
	//echo "<input type='hidden' name='type_affiche' id='type_affiche' value='1'>";
	
	echo "<input type='hidden' name='select_region' id='select_region' value='".$_POST['select_region']."'>";
	echo "<input type='hidden' name='select_station' id='select_station' value='".$_POST['select_station']."'>";
	

 
echo "<h1><span>";
				
	echo "<span>".htmlaccent('Tableau de bord - Synthèse')."</span>";
	if($cumul_pluvio > 0)
	{
		echo "<img src='".DIR_WS_IMG_ICO."pdf.png' style='cursor:pointer;float:right;width:24px;' title='".htmlaccent('Réinitialiser')."' onclick=\"location.href='devis.php';\">\n"; 
		echo "<img src='".DIR_WS_IMG_ICO."excel.png' style='cursor:pointer;float:right;width:24px;margin-right:10px;' title='".htmlaccent('Visualiser')."' onClick=\"view_ajax();\">\n"; 
	}
echo "</h1>"; 	
	

echo "<div id='box_graph_all'>";

	echo "<div id='box_graph' class='lgt'>";
		require(DIR_WS_STRUCTURE . 'stats_box_info.php');
	echo "</div>";
	
	echo "<div id='box_graph' class='lgt_r'>";
	
		echo "<h8>".htmlaccent('Résumé')."</h8>";
		
		echo "<table id='resume' cellspacing='0'>";
			
			echo "<tr class='grey'>";
				echo "<td class='bold'>".htmlaccent('Années')."</td>";
				echo "<td style='text-align:right;'>".$year_first.htmlaccent(' à ').$year_last."</td>";
			echo "</tr>";
				
		echo "</table>";		
		
	echo "</div>";
	
	/*
	if($cumul_pluvio > 0)
	{
		
		echo "<div id='box_graph'>";
			
			echo "<h8>".htmlaccent('Pluviométrie journalière (hauteur de pluie en mm)')."</h8>";
							
			echo "<hr><hr><hr>";
				
			require(DIR_WS_STRUCTURE . 'stats_year_tab.php');
			
			echo "<div class='affiche'>";
				echo "<input type='submit' class='button' name='t1' id='t1' value='".htmlaccent('Afficher le tableau')."'>";
			echo "</div>";		
			
			
			
		echo "</div>";
	}
	*/
		
echo "<hr>";		
echo "</div>";



echo "<div id='box_graph_all'>";

	if($cumul_pluvio_tot > 0)
	{
		echo "<div id='box_graph' class='gd'>";
			
			echo "<h8>".htmlaccent('Pluviométrie - Cumul (hauteur de pluie en mm)')."</h8>";
							
			echo "<hr><hr><hr>";
				
			require(DIR_WS_STRUCTURE . 'stats_years_tab.php');
			
			echo "<div class='affiche'>";
				
			echo "</div>";		
			
		echo "</div>";
		
		
		echo "<div id='box_graph' class='gd'>";
			
			echo "<h8>".htmlaccent('Variations de la pluviométrie mensuelle (hauteur de pluie en mm)')."</h8>";
							
			echo "<hr><hr><hr>";
				
			echo "<div  class='jqPlot' id='chart1' style='height:100%;width:95%;margin-left:3%;'></div>";
			
			
			echo "<div class='affiche' style='height:100%;text-align:left;'>";
				
				echo "<ul>";
				
					$color=0;
					for($yc=$year_first;$yc<=$year_last;$yc++)
					{
						echo "<li>";
							$check ='';
							echo "<input type='checkbox' name='check_year_".$yc."' id='check_year_".$yc."' ".$tab_years_check[$yc]."> <span style='color:".$tab_color[$color]."'>".$yc."</span>";	
						
						echo "</li>";
						
						$color++;
					}
					
					echo "<li>";
						echo "<input type='submit' style='background:none;margin:0;' class='button' name='button_stats' value=\"Relancer\" />";
					echo "</li>";
					
				echo "<hr>";
				echo "</ul>";
			
				
				
			echo "</div>";	
				
			
		echo "</div>";
		
		
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

jQuery.noConflict();


 jQuery(document).ready(function(){
 jQuery.jqplot.config.enablePlugins = true;
  
  
  <?php 
  //for($y=1;$y<=4;$y++)
  //$tab_years_check[]
  //for($y=0;$y<sizeof($tab_years_check);$y++)
  $size_yc=0;
  for($y=$year_first;$y<=$year_last;$y++)
  {	
	if(tep_not_null($tab_years_check[$y]))
	{
		$size_yc++;
		
		echo "var l".$size_yc." = [";
	  
			for($m=1;$m<=12;$m++)
			{
				if(isset($data_array[$y.'-'.$m]))
				{
					//echo "['".${'year_'.$y}.'-'.($m+1)."',".$data_array[${'year_'.$y}.'-'.($m+1)]['cumul_pluvio']."]";
					echo "['2000/".$m."/01 00:00:00',".$data_array[$y.'-'.$m]['cumul_pluvio']."]";
				}
				else
				{
					//echo "['".${'year_'.$y}.'-'.($m+1)."',0]";
					echo "['2000/".$m."/01 00:00:00',0]";
				}
				
				if($m!=12){echo ",";}	
			}
			
		echo "];";
		
  	}
  }
  ?>
  
  


  plot1a = jQuery.jqplot('chart1', [<?php  $size_y=0;for($y=$year_first;$y<=$year_last;$y++){if(tep_not_null($tab_years_check[$y])){$size_y++;echo 'l'.$size_y;if($size_y<$size_yc){echo ",";}}} ?>], 
  { 
  		 legend: {
			  show: false	,
			  location: 'ne',     // compass direction, nw, n, ne, e, se, s, sw, w.
			  xoffset: 0,        // pixel offset of the legend box from the x (or x2) axis.
			  yoffset: 0,        // pixel offset of the legend box from the y (or y2) axis.
			  border: false
		  },
		  
		  series: [
		  <?php
		  	$color=0;
			$color_v=0;
			for($y=$year_first;$y<=$year_last;$y++)
			{
				if(tep_not_null($tab_years_check[$y]))
				{
					echo "{label : '".$y."',";
					echo "color:'".$tab_color[$color]."',lineWidth: 2,shadow: false,shadowWidth: 2,shadowOffset: 1,shadowDepth: 8}";
				
					if($color_v<$size_yc){echo ",";}
					$color_v++;
				}
				
				$color++;
			}
		  
		  ?>
		 
		  ], 
		  grid: { 
			  background:'#ffffff',
			  // turn off the border.  Note, should set
			  // borderColor as well due to way axes shares color
			  // defaults with the grid.
			  borderWidth:0,
			  borderColor:'#0892e1',
			  
			  shadow:false
		  },
		  seriesDefaults: {
			  fill: false,
			  showMarker: false
		  },
		  axes: { 
			  xaxis: {  
				renderer: jQuery.jqplot.DateAxisRenderer,
				
				tickInterval: "1 month",
				
				min: "2000/01/01",
				max: "2000/12/01",
				
				tickOptions: {
				
					showGridline: false,
					showMark: false,
					whiteSpace: "nowrap",
					formatString: '%b'
				
				}
				 
			 },
			 yaxis: {
			   min: 0,
			   max: <?php echo $max_pluvio;?>,
			   tickOptions: {
					formatString: '%d' //arrondi sans virgule
				},
				tickInterval:<?php echo $max_pluvio/2;?>  // intervalle
			 
			 }
		  }, 
		  cursor:{
			  show:true,
			  zoom:true,
			  style: 'default',
			  showTooltip: false
		  
		  },
		  highlighter: {
			  bringSeriesToFront:true
		  }
		  
		  /*
		  $("#chart1").resizable({delay:20});
		  $('#chart1').bind('resize', function(event, ui) {        plot1.replot();    }); 
		  */
  	});
	
	
  
  
});
  
  
  
</script>


	
