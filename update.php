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
	
	if(!file_exists('inc/settings.inc.php'))
	{
		$errorMsg .= '<p class="error"><b>settings.inc.php</b> ontbreekt, pas <b>settings.inc.php.example</b> aan en hernoem deze naar <b>settings.inc.php</b></p>';
		$ok = false;
	}
	
	echo $errorMsg;
	if($ok)
	{
		include 'inc/settings.inc.php';
		
		try {
		    $db = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
		    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
		
		    $succes = $db->exec("
				CREATE TABLE IF NOT EXISTS `".DB_NAME."`. `kwh_h` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `kwh` varchar(20) NOT NULL,
				  `inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  KEY `inserted` (`inserted`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;		    

				INSERT IGNORE INTO `".DB_NAME."`. `settings` (`key`, `value`) VALUES
				('cpkwh_low', '0.10'),
				('dualcount', '0'),
				('cpkwhlow_start', '21:00'),
				('cpkwhlow_end', '07:00'),
				('liveinterval', '1000');
																	
		    ");

			echo "<p style='color:green;'>Update succesvol. Verwijder <b>install.php</b> en <b>update.php</b></p>";

		} catch (PDOException $e) {
		    die(print("<p class='error'>Database error: ". $e->getMessage() ."</p>"));
		}		
	}
?>
			</div>
		</div>
	</body>
</html>
