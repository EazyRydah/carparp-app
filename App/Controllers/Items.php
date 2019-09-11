<?php

namespace App\Controllers;

use \Core\View;

/**
 * Items controller (example)
 * 
 * PHP version 7.3.6
  */
class Items extends Authenticated
{
   
    /**
     * Items index
     * 
     * @return void
      */
    public function indexAction()
    {
        View::renderTemplate('Items/index.html');
    }
}