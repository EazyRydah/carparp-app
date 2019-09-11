<?php

namespace App;

/**
 * Application configuration
 * 
 * PHP Version 7.3.6
  */
class Config
{

    /**
     * Database host
     * @var string
      */
    const DB_HOST = '';

    /**
     * Database name
     * @var string
      */
    const DB_NAME = '';

    /**
     * Database user
     * @var string
      */
    const DB_USER = '';

    /**
     * Database password
     * @var string
      */
    const DB_PASSWORD = '';

    /**
     * Show or hide error messages on screen
     * @var boolean
      */
    const SHOW_ERRORS = true;

    /**
     * Secret key for hashing
     * @var boolean
      */
    const SECRET_KEY = '';

    /**
     * Mailgun API key
     * 
     * @var string
      */
    const MAILGUN_API_KEY = '';

    /**
     * Mailgun domain
     * 
     * @var string
      */
    const MAILGUN_DOMAIN = '';
}