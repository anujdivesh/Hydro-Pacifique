<?php
/*  
----------------------------------------
Copyright (c) 2015 - Vai-Natura
----------------------------------------
*/


$echelle_debit_min=-round($debit_max*10/100,1);


echo "<div id='onglet_contenu'>\n";
	
	echo "<div id='boite1' class='first' style='margin-right:20px;'>\n";
		
		 echo "<div id='box_graph_all'>";
		 
		 	echo "<div id='box_graph' class='lgt'>";
		  
				require(DIR_WS_STATS . 'stats_box_info.php');
				  
			echo "</div>";
			  
			// ----------------------
			
			echo "<div id='box_graph' class='lgt_r'>";
	
				echo "<h8>".htmlaccent('Résumé')."</h8>";
				
				echo "<table id='resume' cellspacing='0'>";
					
					echo "<tr class='grey'>";
						echo "<td class='bold'>".htmlaccent('Période de mesure')."</td>";
						if($date_min == ''){echo "<td style='text-align:right;'>-</td>";}
						else{echo "<td style='text-align:right;'>du ".$date_min." au ".$date_max."</td>";}
					echo "</tr>";
					
					echo "<tr>";
						echo "<td class='bold'>".htmlaccent('Débit Moyen')."</td>";
						echo "<td style='text-align:right;'>".round($debit_mean,2)."</td>";
					echo "</tr>";
					
					echo "<tr class='grey'>";
						echo "<td class='bold'>".htmlaccent('Débit Maximum')."</td>";
						echo "<td style='text-align:right;'>".$debit_max."</td>";
					echo "</tr>";
					
					echo "<tr>";
						echo "<td class='bold'>".htmlaccent('Débit Minimum')."</td>";
						echo "<td style='text-align:right;'>".$debit_min."</td>";
					echo "</tr>";
					
					echo "<tr class='grey'>";
						echo "<td class='bold'>".htmlaccent('Nombre de mesures directes')."</td>";
						echo "<td style='text-align:right;'>".$nb_data_load."</td>";
					echo "</tr>";
		
						
				echo "</table>";		
				
			echo "</div>"; 
		
	
		 echo "<hr>";		
		 echo "</div>";
		 
		 if($nb_data_load > 0)
		 {
			 echo "<div id='box_graph_all'>";
			 
				echo "<h8>".htmlaccent('Relation - Débit x Hauteur d\'eau')."</h8>";
								
				echo "<hr>";
			 
				echo "<div id='container' style='width:98%;height:400px;margin-top:30px;'></div>";
		
			 echo "<hr>";		
			 echo "</div>";
		 }

	
	echo "<hr>\n";
	echo "</div>\n";
	
echo "<hr>\n";
echo "</div>\n";


if($nb_data_load > 0)
{
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
	<?php 
		
	?>
				
	// OPTIONS			
	var options = {
					 'shadowSize': 0,
					 'HtmlText': false, 
					 'fontSize':12, 
					 'legend': {'show': true,position: 'nw'}, 
					 'xaxis': {'showLabels': true, 'tickDecimals': 0, 'min': <?php echo $hauteur_min*0.9; ?>, 'title': 'Hauteur d\'eau [cm]'},
					 'yaxis': {'showLabels': true, 'tickDecimals': 0, 'min': <?php echo $echelle_debit_min*0.9; ?>, 'title': 'Débit <br> [m<sup>3</sup>/s]', 'titleAngle': 90}, 
					 'grid': {'color': '#000000', 'backgroundColor': '#FFFFFF', 'verticalLines': false, 'horizontalLines': true, 'outlineWidth': 1, 'lineColor': '#dddddd'}, 
					 'crosshair': {'mode': 'x', 'color': '#dddddd', 'hideCursor': true, 'lineWidth': 1}, 
					 'mouse': {'track': true,'position': 'nw', 'relative': true, 'margin': 5, 'trackDecimals': 2, trackFormatter: function(obj){return 'Hauteur d\'eau = ' + obj.x +' cm<br> Débit = ' + obj.y+' m<sup>3</sup>/s';}}
				   }
  
	
	//AVEC COURBE EN PLUS f = Flotr.draw($('container'),[{data:d1, color:'#336699'},{data:d1, color:'#007300','lines': {'fill': false}}],Object.extend(Object.clone(options)));
	<?php
	/*
	if(tep_not_null($equation))
	{
	?>	
	f = Flotr.draw($('container'),[{data:d1, color:'#336699',label: 'Points de Jaugeage',points:{ show:true, radius: 1 ,lineWidth: 1,fill: true,fillColor: '#336699' }},{data:d2, color:'#bd1f13',label: 'Courbe de tarage : y = <?php echo $equation;?>', 'lines':{show:true,'fill': false,'fillOpacity': 0.05,'lineWidth': 1}}],Object.extend(Object.clone(options)));
	<?php
	}
	else
	{
	?>	
	f = Flotr.draw($('container'),[{data:d1, color:'#336699',label: 'Points de Jaugeage',points:{ show:true, radius: 1,lineWidth: 1,fill: true,fillColor: '#336699' }}],Object.extend(Object.clone(options)));
	<?php
	}
	*/
	?>
	 
	function drawGraph(opts){
		
		var o = Object.extend(Object.clone(options), opts || {});
		
		return f = Flotr.draw(
			$('container'), 
			o
		);
	}	
	
	/*
	$('container').observe('flotr:select', function(evt){
	
		var area = evt.memo[0];
		
		f = drawGraph({
			xaxis: {min:area.x1, max:area.x2,'trackDecimals': 1},
			yaxis: {min:area.y1, max:area.y2, 'trackDecimals': 1}
		});
	});
	*/
	
	
	// DATA
	<?php 
		
		$graph = '';
		
		$graph = "f = Flotr.draw($('container'),[{data:d1, color:'#336699',label: 'Points de Jaugeage',points:{ show:true, radius: 1,lineWidth: 5,fill: true,fillColor: '#336699' }}";
	
		if(isset($tarage_array))
		{
			echo "var d1 = [".$string_graph_points."];";
		
			for($ta=0;$ta<sizeof($tarage_array);$ta++)
			{
				//echo "var ".${'d'.($ta+2)}." = [".${'string_graph_line_' . $ta}."];";
				echo "var d".($ta+2)." = [".${'string_graph_line_' . $ta}."];";
				
				$graph .= ",{data:d".($ta+2).", color:'".$tab_color[$ta]."',label: 'Courbe de tarage : y = ". $tarage_array[$ta]['equation'] ."', 'lines':{show:true,'fill': false,'fillOpacity': 0.05,'lineWidth': 2}}";
			}
		}
		else{echo "var d1 = [".$string_graph_points."];";}
			
	
		$graph .= "],Object.extend(Object.clone(options)));";
			
			echo $graph;
	?>	
	
});


</script>

<?php } ?>
