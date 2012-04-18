#!/usr/bin/php

<?php
$dbname = "wpdb";
$dbuser = "wpdb_user";
$dbpass = $argv[1];
$dbprefix = substr(md5(uniqid(rand(), true)),4,3) . "_";

$secret_keys = file('/tmp/keys.txt');
foreach ( $secret_keys as $k => $v ) {
  $secret_keys[$k] = substr( $v, 28, 64 );
}
$key = 0;

$configFile = file('/var/www/html/wp-config-sample.php');

foreach ($configFile as $line_num => $line) {
  switch (substr($line,0,16)) {
  case "define('DB_NAME'":
    $configFile[$line_num] = str_replace("database_name_here", $dbname, $line);
    break;
  case "define('DB_USER'":
    $configFile[$line_num] = str_replace("'username_here'", "'$dbuser'", $line);
    break;
  case "define('DB_PASSW":
    $configFile[$line_num] = str_replace("'password_here'", "'$dbpass'", $line);
    break;
  case '$table_prefix  =':
    $configFile[$line_num] = str_replace('wp_', $dbprefix, $line);
    break;
  case "define('AUTH_KEY":
  case "define('SECURE_A":
  case "define('LOGGED_I":
  case "define('NONCE_KE":
  case "define('AUTH_SAL":
  case "define('SECURE_A":
  case "define('LOGGED_I":
  case "define('NONCE_SA":
    $configFile[$line_num] = str_replace('put your unique phrase here', $secret_keys[$key++], $line );
    break;
  }
}

$handle = fopen('/var/www/html/wp-config.php', 'w');
foreach( $configFile as $line ) {
  fwrite($handle, $line);
}

fclose($handle);
chmod('/var/www/html/wp-config.php', 0666);

?>
