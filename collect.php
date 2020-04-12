<?php

require_once './vendor/autoload.php';

require_once 'conf.php';
require_once 'lib.php';

date_default_timezone_set($timezone);

$data = getCurrentData();

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