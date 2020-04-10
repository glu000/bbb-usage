<?php

require_once './vendor/autoload.php';

require_once 'conf.php';

use BigBlueButton\BigBlueButton;

putenv ("BBB_SECRET=98b204dc3310d8df98e1bfe986fd61bc");
putenv ("BBB_SERVER_BASE_URL=https://bbb.gl.co.at/bigbluebutton/");

date_default_timezone_set($timezone);

$data = array ();

$bbb = new BigBlueButton();

$response = $bbb->getMeetings();


if ($response->getReturnCode() == 'SUCCESS') {
    $content = $response->getRawXml();
    foreach ($content->meetings->meeting as $meeting) {
        // process all meetings and save usage data to array (by server)

        $server = (string)$meeting->metadata->{'bbb-origin-server-name'};

        if (!array_key_exists($server, $data))
        {
            // new server - init stats (to avoid php warning undefined index)
            $data [$server]['meetingCount'] = 0;
            $data [$server]['participantCount'] = 0;
            $data [$server]['voiceParticipantCount'] = 0;
            $data [$server]['videoCount'] = 0;
            $data [$server]['breakoutCount'] = 0;
        }

        $data [$server]['meetingCount'] += 1;
        $data [$server]['participantCount'] += $meeting->participantCount;
        $data [$server]['voiceParticipantCount'] += $meeting->voiceParticipantCount;
        $data [$server]['videoCount'] += $meeting->videoCount;
        if ((string)$meeting->isBreakout == "true")
        {
            $data [$server]['breakoutCount'] += 1;
        }
        $x=1;
    }
}

$day = date('Y-m-d');
$time = date('H:i');

$output  = $day . $delimiter;
$output .= $time . $delimiter;
$output .= count ($data);

foreach ($data as $server => $stats)
{
    $output .= $delimiter;

    $output .= $server . $delimiter;
    $output .= $stats['meetingCount'] . $delimiter;
    $output .= $stats['participantCount'] . $delimiter;
    $output .= $stats['voiceParticipantCount'] . $delimiter;
    $output .= $stats['videoCount'] . $delimiter;
    $output .= $stats['breakoutCount'];

}

$output .= "\n";

print $output;

file_put_contents($filename, $output, FILE_APPEND);