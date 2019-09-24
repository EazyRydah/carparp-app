<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\User;
use \App\Models\Parkings;
use \App\Models\Shares;

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
     * @return void
    */  
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

            Flash::addMessage('Combination not found, please try again', Flash::WARNING);

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
        if (Auth::getUser()) {

            return Parkings::findByUserID($_SESSION['user_id']);

        }
       
    }

    /**
     * Share selected parking with id comming from the query string
     *
     * @return void
    */
    public function shareAction() 
    {
        $parking = Parkings::findByID($this->route_params['id']);

        View::renderTemplate('Parking/share.html', [
            'parking' => $parking
        ]);
    }

     /**
     * Create share on seletected parking with parking id commiing from the 
     * query string. 
     *
     * @return void
    */
    public function shareCreateAction() 
    {
        $parking = Parkings::findByID($this->route_params['id']);

        $share = new Shares($_POST);

        if ($share->add($parking->id)) {

            // 

            Flash::addMessage('Parking shared successfully');


            // TODO redirect to share_success
            View::renderTemplate('Parking/share.html', [
                'parking' => $parking,
                'share' => $share
            ]); 

        } else {
            
            Flash::addMessage('invalid date input, please try again', Flash::WARNING);

            View::renderTemplate('Parking/share.html', [
                'parking' => $parking,
                'share' => $share
            ]); 

        }

        // var_dump($share);
    }

}