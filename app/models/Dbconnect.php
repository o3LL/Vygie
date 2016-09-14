<?php

class Dbconnect
{

  function __construct($settings = false) 
  {

    
		$this->Settings['mysql_host'] = "localhost"; // Host de la base
		$this->Settings['mysql_database'] = ""; // Nom de la base
		$this->Settings['mysql_table'] = "DATA_POST"; // Nom de la table
		$this->Settings['mysql_user'] = "USER"; // Nom d'utilisateur de la base
		$this->Settings['mysql_pass'] = "PASS"; // Mot de passe de la base²

		if(file_exists($settings))
		{
			$set = parse_ini_file($settings,true);
			foreach($set['db'] as $key => $value)
			{
				$this->Settings[$key] = $value;	
			}			
		}


    $this->orderby = false;
    $this->username = $this->Settings['mysql_user']; // Votre nom d'utilisateur
    $this->password = $this->Settings['mysql_pass']; // Votre mot de passe
    $this->database = $this->Settings['mysql_database']; // Le nom de la base de donnée
    $this->hostname = $this->Settings['mysql_host']; // l'adresse du serveur mysql (le nom de l'hôte)
    $this->error = 0; // init l'erreur a 0
    $this->debug = 0;

  }

	public function getColumnNames($table)
  {
    
        $sql = 'SHOW COLUMNS FROM ' . $table;
        $this->connectDB();
        $this->prepareRequest($sql);
        $this->executeRequest($arg);
        $data = $this->fetchAll();
		        
        return $data;
             
    }	

  public function selectSQL($sql, $exec = NULL) 
  {
    if ($this->debug == 1) {
      echo "<br /> Exec = ";
      print_r($exec);
      echo "<br /> SQL = ";
      print_r($sql);
    }
    $this->connectDB();
    $orderby = $this->getOrderBy();
    if($this->error == 1) {
      return false;
    }
    $this->prepareRequest($sql.$orderby);
    $this->executeRequest($exec);
    $this->fetchAll(PDO::FETCH_ASSOC);
    return $this->result;
  }

  public function updateSQL($sql, $exec) 
  {
    $this->connectDB();
    if($this->error == 1) {
      return false;
    }
    $this->prepareRequest($sql);
    $this->executeRequest($exec);
    $this->result = $this->request->rowCount();
    return $this->result;
  }

  private function connectDB() 
  {
    try {
      $this->bdd = new PDO("mysql:host=".$this->hostname.";dbname=".$this->database.";charset=utf8", $this->username, $this->password);
      return true;
    }
    catch (Exception $e) {
      $this->error = 1;
      $this->errormsg = $e;
      print_r($this->errormsg);
      return false;
    }

  }

  public function debugSql()
  {
    $this->debugDumpParams();
  }

  private function prepareRequest($sql) 
  {
    $this->debug_msg['sql'][] = $sql;
    $this->request = $this->bdd->prepare($sql);
  }

  private function executeRequest($exec) 
  {
    $this->request->execute($exec);
  }

  private function fetchAll() 
  {
    $this->result = $this->request->fetchAll();
  }

  /**
  * Function returning an ORDER BY $value
  */
  private function getOrderBy() 
  {
    $orderby = " ";
    if ($this->orderby) {
      $orderby = " ORDER BY ".$this->orderby;
    }
    return $orderby;
  }

}


 ?>
