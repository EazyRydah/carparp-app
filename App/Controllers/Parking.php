<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\User;
use \App\Models\Parkings;

/* 
* Parking Controller
* 
* PHP version 7.3.6
*/
class Parking extends Authenticated
{

    /**
     * Before filter - called before each action method
     * 
     * @return void */  
    protected function before() 
    {
        parent::before();

        $this->user = Auth::getUser();
    }

    /**
     * Show the add parking page
     * 
     * @return void
      */
    public function newAction() 
    {
        View::renderTemplate('Parking/new.html');
    }

    /**
     * Add a parking
     * 
     * @return void
      */
    public function createAction() 
    {

        $parking = Parkings::authenticate($_POST['contract_id'], $_POST['key_id']);

        if ($parking) {

            Flash::addMessage('Parking added successfully');

            $this->redirect('/');

        } 


    }

}