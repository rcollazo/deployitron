<?php
ob_start();

include_once('lib/functions.php');

$authValues = checkCookieExists("info");
$authValues = getCookieData($authValues);
$software = $authValues['software'];

$serverValues = checkCookieExists("serverdetails");
$serverValues = getCookieData($serverValues);
deleteMyCookie("info");
deleteMyCookie("serverdetails");
deleteMyCookie("apidetails");
$ip = $serverValues['serverAddresses']['public'][0];

include('header3.php');
flush_buffers();

print "<h3>Deploying $software ..<h3>";
print "<h3>Waiting on the server to start up ..";
flush_buffers();

$i = 0;
$y = 2;
while(!checkSSH($ip)) {
  $i++;
  echo ".";
  sleep(1);
  flush_buffers();
  if($i > 120) {
      print "<br /></h3>There seems to be a problem. It took longer than 120 seconds to start the build. You can check your <a href=\"https://manage.rackspacecloud.com/\">control panel</a> and visit the console of the server to see if it is responsive. You can try rebooting the server or <a href=\"/\">starting over</a>.</h3><br /> I'll try $y more timesy..";
      include ('footer2.php');
      flush_buffers();
      $i = 0;
      $y--;
      if($y = 0) {
	exit(1);
      }
  }
}

print "</h3>\n<br><h3>Updating the server at $ip (please be patient) .. </h3>\n";

flush_buffers();

//print_r($authValues);
//print_r($serverValues);
//die();

$username = "root";
$password = $serverValues['serverPwd'];

print "<p>Your server password is '$password' - SAVE THIS!</p>";

$connection = ssh2_connect($ip, 22);

ssh2_auth_password($connection, $username, $password);

echo "<h2>Starting installation .. </h2>\n";
flush_buffers();
ssh2_scp_send($connection, $script_path.$software.'/scripts.tar.gz', '/scripts.tar.gz', 0644);
$stream = ssh2_exec($connection, 'cd / && tar xzvf /scripts.tar.gz && service iptables stop');
stream_set_blocking($stream, true);
$output = stream_get_contents($stream);
fclose($stream);

$stream = ssh2_exec($connection, "/tmp/pyserver.py /tmp/run.sh".PHP_EOL);
$output = stream_get_contents($stream);
fclose($stream);
sleep(4);

print "<iframe id=\"serverIframeId\" src =\"http://$ip:8080\" width=\"100%\" height=\"80%\"><p>Your browser does not support iframes.</p></iframe>";

include('footer2.php');
flush_buffers();

?>
