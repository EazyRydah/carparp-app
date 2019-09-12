<?php

namespace App\Models;


use PDO;
use \App\Auth;

/**
 * Parking model
 * 
 * PHP version 7.3.6
  */
class Parkings extends \Core\Model
{
    /**
    * Errors messages
    * 
    * @var array
     */
    public $errors = [];

    /**
     * Class constructor
     * 
     * @param array $data Initial property values
     * 
     * @return void
    */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Add the parking model with the current property values
     * 
     * @return void
      */
    public function add() {

        $this->validate();

        if (empty($this->errors)) {

            if ($this->parkingExists($this->contract_id, $this->key_id)) {
                
                $user = Auth::getUser();

                var_dump($user->id);

                $sql = 'UPDATE parkings 
                        SET user_id = :user_id
                        WHERE contract_id = :contract_id 
                        AND key_id = :key_id';
            
                $db = static::getDB();
                $stmt = $db->prepare($sql);
    
                $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
                $stmt->bindValue(':contract_id', $this->contract_id, PDO::PARAM_INT);
                $stmt->bindValue(':key_id', $this->key_id, PDO::PARAM_INT);

                return $stmt->execute(); // returns true on success

            } else {

                $this->errors[] = 'Kombination existiert nicht du Bauer!';

            }

        }

       return false;

    }

    /**
    * Validate current property values, adding validation error messages to the errors array property
    * 
    * @return void
    */
    protected function validate()
    {
        // Contract Id
        if ($this->contract_id == '') {
            $this->errors[] = 'Contract ID is required';
        }

        // Key Id
        if ($this->key_id == '') {
            $this->errors[] = 'Key ID is required';
        }
    }

    /**
     * Find a parking model by combination of contractID and keyID
     * 
     * @param integer $contractID contractID
     * @param integer $password KeyID
     * 
     * @return mixed The parking object oder false if authentication fails  
     */  
    public function findByContractAndKeyID($contractID, $keyID) {

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

    /**
    * See if a parking record exists with the specified contractID/keyID
    * combination
    * 
    * @param string $contractID to search for
    * @param string $keyID to search for
    * 
    * @return boolean True if a record exists with the specified contractID/    * keyID combination, false otherwise
     */
    public function parkingExists($contractID, $keyID)
    {
        $parking = $this->findByContractAndKeyID($contractID, $keyID);

        if ($parking) {
           
            return true;
          
        }

        return false;
    }

    /**
    * Find all user id related parkings
    * 
    * @param string $id The user ID
    * 
    * @return mixed User object collection if found, false otherwise
     */
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM parkings WHERE user_id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // get namespace dynamicly with get_called_class()
        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetchAll();  // fetch() only gets first element
    }
 
}