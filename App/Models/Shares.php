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
     * @return void
      */
      public function add($parkingID) {

        $this->validate();

        if (empty($this->errors)) {
            
            return true;

        } else {

            return false;

        }
    }

    /**
    * Validate current property values, adding validation error messages to the errors array property. 
    * 
    * @return void
    */
    protected function validate()
    {

        // SHARE START DATE VALIDATION
        if ($this->share_start == '') {

            $this->errors[] = 'share start date is required';

        } 

        if ($this->share_start != '') {
            
            // init new property, because view cannot render DateTime
            $this->start_date = new DateTime($this->share_start);            
            $this->start_date->setTime(0,0,0); // normalize time for calculation

            // Check if date is valid, for e.g. 2019-02-31
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
            }
        } 

        // CHECK FOR EXISTING SHAREPERIOD IN DATABASE
        if(empty($this->errors)) {

            // built dateperiod 
            $begin = new DateTime($this->share_start);
            $end = new DateTime($this->share_end);
            $interval = new DateInterval('P1D');
            $daterange = new DatePeriod( $begin, $interval, $end);

            foreach ($daterange as $date) {
                echo $date->format(DateTime::ISO8601) . "<br>";
            }

            // TODOOO CHECK IF ANY SHARE IN DB HAVE DATE FROM THIS PERIOD
        }
    }
}
    
