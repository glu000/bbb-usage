<?php

require_once './vendor/autoload.php';

require_once 'conf.php';
require_once 'lib.php';

date_default_timezone_set($timezone);

$row = 0;
$imported = 0;

$conn = new mysqli($db_server, $db_user, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS bbb_usage_data (
                    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    ts TIMESTAMP NOT NULL,
                    server_count SMALLINT UNSIGNED,
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


$handle = fopen ($import_filename, "r");

if ($handle && $db_name)
{
    if ($_GET ['kill'] == "1")
    {
        // delete table
        $sql = "truncate table bbb_usage_data";

        $ressql = $conn->query($sql);

        echo "Table initialized\n";
    }

    while (($data = fgetcsv($handle, 255, $delimiter)) !== FALSE) {

        $ts = $data[0] . " " . $data [1] . ":00";

        $nr_server = $data [2];

        $serveridx = 3;

        if ($nr_server == 0)
        {
            $sql = "INSERT INTO bbb_usage_data (ts, server_count)
                VALUES ('$ts', 0)";

            $ressql = $conn->query($sql);
        }

        for ($i = 0; $i < $nr_server; $i++) {
            $server = $data [$serveridx];

            $mc = (int)$data[4 + (6 * $i)];
            $pc = (int)$data[5 + (6 * $i)];
            $vpc = (int)$data[6 + (6 * $i)];
            $vc = (int)$data[7 + (6 * $i)];
            $bc = (int)$data[8 + (6 * $i)];

            $sql = "INSERT INTO bbb_usage_data (ts, server_count, server, meeting_count, participant_count, voice_participant_count, video_count, breakout_count)
                VALUES ('$ts', $nr_server, '$server', $mc, $pc, $vpc, $vc, $bc)";

            $ressql = $conn->query($sql);

            $imported++;

            $serveridx += 6;
        }


        $row++;
    }

    echo "$imported rows imported\n";

    fclose($handle);

    $conn->close();
}
