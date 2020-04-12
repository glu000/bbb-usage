<?php

require_once 'conf.php';
require_once 'lib.php';

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');
require_once ('jpgraph/jpgraph_date.php');

$color = array ("#CB4335", "#2471A3", "#138D75", "#D4AC0D", "#2E4053");

$title = array ('meetingCount' => "Number of active Rooms",
    'participantCount' => "Number of participants",
    'voiceParticipantCount' => "Number of voice connections",
    'videoCount' => "Number of video connections",
    'breakoutCount' => "Number of Breakout-Rooms");

$width = $_GET ['width'];

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


// fill empty values
for ($i=0; $i<$row; $i++)
{
    foreach ($ydata as $key => $stat) {
        foreach ($server_arr as $key1 => $srvname) {
            if (!isset ($stat[$srvname][$i])) $ydata[$key][$srvname][$i] = 0;
        }
    }
}

// Sort
foreach ($ydata as $key => $stat) {
    foreach ($server_arr as $key1 => $srvname) {
        ksort($ydata[$key][$srvname]);
    }
}


foreach ($ydata as $key => $stat) {

    // Setup the graph
    $graph = new Graph($width, 300);
    $graph->SetScale( 'datlin' );

    $theme_class = new UniversalTheme;

    $graph->SetTheme($theme_class);
    $graph->img->SetAntiAliasing(false);
    $graph->title->Set($title [$key]);
    $graph->SetBox(false);

    $graph->SetMargin(40, 20, 36, 63);

    $graph->img->SetAntiAliasing();

    $graph->yaxis->HideZeroLabel();
    $graph->yaxis->HideLine(false);
    $graph->yaxis->HideTicks(false, false);

    $graph->xgrid->Show();
    $graph->xgrid->SetLineStyle("solid");
    //$graph->xaxis->SetTickLabels($xdata);
    $graph->xgrid->SetColor('#E3E3E3');

    $graph->xaxis->SetLabelAngle(90);

    // Create the first line

    foreach ($server_arr as $key1 => $srvname)
    {
        $p1 = new LinePlot($stat[$srvname], $xdata);
        $graph->Add($p1);
        $p1->SetColor($color [$key1]);
        $p1->SetLegend($srvname);

        unset ($p1);
    }

    $graph->legend->SetFrameWeight(1);

    // Output line
    $imgname = "tmp/imagefile_$key.png";

    $graph->Stroke($imgname);

    unset ($graph);

}

$currdata = getCurrentData();

print '<html><head>';
print '<link rel="stylesheet" type="text/css" href="main.css">';
print '</head>';

print "<body>";


print "<h2>Usage statistics for $servername</h2>";

$currdata = getCurrentData();

if (empty ($currdata))
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

    print "</table><br><br>";
}
else
{
    print "<p>Currently no active meetings</p><br><br>";
}





foreach ($ydata as $key => $stat) {

    $imgname = "tmp/imagefile_$key.png";

    print '<img src="' . $imgname . '" />';

    print "<br><br><br>";
}



print "</body></html>";




