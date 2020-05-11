<?php

// Enter your settings and save as conf.php


// You can define different secret to filter bbb-origin-server-name
// Use * as wildcard to show all origin servers
$secrets = array ("<Secret_to_show_all_origin_servers>" => "*",
                  "<SecondSecret>" => "origin.server1.tld",
                  "<ThirdSecret>"  => "origin.server2.tld" );

$bbb_secret="your BBB secret";
$servername="your server name";         // e.g. https://bbb.yourdomain.com

$filename = "/var/log/bbb-usage.csv";   // filename for data file
$timezone = "Europe/Vienna";            // your timezone
$delimiter = ";";                       // delimiter for data file

$db_server = "localhost";
$db_port = "12987";
$db_user = "bbb";
$db_password = "BBB-Usage!";
$db_name = "bbb-usage";