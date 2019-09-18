<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\User;
use \App\Models\Parkings;
use \App\Models\Shares;

/* 
* Share Controller
* 
* PHP version 7.3.6
*/
class Share extends Authenticated
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
     * Create a share related to specific parking
     * 
     * @return void
      */
    public function createAction() 
    {
        $parking = Parkings::findByID($this->route_params['id']);
        
        $share = new Shares($_POST);

        if ($share->add($parking->id)) {
            
            Flash::addMessage('Parking shared successfully');

            // var_dump($share);

        } else {
            
            Flash::addMessage('SHIT IS GOING ON!', Flash::WARNING);

            View::renderTemplate('Parking/share.html', [
                'parking' => $parking,
                'share' => $share
            ]);

        }
    }

}