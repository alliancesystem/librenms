<?php

if ($_SESSION['userlevel'] >= '5')
{
  $sql  = "SELECT * FROM `storage` AS S, `devices` AS D WHERE S.device_id = D.device_id ORDER BY D.hostname, S.storage_descr";
} else {
  $sql  = "SELECT * FROM `storage` AS S, `devices` AS D, devices_perms as P WHERE S.device_id = D.device_id AND ";
  $sql .= "D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, S.storage_descr";
}

$query = mysql_query($sql);

echo("<div style='padding: 5px;'>
        <table width=100% cellspacing=0 cellpadding=2>");

echo("<tr class=tablehead>
        <th width=280>Device</th>
        <th>Storage</th>
        <th width=100></th>
        <th width=280>Usage</th>
        <th width=50>Used</th>
      </tr>");

$row = 1;

while ($drive = mysql_fetch_assoc($query))
{
  $skipdrive = 0;

  if ($drive["os"] == "junos")
  {
    foreach ($config['ignore_junos_os_drives'] as $jdrive)
    {
      if (preg_match($jdrive, $drive["storage_descr"]))
      {
        $skipdrive = 1;
      }
    }

    $drive["storage_descr"] = preg_replace("/.*mounted on: (.*)/", "\\1", $drive["storage_descr"]);

  }

  if ($drive['os'] == "freebsd")
  {
    foreach ($config['ignore_bsd_os_drives'] as $jdrive)
    {
      if (preg_match($jdrive, $drive["storage_descr"]))
      {
        $skipdrive = 1;
      }
    }
  }

  if ($skipdrive) { continue; }
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $perc  = round($drive['storage_perc'], 0);
  $total = formatStorage($drive['storage_size']);
  $free = formatStorage($drive['storage_free']);
  $used = formatStorage($drive['storage_used']);

  $store_url    = "graph.php?id=" . $drive['storage_id'] . "&amp;type=storage&amp;from=$month&amp;to=$now&amp;width=400&amp;height=125";
  $store_popup = "onmouseover=\"return overlib('<img src=\'$store_url\'>', LEFT);\" onmouseout=\"return nd();\"";

  $mini_graph = $config['base_url'] . "/graph.php?id=".$drive['storage_id']."&amp;type=storage&amp;from=".$day."&amp;to=".$now."&amp;width=80&amp;height=20&amp;bg=f4f4f4";

  $background = get_percentage_colours($perc);

  echo("<tr bgcolor='$row_colour'><td>" . generate_device_link($drive) . "</td><td class=tablehead>" . $drive['storage_descr'] . "</td>
       <td><img src='$mini_graph'></td>
       <td>
        <a href='#' $store_popup>".print_percentage_bar (400, 20, $perc, "$used / $total", "ffffff", $background['left'], formatStorage($free), "ffffff", $background['right'])."</a>
        </td><td>$perc"."%</td></tr>");

  $row++;
}

echo("</table></div>");

?>
