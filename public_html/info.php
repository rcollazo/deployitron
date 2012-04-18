<?php
include_once('lib/functions.php');

$cookieValue = checkCookieExists("info");
$cookieValue = getCookieData($cookieValue);

$userName = $cookieValue['userName'];
$authKey  = $cookieValue['authKey'];
$software = $cookieValue['software'];

$auth = new RackAuth($userName,$authKey);
@$auth->auth();

$lastHTTPCode = Request::getLastHTTPCode();
$lastErrorMessage = Request::getLastError();

if($lastHTTPCode == 204) {
  $apiAuthInfo = array('XAuthToken' => $auth->getXAuthToken(),
		       'XStorageToken' => $auth->getXStorageToken(),
		       'XStorageUrl' => $auth->getXStorageUrl(),
		       'XServerManagementUrl' => $auth->getXServerManagementUrl(),
		       'XCDNManagementUrl' => $auth->getXCDNManagementUrl()
		       );

  $cookieContent = makeCookie($apiAuthInfo);
  setMyCookie("apidetails", $cookieContent, 3600);  
}

include('header2.php');
ob_start();
if($software == "magento") {
  print "<p>Since you have chosen Magento, you may want to choose a size larger than 4Gb.</p>";
}
if($debug) {
  echo "<p>HTTPCode: $lastHTTPCode<br>\nErrorMessage: $lastErrorMessage</p>\n";
}

if($lastHTTPCode == (401|403)) {
  print "There seems to be a problem with your authentication info. Please <a href=\"/\">try again</a>.";
  include('footer2.php');
  exit(1);
}

if($lastHTTPCode == 413) {
  print "Too many servers generated today. Please <a href=\"/\">try again</a> later.";
  include('footer2.php');
  exit(1);
}

$cloudServers = new RackCloudService($auth);

$flavors = new RackCloudFlavor($auth);

$flavor = $flavors->listFlavors(true);
$flavor = object2array($flavor);

$images = new RackCloudImage($auth);
$image = $images->listImages(true);
$image = object2array($image);

print '<form action="kick.php" method="post">' . "\n";

print "<div class=\"row\"><label class=\"col1\">Server Name: </label><span class=\"col2\"><input name=\"serverName\"></span></div>\n<br \>\n";
print "<div class=\"row\"><label class=\"col1\">Flavor: </label>";
foreach ( $flavor as $sizes ) {
  while(list($k, $v) = each ($sizes)) {
    if(is_array($v)) {
      foreach ($sizes as $size) {
	if(($software == "magento") && (!preg_match("/4|8|15\.5/",$size['name']))) {
	  continue;
	}
	print '<div class="row"><label class="col1"></label><span class="col3"><input type="radio" name="flavorID" value="'.$size['id'].'">'.$size['name'].'</span></div><br>' ."\n";
	next($size);
      }
    }
  }
}

print "</div>";

echo "<br />\n";
print "<div class=\"row\"><label class=\"col1\">Image: </label>";

foreach ( $image as $distros ) {
  while(list($k, $v) = each ($distros)) {
    if(is_array($v)) {
      foreach ($distros as $distro) {
	if (preg_match("/Red Hat|CentOS/",$distro['name'])) {
	  print '<div class="row"><label class="col1"></label><span class="col3"><input type="radio" name="imageID" value="'.$distro['id'].'">'.$distro['name']."</input></span></div><br>\n";
	  next($size);
	}
      }
    }
  }
}
print "</div>";
print "<br />\n";
print "<div class=\"submit\" align=\"center\"><input value=\"Deploy!\" name=\"Submit\" type=\"submit\"></div>\n";
print "</form>\n";

include('footer2.php');
flush_buffers();
?>
