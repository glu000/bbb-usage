<?php

require_once './vendor/autoload.php';

require_once 'conf.php';
require_once 'lib.php';

date_default_timezone_set($timezone);

$data = getCurrentData();

$day = date('Y-m-d');
$time = date('H:i');

$exec_loads = sys_getloadavg();
$exec_cores = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
$cpu = round($exec_loads[1]/($exec_cores + 1)*100, 0);

$exec_free = explode("\n", trim(shell_exec('free')));
$get_mem = preg_split("/[\s]+/", $exec_free[1]);
$mem = round($get_mem[2]/$get_mem[1]*100, 0);

if ($db_name != "")
{
    // put data into database

    $conn = new mysqli($db_server, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE TABLE IF NOT EXISTS bbb_usage_data (
                    id INT(9) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    ts TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    server VARCHAR(255),
                    meeting_count SMALLINT UNSIGNED,
                    participant_count SMALLINT UNSIGNED,
                    voice_participant_count SMALLINT UNSIGNED,                                        
                    video_count SMALLINT UNSIGNED,
                    breakout_count SMALLINT UNSIGNED,
                    cpu SMALLINT UNSIGNED,
                    mem SMALLINT UNSIGNED,
                    netout INT (11) UNSIGNED,
                    netin INT (11) UNSIGNED,
                    INDEX ts_index (ts)                                       
                    )";

    $ressql = $conn->query($sql);

    foreach ($data as $server => $stats)
    {
        $mc = $stats['meetingCount'];
        $pc = $stats['participantCount'];
        $vpc = $stats['voiceParticipantCount'];
        $vc = $stats['videoCount'];
        $bc = $stats['breakoutCount'];

        $sql = "INSERT INTO bbb_usage_data (server, meeting_count, participant_count, voice_participant_count, video_count, breakout_count, cpu, mem)
                VALUES ('$server', $mc, $pc, $vpc, $vc, $bc, $cpu, $mem)";

        $ressql = $conn->query($sql);
    }


    $conn->close();
}
//else
//{
    // put data into csv-file

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
//}

