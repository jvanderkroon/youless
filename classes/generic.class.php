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
			$beginData = $this->db->getKwhCount($checkDate.' 00:00:00');
			$endData = $this->db->getKwhCount($checkDate.' 23:59:00');
			
			$beginLowData = $this->db->getKwhCount($checkDate.' '.$settings['cpkwhlow_start'].':00');
			$endLowData = $this->db->getKwhCount($checkDate.' '.$settings['cpkwhlow_end'].':00');			
			
			$timeStart = (int)str_replace(":","", $settings['cpkwhlow_start']);
			$timeEnd = (int)str_replace(":","", $settings['cpkwhlow_end']);
						
			if($timeStart > $timeEnd)
			{
				$kwh = str_replace(",",".", $beginLowData->kwh) - str_replace(",",".", $endLowData->kwh);
				$kwhLow = (str_replace(",",".", $endData->kwh) - str_replace(",",".", $beginData->kwh)) - $kwh;
			}
			else
			{
				$kwhLow = str_replace(",",".", $endLowData->kwh) - str_replace(",",".", $beginLowData->kwh);
				$kwh = (str_replace(",",".", $endData->kwh) - str_replace(",",".", $beginData->kwh)) - $kwhLow;			
			}
			
			// Calculate price
			$price = $kwh * (float)$settings['cpkwh'];
			$priceLow = $kwhLow * (float)$settings['cpkwh_low'];	
			
			$data = array();
			
			$data['kwh'] = $kwh;
			$data['kwhLow'] = $kwhLow;	
			$data['price'] = $price;
			$data['priceLow'] = $priceLow;				
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
     
}

?>