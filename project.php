<?php
session_start();
ini_set('session.gc_maxlifetime', 60*60); 
				//one hour. 
				//does not see to be working.
$salt = "salt andpersandpepperspray_-_";
$pepper = "_-_somebodyThoughtTheywere clever";
$output = "";
require_once "login.php";

// Logout function
if(isset($_POST['logout'])){
	destroy_session_and_data();
	unset($_POST['logout']);
}
// SESSION variables for staying logged in.
if(isset($_POST['username'])){
	$_SESSION['username'] = $_POST['username'];
	$_SESSION['password'] = hash('ripemd160', $salt . $_POST['password'] . $pepper);
	unset($_POST['username']);
	unset($_POST['password']);
}

if($_FILES){
if(!$_FILES['userfile']['error']){

	// FILE byte-wise
        $inFile = fopen($_FILES['userfile']['tmp_name'], 'r');
        $input = fread($inFile, filesize($_FILES['userfile']['tmp_name']));
		fclose($inFile);
		$conn = new mysqli($hn, $un, $pw, $db);
	
	if(! $_POST['virus']){      // virusCheck
        	$ripemd = hash('ripemd160', $input);
        	$query = "SELECT name, description FROM malware WHERE ripemd LIKE '$ripemd'";

        	$result = $conn->query($query);
            if(!$result) 
            	die($conn->error);
            $result = $result->fetch_array();
			if(empty($result['name'])){$color = "#0F64";}
			else {$color = "orange";}

        	$output = div($color)
				. "--- <b>{$result['name']}</b> --- <br> {$result['description']}</div>";
	}
	else if($_POST['virus']){ 	// virusUpload
		if(login($conn)){
			$name = sanitize($conn, $_POST['name']);
        	$desc = sanitize($conn, $_POST['desc']);
			$ripemd = hash('ripemd160', $input);
			$query = "SELECT name, submitted_by FROM malware WHERE ripemd LIKE '$ripemd'";
			$result = $conn->query($query);
			
			$regex = "/^[w]/"; // english letters, digits, and "_"
			if($result->num_rows == 0){	// not already in database
				if(! preg_match($regex, $name) || $name == ""){ // name validation
					$query = "INSERT INTO malware(name, description, submitted_by, ripemd)" .
					  	 	" VALUES('$name', '$desc', '{$_SESSION['username']}', '$ripemd')";
        			$result = $conn->query($query);
		
	        		if(!$result) die($conn->error);
	        		$color = "green";
					$output = div($color)
						. "$name was added to the database.</div>";
				}
				else{ // bypassed javascript form validation
					$color = "red";
					$outut = div($color)
						. "'$name' is not a valid format.</div>"	;
				}
			}
			else{ // already in database
				$result = $result->fetch_assoc();
				$color = "orange";
				$output = div($color)
					. "$name was already in the database under the name: {$result['name'][0]}<br>"
					. "And was uploaded by: {$result['submitted_by'][0]}</div>";	
			}
		}
		else{ // wrong login
			destroy_session_and_data();
			$color = "red";
			$output = div($color) 
				. "wrong username/password combination</div>";
		}
	}
$conn->close();
}
}

main_html($output);

/*
	Functions below ----
*/

//Checks if password is correct every Upload.
function login($conn){

	$query = "SELECT password FROM users WHERE username LIKE '{$_SESSION['username']}'";
	$result = $conn->query($query);
	if(!$result) return false;
	$result = $result->fetch_array();

	return $result['password'] == $_SESSION['password'];
}

// forget me
function destroy_session_and_data(){
	$_SESSION = array();
	unset($_SESSION['username']);
	unset($_SESSION['password']);
	setcookie(session_name(), '', time() - 2592000, '/');
	session_destroy();
}

// sanitize input for all SQL 
function sanitize($conn, $string){

         if(get_magic_quotes_gpc()) $string = stripslashes($string);
         return $conn->real_escape_string(htmlentities($string));
}

// div with given color
function div($color){
	return "<div id='result' style='background-color:$color; " 
			. "display:inline-block; max-width:80%; padding:10px; border-radius:10px;'>";
}
/*
	HTML below ---
*/
function main_html($output){
	if(isset($_SESSION['username'])){
	$login_html = "";
	}
	else{
	$login_html = login_html();
	}
	
	echo <<<_HTML
	<html>
	<head></head>
	<body style="text-align:center; margin-top:10%;">
	<script type="text/javascript">
		function visible() {
    		if (document.getElementById('virus').checked) {
        		document.getElementById('upload').style.visibility = 'visible';
    		} 
			else {
        		document.getElementById('upload').style.visibility = 'hidden';
    		}
		}
		function validation(form){
			if (document.getElementById('virus').unchecked){
				var fail = "";
				var virus_name = form.name.value;
				var regex = /[^A-Za-z0-9_\-]/;
				if (regex.test(virus_name) || virus_name.length == 0){fail += "Invalid name of virus.";}
				
				if(fail == ""){return true;}
				else{
				alert(fail);
				return false;	
				}
			}
			return true;
		}
	</script>
	<form name= 'logout' method='post' action="project.php" enctype='multipart/form-data'>
    	<input type="submit" name="logout" value="Logout"></form><br>
	<div id="form" style="display:inline-block;border-radius:10px; padding:15px; border:1px solid black; text-align:left;">
	<form name='main' method='post' action='project.php' enctype='multipart/form-data' onsubmit="return validation(this)">
		<div style="background-color:#EEE; display:inline-block;"><label>I know this is a virus</label>
		<input name="virus" id="virus" type="radio" onchange="javascript:visible();" value="1">
		</div>
		||
		<div style="background-color:#EEE; display:inline-block;">
		<input name="virus"  type="radio" onchange="javascript:visible();" value="0" checked="checked">
		<label>Check if this is a virus</label></div>
	 <br>
	<ul>
		<label>Enter file:</label> 
		<input type='file' name='userfile' size='10'>
	</ul>
	<div id="upload" style="visibility:hidden;">
	<ul>
	      <label>Name of virus</label>
	      <input type="smalltext" name="name">
	</ul>
	<ul>
	      <label>Description</label>
	      <input type="text" name="desc">
	</ul>
	{$login_html}
    </div>
	<ul>
        <input type='submit'>
	</ul>
	</form>
	</div> <br><br><br>
    {$output}
_HTML;
}
function login_html(){
	return <<< _HTML
	<div id="login">
	<ul>
		<label>Username</label>
	    <input type="text" name="username" onclick="return [a-zA-Z0-9].test(event.target.value)">
	</ul>
	<ul>
		<label>Password</label>
    	<input type="text" name="password">
	</ul>
	</div>
_HTML;
}
?>