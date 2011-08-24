<?
    $usr = "username"; 
    $pwd = "password"; 
    $db = "database"; 
    $host = "host"; 
    # connect to database 
    $cid = mysql_connect($host,$usr,$pwd); 
    if (!$cid) { echo("ERROR: " . mysql_error() . "\n");    }
?>