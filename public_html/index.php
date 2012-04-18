<?php
include('header.php');
include_once('lib/functions.php');
?>
<h2>Usage</h2>
<p>Enter your username and authkey to start. Please choose a software package to install too.</p>
<form method="post" action="login.php">
<div class="row"><label class="col1">username: </label>
  <span class="col2"><input type="text" name="username"></span></div><br>
<div class="row"><label class="col1">authkey: </label>
  <span class="col2"><input name="authkey" type="password"></span></div><br>
<div class="row"><label class="col1">Select one: </label>
  <span class=\"col2\"><select name="software" id="swpkg">
  <option value="">Please choose one: </option>
<?php
if ($handle = opendir($script_path)) {
  while (false !== ($file = readdir($handle))) {
    if ($file != "." && $file != "..") {
      print "<option value=\"$file\">$file</option>\n";
    }
  }
  closedir($handle);
}
?>
  </select></span></div><br>
  <div class="submit" align="center"><input name="authenticate" value="authenticate"
 type="submit"></div><br>
</form>
</p>
<?
include('footer.php')
?>
