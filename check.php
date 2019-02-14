<?php

echo<<<_HTML
<html>
<head></head>
<body style="text-align:center; margin-top:10%;">
<from method='post' action='#' enctype='multipart/form-data'>
	Maybe virus? <input type='file' name='filename' size='10'>
	<input type='submit'> 
</form>
_HTML;

if($_FILES){
	require_once "login.php";

	$inFile = fopen($_FILES['filename']['tmp_name'], 'r');
	$input = fread($inFile, filesize($_FILES['filename']['tmp_name']));
	fclose($inFile);

	//now we have file byte-wise in input.

//	$SHA2 = hash('sha256', $input);
//	$MD5 = hash('md5', $input);
	$ripemd = hash('ripemd160', $input);

	$conn =	new mysqli($hn, $un, $pw, $db);
	$query = "SELECT name FROM malware WHERE ripemd = $ripemd";

	$result = $conn->query($query);
	
	echo "<br><br> <h1>$result</h1> <br>";
}

?>