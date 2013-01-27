<?php

class Request {

	private $source;
	private $format;
	private $password;
	private $data;
	private $opts;
	private $optsSetSes;
	private $cookie;
    
	public function __construct() {
	
		$this->source = 'http://'.YL_ADDRESS.'/';
		$this->format = '&f=j'; // JSON
		$this->password = YL_PASSWORD;
		$this->opts = array( 
			CURLOPT_RETURNTRANSFER => true, 
			CURLOPT_FOLLOWLOCATION => false  
		);	
	}       
      

    /**
     * Get live data
     */
	public function getLiveData() {

        $data['cookie'] = $this->setCurlSession();
		$curl = new Curl();
		
		$curl->addSession( $this->source.'a'.$this->format, $this->opts );

		$result = $curl->exec();
		$curl->clear();

        $this->delCookie();
        
		return $result;
	} 	
	

    /**
     * Set curl session
     */
	public function setCurlSession() {
	
		if($this->password != '')
		{
			$curl = new Curl();
			$curl->retry = 2;
			
			$this->cookie = tempnam(sys_get_temp_dir(), 'YL_KOEK_');
			$this->opts[CURLOPT_COOKIEFILE] = $this->cookie;			
			
			$optsSet = array( 
				CURLOPT_RETURNTRANSFER => true, 
				CURLOPT_FOLLOWLOCATION => false,
				CURLOPT_COOKIEJAR => $this->cookie			
			);			
			
			$curl->addSession( $this->source.'L?w='.$this->password, $optsSet );
	
			$curl->exec();
			$result = $curl->info();		
			$curl->clear();		
			
			return $result;
		}
	} 	
	
	
    /**
     * Check if password protected and delete old cookie
     */
	public function delCookie() {	
		
		if($this->password != '')
		{
			unlink($this->cookie);
		}			
	}
	
    /**
     * Get last hour
     */
	public function getLastHour() {
	
		// Check for password and create cookie
		$data['cookie'] = $this->setCurlSession();
	
		$curl = new Curl();
		$curl->retry = 2;
		
		$curl->addSession( $this->source.'V?h=1'.$this->format, $this->opts );
		$curl->addSession( $this->source.'V?h=2'.$this->format, $this->opts );

		$result = $curl->exec();
		$curl->clear();	
		
		// Check for password and delete cookie
		$this->delCookie();	
		
		$part1 = json_decode($result[0], true);
		$part2 = json_decode($result[1], true);
		
		$values = array_merge($part2['val'], $part1['val']);
		
		foreach($values as $k => $v){
			if($v == NULL){
				unset($values[$k]);
			}
			elseif($v == '*')
			{
				$values[$k] = '0';
			}
		}
		$val = implode('","', $values);
		
		$data['un'] = $part2['un'];
		$data['tm'] = $part2['tm'];
		$data['dt'] = $part2['dt'];
		$data['val'] = $val;
		
		return $data;
	} 		 
	
	
    /**
     * Get specific month
     */
	public function getSpecificMonth($month) {

		// Check for password and create cookie
		$this->setCurlSession();

		$curl = new Curl();
		$curl->retry = 2;
		
		$curl->addSession( $this->source.'V?m='.$month.$this->format, $this->opts );

		$result = $curl->exec();
		$curl->clear();	
		
		// Check for password and delete cookie
		$this->delCookie();	

		$json = json_decode($result, true);
		
		$values = $json['val'];
		foreach($values as $k => $v){
			if($v == NULL){
				unset($values[$k]);
			}
			elseif($v == '*')
			{
				$values[$k] = '0';
			}
		}
		$val = implode('","', $values);
		
		$data['un'] = $json['un'];
		$data['tm'] = $json['tm'];
		$data['dt'] = $json['dt'];
		$data['val'] = $val;

		return $data;
	} 	
}

?>
