<?php
include_once('lib/CookieManager.php');
include_once('lib/phprackcloud/class.rackcloudmanager.php');
include_once('lib/globals.php');

function setMyCookie($cookieName,$cookieContent,$time=600){
  $id = $GLOBALS['id'];
  $secretKey = $GLOBALS['secretKey'];
  $config = $GLOBALS['config'];

  $manager = new BigOrNot_CookieManager($secretKey, $config);

  $expire = time() + $time;  
  $value = $manager->setCookie($cookieName, $cookieContent, $id, $expire);
  return $value;
}

function deleteMyCookie($cookieName){
  $id = $GLOBALS['id'];
  $secretKey = $GLOBALS['secretKey'];
  $config = $GLOBALS['config'];

  $manager = new BigOrNot_CookieManager($secretKey, $config);
  $value = $manager->deleteCookie($cookieName);
  return $value;
}

function checkCookieExists($cookieName) {
  $secretKey = $GLOBALS['secretKey'];
  $config = $GLOBALS['config'];

  $manager = new BigOrNot_CookieManager($secretKey, $config);
  $cookieValue = $manager->getCookieValue($cookieName);

  if(!$cookieValue) {
    if(!headers_sent()) {
      include('header.php');
    }
    echo "There appears to be a problem. Please <a href=\"/\">start over</a>.";
    include('footer2.php');
    exit(1);
  }
  return $cookieValue;
}

function flush_buffers(){
  ob_end_flush();
  ob_flush();
  flush();
  ob_start();
} 

function makeCookie($cookieData) {
  $cookieContent = serialize($cookieData);
  return $cookieContent;
}

function getCookieData($cookieData) {
  $cookieContent = unserialize($cookieData);
  return $cookieContent;
}

function XMLToArray($xml)
{
  if ($xml instanceof SimpleXMLElement) {
    $children = $xml->children();
    $return = null;
  }

  foreach ($children as $element => $value) {
    if ($value instanceof SimpleXMLElement) {
      $values = (array)$value->children();
     
      if (count($values) > 0) {
        $return[$element] = XMLToArray($value);
      } else {
        if (!isset($return[$element])) {
          $return[$element] = (string)$value;
        } else {
          if (!is_array($return[$element])) {
            $return[$element] = array($return[$element], (string)$value);
          } else {
            $return[$element][] = (string)$value;
          }
        }
      }
    }
  }
 
  if (is_array($return)) {
    return $return;
  } else {
    return $false;
  }
} 

// From http://forum.weblivehelp.net/web-development/php-convert-array-object-and-vice-versa-t2.html
function array2object($data) {
  if(!is_array($data)) return $data;
   
  $object = new stdClass();
  if (is_array($data) && count($data) > 0) {
    foreach ($data as $name=>$value) {
      $name = strtolower(trim($name));
      if (!empty($name)) {
	$object->$name = array2object($value);
      }
    }
  }
  return $object;
}

function object2array($data){
  if(!is_object($data) && !is_array($data)) return $data;

  if(is_object($data)) $data = get_object_vars($data);

  return array_map('object2array', $data);
}

// From http://www.php.net/manual/en/function.get-object-vars.php#80260
function parseObject($obj, $values=true){
 
  $obj_dump  = print_r($obj, 1);
  $ret_list = array();
  $ret_map = array();
  $ret_name = '';
  $dump_lines = preg_split('/[\r\n]+/',$obj_dump);
  $ARR_NAME = 'arr_name';
  $ARR_LIST = 'arr_list';
  $arr_index = -1;
   
  // get the object type...
  $matches = array();
  preg_match('/^\s*(\S+)\s+\bObject\b/i',$obj_dump,$matches);
  if(isset($matches[1])){ $ret_name = $matches[1]; }//if
   
  foreach($dump_lines as &$line){
   
    $matches = array();
   
    //load up var and values...
    if(preg_match('/^\s*\[\s*(\S+)\s*\]\s+=>\s+(.*)$/', $line, $matches)){
       
      if(mb_stripos($matches[2],'array') !== false){
       
	$arr_map = array();
	$arr_map[$ARR_NAME] = $matches[1];
	$arr_map[$ARR_LIST] = array();
	$arr_list[++$arr_index] = $arr_map;
       
      }else{
       
	// save normal variables and arrays differently...
	if($arr_index >= 0){ 
	  $arr_list[$arr_index][$ARR_LIST][$matches[1]] = $matches[2];
	}else{
	  $ret_list[$matches[1]] = $matches[2];
	}//if/else
       
      }//if/else
     
    }else{
     
      // save the current array to the return list...
      if(mb_stripos($line,')') !== false){
       
	if($arr_index >= 0){
           
	  $arr_map = array_pop($arr_list);
           
	  // if there is more than one array then this array belongs to the earlier array...
	  if($arr_index > 0){
	    $arr_list[($arr_index-1)][$ARR_LIST][$arr_map[$ARR_NAME]] = $arr_map[$ARR_LIST];
	  }else{
	    $ret_list[$arr_map[$ARR_NAME]] = $arr_map[$ARR_LIST];
	  }//if/else
           
	  $arr_index--;
           
	}//if
       
      }//if
     
    }//if/else
     
  }//foreach
   
  $ret_map['name'] = $ret_name;
  $ret_map['variables'] = $ret_list;
  return $ret_map;
   
}//method

function generatePassword ($length = 8)
{

  // start with a blank password
  $password = "";

  // define possible characters
  $possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
    
  // set up a counter
  $i = 0; 
    
  // add random characters to $password until $length is reached
  while ($i < $length) { 

    // pick a random character from the possible ones
    $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
        
    // we don't want this character if it's already in the password
    if (!strstr($password, $char)) { 
      $password .= $char;
      $i++;
    }

  }

  // done!
  return $password;
}

/* Notify the user if the server terminates the connection */
function my_ssh_disconnect($reason, $message, $language) {
  printf("Server disconnected with reason code [%d] and message: %s\n",
         $reason, $message);
}

function checkSSH($host_ip) {
  exec("/usr/lib64/nagios/plugins/check_ssh -t 1 $host_ip", $output=array(), $return);
  if($return==0) {
    return true;
  }
  else {
    return false;
  }
}

?>