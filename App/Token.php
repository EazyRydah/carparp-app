<?php 

namespace App;

/**
 * Unique random tokens
 * 
 * PHP version 7.3.6
  */
class Token
{
    /**
     * The token value
     * @var array
      */
    protected $token;

    /**
     * Class constructor. Create a new random token.
     * 
     * @return void
      */
    public function __construct($token_value = null)
    {
        if ($token_value) {

            $this->token = $token_value;

        } else {

            // 16 bytes = 128 bits = 32 hex characters
            $this->token = bin2hex(random_bytes(16)); 

        }
    }

    /**
     * Get the token value
     * 
     * @return string The value
      */
    public function getValue()
    {
        return $this->token;
    }

    /**
     * Get the hashed token value
     * 
     * @return string The hashed value
      */
    public function getHash()
    {
        // sha256 = 64 chars
        return hash_hmac('sha256', $this->token, \App\Config::SECRET_KEY);
    }
}