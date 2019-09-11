<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
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

        $mail = new PHPMailer(true);

        try {
        
          // Server settings
          $mail->isSMTP();
          $mail->Host = Config::MAIL_SMTP_HOST;
          $mail->SMTPAuth = true;
          $mail->Username = 'web53p1';
          $mail->Password = 'ahd9Aegh';
          $mail->SMTPSecure = Config::MAIL_SECURE_PROTOCOL;
          $mail->Port = 587;
        
          // Recipients
          $mail->setFrom("info@carparkapp.com", "Carpark Info");
          $mail->addAddress($to); 

          // Content
          $mail->isHTML(true); // Set email format to HTML
          $mail->Subject = $subject;
          $mail->Body =  $html;
          $mail->AltBody = $text;

          if ($mail->send()) {
            return true;
          }

      } catch (Exception $e) {
       
          return $mail->ErrorInfo;

      }
        
    }
}