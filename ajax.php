<?php
include "inc/settings.inc.php";
include "classes/curl.class.php";
include "classes/request.class.php";	
include "classes/database.class.php";
include "classes/generic.class.php";

session_start();

$request = new Request();
$db = new Database();
$gen = new Generic();
$settings = $db->getSettings();

if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != false)
{

	if(isset($_GET['a']) && $_GET['a'] == 'live')
	{
		echo $request->getLiveData();
	}
	elseif(isset($_GET['a']) && $_GET['a'] == 'day' && isset($_GET['date']))
	{	
		
		$sqlDate = $_GET['date'];
		
		// Get data from specific day
		$rows = $db->getSpecificDay($sqlDate);
				
		if(count($rows) == 0)
		{
		
			echo '{"ok": 0, "msg":"Geen data beschikbaar op deze datum", "start": "'. $sqlDate .'", "val": " 0, 0", "kwh": 0, "price": 0}';
		
		}
		else
		{
		
			$i=0;
			
			foreach($rows as $k)
			{
				$row = explode(",", $k->value);
				$total = count($row);
				
				$time = strtotime($k->time);
				
				$timeAr[$i][] = $time;
				$dataAr[$i] = $row;
				
				for($t=1;$t<$total;$t++)
				{
					$timeAr[$i][$t] = $timeAr[$i][$t-1] +  (int)$k->delta;
				}
				$i++;
			}
			
			$timeStr = '';
			foreach($timeAr as $k)
			{
				$timeStr .= implode(",", $k);
			}
			
			// Create JS data string
			$i=0;
			$dataStr = '';
			
			foreach($dataAr as $k)
			{
				$dataStr .= ($i!=0 ? "," : "").implode(",", $k);
				$i++;
			}	
			
			// Output data
			echo '{"ok": 1, "start": "'. $sqlDate .'", "val": "'. str_replace("\"", "", $dataStr) .'"}';	
		
		}
			
	}
	elseif(isset($_GET['a']) && $_GET['a'] == 'calculate_day' && isset($_GET['date']))
	{	
		
		$sqlDate = $_GET['date'];
		
		// Get data from specific day
		$costs = $gen->calculateDayKwhCosts($sqlDate);	
			
		// Output data
		echo '{"ok": 1, "kwh": "'. number_format($costs['kwh'], 2, ',', '') .'", "kwhLow": "'. number_format($costs['kwhLow'], 2, ',', '') .'", "totalKwh": "'. number_format($costs['totalKwh'], 2, ',', '') .'", "price": "'. number_format($costs['price'], 2, ',', '') .'", "priceLow": "'. number_format($costs['priceLow'], 2, ',', '') .'", "totalPrice": "'. number_format($costs['totalPrice'], 2, ',', '') .'"}';	
		
			
	}	
	elseif(isset($_GET['a']) && $_GET['a'] == 'week' && isset($_GET['date']))
	{	
		
		$sqlDate = $_GET['date'];
		
		$week = date('W',strtotime($sqlDate));
		$year = date('Y',strtotime($sqlDate));
	
		$begin = date("Y-m-d", strtotime($year."W".$week));
		$end = date("Y-m-d", strtotime($year."W".$week)+(6*86400));		
				
		// Get data from specific week
		$rows = $db->getSpecificRange($begin, $end);

		if(count($rows) == 0)
		{
		
			echo '{"ok": 0, "msg":"Geen data beschikbaar op deze datum", "start": "'. $begin .'", "val": " 0, 0", "kwh": 0, "price": 0}';
		
		}
		else
		{
				
			$i=0;
			
			foreach($rows as $k)
			{
				$row = explode(",", $k->value);
				$total = count($row);
				
				$time = strtotime($k->time);
				
				$timeAr[$i][] = $time;
				$dataAr[$i] = $row;
				
				for($t=1;$t<$total;$t++)
				{
					$timeAr[$i][$t] = $timeAr[$i][$t-1] +  (int)$k->delta;
				}
				$i++;
				
			}
			
			$timeStr = '';
			foreach($timeAr as $k)
			{
				$timeStr .= implode(",", $k);
			}
			
			// Create JS data string
			$i=0;
			$dataStr = '';
			
			foreach($dataAr as $k)
			{
				$dataStr .= ($i!=0 ? "," : "").implode(",", $k);
				$i++;
			}
			
			// Output data
			echo '{"ok": 1, "start": "'. $begin .'", "val": "'. str_replace("\"", "", $dataStr) .'"}';	
		}
	}
	elseif(isset($_GET['a']) && $_GET['a'] == 'calculate_week' && isset($_GET['date']))
	{	
		
		$sqlDate = $_GET['date'];
		
		$week = date('W',strtotime($sqlDate));
		$year = date('Y',strtotime($sqlDate));
	
		$start = date("Y-m-d", strtotime($year."W".$week));
		$end = date("Y-m-d", strtotime($year."W".$week)+(6*86400));
		
		// Calculate totals/costs
		$costs = $gen->calculateRangeKwhCosts($start, $end);
		
		// Output data
		echo '{"ok": 1, "kwh": "'. number_format($costs['kwh'], 2, ',', '') .'", "kwhLow": "'. number_format($costs['kwhLow'], 2, ',', '') .'", "totalKwh": "'. number_format($costs['totalKwh'], 2, ',', '') .'", "price": "'. number_format($costs['price'], 2, ',', '') .'", "priceLow": "'. number_format($costs['priceLow'], 2, ',', '') .'", "totalPrice": "'. number_format($costs['totalPrice'], 2, ',', '') .'"}';	
	}	
	elseif(isset($_GET['a']) && $_GET['a'] == 'month' && isset($_GET['date']))
	{	
		
		$sqlDate = $_GET['date'];
		
		$month = date('m',strtotime($sqlDate));
		
		$data = $request->getSpecificMonth($month);	
		$values = explode('","', $data['val']);
		
		$begin = date("Y-m-d", strtotime($data['tm']));
		foreach($values as $k => $v)
		{
			$v = str_replace('"', '', $v);
			$values[$k] = str_replace(',','.',$v);
		}
		$dataStr = implode(',', $values);
		
		// Output data
		echo '{"ok": 1, "start": "'. $begin .'", "val": "'. $dataStr .'"}';	
	}	
	elseif(isset($_GET['a']) && $_GET['a'] == 'calculate_month' && isset($_GET['date']))
	{	
		
		$sqlDate = $_GET['date'];
		
		$month = date('m',strtotime($sqlDate));
		
		$start = date('Y-m',strtotime($sqlDate)).'-01';
		$end = date('Y-m-d', strtotime('-1 second', strtotime('+1 month', strtotime($start))));
				
		// Calculate totals/costs
		$costs = $gen->calculateRangeKwhCosts($start, $end);
		
		// Output data
		echo '{"ok": 1, "kwh": "'. number_format($costs['kwh'], 2, ',', '') .'", "kwhLow": "'. number_format($costs['kwhLow'], 2, ',', '') .'", "totalKwh": "'. number_format($costs['totalKwh'], 2, ',', '') .'", "price": "'. number_format($costs['price'], 2, ',', '') .'", "priceLow": "'. number_format($costs['priceLow'], 2, ',', '') .'", "totalPrice": "'. number_format($costs['totalPrice'], 2, ',', '') .'"}';
	}	
	elseif(isset($_GET['a']) && $_GET['a'] == 'saveSettings')
	{
	
		$excludedFields = array(
			'password',
			'confirmpassword',
			'cpkwhlow_start_hour',
			'cpkwhlow_start_min',
			'cpkwhlow_end_hour',
			'cpkwhlow_end_min'
		);
		
		foreach($_POST as $k => $v)
		{
			$$k = $v;
			if(!in_array($k, $excludedFields))
			{
				$db->updateSettings($k, $v);
			}
		}
		
		$cpkwhlow_start = $cpkwhlow_start_hour.":".$cpkwhlow_start_min;
		$cpkwhlow_end = $cpkwhlow_end_hour.":".$cpkwhlow_end_min;
		
		$db->updateSettings('cpkwhlow_start', $cpkwhlow_start);
		$db->updateSettings('cpkwhlow_end', $cpkwhlow_end);
	
		if($password != "" && $confirmpassword != "" && $password == $confirmpassword)
		{
			$db->updateLogin(sha1($password));
		}
		
		echo '{"ok": 1, "msg":"Instellingen succesvol opgeslagen"}';	
		
	}
	else
	{
		echo "Error!";
	}
}
else
{
	echo "Login required!";
}
?>