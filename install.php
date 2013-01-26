<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>YouLess - Energy Monitor</title>
		<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
		<style>
			html, body{
				font-family: Verdana, sans-serif;
				font-size:10px;
				color:#2d2d2d;
			}	
			#installDiv{
				position:absolute;
				top:50%;
				left:-200px;
				margin-left:50%;
				padding:20px;
				width:400px;
				height:200px;
				border:1px solid #e5e5e5;
				background:#f1f1f1;	
			}
			.error{
				color:red;
			}
			#topHeader{
				position:absolute;
				left:0;
				top:0;
				right:0;
				height:25px;
				color:#d2d2d2;
				background: -moz-linear-gradient(top, #474747 0%, #363636 100%); /* FF3.6+ */
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#474747), color-stop(100%,#363636)); /* Chrome,Safari4+ */
				background: -webkit-linear-gradient(top, #474747 0%,#363636 100%); /* Chrome10+,Safari5.1+ */
				background: -o-linear-gradient(top, #474747 0%,#363636 100%); /* Opera 11.10+ */
				background: -ms-linear-gradient(top, #474747 0%,#363636 100%); /* IE10+ */
				background: linear-gradient(top, #474747 0%,#363636 100%); /* W3C */
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#474747', endColorstr='#363636',GradientType=0 ); /* IE6-9 */
			}
			#header{
				position:absolute;
				left:0;
				top:25px;
				right:0;
				height:80px;
				border-bottom:1px solid #e5e5e5;
				background:#f1f1f1;	
			}
			#logo{
				position:absolute;
				top:20px;
				left:60px;
				width:125px;
				height:40px;
				background:url('img/logo.png') no-repeat 0 0;
			}
			#container{
				position:absolute;
				left:0;
				top:106px;
				right:0;
				padding:20px 10px;
				background:white;
			}			
		</style>
	</head>
	<body>
		
		
		<div id="topHeader">
		</div>
		<div id="header">
		
			<div id="logo"></div>
		
		</div>
		<div id="container">
			<div id="installDiv">
		
<?php

	$errorMsg = '';
	$ok = true;

	if (version_compare(PHP_VERSION, '5.2.0') <= 0) 
	{
		$errorMsg .= '<p class="error"><b>PHP 5.2.0</b> is vereist</p>';
		$ok = false;
	}	
	if(!file_exists('inc/settings.inc.php'))
	{
		$errorMsg .= '<p class="error"><b>settings.inc.php</b> ontbreekt, pas <b>settings.inc.php.example</b> aan en hernoem deze naar <b>settings.inc.php</b></p>';
		$ok = false;
	}
	if(!extension_loaded('pdo_mysql'))
	{
		$errorMsg .= '<p class="error"><b>PDO Mysql</b> extension ontbreekt!</p>';
		$ok = false;
	}
	if(!extension_loaded('curl'))
	{
		$errorMsg .= '<p class="error"><b>CURL extension</b> ontbreekt!</p>';
		$ok = false;
	}
	
	echo $errorMsg;
	if($ok)
	{
		include 'inc/settings.inc.php';
		
		try {
		    $db = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
		    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		
		    $succes = $db->exec("CREATE DATABASE `".DB_NAME."`;
				CREATE TABLE IF NOT EXISTS `".DB_NAME."`. `data_h` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `time` datetime NOT NULL,
				  `unit` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				  `delta` int(11) NOT NULL,
				  `value` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				  `inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `time` (`time`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;
				
				CREATE TABLE IF NOT EXISTS `".DB_NAME."`. `kwh_h` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `kwh` varchar(20) NOT NULL,
				  `inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `inserted` (`inserted`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;					

				CREATE TABLE IF NOT EXISTS `".DB_NAME."`. `settings` (
				  `key` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				  `value` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				  UNIQUE KEY `key` (`key`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;	

				INSERT INTO `".DB_NAME."`. `settings` (`key`, `value`) VALUES
				('cpkwh', '0.22'),
				('cpkwh_low', '0.10'),
				('dualcount', '0'),
				('cpkwhlow_start', '21:00'),
				('cpkwhlow_end', '07:00'),
				('liveinterval', '1000');	
				
				CREATE TABLE IF NOT EXISTS `".DB_NAME."`. `users` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;
				
				INSERT INTO `".DB_NAME."`. `users` (`id`, `username`, `password`) VALUES
				(2, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997');														
		    ");
			if($succes > 0)
			{
				echo "<p style='color:green;'>Installatie succesvol. Verwijder <b>install.php</b> en <b>update.php</b></p>";
				echo "<p style='color:green;'>Default gebruikersnaam/wachtwoord is <b>admin</b>/<b>admin</b></p>";
			}
		} catch (PDOException $e) {
		    die(print("<p class='error'>Database error: ". $e->getMessage() ."</p>"));
		}		
	}
?>
			</div>
		</div>
	</body>
</html>
