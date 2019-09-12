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

        $parking = new Parkings($_POST);

        if ($parking->add()) {

            Flash::addMessage('Parking added successfully');

            $this->redirect('/');

        } else {

            View::renderTemplate('Parking/new.html', [
                'parking' => $parking
            ]);

        }
    }

    /**
     * Get all parkings related to current logged-in user
     * 
     * @return mixed The collection of parking objects or null if nothing found
      */
    public static function getParkings()
    {
        return Parkings::findByID($_SESSION['user_id']);
    }

}