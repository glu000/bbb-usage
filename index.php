<?php

require_once 'conf.php';
require_once 'lib.php';

$title = array ('meeting_count' => "Number of active rooms (incl. breakout-rooms)",
    'participant_count' => "Number of participants",
    'voice_participant_count' => "Number of voice connections",
    'video_count' => "Number of video connections",
    'breakout_count' => "Number of breakout-rooms");

date_default_timezone_set($timezone);

$maxserver = 0;
$server_arr = array ();
$startdate = 0;
$selserver = array ();
$startdate = strtotime (date ("Y-m-d", time ()));   // Default: Show current day
$enddate = time ();
$startdate_str = date ("Y-m-d", time ());
$enddate_str = date ("Y-m-d", time ());
$secret_input = "";
$gdata = array();

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

    $srv_allowed = array_key_exists($secret_input, $secrets);

    if ($srv_allowed) {
        $show_server = $secrets[$secret_input];

        if ($show_server == "%") {
            $showall = true;
        } else {
            $showall = false;

            $selserver = array($show_server);
        }

    } else {
        print "nope";
        exit;
    }

    if ($db_name != "")
    {
        $conn = new mysqli($db_server, $db_user, $db_password, $db_name);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $startdate_sql = $startdate_str . " 00:00:00";
        $enddate_sql = $enddate_str . " 23:59:59";
        $where_str1 = " WHERE (server like '$show_server') AND (ts >= '$startdate_sql') AND (ts <= '$enddate_sql')";
        $where_str2 = " WHERE ((server like '$show_server') or (server_count = 0)) AND (ts >= '$startdate_sql') AND (ts <= '$enddate_sql')";

        $sql = "SELECT DISTINCT server FROM bbb_usage_data $where_str1 ORDER BY server";

        $ressql = $conn->query($sql);

        if ($ressql->num_rows > 0) {

            for ($i=0; $i<$ressql->num_rows; $i++)
            {
                $result = $ressql->fetch_assoc();
                $server_arr [$i] = $result ['server'];
                $server_arr_idx [$result ['server']] = $i;
            }
            $maxserver = $ressql->num_rows;

            $allservers = $server_arr;
            if (!empty ($selserver)) {
                foreach ($server_arr as $key => $server) {
                    if (array_search($server, $selserver) === false) {
                        unset ($server_arr [$key]);
                        $maxserver--;
                    }
                }
                $server_arr = array_values($server_arr);
            }

            $sql = "SELECT * FROM bbb_usage_data $where_str2";

            $ressql = $conn->query($sql);

            $row = -1;
            $last_ts = 0;
            $last_row_was_0 = true;

            for ($i=0; $i<$ressql->num_rows; $i++)
            {
                $result = $ressql->fetch_assoc();

                $server_count = $result ['server_count'];
                $timestamp = strtotime($result['ts']);

                if ($last_ts != $timestamp)
                {
                    // new probe - init array, because google charts needs always the same number of data elements

                    if (($last_row_was_0) && ($server_count == 0))
                    {
                        $last_ts = $timestamp;
                        continue;
                    }

                    $row++;

                    if ($last_row_was_0)
                    {
                        foreach ($title as $stat => $value) {
                            $gdata [$stat][$row][0] = 'new Date(' . ($last_ts * 1000) . ')';
                            for ($server_idx = 0; $server_idx < $maxserver; $server_idx++)
                            {
                                $idx = ($server_idx) * 2 + 1;
                                $gdata [$stat][$row][$idx] = 0;
                                $gdata [$stat][$row][$idx+1] = "'" . date('y-m-d H:i', $last_ts) . " - " . $server_arr[$server_idx] . ": " . 0 . "'";
                            }
                        }

                        $row++;
                    }

                    foreach ($title as $stat => $value) {
                        $gdata [$stat][$row][0] = 'new Date(' . ($timestamp * 1000) . ')';
                        for ($server_idx = 0; $server_idx < $maxserver; $server_idx++)
                        {
                            $idx = ($server_idx) * 2 + 1;
                            $gdata [$stat][$row][$idx] = 0;
                            $gdata [$stat][$row][$idx+1] = "'" . date('y-m-d H:i', $timestamp) . " - " . $server_arr[$server_idx] . ": " . 0 . "'";
                        }
                    }

                    $last_ts = $timestamp;
                }

                if ($server_count > 0)
                {
                    $last_row_was_0 = false;

                    $server_idx = array_search($result['server'], $server_arr);

                    if ($server_idx === FALSE) continue;
                    $server_idx = ($server_idx * 2) + 1;

                    foreach ($title as $stat => $value)
                    {
                        $gdata [$stat][$row][$server_idx] = (int)$result[$stat];
                        $gdata [$stat][$row][$server_idx+1] = "'" . date('y-m-d H:i', $timestamp) . " - " . $result['server'] . ": " . $result[$stat] . "'";
                    }
                }
                else
                {
                    $last_row_was_0 = true;
                }
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


    print "<h2>Usage statistics for " . ($showall ? $servername : $show_server) . "</h2>";


    $currdata = getCurrentData($show_server);

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

    print '<input type="hidden" name="secret" value="' . $secret_input . '">';

    //print '<div class="sel">';

    print "<table>";

    print '<tr>';

    if ($showall)
    {
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
    }

    print '<td  class="sel">';
    print '<p>Start Date: <input type="text" id="datepicker" name="startdate" value="' . $startdate_str . '"></p>';
    print "</td>";

    print '<td  class="sel">';
    print '<p>End Date: <input type="text" id="datepicker1" name="enddate" value="' . $enddate_str . '"></p>';
    print "</td>";

    print '<td>';
    print '<input id="but" type="submit" value="Submit">';
    print "</td>";

    print "</tr>";

    print "</table>";

    print "</div>";


    print '</form>';

    print "<script>$('#selectserver').select2({ placeholder: 'Select servers' });</script>";

    print "<script>$('input#datepicker').datepicker({dateFormat: 'yy-mm-dd'})</script>";

    print "<script>$('input#datepicker1').datepicker({dateFormat: 'yy-mm-dd'})</script>";


    foreach ($title as $key => $stat) {

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

