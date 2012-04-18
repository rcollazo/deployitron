<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>Automagic Deploy-itron</title>
<link rel="stylesheet" type="text/css" href="/css/style.css"/> 
</head>

<body>
<div id="container">
    <div id="header">
        <h1>
           Deploy-itron
        </h1>
    </div>
    <div id="content">
<h2>What is this?</h2>

<p>This site will deploy a Rackspace Cloud Server with Drupal 7.0, Wordpress, or Magento 1.3.2.1 installed.</p>
<p>Currently the only supported distros are CentOS or Red Hat. Other distros will be available shortly.</p>

<p>The Rackspace Cloud Server will be installed with iptables configured to only allow SSH and HTTP/HTTPS traffic. MySQL and the PHP APC module  will be installed and configured as well.</p>

<p>Wordpress will be installed with the <a target="_blank" href="http://wordpress.org/extend/plugins/w3-total-cache/">W3 Total Cache</a> (use Rackspace Cloud Files!) plugin. Don't forget to activate it.</p>

