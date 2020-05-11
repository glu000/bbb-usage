<?php

require_once './vendor/autoload.php';

require_once 'conf.php';
require_once 'lib.php';

date_default_timezone_set($timezone);

$data = getCurrentData();

$day = date('Y-m-d');
$time = date('H:i');
$timestamp = "$day $time:00";

/*
$exec_loads = sys_getloadavg();
$exec_cores = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
$cpu = round($exec_loads[1]/($exec_cores + 1)*100, 0);

$exec_free = explode("\n", trim(shell_exec('free')));
$get_mem = preg_split("/[\s]+/", $exec_free[1]);
$mem = round($get_mem[2]/$get_mem[1]*100, 0);
*/

if ($db_name != "")
{
    // put data into database

    $conn = new mysqli($db_server, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE TABLE IF NOT EXISTS bbb_usage_data (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    ts TIMESTAMP NOT NULL,
                    server_count SMALLINT,
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

    $server_count = count ($data);

    if ($server_count == 0)
    {
        $sql = "INSERT INTO bbb_usage_data (ts, server_count)
                VALUES ('$timestamp', 0)";

        $ressql = $conn->query($sql);
    }
    else
    {
        foreach ($data as $server => $stats)
        {
            $mc = $stats['meeting_count'];
            $pc = $stats['participant_count'];
            $vpc = $stats['voice_participant_count'];
            $vc = $stats['video_count'];
            $bc = $stats['breakout_count'];

            $sql = "INSERT INTO bbb_usage_data (ts, server_count, server, meeting_count, participant_count, voice_participant_count, video_count, breakout_count)
                VALUES ('$timestamp', $server_count, '$server', $mc, $pc, $vpc, $vc, $bc)";

            $ressql = $conn->query($sql);
        }
    }

    $conn->close();
}


