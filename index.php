<?php
	include "inc/settings.inc.php";
	include "classes/database.class.php";
	include "classes/generic.class.php";	
	include "inc/session.inc.php";
		
	$db = new Database();
	$gen = new Generic();
	$settings = $db->getSettings();
	
	$startTime = explode(":", $settings['cpkwhlow_start']);
	$endTime = explode (":", $settings['cpkwhlow_end']);
	
	$startSelect = $gen->timeSelector($startTime[0], $startTime[1], 'cpkwhlow_start');
	$endSelect = $gen->timeSelector($endTime[0], $endTime[1], 'cpkwhlow_end');

	$intervalOptions = array(
		'500' => '500',
		'1000' => '1000',
		'2000' => '2000',
		'5000' => '5000'
	);
	$intervalSelect = $gen->selector('liveinterval', $settings['liveinterval'], $intervalOptions);

?>	
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>YouLess</title>
		<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico" />
		<link type="text/css" href="css/style.min.css" rel="stylesheet" />
		<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.18.custom.min.js"></script>
		<script type="text/javascript" src="js/highstock.js"></script>
		<script type="text/javascript" src="js/modules/exporting.js"></script>
		<script type="text/javascript" src="js/script.min.js"></script>
		
		<link rel="apple-touch-icon" href="img/touch-icon-iphone.png" />
		<link rel="apple-touch-icon" sizes="72x72" href="img/touch-icon-ipad.png" />
		<link rel="apple-touch-icon" sizes="114x114" href="img/touch-icon-iphone-retina.png" />
		<link rel="apple-touch-icon" sizes="144x144" href="img/touch-icon-ipad-retina.png" />

		<meta content="yes" name="apple-mobile-web-app-capable" />
		<meta name="apple-mobile-web-app-status-bar-style" content="black" />
		<meta content="initial-scale=1.0, width=device-width, user-scalable=no" name="viewport" />
		<meta name="format-detection" content="telephone=no">
	</head>
	<body>
	
		<div id="overlay">
			<div id="dialog">
				<div id="message"></div>
				<input type="button" id="closeDialog" value="Sluit"/>
			</div>
			<div id="overlayBack"></div>
		</div>
		<div id="settingsOverlay" data-dualcount="<?php echo $settings['dualcount']; ?>" data-liveinterval="<?php echo $settings['liveinterval']; ?>">

			<form>
				<table>
					<tr>
						<td style="width:200px;">Meter type:</td><td>Enkel<input type="radio" name="dualcount" value="0" <?php echo ($settings['dualcount'] == 0 ? 'checked=checked' : '') ?>/> Dubbel<input type="radio" name="dualcount" value="1" <?php echo ($settings['dualcount'] == 1 ? 'checked=checked' : '') ?>/></td>
					</tr>				
					<tr>
						<td>Prijs per kWh:</td><td><input type="text" name="cpkwh" value="<?php echo $settings['cpkwh']; ?>"/></td>
					</tr>
					<tr class="cpkwhlow" <?php echo ($settings['dualcount'] == 1 ? '' : 'style="display:none;"') ?>;>
						<td>Prijs per kWh (laagtarief):</td><td><input type="text" name="cpkwh_low" value="<?php echo $settings['cpkwh_low']; ?>"/></td>
					</tr>	
					<tr class="cpkwhlow" <?php echo ($settings['dualcount'] == 1 ? '' : 'style="display:none;"') ?>;>
						<td>Tijd laagtarief:</td><td><?php echo $startSelect; ?> tot <?php echo $endSelect; ?></td>
					</tr>
					<tr>
						<td>Update interval live weergave:</td><td><?php echo $intervalSelect; ?> ms</td>
					</tr>															
					<tr>
						<td>Admin wachtwoord:</td><td><input type="password" name="password" value=""/></td>
					</tr>
					<tr>
						<td>Bevestig admin wachtwoord:</td><td><input type="password" name="confirmpassword" value=""/></td>
					</tr>										
					<tr>
						<td><input type="submit" id="saveSettings" value="Opslaan"/></td><td><input type="button" id="hideSettings" value="Sluit"/></td>
					</tr>
				</table>
			</form>	

			<div id="version">v1.2.2</div>
		</div>
		
		<div id="topHeader">
			<div id="settings"><a href="#" id="showSettings">Instellingen</a></div>
			<div id="logout"><a href="?logout=1">Logout</a></div>
		</div>
		<div id="header">
		
			<div id="logo"></div>
		
			<div id="menu">
				<ul class="btn">
					<li class="selected"><a href="#" data-chart="live" class="showChart">Live</a></li>
					<li><a href="#" data-chart="day" class="showChart">Dag</a></li>
					<li><a href="#" data-chart="week" class="showChart">Week</a></li>
					<li><a href="#" data-chart="month" class="showChart">Maand</a></li>
				</ul>
			</div>
			
			<div id="cpkwhCounter" class="counter chart day week month"></div>
			<div id="wattCounter" class="counter chart live"></div>
			<div id="kwhCounter" class="counter chart day week month" style="display:none;"></div>
		</div>
		<div id="container">
			<div id="datepickContainer" class="chart day week month">
				<input type="text" id="datepicker" value="<?php echo date("Y-m-d"); ?>">			
			</div>
			<div id="history" class="chart day week month"></div>
			<div id="live" class="chart live" style="height: 500px; min-width: 500px;"></div>

		</div>
	</body>
</html>
