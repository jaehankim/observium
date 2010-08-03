<?php

if($_SESSION['userlevel'] >= '5') {
  $sql = "SELECT * FROM `sensors` AS S, `devices` AS D WHERE S.sensor_class='voltage' AND S.device_id = D.device_id ORDER BY D.hostname, S.sensor_descr";
} else {
  $sql = "SELECT * FROM `sensors` AS S, `devices` AS D, devices_perms as P WHERE S.sensor_class='voltage' AND S.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, S.sensor_descr";
}

$graph_type = "sensor_voltage";

$query = mysql_query($sql);

echo('<table cellspacing="0" cellpadding="6" width="100%">');

echo('<tr class=tablehead>
        <th width="280">Device</th>
        <th width="180">Sensor</th>
	<th></th>
	<th></th>
        <th width="100">Current</th>
        <th width="250">Range limit</th>
        <th>Notes</th>
      </tr>');

$row = 1;

while($volt = mysql_fetch_array($query))
{
  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $weekly_volt  = "graph.php?id=" . $volt['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$week&amp;to=$now&amp;width=500&amp;height=150";
  $volt_popup = "<a onmouseover=\"return overlib('<img src=\'$weekly_volt\'>', LEFT);\" onmouseout=\"return nd();\">
        " . $volt['sensor_descr'] . "</a>";

  if($volt['sensor_current'] >= $volt['sensor_limit']) { $alert = '<img src="images/16/flag_red.png" alt="alert" />'; } else { $alert = ""; }
   
  $volt_day    = "graph.php?id=" . $volt['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$day&amp;to=$now&amp;width=300&amp;height=100";
  $volt_week   = "graph.php?id=" . $volt['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$week&amp;to=$now&amp;width=300&amp;height=100";
  $volt_month  = "graph.php?id=" . $volt['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$month&amp;to=$now&amp;width=300&amp;height=100";
  $volt_year   = "graph.php?id=" . $volt['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$year&amp;to=$now&amp;width=300&amp;height=100";

  $volt_minigraph = "<img src='graph.php?id=" . $volt['sensor_id'] . "&amp;type=".$graph_type."&amp;from=$day&amp;to=$now&amp;width=100&amp;height=20'";
  $volt_minigraph .= " onmouseover=\"return overlib('<div class=list-large>".$volt['hostname']." - ".mres($volt['sensor_descr']);
  $volt_minigraph .= "</div><div style=\'width: 750px\'><img src=\'$volt_day\'><img src=\'$volt_week\'><img src=\'$volt_month\'><img src=\'$volt_year\'></div>', RIGHT".$config['overlib_defaults'].");\" onmouseout=\"return nd();\" >";

  echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generate_device_link($volt) . "</td>
          <td>$volt_popup</td>
	  <td>$volt_minigraph</td>
	  <td width=100>$alert</td>
          <td style='text-align: center; font-weight: bold;'>" . $volt['sensor_current'] . "V</td>
          <td style='text-align: center'>" . $volt['sensor_limit_low'] . "V - " . $volt['sensor_limit'] . "V</td>
          <td>" . (isset($volt['sensor_notes']) ? $volt['sensor_notes'] : '') . "</td>
        </tr>\n");

      if($_GET['optb'] == "graphs") { ## If graphs

  echo("<tr bgcolor='$row_colour'><td colspan=7>");

  $daily_graph   = "graph.php?id=" . $volt['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=211&height=100";
  $daily_url       = "graph.php?id=" . $volt['sensor_id'] . "&type=".$graph_type."&from=$day&to=$now&width=400&height=150";

  $weekly_graph  = "graph.php?id=" . $volt['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=211&height=100";
  $weekly_url      = "graph.php?id=" . $volt['sensor_id'] . "&type=".$graph_type."&from=$week&to=$now&width=400&height=150";

  $monthly_graph = "graph.php?id=" . $volt['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=211&height=100";
  $monthly_url     = "graph.php?id=" . $volt['sensor_id'] . "&type=".$graph_type."&from=$month&to=$now&width=400&height=150";

  $yearly_graph  = "graph.php?id=" . $volt['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=211&height=100";
  $yearly_url  = "graph.php?id=" . $volt['sensor_id'] . "&type=".$graph_type."&from=$year&to=$now&width=400&height=150";

  echo("<a onmouseover=\"return overlib('<img src=\'$daily_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$daily_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$weekly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$weekly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$monthly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$monthly_graph' border=0></a> ");
  echo("<a onmouseover=\"return overlib('<img src=\'$yearly_url\'>', LEFT);\" onmouseout=\"return nd();\">
        <img src='$yearly_graph' border=0></a>");
  echo("</td></tr>");

    } # endif graphs




  $row++;

}

echo("</table>");


?>

