<?php

use BigBlueButton\BigBlueButton;

function getCurrentData ($show_server = "")
{
    require_once './vendor/autoload.php';
    require_once 'conf.php';

    global $bbb_secret , $servername;

    putenv ("BBB_SECRET=$bbb_secret");
    putenv ("BBB_SERVER_BASE_URL=$servername/bigbluebutton/");

    $data = array ();

    $bbb = new BigBlueButton();

    $response = $bbb->getMeetings();


    if ($response->getReturnCode() == 'SUCCESS') {
        $content = $response->getRawXml();
        foreach ($content->meetings->meeting as $meeting) {
            // process all meetings and save usage data to array (by server)

            $server = (string)$meeting->metadata->{'bbb-origin-server-name'};

            if (($show_server == "*") or ($show_server == $server))
            {
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
                $data [$server]['voiceParticipantCount'] += $meeting->voiceParticipantCount + $meeting->listenerCount;
                $data [$server]['videoCount'] += $meeting->videoCount;
                if ((string)$meeting->isBreakout == "true")
                {
                    $data [$server]['breakoutCount'] += 1;
                }
            }
        }
        return $data;
    }
}