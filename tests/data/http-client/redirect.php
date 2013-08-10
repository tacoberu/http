<?php
header('Location: http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/redirect-to.php');
?><!DOCTYPE html>
<html>
<head>
<meta name=robots content=noindex>
<meta name=generator content='Taco'> 
 
<style>body{color:#333;background:white;width:500px;margin:100px auto}h1{font:bold 47px/1.5 sans-serif;margin:.6em 0}p{font:21px/1.5 Georgia,serif;margin:1.5em 0}small{font-size:70%;color:gray}</style> 
 
<title>Redirect</title> 
 
</head>
<body>
<h1>Redirect</h1> 
 
<p><a href="redirect-to.php">Please click here to continue</a>.</p>
 
<p><small>See Other 303</small></p>
</body>
</html>

