<?php

namespace App\Models;


use PDO;
use DateTime;
use DateInterval;
use DatePeriod;
use \App\Auth;

/**
 * Parking model
 * 
 * PHP version 7.3.6
  */
class Shares extends \Core\Model
{
    /**
    * Errors messages
    * 
    * @var array
     */
    public $errors = [];

    /**
    * Start date of the share
    * 
    * @var string
     */
    public $share_start;

    /**
     * End date of the share
     * 
     * @var string
     */  
    public $share_end;

    /**
     * Class constructor
     * 
     * @param array $data Initial property values
     * 
     * @return void
    */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Add the share model with the current property values
     * 
     * @param string $id The parking ID
     * 
     * @return void
      */
      public function add($id) {

        $this->validate($id);

        if (empty($this->errors)) {
            
            var_dump($this);


            $sql = 'INSERT INTO shares (share_start, share_end, parking_id) 
                    VALUES (:share_start, :share_end, :parking_id)';
            
            $db = static::getDB();
            $stmt = $db->prepare($sql);
    
            $stmt->bindValue(':share_start', $this->share_start, PDO::PARAM_STR);
            $stmt->bindValue(':share_end', $this->share_end, PDO::PARAM_STR);
            $stmt->bindValue(':parking_id', $id, PDO::PARAM_INT);
    
            return $stmt->execute(); // returns true on success

            // TOOODOOOOO UPDATE EXISTING SHARE! compare periods THING ABOUT THIS ONE!

        } else {

            return false;

        }
    }

    /**
    * Validate current property values, adding validation error messages to the errors array property. 
    * 
    * @param string $id The parking ID
    * 
    * @return void
    */
    private function validate($id)
    {

        $existingShares = $this->getByParkingID($id);

        // SHARE START DATE VALIDATION
        if ($this->share_start == '') {

            $this->errors[] = 'share start date is required';

        } 

        if ($this->share_start != '') {
            
            // init new property, because view cannot render DateTime
            $this->start_date = new DateTime($this->share_start);              
            // normalize time for calculation
            // Check if date is valid, for e.g. 2019-02-31
            $this->start_date->setTime(0,0,0); 
        
            $date_errors = DateTime::getLastErrors();

            if ($date_errors['warning_count'] > 0) {

                $this->errors[] = 'share start date invalid';

            }

            $min_start_date = new DateTime('now');
            $min_start_date->setTime(0,0,0);
            $min_start_date->modify('+2 days');

            if ($this->start_date < $min_start_date) {

                $this->errors[] = 'earliest share start date is two days from today';

            }
            
            // Check if startdate already exist in shareperiod of one share in db
            if ($existingShares) {

                $existingShareDates = $this->getDatesFromShares($existingShares);

                $needle = $this->start_date->format(DateTime::ISO8601);
                $haystack = $existingShareDates;
              
                if(in_array($needle, $haystack)) {
                    $this->errors[] = 'share start date already exists';
                }
            }
        } 

        // SHARE END DATE VALIDATION
        if ($this->share_end == '') {
            $this->errors[] = 'share end date is required';
        }

        if ($this->share_end != '') {
            
            $this->end_date = new DateTime($this->share_end);

            $date_errors = DateTime::getLastErrors();

            if ($date_errors['warning_count'] > 0) {

                $this->errors[] = 'share end date invalid';

            }

            // Checks that only work if both dates are set
            if ($this->share_start != '') {
            
                $min_end_date = $this->start_date->modify('+7 days');

                if ($this->end_date < $min_end_date) {

                    $this->errors[] = 'earliest share end date is seven days from share start';

                }         

                // Check if startdate already exist in shareperiod of one share in db
                if ($existingShares) {

                    $existingShareDates = $this->getDatesFromShares($existingShares);

                    $needle = $this->end_date->format(DateTime::ISO8601);
                    $haystack = $existingShareDates;
                
                    if(in_array($needle, $haystack)) {
                        $this->errors[] = 'share end date already exists';
                    }
                }
            }
        } 
    }

    /**
    * Get all Dates from given share object
    * 
    * @param Shares $shares share objects to get dates from
    * 
    * @return mixed $dates array with ISO8601 formatted dates
    */
    private function getDatesFromShares($shares)
    {

        $sharePeriods = [];

        foreach ($shares as $share) {
            $sharePeriods[] = $this->createDatePeriod($share->share_start, $share->share_end);
        }

        $dates = [];

        foreach ($sharePeriods as $daterange) {
            foreach ($daterange as $date) {
                $dates[] = $date->format(DateTime::ISO8601);
            }
        }

        return $dates;   
    }

    /**
    * Create an DatePeriod object from given start and end dates 
    * With ISO8601 matching interval declaration P1D (Period1Day)
    * 
    * @param string $start The start date
    * @param string $end The end date
    * 
    * @return DatePeriod object
    */
    private function createDatePeriod($start, $end) 
    {
        $start = new DateTime($start);
        $end = new DateTime($end);
        $end = $end->modify('+1 day');
        $interval = new DateInterval('P1D');
        $dateperiod = new DatePeriod($start, $interval, $end);

        return $dateperiod;
    }

    /**
    * Get Dates from DatePeriod object in ISO8601 format
    * 
    * @param DatePeriod $dateperiod The Dateperiod object
    * 
    * @return array $dates ISO8601 formatted dates as strings
    */
    private function getDatesFromDatePeriod($dateperiod) {

        $dates = [];

        foreach ($dateperiod as $date) {

            $dates[] = $date->format(DateTime::ISO8601);

        }

        return $dates;
    }

    /**
    * Find all parking ID related shares
    * 
    * @param string $id The parking ID
    * 
    * @return mixed Share object collection if found, false otherwise
     */
    private function getByParkingID($id) {

        $sql = 'SELECT * FROM shares WHERE parking_id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // get namespace dynamically with get_called_class()
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();  // fetch() only gets first element
    }


}
    
