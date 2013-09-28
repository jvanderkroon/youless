<?php

class Database {

    private $_db = null;

    /**
     * Constructor, makes a database connection
     */
    public function __construct() {

        try {
            $this->_db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array( 
      			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
   			));
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_db->query('SET CHARACTER SET utf8');
        } catch (PDOException $e) {
            exit('Error while connecting to database.'.$e->getMessage());
        }
    }

    private function printErrorMessage($message) {
        echo $message;
    }

    /**
     * Get login 
     */
     public function getLogin($username, $password) {
        try {
            $sth = $this->_db->prepare("SELECT id FROM users WHERE username= ? AND password= ? ");

            $sth->bindValue(1, $username, PDO::PARAM_STR);
            $sth->bindValue(2, $password, PDO::PARAM_STR);
            $sth->execute();
            $row = $sth->fetch(PDO::FETCH_OBJ);
			return $row->id;
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }

    /**
     * Update login 
     */
     public function updateLogin($id, $password) {
        try {
            $sth = $this->_db->prepare("UPDATE users SET password= ? WHERE id= $id ");

            $sth->bindValue(1, $password, PDO::PARAM_STR);
            $sth->execute();

			return $sth->rowCount();
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }
    
	/**
     * Update settings
     */
    public function updateSettings($key, $value) {
        try {
			$sth = $this->_db->prepare("INSERT INTO settings (`value`,`key`) VALUES (:value, :key) ON DUPLICATE KEY UPDATE `value`=:value, `key`=:key");
			
			$sth->bindValue(':value', $value, PDO::PARAM_STR);
			$sth->bindValue(':key', $key, PDO::PARAM_STR);			
            $sth->execute();
            
			return $sth->rowCount();
       } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }    
        
    /**
     * Get settings 
     */
     public function getSettings() {
        try {
            $sth = $this->_db->prepare("SELECT * FROM settings");
            
            $sth->setFetchMode(PDO::FETCH_ASSOC);
            $sth->execute();

            $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = array();
            foreach($rows as $k => $v)
            {
            	$settings[$v['key']] = $v['value'];
            }
            
            return $settings;
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }    

  
	/**
	* Get specific day
	*/
    public function getSpecificDay($date) {
        try {
            $sth = $this->_db->prepare("
            SELECT
            	*
            FROM 
            	data_h
            WHERE
            	DATE_FORMAT(time, '%Y-%m-%d') = ?	
            ORDER BY  
				time ASC");

			$sth->bindValue(1, $date, PDO::PARAM_STR);           			
            $sth->setFetchMode(PDO::FETCH_ASSOC);
            $sth->execute();

            $rows = $sth->fetchAll(PDO::FETCH_OBJ);
			return $rows;
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }

	/**
	* Get specific range
	*/
    public function getSpecificRange($begin, $end) {
        try {
            $sth = $this->_db->prepare("
            SELECT
            	*
            FROM 
            	data_h
            WHERE
            	DATE_FORMAT(time, '%Y-%m-%d') BETWEEN ? AND ?	
            ORDER BY  
				time ASC");

			$sth->bindValue(1, $begin, PDO::PARAM_STR);  
			$sth->bindValue(2, $end, PDO::PARAM_STR);  			         			
            $sth->setFetchMode(PDO::FETCH_ASSOC);
            $sth->execute();

            $rows = $sth->fetchAll(PDO::FETCH_OBJ);
			return $rows;
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }  
    
	/**
	* Get month
	*/
    public function getMonth($date) {
        try {
            $sth = $this->_db->prepare("
            SELECT
            	time,
            	GROUP_CONCAT(value) as value
            FROM 
            	data_h
            WHERE
            	DATE_FORMAT(time, '%Y-%m') = ?
            GROUP BY
            	DATE_FORMAT(time, '%d')
            ORDER BY  
				time ASC");

			$sth->bindValue(1, $date, PDO::PARAM_STR);   			         			
            $sth->setFetchMode(PDO::FETCH_ASSOC);
            $sth->execute();

            $rows = $sth->fetchAll(PDO::FETCH_OBJ);
			return $rows;
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }    
    
	/**
	* Get kwh count
	*/
    public function getKwhCount($datetime) {
        try {
            $sth = $this->_db->prepare("
			SELECT 
				*,
				if((SIGN(timestampdiff(second, inserted,:date)) = -1),
				( ( (timestampdiff(second, inserted,:date))*(timestampdiff(second, inserted,:date)) ) / (-1*(timestampdiff(second, inserted,:date))) ),
				(timestampdiff(second, inserted,:date)) ) as TimeDif
			FROM 
				kwh_h
			WHERE 
				inserted <= :date OR inserted > :date
			ORDER BY 
				TimeDif ASC 
			LIMIT 1;");

			$sth->bindValue(':date', $datetime, PDO::PARAM_STR);           			
            $sth->setFetchMode(PDO::FETCH_ASSOC);
            $sth->execute();
            
            $row = $sth->fetch(PDO::FETCH_OBJ);
			return $row;
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }     
	
   /**
    * Add hourly data (cronjob)
    */ 
    public function addHourlyData($time, $unit, $delta, $values) {
        try {
            $sth = $this->_db->prepare("INSERT INTO data_h (
            	time,
				unit,
				delta,
				value
            ) VALUES (
            	?,
				?,
				?,
				?
            )");

            $sth->bindValue(1, $time, PDO::PARAM_STR);
			$sth->bindValue(2, $unit, PDO::PARAM_STR);
			$sth->bindValue(3, $delta, PDO::PARAM_INT);
			$sth->bindValue(4, $values, PDO::PARAM_STR);
            $sth->execute();
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    } 	
    
   /**
    * Add hourly kwh (cronjob)
    */ 
    public function addHourlyKwh($kwh) {
        try {
            $sth = $this->_db->prepare("INSERT INTO kwh_h (
            	kwh
            ) VALUES (
				?
            )");

            $sth->bindValue(1, $kwh, PDO::PARAM_STR);
            $sth->execute();
        } catch (PDOException $e) {
            $this->printErrorMessage($e->getMessage());
        }
    }     
    
}

?>
