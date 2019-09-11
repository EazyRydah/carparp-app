<?php

namespace App;

use Mailgun\Mailgun;
use App\Config;

/**
 * Mail
 * 
 * PHP version 7.3.6
  */
class Mail
{
    /**
     * Send a message
     * 
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $text Text-only content of the message
     * @param string $html HTML content of the message
     * 
     * @return mixed
      */
    public static function send($to, $subject, $text, $html)
    {
        $mg = new Mailgun(Config::MAILGUN_API_KEY);
        $domain = Config::MAILGUN_DOMAIN;

        // Now, compose and send your message.
        $mg->sendMessage($domain, array(
                                    'from'      => 'your-sender@example.com',
                                    'to'        => $to,
                                    'subject'   => $subject,
                                    'text'      => $text,
                                    'html'      => $html));
    }
}