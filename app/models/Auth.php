<?php

/**
 * Created by PhpStorm.
 * User: Stagiaire
 * Date: 01/08/2016
 * Time: 10:22
 */
class Auth
{
    public function __construct()
    {
        //init db object
        require_once BASEPATH . 'models/Dbconnect.php';
        $this->db = new db();
        $this->author = 'author';
        $this->error = array();
        $this->err = 0;
        $this->errMsg = array();
        $this->user = false;
        $this->secretKey = "iaz_rgfè_egzàùçé&$*sd2342:.&é";
        $this->basedir = '/alert/';
    }


    private function cryptPassword($uncrypted)
    {
        $crypted = md5($uncrypted . $this->secretKey);
        return $crypted;
    }

    /**
     * Function verifUser
     *
     *
     */
    public function verifUser($array, $registered = true)
    {
        // Check if user is registered
        if ($registered) {
            $data['first'] = $this->validFname($array['first']);
            $data['last'] = $this->validLname($array['last']);
            $data['pseudo'] = $this->validPseudo($array['pseudo']);
            $data['mail'] = $this->validMail($array['mail']);
            $data['password'] = $this->validPassword($array['password']);
        }
        else
        {
            $data['pseudo'] = $this->validPseudo($array['pseudo']);
        }

        // Verify if there is no error
        if ($this->err == 1) {
            return false;
        } else {
            return $data;
        }
    }

    /**
     * Function verifUser
     *
     *
     */
    public function validFname($fName)
    {
        if (!isset($fName)) {
            $this->err = 1;
            $this->errMsg[] = "Champ du Prénom vide";
        }
        if (strlen($fName) >= 45) {
            $this->err = 1;
            $this->errMsg[] = "Nom trop long";
        }
        if ($this->err == 1) {
            return false;
        } else {
            return $fName;
        }
    }

    public function validLname($lName)
    {
        if (!isset($lName)) {
            $this->err = 1;
            $this->errMsg[] = "Champ du Nom vide";
        }
        if (strlen($lName) >= 45) {
            $this->err = 1;
            $this->errMsg[] = "Nom trop long";
        }
        if ($this->err == 1) {
            return false;
        } else {
            return $lName;
        }
    }

    public function validPseudo($pseudo)
    {
        if (!isset($pseudo)) {
            $this->err = 1;
            $this->errMsg[] = "Champ du Pseudo vide";
        }
        if (strlen($pseudo) >= 25) {
            $this->err = 1;
            $this->errMsg[] = "Pseudo trop long";
        }
        $arg['pseudo'] = $pseudo;
        $check = $this->db->selectSQL("SELECT * FROM " . $this->author . " WHERE pseudo = :pseudo", $arg);
        if (!empty($check)) {
            $this->err = 1;
            $this->errMsg[] = "Ce pseudo est déjà utilisé";
        }
        if ($this->err == 1) {
            $this->err = 1;
            return false;
        } else {
            return $pseudo;
        }
    }

    public function validMail($mail)
    {
        if (!isset($mail)) {
            $this->err = 1;
            $this->errMsg[] = "Champ du Mail vide";
        }
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->err = 1;
            $this->errMsg[] = "Email incorrect";
        }
        if ($this->err == 1) {
            return false;
        } else {
            return $mail;
        }
    }

    public function validPassword($password)
    {
        if (!isset($password)) {
            $this->err = 1;
            $this->errMsg[] = "Champ du Nom vide";
        }
        if (isset($password) && strlen($password) >= 8 && strlen($password) <= 50) {
            $password = $this->cryptPassword($password);
        } else {
            $this->err = 1;
            $this->errMsg[] = "Mot de passe trop court!";
        }
        if ($this->err == 1) {
            return false;
        } else {
            return $password;
        }
    }

    public function checkLogin($array)
    {
        $sql = 'SELECT * FROM ' . $this->author . ' WHERE pseudo = :pseudo AND `password` = :password';
        $exec = array(
            'pseudo' => $array['pseudo'],
            'password' => $this->cryptPassword($array['password'])
        );
        $result = $this->db->selectSQL($sql, $exec);
        if (isset($result[0]['id'])) {
            $this->user = $result[0];
            return $this->user;
        } else {
            return false;
        }
    }

    /**
     * Function createSessionToken
     *
     * Create a unique token for logged user
     *
     * @return bool|string
     */
    public function createSessionToken()
    {
        if ($this->user) {
            $token = md5($this->user['id'] . $this->secretKey . $_SERVER['HTTP_USER_AGENT']);
            $token .= "|" . $this->user['id'];
            $this->token = $token;
            return $token;
        } else {
            return false;
        }
    }

    /**
     * Function checkSessionToken
     *
     * Check user token for identification
     *
     * @return bool
     */
    public function checkSessionToken($token)
    {
        $check = explode('|', $token);
            $checkToken = md5($check[1] . $this->secretKey . $_SERVER['HTTP_USER_AGENT']);
            $checkId = $check[1];
            if ($checkToken == $check[0]) {
                $this->token = $token;
                $this->user = $this->getUserById($checkId);
                return true;
            } else {
                return false;
            }
    }

    /**
     * Function getUserById
     *
     */
    private function getUserById($id) {
        $user = $this->db->selectSQL('SELECT * FROM author WHERE id = ' . $id);
        return $user[0];
    }

    /**
     * @param $array
     * @return bool|null
     */
    public function newUser($array, $registered = true)
    {
        if ($registered)
        {
            if (empty($array))
            {
                return null;
            }
            else
            {
                $data = $this->verifUser($array);
            }
            if ($data == false) {
                return false;
            } else {
                // generate token
                $token = $this->createSessionToken();
                $req = $this->db->selectSQL(
                    "INSERT INTO author SET first = :user_first, last = :user_last, pseudo = :pseudo, mail = :mail, password = :user_password, date =  NOW(), ipadress = :ipadress, useragent = :useragent, registered = :registered, token = :token",
                    array(
                        "user_first" => htmlentities($data['first']),
                        "user_last" => htmlentities($data['last']),
                        "pseudo" => htmlentities($data['pseudo']),
                        "mail" => htmlentities($data['mail']),
                        "user_password" => $data['password'],
                        "ipadress" => $_SERVER['REMOTE_ADDR'],
                        "useragent" => htmlentities($_SERVER['HTTP_USER_AGENT']),
                        "registered" => 1,
                        "token" => $token
                    ));
               
                return $req;
            }
        }
        else
        {
            if (empty($array))
            {
                return null;
            }
            else
            {
                $data = $this->verifUser($array, false);
            }
            if ($data == false) {
                return false;
            } else {
                $token = $this->createSessionToken();
                $req = $this->db->selectSQL(
                    "INSERT INTO author SET first = :user_first, last = :user_last, pseudo = :pseudo, mail = :mail, password = :user_password, date =  NOW(), ipadress = :ipadress, useragent = :useragent, registered = :registered, token = :token",
                    array(
                        "user_first" => NULL,
                        "user_last" => NULL,
                        "pseudo" => htmlentities($data['pseudo']),
                        "mail" => NULL,
                        "user_password" => NULL,
                        "ipadress" => $_SERVER['REMOTE_ADDR'],
                        "useragent" => htmlentities($_SERVER['HTTP_USER_AGENT']),
                        "registered" => 0,
                        "token" => $token
                    ));
            }
        }
    }


    /**
     * @param string $token
     *
     */
    public function createCookie($token)
    {
        setcookie("token", $token, time()+80000, "/");
        return true;
    }

    /**
     * @return bool
     */
    public function deleteCookie()
    {
        setcookie("token", '', time()+80000, "/");
        return true;
    }


    public function disconnect()
    {
        $this->deleteCookie();
        return true;
    }
}
