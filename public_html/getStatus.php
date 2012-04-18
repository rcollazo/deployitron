<?php
include_once('lib/functions.php');

$cookieValue = checkCookieExists("info");
$cookieValue = getCookieData($cookieValue);
$userName = $cookieValue['userName'];
$authKey  = $cookieValue['authKey'];

$cookieValue = checkCookieExists("serverdetails");
$serverInfo = getCookieData($cookieValue);

$cookieValue = checkCookieExists("apidetails");
$cookieValue = getCookieData($cookieValue);

$auth = new RackAuth($userName,$authKey);
$auth->setXAuthToken($cookieValue['XAuthToken']);
$auth->setXStorageToken($cookieValue['XStorageToken']);
$auth->setXStorageUrl($cookieValue['XStorageUrl']);
$auth->setXServerManagementUrl($cookieValue['XServerManagementUrl']);
$auth->setXCDNManagementURL($cookieValue['XCDNManagementUrl']);

ob_start();

if(empty($cookieValue['XStorageToken']) || (!isset($serverInfo))) {
  $invalid = array("server" => array(
				     "progress" => "0",
				     "status" => 'Could not get server details. Please <a href="/">try again.</a>'
				     )
		   );
  echo json_encode($invalid);
  exit(1);
}

$cloudServers = new RackCloudService($auth);

$serverDetails = $cloudServers->listServer($serverInfo['serverID']);
$serverDetails = object2array($serverDetails);

echo json_encode($serverDetails);

flush_buffers();
?>