<?php

class Generic {

	
    /**
     * Create selector
     */
	public function selector($name, $selected, $options){
		$html = "<select name='".$name."'>\n";
		
		foreach($options as $k => $v) 
		{
			$html .= "<option value='" . $k . "'" . ($k==$selected?" selected":"") . ">$v</option>\n";
		}
		
		$html .= "</select>\n";
		
		return $html;
	}

    /**
     * Create time selector
     */
	public function timeSelector($selectedHour, $selectedMin, $prefix){
		$html = "<select name='".$prefix."_hour'>\n";
		for ($i=0;$i<24;$i++) 
		{
			$html .= "<option value='" . sprintf("%02d", $i) . "'" . ($i==$selectedHour?" selected":"") . ">$i</option>\n";
		}
		$html .= "</select>:<select name='".$prefix."_min'>\n";
		for ($i=0;$i<60;$i+=5) 
		{
			$html .= "<option value='" . sprintf("%02d", $i) . "'" . ($i==$selectedMin?" selected":"") . ">" . sprintf("%02d", $i) . "</option>\n";
		}
		$html .= "</select>";
		
		return $html;
	}
	
    /**
     * Calculate kwhs and costs for a range of days
     */	
     public function calculateRangeKwhCosts($beginDate, $endDate){
     	 
		$checkDate = $beginDate;  
		$data = array(
			'kwh' => 0,
			'kwhLow' => 0,
			'price' => 0,
			'priceLow' => 0
		);
			
		while ($checkDate != $endDate) { 

	
			$dailyData = $this->calculateDayKwhCosts($checkDate); 
			
			$data['kwh'] += $dailyData['kwh'];
			$data['kwhLow'] += $dailyData['kwhLow'];	
			$data['price'] += $dailyData['price'];
			$data['priceLow'] += $dailyData['priceLow'];			
			
			$checkDate = date ("Y-m-d", strtotime ("+1 day", strtotime($checkDate))); 
			
		}
		
		return $data;	
		  
     }
     
   /**
     * Calculate kwhs and costs for specific day
     */	
     public function calculateDayKwhCosts($checkDate){
     	
     	$this->db = new Database();
     	$settings = $this->db->getSettings();
	
		if($settings['dualcount'] == 1)
		{
			$getDay = strftime('%u', strtotime($checkDate));
			$beginData = $this->db->getKwhCount($checkDate.' 00:00:00');
			$endData = $this->db->getKwhCount($checkDate.' 23:59:00');
			
			$beginLowData = $this->db->getKwhCount($checkDate.' '.$settings['cpkwhlow_start'].':00');
			$endLowData = $this->db->getKwhCount($checkDate.' '.$settings['cpkwhlow_end'].':00');			
			
			$timeStart = (int)str_replace(":","", $settings['cpkwhlow_start']);
			$timeEnd = (int)str_replace(":","", $settings['cpkwhlow_end']);

			$holiday = $this->calculateHoliday($checkDate);     	
									
			if($timeStart > $timeEnd)
			{
				if($getDay == '6' || $getDay == '7'|| $holiday == true ){
					$kwh = 0;
					$kwhLow = str_replace(",",".", $endData->kwh) - str_replace(",",".", $beginData->kwh);					
				}
				else{
					$kwh = str_replace(",",".", $beginLowData->kwh) - str_replace(",",".", $endLowData->kwh);
					$kwhLow = (str_replace(",",".", $endData->kwh) - str_replace(",",".", $beginData->kwh)) - $kwh;
				}
			}
			else
			{
				if($getDay == '6' || $getDay == '7'|| $holiday == true ){
					$kwh = 0;
					$kwhLow = str_replace(",",".", $endData->kwh) - str_replace(",",".", $beginData->kwh);
				}
				else{
					$kwhLow = str_replace(",",".", $endLowData->kwh) - str_replace(",",".", $beginLowData->kwh);
					$kwh = (str_replace(",",".", $endData->kwh) - str_replace(",",".", $beginData->kwh)) - $kwhLow;						
				}
		
			}
			
			// Calculate price
			$price = $kwh * (float)$settings['cpkwh'];
			$priceLow = $kwhLow * (float)$settings['cpkwh_low'];	
			
			$data = array();
			
			$data['kwh'] = $kwh;
			$data['kwhLow'] = $kwhLow;
			$data['totalKwh'] = $kwh + $kwhLow;	
			$data['price'] = $price;
			$data['priceLow'] = $priceLow;
			$data['totalPrice'] = $price + $priceLow;							
		}
		else
		{
			$beginData = $this->db->getKwhCount($checkDate.' 00:00:00');
			$endData = $this->db->getKwhCount($checkDate.' 23:59:00');
			
			$kwh = str_replace(",",".", $endData->kwh) - str_replace(",",".", $beginData->kwh);
			
			// Calculate price
			$price = $kwh * (float)$settings['cpkwh'];
			
			$data = array();
			
			$data['kwh'] = $kwh;
			$data['kwhLow'] = 0;	
			$data['price'] = $price;
			$data['priceLow'] = 0;				
		}   		
		
		return $data;		  
     }  
        
     /**
     * Calculate if a specific day is a holiday
     */	
     public function calculateHoliday($checkDate){
		$jaar = date('Y');
		$feestdag = array();
	    $a = $jaar % 19;
	    $b = intval($jaar/100);
	    $c = $jaar % 100;
	    $d = intval($b/4);
	    $e = $b % 4;
	    $g = intval((8 *  $b + 13) / 25);
	    $theta = intval((11 * ($b - $d - $g) - 4) / 30);
	    $phi = intval((7 * $a + $theta + 6) / 11);
	    $psi = (19 * $a + ($b - $d - $g) + 15 -$phi) % 29;
	    $i = intval($c / 4);
	    $k = $c % 4;
	    $lamda = ((32 + 2 * $e) + 2 * $i - $k - $psi) % 7;
	    $maand = intval((90 + ($psi + $lamda)) / 25);
	    $dag = (19 + ($psi + $lamda) + $maand) % 32;    
	 
	    $feestdag[] = date('Y-m-d', mktime (1,1,1,1,1,$jaar));           // Nieuwjaarsdag
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,$maand,$dag-2,$jaar));  // Goede Vrijdag
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,$maand,$dag,$jaar));    // 1e Paasdag
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,$maand,$dag+1,$jaar));  // 2e Paasdag
	    if ($jaar < '2014'){
			$feestdag[] = date('Y-m-d',mktime (0,0,0,4,30,$jaar));       // Koninginnedag    
	    }
	    else{
			$feestdag[] = date('Y-m-d',mktime (0,0,0,4,26,$jaar));       // Koningsdag    
	    }
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,5,5,$jaar));            // Bevrijdingsdag
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,$maand,$dag+39,$jaar)); // Hemelvaart
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,$maand,$dag+49,$jaar)); // 1e Pinksterdag
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,$maand,$dag+50,$jaar)); // 2e Pinksterdag
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,12,25,$jaar));          // 1e Kerstdag
	    $feestdag[] = date('Y-m-d',mktime (0,0,0,12,26,$jaar));          // 2e Kerstdag
	    return in_array($checkDate, $feestdag) ? true : false;
	}   
}

?>