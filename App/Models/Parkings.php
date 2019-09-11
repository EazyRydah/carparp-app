<?php

namespace App\Models;

use PDO;

/**
 * Parking model
 * 
 * PHP version 7.3.6
  */
class Parkings extends \Core\Model
{

   /**
     * Authenticate a parking by contractID and keyID 
     * 
     * @param integer $contractID contractID
     * @param integer $password KeyID
     * 
     * @return mixed The parking object oder false if authentication fails
    */
    public static function authenticate($contractID, $keyID)
    {

        $parking = static::findByContractAndKeyID($contractID, $keyID);

        if ($parking) {
            
            return $parking;
            
        } 

        return false;

    }

    /**
     * Find a parking model by combination of contractID and keyID
     * 
     * @param integer $contractID contractID
     * @param integer $password KeyID
     * 
     * @return mixed The parking object oder false if authentication fails  
     */  
    public static function findByContractAndKeyID($contractID, $keyID) {

        $sql = 'SELECT * FROM parkings 
                WHERE contract_id = :contract_id 
                AND key_id = :key_id';
  
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':contract_id', $contractID, PDO::PARAM_INT);
        $stmt->bindParam(':key_id', $keyID, PDO::PARAM_INT);

        // get namespace dynamicly with get_called_class()
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch(); 

    }
 
}