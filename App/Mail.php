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
          $mail->Host = 's157.goserver.host';
          $mail->SMTPAuth = true;
          $mail->Username = 'web53p2';
          $mail->Password = 'l1NPDZMfQ6jq37th';
          $mail->SMTPSecure = 'tls';
          $mail->Port = 587;
        
          // Recipients
          $mail->setFrom("info@carparkapp.com", "Carpark Info");
          $mail->addAddress("fabian.rhoda@stud.hawk.de"); 

          // Content
          $mail->isHTML(true); // Set email format to HTML
          $mail->Subject = $subject;
          $mail->Body =  $html;
          $mail->AltBody = $text;
          // HTML aktivieren
         
          
          // Empfänger Adresse und Alias hinzufügen
          
          
          // Betreff
          
          // Nachtrichteninhalt als HTML
          

          // Alternativer Nachrichteninhalt für Clients, die kein HTML darstellen
          // $mail->AltBody = strip_tags($mail->$text);

          if ($mail->send()) {
            return true;
          }

      } catch (Exception $e) {
       
          return $mail->ErrorInfo;

      }
        
    }
}