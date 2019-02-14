<?php
echo <<<_HTML
<html>
<head>
<title>Final Project</title>
</head>
<body style="text-align:center">
<h1>upload your virus NOW, and recieve a free virus-check!</h1>
<form enctype="multipart/form-data" method="post" action="#">

<ul>
      <label><b>Enter Virus:</b></label>
      <input type="file" name="userFile">
</ul>      
<ul>
      <label><b>Name of virus</b></label>
      <input type="smalltext" name="name">
</ul>      
<ul>
      <label><b>Description</b></label>
      <input type="text" name="desc">      
</ul>
<ul>
      <label><b>Username</b><label>
      <input type="smalltext" name="username">
</ul>
<ul>    
      <label><b>Password</b></label>
      <input type="text" name="password">
</ul>
<ul>
      <input type="submit">
</ul>
</form>
_HTML;

if($_FILES){
	if(!$_FILES['userFile']['error']){
		$salt = "aenfj234***2454GH";

		require_once 'login.php';
		$inFile = fopen($_FILES['userFile']['tmp_name'], 'r');
		$input = fread($inFile, filesize($_FILES['userFile']['tmp_name']));
		fclose($inFile); 		

		$conn = new mysqli($hn, $un, $pw, $db);
		$username = sanitize($conn, $_POST['username']);
		$password = sanitize($conn, $_POST['password']);
		$password = hash('ripemd160', $password . $salt);
		$ripemd = hash('ripemd160', $input);		

		$query = "SELECT admin FROM users WHERE username = $username AND password = $password";
		
		$result = $conn->query($query);

		if($result == "yes"){
			   $name = sanitize($conn, $_POST['name']);
			   $desc = sanitize($conn, $_POST['desc']);

			   $query = "INSERT INTO malware VALUES(" . 
			   	  "NULL, $name, $desc, $username, $ripemd)";
			   $result = $conn->query($query);

			   if(!$result) die($conn->error);
			   echo "$name was added to the database.";
		}
		else{
			echo "<div style='background-color:pink; display:inline-block'>wrong username/password combination</div>";
		}
	}
}

function sanitize($conn, $string){

         if(get_magic_quotes_gpc()) $string = stripslashes($string);
         return $conn->real_escape_string($string);
}
?>