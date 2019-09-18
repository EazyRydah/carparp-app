<?php

namespace App\Models;


use PDO;
use DateTime;
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
    * Validate current property values, adding validation error messages to the errors array property
    * 
    * @return void
    */
    protected function validate()
    {
        // Check share_start input isnt empty
        if ($this->share_start == '') {

            $this->errors[] = 'share start date is required';

        } 

        // check share_start is valid date
        if ($this->share_start != '') {
            
            try {

                // put into new property and keep $this->share_start of type string, because view template cant render DateTime
                $this->start_date = new DateTime($this->share_start);

            } catch (Exception $e) {

                echo $e->getMessage();
                exit(1);

            }

            // Check if date is valid
            $date_errors = DateTime::getLastErrors();

            if ($date_errors['warning_count'] > 0) {

                $this->errors[] = 'share start date invalid';

            }

            // var_dump($this->start_date->format(DateTime::ISO8601));
        } 

        // Share end date
        if ($this->share_end == '') {
            $this->errors[] = 'share end date is required';
        }

        // check share_end is valid date
        if ($this->share_end != '') {
            
            try {

                // put into new property and keep $this->share_end of type string, because view template cant render DateTime
                $this->end_date = new DateTime($this->share_end);

            } catch (Exception $e) {

                echo $e->getMessage();
                exit(1);

            }

            // Check if date is valid
            $date_errors = DateTime::getLastErrors();

            if ($date_errors['warning_count'] > 0) {

                $this->errors[] = 'share end date invalid';

            }

            // var_dump($this->end_date->format(DateTime::ISO8601));
        } 
  
    }
 
    

    
}