<?php
include_once('lib/functions.php');

$userName = $_POST['username'];
$authKey  = $_POST['authkey'];
$software = $_POST['software'];

if(!preg_match('/^[a-z][0-9a-z]{2,15}$/',$userName)) {
  include('header.php');
  print "There seems to be a problem with your username. Please <a href=\"javascript: history.go(-1)\">try again</a>.";
  include('footer2.php');
  exit(1);
}

if(strlen($authKey) != 32) {
  include('header.php');
  print "There seems to be a problem with your authkey. Please <a href=\"javascript: history.go(-1)\">try again</a>.";
  include('footer2.php');
  exit(1);
}

if(empty($software)) {
  include('header.php');
  print "You must choose a software package to install. Please <a href=\"javascript: history.go(-1)\">try again</a>.";
  include('footer2.php');
  exit(1);
}

$info = array('userName' => $userName,
	      'authKey'  => $authKey,
	      'software' => $software);

$cookieContent = makeCookie($info);

setMyCookie("info", $cookieContent, 3600);


header('Location: http://' . $_SERVER['SERVER_NAME'] .'/info.php');
?>
