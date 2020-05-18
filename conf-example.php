<?php

// Enter your settings and save as conf.php

// If you want to protect the site you can define access codes here with $secrets
// Don't mix up these secrets with your BBB-secret
// if $secrets is not set, anyone can access the site

// If you just have one frontend (e.g. Greenlight) you can use following setting
// When opening the page enter "MySecret" and hit return

//$secrets = array ("MySecret" => "%");

// You can define different secrets (access codes) to filter different frontends (API-parameter bbb-origin-server-name)
// Use % as wildcard to show all origin servers
/*$secrets = array ("<Secret_to_show_all_origin_servers>" => "%",
                    "<SecondSecret>" => "origin.server1.tld",
                    "<ThirdSecret>"  => "origin.server2.tld" );
*/

$bbb_secret="your BBB secret";          // your bbb-secret. Use bbb-conf --secret on your BBB-server to get the secret
$servername="your server name";         // e.g. https://bbb.yourdomain.com

$timezone = "Europe/Vienna";            // your timezone

$db_server = "localhost";               // mySQL settings.
$db_port = "3306";
$db_user = "<username>";
$db_password = "<password>";
$db_name = "bbb-usage";                 // database has to be created before starting bbb-usage
