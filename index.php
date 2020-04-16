<?php

require_once 'conf.php';
require_once 'lib.php';

$title = array ('meetingCount' => "Number of active rooms",
    'participantCount' => "Number of participants",
    'voiceParticipantCount' => "Number of voice connections",
    'videoCount' => "Number of video connections",
    'breakoutCount' => "Number of breakout-rooms");

$xdata = array ();

date_default_timezone_set($timezone);

$file = fopen ($filename, 'r');

$row = 0;
$maxserver = 0;
$server_arr = array ();
$startdate = 0;
$selserver = array ();
$startdate = 0;
$enddate = time ();
$startdate_str = "";
$enddate_str = date ("Y-m-d", time ());
$secret_input = "";

if (!empty ($_GET))
{
    if (array_key_exists('secret', $_GET)) $secret_input = $_GET['secret'];
    if (array_key_exists('selectserver', $_GET)) $selserver = $_GET['selectserver'];
    if (array_key_exists('startdate', $_GET))
    {
        $startdate = strtotime ($_GET ['startdate']);
        $startdate_str = $_GET ['startdate'];
    }
    if (array_key_exists('enddate', $_GET))
    {
        $enddate = strtotime ($_GET ['enddate']) + (24 * 60 * 60) - 1;
        $enddate_str = $_GET ['enddate'];
    }
}


if ($secret_input != "") {
}



if (!1)
{
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 255, $delimiter)) !== FALSE) {

            //$num = count($data);  // TODO: Check if data row is correct, i.e. $num and $nr_server fit

            $xdata [$row] = strtotime($data[0] . " " . $data [1]);

            $nr_server = $data [2];

            if ($nr_server > $maxserver) $maxserver = $nr_server;

            $serveridx = 3;

            for ($i = 0; $i < $nr_server; $i++) {
                $srvname = $data [$serveridx];
                if (!in_array($srvname, $server_arr)) $server_arr [] = $srvname;

                $ydata['meetingCount'][$srvname][$row] = (int)$data[4 + (6 * $i)];
                $ydata['participantCount'][$srvname][$row] = (int)$data[5 + (6 * $i)];
                $ydata['voiceParticipantCount'][$srvname][$row] = (int)$data[6 + (6 * $i)];
                $ydata['videoCount'][$srvname][$row] = (int)$data[7 + (6 * $i)];
                $ydata['breakoutCount'][$srvname][$row] = (int)$data[8 + (6 * $i)];

                $serveridx += 6;
            }
            $row++;
        }


        fclose($handle);
    } else {
        exit; // TODO: Print error message
    }

    $allservers = $server_arr;
    //$allservers[] = "Sum";
    if (!empty ($selserver)) {
        foreach ($server_arr as $key => $server) {
            if (array_search($server, $selserver) === false) {
                unset ($server_arr [$key]);
            }
        }
    }


    $gdata = array();

    // fill empty values & convert to Google format
    for ($i = 0; $i < $row; $i++) {
        if (($xdata[$i] >= $startdate) && ($xdata[$i] <= $enddate)) {
            foreach ($ydata as $key => $stat) {
                $gdata [$key][$i][0] = 'new Date(' . ($xdata[$i] * 1000) . ')';
                $sum = 0;
                foreach ($server_arr as $key1 => $srvname) {
                    if (!isset ($stat[$srvname][$i])) $ydata[$key][$srvname][$i] = 0;
                    $gdata [$key][$i][] = $ydata[$key][$srvname][$i];
                    $sum += $ydata[$key][$srvname][$i];
                    // Tooltip
                    $gdata [$key][$i][] = "'" . date('y-m-d H:i', $xdata[$i]) . " - " . $srvname . ": " . $ydata[$key][$srvname][$i] . "'";
                }
                // Sum
                //$gdata [$key][$i][] = $sum;
                //$gdata [$key][$i][] = "'" . date('y-m-d H:i', $xdata[$i]) . " - Sum: " . $sum . "'";


            }
        }
    }


    print '<html><head>';
    print '<link rel="stylesheet" type="text/css" href="main.css">';
    print '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';

    print '<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />';
    //print '<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.min.js" rel="stylesheet" />';
    print '<link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" rel="stylesheet" />';
    //print '<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>';
    print '<script src="https://cdn.jsdelivr.net/npm/jquery@3.2.1/dist/jquery.min.js"></script>';
    print '<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>';
    //print '<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.12/js/select2.full.min.js"></script>';

    print '<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>';

    $nodata = false;

    $script = "<script>\n";

    if (!empty($gdata)) {
        foreach ($gdata as $key => $stat) {

            $script .= "google.charts.load('current', {packages: ['corechart', 'line']}); \n";
            //$script .= "google.charts.load('current', {'packages':['bar']}); \n";
            $script .= "google.charts.setOnLoadCallback(drawChart" . $key . "); \n";

            $script .= "function drawChart" . $key . "() { var data = new google.visualization.DataTable(); \n";
            $script .= "data.addColumn('date', 'X'); \n";

            foreach ($server_arr as $key1 => $srvname) {
                $script .= "data.addColumn('number', '" . $srvname . "'); \n";
                $script .= "data.addColumn({type: 'string', role: 'tooltip'}); \n";
            }
            //$script .= "data.addColumn('number', 'Sum'); \n";
            //$script .= "data.addColumn({type: 'string', role: 'tooltip'}); \n";

            $stat = array_values($stat);
            $js_gdata = json_encode($stat);
            $js_gdata = str_replace('"', '', $js_gdata);

            $script .= "data.addRows(" . $js_gdata . "); \n";

            //'#CB4335', '#2471A3', '#138D75', '#D4AC0D', '#2E4053'        colors: ['#10a513', '#097138'],

            $script .= "var options = { height:400, colors: ['#CB4335', '#2471A3', '#D4AC0D', '#138D75', '#2E4053'], isStacked: 'true', \n";
            $script .= "hAxis: { format: 'yy-MM-dd HH:mm' }, \n";
            $script .= "title: '" . $title[$key] . "', \n";
            //$script .= "vAxis: { title: '".$title[$key]."' }, \n";
            $script .= "}; \n";

            $script .= "var chart = new google.visualization.AreaChart(document.getElementById('chart_" . $key . "')); \n";
            $script .= "chart.draw(data, options); \n";

            //$script .= "var chart = new google.charts.Bar(document.getElementById('chart_".$key."')); ";

            //$script .= "chart.draw(data, google.charts.Bar.convertOptions(options)); ";


            $script .= "} \n";
        }
    } else {
        $nodata = true;
    }


    $script .= "</script>";

    print $script;


    print '</head>';

    print "<body>";


    print "<h2>Usage statistics for $servername</h2>";


    $currdata = getCurrentData();

    print '<div id="divcurrdata">';

    if (!empty ($currdata)) {
        print '<br><table id="currdata">';

        print "<tr>";
        print "<th>Current data</th>";

        foreach ($title as $text) {
            print "<th>$text</th>";
        }
        print "</tr>";

        foreach ($currdata as $key => $stat) {

            print "<tr>";
            print "<td>";
            print $key;
            print "</td>";

            foreach ($stat as $value) {
                print "<td>";
                print $value;
                print "</td>";
            }
            print "</tr>";
        }

        print "</table><br>";
    } else {
        print '<p id="nomeetings">Currently no active meetings</p><br><br>';
    }


    print '<form method="get" name="form" action="index.php">';

    print '<input type="hidden" name="secret" value="'.$secret_input.'">';

    //print '<div class="sel">';

    print "<table>";

    print '<tr>';

    print "<td>";

    print '<select id="selectserver" name="selectserver[]" multiple="multiple">';

    foreach ($allservers as $key => $server) {
        if (!empty ($selserver)) {
            print '<option value="' . $server . '"';
            if (array_search($server, $selserver) !== false) {
                print " selected";
            }
            print '>' . $server . '</option>';
        } else {
            print '<option value="' . $server . '" selected>' . $server . '</option>';
        }
    }


    print '</select>';

    print "</td>";

    print '<td  class="sel">';
    print '<p>Start Date: <input type="text" id="datepicker" name="startdate" value="' . $startdate_str . '"></p>';
    print "</td>";

    print '<td  class="sel">';
    print '<p>End Date: <input type="text" id="datepicker1" name="enddate" value="' . $enddate_str . '"></p>';
    print "</td>";

    print '<td>';
    print '<input id="but" type="submit" value="Submit">';
    print "</td>";
    //print '<div class="submit">';


    //print '</div>';
    //print '</div>';


    print "</tr>";

    print "</table>";

    print "</div>";


    print '</form>';

    print "<script>$('#selectserver').select2({ placeholder: 'Select servers' });</script>";

    print "<script>$('input#datepicker').datepicker({dateFormat: 'yy-mm-dd'})</script>";

    print "<script>$('input#datepicker1').datepicker({dateFormat: 'yy-mm-dd'})</script>";


    foreach ($ydata as $key => $stat) {

        print '<div id="chart_' . $key . '"></div>';

    }

    if ($nodata) print '<br><p id="nomeetings">No data</p>';


    print "</body></html>";


}
else
{
    print '<html><head>';
    print '<link rel="stylesheet" type="text/css" href="main.css">';
    print "</head>";
    print "<body style='background-color: darkgray'>";


    print "<form>";

    print '<input type="text" id="secret" name="secret" placeholder="Please enter the secret">';
    print '<input type="submit" id ="hidden" value="OK">';


    print "</form>";

    print "</body>";
}

