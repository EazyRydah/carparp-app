<?php

namespace App\Controllers;

use \App\Models\User;

/**
 * Account controller
 * 
 * PHP version 7.3.6
  */
class Account extends \Core\Controller
{

    /**
     * Validate if email is available (AJAX) for a new signup.
     * 
     * @return void
      */
    public function validateEmailAction()
    {
        // call static method from user model class
        // validation plugin remote function uses get-method to send data
        $is_valid = ! User::emailExists($_GET['email'], $_GET['ignore_id'] ?? null); 

        // jquery validation plugin expects json response
        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }
}