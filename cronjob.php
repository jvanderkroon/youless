#!/usr/bin/php
<?php
if (PHP_SAPI == "cli" || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
	include "inc/settings.inc.php";
	include "classes/curl.class.php";
	include "classes/request.class.php";
	include "classes/database.class.php";
	
	$request = new Request();
	$db = new Database();
	
	// Update data table
	$data = $request->getLastHour();		
	
	$db->addHourlyData($data['tm'], $data['un'], $data['dt'], '"'. $data['val'] .'"');
	
	// Update kwh count
	$liveData = json_decode($request->getLiveData(), true);

	$db->addHourlyKwh($liveData['cnt']);
	
	echo $data['tm']."\n";
}
else
{
	echo "No direct access allowed!";
}
?>