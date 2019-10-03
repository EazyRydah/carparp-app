<?php

namespace App\Controllers;

use \App\Models\User;
use \App\Auth;
use \Core\View;

/**
 * Account controller
 * 
 * PHP version 7.3.6
  */
class Admin extends Authenticated
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

        if ($this->user->is_admin != 1) {
            $this->redirect('/');
        }
    }

    /**
     * Show the admin index page
     * 
     * @return void
     */
    public function indexAction()
    {
        var_dump($this->user);

        View::renderTemplate('Admin/index.html');

    }
 
}