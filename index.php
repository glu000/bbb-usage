<?php

require_once './vendor/autoload.php';

use BigBlueButton\BigBlueButton;

putenv ("BBB_SECRET=98b204dc3310d8df98e1bfe986fd61bc");
putenv ("BBB_SERVER_BASE_URL=https://bbb.gl.co.at/bigbluebutton/");

date_default_timezone_set('Europe/Vienna');


$bbb = new BigBlueButton();
$response = $bbb->getMeetings();

$content = $response->getRawXml();

$output  = $content->meetings->asXML();
$filename = '/var/log/bbb-usage' . date('Ymd_Hi') . '.xml';

file_put_contents($filename, $output);

$i=0;
if ($response->getReturnCode() == 'SUCCESS') {
    foreach ($content->meetings->meeting as $meeting) {
        // process all meeting
        $i++;
    }
}

$content = date('Ymd_hi') . ": Anzahl Meetings: " . $i . "\n";

file_put_contents("/var/log/bbb-usage-meetings.txt", $content, FILE_APPEND);