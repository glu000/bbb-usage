<?php

require_once './vendor/autoload.php';

use BigBlueButton\BigBlueButton;

putenv ("BBB_SECRET=98b204dc3310d8df98e1bfe986fd61bc");
putenv ("BBB_SERVER_BASE_URL=https://bbb.gl.co.at/bigbluebutton/");



$bbb = new BigBlueButton();
$response = $bbb->getMeetings();

if ($response->getReturnCode() == 'SUCCESS') {
    foreach ($response->getRawXml()->meetings->meeting as $meeting) {
        // process all meeting
        $x=1;
    }
}