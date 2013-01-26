<?php	
	session_start();
	
	$loginInvalid = false;
	
	if(isset($_SESSION['user_id']) && !$_SESSION['user_id'])
	{
		$loginInvalid = true;
		unset($_SESSION['user_id']);
	}
	
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>YouLess - Energy Monitor</title>
		<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
		<link type="text/css" href="css/style.css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
		<script>
			$(document).ready(function() {
				$('input[name=user]').focus();
			});
		</script>
	</head>
	<body>
		<div id="topHeader"></div>
		<div id="header">
		
			<div id="logo"></div>
					
		</div>
		<div id="container">
		
			<div id="loginForm">
				<form method="post" action="index.php">
				<table>
					<tr>
						<td colspan="2" id="invalidLogin"><?php echo ($loginInvalid ? 'Gebruikersnaam en/of wachtwoord onjuist' : '') ?></td>
					</tr>				
					<tr>
						<td>Gebruikersnaam:</td>
						<td><input type="text" name="user" size="20" /></td>
					</tr>
					<tr>
						<td>Wachtwoord:</td>
						<td><input type="password" name="pass" size="20" /></td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="hidden" name="login" value="1" />
							<input id="loginSubmit" type="submit" value="Inloggen"/>
						</td>
					</tr>
				</table>

				</form>
			</div>

		</div>
	</body>
</html>