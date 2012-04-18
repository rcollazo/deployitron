<?php
include_once('lib/functions.php');

$cookieValue = checkCookieExists("info");
$cookieValue = getCookieData($cookieValue);
$userName = $cookieValue['userName'];
$authKey  = $cookieValue['authKey'];
$software = $cookieValue['software'];

$cookieValue = checkCookieExists("apidetails");
$cookieValue = getCookieData($cookieValue);

$auth = new RackAuth($userName,$authKey);
$auth->setXAuthToken($cookieValue['XAuthToken']);
$auth->setXStorageToken($cookieValue['XStorageToken']);
$auth->setXStorageUrl($cookieValue['XStorageUrl']);
$auth->setXServerManagementUrl($cookieValue['XServerManagementUrl']);
$auth->setXCDNManagementURL($cookieValue['XCDNManagementUrl']);

$cloudServers = new RackCloudService($auth);

$serverName = $_POST['serverName'];
$imageID = $_POST['imageID'];
$flavorID = $_POST['flavorID'];
settype($imageID,"integer");
settype($flavorID,"integer");

$newServer = $cloudServers->createServer($serverName,$imageID,$flavorID,array("Description"=>"Deployed by http://deployitron.com"));

$lastHTTPCode = Request::getLastHTTPCode();
$lastErrorMessage = Request::getLastError();

if($lastHTTPCode != 202) {
  include('header2.php');
  print "There was a problem creating the image. Maybe you should try a different image name. Please <a href=\"javascript: history.go(-1)\">try again</a>.";
  include('footer.php');
  exit(1);
}

$newServer = object2array($newServer);

$serverInfo = array('serverID' => $newServer['server']['id'],
		    'serverPwd' => $newServer['server']['adminPass'],
		    'serverAddresses' => $newServer['server']['addresses']);

$cookieContent = makeCookie($serverInfo);
setMyCookie("serverdetails", $cookieContent, 3600);

// Add info to the DB for analysis
require_once 'MDB2.php';

$dsn = "mysql://$dbuser:$dbpasswd@$dbhost/$dbname";
$options = array ('persistent' => true);
$mdb2 =& MDB2::factory($dsn, $options);

$userName = md5($userName);
$sql = "SELECT * FROM user_info WHERE username='$userName'";
$result = $mdb2->query($sql);
$data = $result->fetchAll();
$result->free();
$data = $mdb2->queryAll($sql);

if(empty($data)) {
  $sql = "INSERT INTO user_info (username,count) VALUES (\"$userName\",1);";
  $statement = $mdb2->prepare($sql);
  $statement->execute($data);
  $statement->free();
  echo "it was empty!\n";
} else {
  $sql = "UPDATE user_info SET count=count+1 WHERE username=\"$userName\";";
  $statement = $mdb2->queryAll($sql);
}

$sql = "UPDATE flavor_info SET count=count+1 WHERE id=\"$flavorID\";";
$statement = $mdb2->queryAll($sql);

$sql = "UPDATE image_info SET count=count+1 WHERE id=\"$imageID\";";
$statement = $mdb2->queryAll($sql);

$sql = "UPDATE software_info SET count=count+1 WHERE name=\"$software\";";
$statement = $mdb2->queryAll($sql);


header('Location: http://' . $_SERVER['SERVER_NAME'] . '/status.php');
flush_buffers();

?>