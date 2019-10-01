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

            $existingShares = $this->getByParkingID($id);

            if($existingShares) 
            {
                $shareIDs = $this->getIncludedShareIDs($existingShares);

                $this->removeByIDs($shareIDs);
            }

            $sql = 'INSERT INTO shares (share_start, share_end, parking_id) 
                    VALUES (:share_start, :share_end, :parking_id)';
            
            $db = static::getDB();
            $stmt = $db->prepare($sql);
    
            $stmt->bindValue(':share_start', $this->share_start, PDO::PARAM_STR);
            $stmt->bindValue(':share_end', $this->share_end, PDO::PARAM_STR);
            $stmt->bindValue(':parking_id', $id, PDO::PARAM_INT);
    
            return $stmt->execute();  // returns true on success 

        } else {

            return false;

        }
    }

    /**
     * Extract all IDs from share object collection, which start_date is included by daterange of current share object
     * 
     * @param Shares $shares An share object collection
     * 
     * @return mixed $shareIDs An Array with all IDs which are included in daterange of current share object
    */
    private function getIncludedShareIDs($shares)
    {
        $includesdShareIDs = [];

        $existingShares = $shares;

        $currentShareDates = $this->getDatesFromSingleShare($this);
        $haystack = $this->sanitizeISO8601DateString($currentShareDates);

        foreach ($existingShares as $element) {
            
            $needle = $element->share_start;

            if (in_array($needle, $haystack)) {
                $includesdShareIDs[] = $element->id;
            }
        }

        return $includesdShareIDs;
    }

    /**
     * Cuts off time and timezone information from ISO8601 formatted date array
     * 
     * @param mixed $dates An array with string dates formatted in ISO8601
     * 
     * @return mixed $shareIDs An Array with all IDs which are included in daterange of current share object
    */
    private function sanitizeISO8601DateString($dates) {

        $sanitizedDates = [];

        foreach ($dates as $date) {
            $sanitizedDates[] = substr($date, 0, strpos($date, "T"));
        }

        return $sanitizedDates;
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
                // var_dump($existingShares);
                $existingShareDates = $this->getDatesFromMultipleShares($existingShares);
                
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
            
                $min_end_date = $this->start_date->modify('+6 days');

                if ($this->end_date < $min_end_date) {

                    $this->errors[] = 'earliest share end date is six days from share start';

                }         

                // Check if startdate already exist in shareperiod of one share in db
                if ($existingShares) {

                    $existingShareDates = $this->getDatesFromMultipleShares($existingShares);

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
    * Get all Dates from single share object
    * 
    * @param Shares $shares share object to get dates from
    * 
    * @return mixed $dates array with ISO8601 formatted dates
    */
    private function getDatesFromSingleShare($share)
    {

        $sharePeriod = $this->createDatePeriod($share->share_start, $share->share_end);

        $dates = [];

        foreach ($sharePeriod as $date) {
            $dates[] = $date->format(DateTime::ISO8601);
        }

        return $dates;
    }

    /**
    * Get all Dates from multiple share objects
    * 
    * @param Shares $shares share objects to get dates from
    * 
    * @return mixed $dates array with ISO8601 formatted dates
    */
    private function getDatesFromMultipleShares($shares)
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
    public static function getByParkingID($id) {

        $sql = 'SELECT * FROM shares WHERE parking_id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // get namespace dynamically with get_called_class()
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();  // fetch() only gets first element
    }

    /**
    * Find single share by ID
    * 
    * @param string $id The share ID
    * 
    * @return mixed Share object if found, false otherwise
    */
    public static function findByID($id) {

        $sql = 'SELECT * FROM shares WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch(); 
    }

    /**
    * Remove shares by IDs
    * 
    * @param string $ids The share IDs
    * 
    * @return boolean true if removing successfull, false otherwise
     */
    private function removeByIDs($ids) {

        if (!empty($ids)) {

            $sql = 'DELETE FROM shares WHERE id IN (';

            $values = [];

            foreach ($ids as $id) {
                $values[] = "{$id},";
            }

            $sql .= implode(" ", $values);

            // Removelast character from statement if it is ","
            $sql = rtrim($sql, ",");

            // Add closing bracket to complete sql statement
            $sql .= ")";

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            return $stmt->execute(); 
        }
    }

    /**
    * Remove single share by ID
    * 
    * @return boolean true if removing successfull, false otherwise
    *
    */
    public function remove() {

        $sql = 'DELETE FROM shares WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute(); 

    }
}
    
