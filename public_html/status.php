<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Automagic Deploy-itron</title>
<link rel="stylesheet" type="text/css" href="/css/style.css"> 
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>
<script type="text/javascript">
   var count = 0;

function update() {
  $.get('http://deployitron.com/getStatus.php', function(data) {
    var results = $.parseJSON( data );

    if (typeof results == undefined) { document.location="http://deployitron.com/"; }
    if (results["server"]["status"] == "ACTIVE") { document.location="http://deployitron.com/deploy.php"; }
    // alert(xmlhttp.responseText);
    document.getElementById("progress").innerHTML=results["server"]["progress"]+"%";
    document.getElementById("status").innerHTML=results["server"]["status"];
    setTimeout("update()",5000)
      });
}
</script>

</head>

<body onload="setTimeout('update()', 1000)">
<div id="container">
    <div id="header">
        <h1>
           Deploy-itron
        </h1>
    </div>
    <div id="content">
<h2>What now?</h2>
<p>The server is being kicked now. Please wait until the progress reaches 100% and the status is marked <strong>ACTIVE</strong>.</p>
<div>Progress: <strong><span id="progress">0%</span></strong></div>
<div>Status: <strong><span id="status">UNKNOWN</span></strong></div>

</div>
    <div id="footer">
        Made by <a href="http://www.collazo.ws">Robert Collazo</a> and made pretty by <a href="http://nata2.org">Harper</a>. ©2010-2012.
    </div>
</div>
    
</body>
</html>
