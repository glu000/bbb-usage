<?php

require_once 'conf.php';
require_once 'lib.php';

$title = array ('meetingCount' => "Number of active Rooms",
    'participantCount' => "Number of participants",
    'voiceParticipantCount' => "Number of voice connections",
    'videoCount' => "Number of video connections",
    'breakoutCount' => "Number of Breakout-Rooms");

$xdata = array ();

date_default_timezone_set($timezone);

$file = fopen ($filename, 'r');

$row = 0;
$maxserver = 0;
$server_arr = array ();


if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 255, $delimiter)) !== FALSE) {

        //$num = count($data);  // TODO: Check if data row is correct, i.e. $num and $nr_server fit

        $xdata [$row] = strtotime( $data[0] . " " . $data [1]);

        $nr_server = $data [2];

        if ($nr_server > $maxserver) $maxserver = $nr_server;

        $serveridx = 3;

        for ($i = 0; $i<$nr_server; $i++)
        {
            $srvname = $data [$serveridx];
            if (!in_array($srvname, $server_arr)) $server_arr [] = $srvname;

            $ydata['meetingCount'][$srvname][$row] = (int)$data[4];
            $ydata['participantCount'][$srvname][$row] = (int)$data[5];
            $ydata['voiceParticipantCount'][$srvname][$row] = (int)$data[6];
            $ydata['videoCount'][$srvname][$row] = (int)$data[7];
            $ydata['breakoutCount'][$srvname][$row] = (int)$data[8];

            $serveridx += 6;
        }
        $row++;
    }


    fclose($handle);
}
else
{
    exit; // TODO: Print error message
}


// fill empty values & convert to Google format
for ($i=0; $i<$row; $i++)
{
    foreach ($ydata as $key => $stat) {
        $gdata [$key][$i][0] = 'new Date('.($xdata[$i]*1000).')';
        foreach ($server_arr as $key1 => $srvname) {
            if (!isset ($stat[$srvname][$i])) $ydata[$key][$srvname][$i] = 0;
            $gdata [$key][$i][] = $ydata[$key][$srvname][$i];
            // Tooltip
            $gdata [$key][$i][] = "'" . date ('y-m-d H:i', $xdata[$i]) . ": " . $ydata[$key][$srvname][$i] . "'";
        }

    }
}


print '<html><head>';
print '<link rel="stylesheet" type="text/css" href="main.css">';
print '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';

$script = "<script>\n";


foreach ($ydata as $key => $stat) {

    $script .= "google.charts.load('current', {packages: ['corechart', 'line']}); \n";
    $script .= "google.charts.setOnLoadCallback(drawChart".$key."); \n";

    $script .= "function drawChart".$key."() { var data = new google.visualization.DataTable(); \n";
    $script .= "data.addColumn('date', 'X'); \n";

    foreach ($server_arr as $key1 => $srvname)
    {
        $script .= "data.addColumn('number', '".$srvname."'); \n";
        $script .= "data.addColumn({type: 'string', role: 'tooltip'}); \n";
    }


    $js_gdata = json_encode($gdata[$key]);
    $js_gdata = str_replace('"', '', $js_gdata);

    $script .= "data.addRows(".$js_gdata."); \n";

    //'#CB4335', '#2471A3', '#138D75', '#D4AC0D', '#2E4053'        colors: ['#10a513', '#097138'],

    $script .= "var options = { height:400, colors: ['#CB4335', '#2471A3', '#D4AC0D', '#138D75', '#2E4053'], \n";
    $script .= "hAxis: { title: 'Date', format: 'yy-MM-dd HH:mm' }, \n";
    $script .= "vAxis: { title: '".$title[$key]."' }, \n";
    $script .= "}; \n";

    $script .= "var chart = new google.visualization.LineChart(document.getElementById('chart_".$key."')); \n";
    $script .= "chart.draw(data, options); \n";
    $script .= "} \n";
}

$script .= "</script>";

print $script;


print '</head>';

print "<body>";



print "<h2>Usage statistics for $servername</h2>";


$currdata = getCurrentData();

if (!empty ($currdata))
{
    print '<br><table id="currdata">';

    print "<tr>";
    print "<th>Current data</th>";

    foreach ($title as $text)
    {
        print "<th>$text</th>";
    }
    print "</tr>";

    foreach ($currdata as $key => $stat) {

        print "<tr>";
        print "<td>";
        print $key;
        print "</td>";

        foreach ($stat as $value)
        {
            print "<td>";
            print $value;
            print "</td>";
        }
        print "</tr>";
    }

    print "</table><br>";
}
else
{
    print "<p>Currently no active meetings</p><br><br>";
}


foreach ($ydata as $key => $stat) {

    print '<div id="chart_'.$key.'"></div>';

}


print "</body></html>";




