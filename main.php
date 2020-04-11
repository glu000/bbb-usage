<?php

require_once 'conf.php';

require_once ('jpgraph/jpgraph.php');
require_once ('jpgraph/jpgraph_line.php');


$width = $_GET ['width'];

$xdata = array ();

if (!file_exists('tmp')) {  // TODO: This does not work
    mkdir('tmp', 0777, true);
}

$file = fopen ($filename, 'r');

$row = 0;
$maxserver = 0;
$server_arr = array ();


if (($handle = fopen($filename, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 255, $delimiter)) !== FALSE) {

        //$num = count($data);  // TODO: Check if data row is correct, i.e. $num and $nr_server fit

        $xdata [$row] = $data [0] . " " . $data [1];

        $nr_server = $data [2];

        if ($nr_server > $maxserver) $maxserver = $nr_server;

        $serveridx = 3;

        for ($i = 0; $i<$nr_server; $i++)
        {
            $srvname = $data [$serveridx];
            if (!in_array($srvname, $server_arr)) $server_arr [] = $srvname;

            $nr_meetings [$srvname][$row] = (int)$data[4];

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
    foreach ($server_arr as $srvname)
    {
        if (!array_key_exists($srvname, $nr_meetings))
        {
           $nr_meetings [$srvname][$i] = 0;
        }
        else
        {
            if (!array_key_exists($i, $nr_meetings[$srvname]))
            {
                $nr_meetings [$srvname][$i] = 0;
            }
        }
    }
}


foreach ($server_arr as $srvname) {


    ksort($nr_meetings[$srvname]);


    // Setup the graph
    $graph = new Graph($width, 300);
    $graph->SetScale( 'datlin' );

    $theme_class = new UniversalTheme;

    $graph->SetTheme($theme_class);
    $graph->img->SetAntiAliasing(false);
    $graph->title->Set('Usage: '.$srvname);
    $graph->SetBox(false);

    $graph->SetMargin(40, 20, 36, 63);

    $graph->img->SetAntiAliasing();

    $graph->yaxis->HideZeroLabel();
    $graph->yaxis->HideLine(false);
    $graph->yaxis->HideTicks(false, false);

    $graph->xgrid->Show();
    $graph->xgrid->SetLineStyle("solid");
    $graph->xaxis->SetTickLabels($xdata);
    $graph->xgrid->SetColor('#E3E3E3');

    // Create the first line
    $p1 = new LinePlot($nr_meetings[$srvname]);
    $graph->Add($p1);
    $p1->SetColor("#6495ED");
    $p1->SetLegend("Number of active Rooms");

    /*
    // Create the second line
    $p2 = new LinePlot($datay2);
    $graph->Add($p2);
    $p2->SetColor("#B22222");
    $p2->SetLegend('Line 2');

    // Create the third line
    $p3 = new LinePlot($datay3);
    $graph->Add($p3);
    $p3->SetColor("#FF1493");
    $p3->SetLegend('Line 3');
    */

    $graph->legend->SetFrameWeight(1);

    // Output line
    $imgname = "tmp/imagefile_$srvname.png";

    $graph->Stroke($imgname);

    //$graph->img->Stream($fileName);


}


print "<html><body>";


print "<h2>Usage statistics for $servername</h2>";

foreach ($server_arr as $srvname) {

    $imgname = "tmp/imagefile_$srvname.png";

    print '<img src="'.$imgname.'" />';

    print "<br><br><br>";

}


print "</body></html>";




