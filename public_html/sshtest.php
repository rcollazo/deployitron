<?php

include('lib/functions.php');

function checkSSH($host_ip) {
  exec("/usr/lib64/nagios/plugins/check_ssh $host_ip", $output=array(), $return);
  if($return==0) {
    return true;
  }
  else {
    return false;
  }
}

@apache_setenv('no-gzip', 1);
@ini_set('output_buffering', 0);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
ob_start();

while(!checkSSH('174.143.202.140')) {
  echo "waiting..<br>";
  flush_buffers();
}

echo "SSH is up!";
?>