<?php
/*
	DB IMPORT UTIL
	
	
*/
	
class DataImporter {

	function __construct($settings = false)
		{

				$this->Settings['url_file'] = "data.csv"; // Url du fichier
				$this->Settings['mysql_host'] = "localhost"; // Host de la base
				$this->Settings['mysql_database'] = ""; // Nom de la base
				$this->Settings['mysql_table'] = "DATA_ECOLE"; // Nom de la table
				$this->Settings['mysql_user'] = "USER"; // Nom d'utilisateur de la base
				$this->Settings['mysql_pass'] = "PASS"; // Mot de passe de la base

				// Vous ne devriez pas avoir à éditer les paramètres suivant:
				$this->Settings['column_delimiter'] = ";";
				$this->Settings['line_delimiter'] = "\r\n";
				$this->Settings['convert_encoding'] = true;


			if(file_exists($settings))
				{
					$set = parse_ini_file($settings,true);
					foreach($set['importer'] as $key => $value)
						{
							$this->Settings[$key] = $value;	
						}
					foreach($set['phpsettings'] as $key => $value)
						{
							ini_set($key, $value);
						}
					
				
				}
								
			$this->Current['error'] = false;	
			$this->Current['error_msg'] = "";
			
			require_once("app/models/Dbconnect.php");
			
			$this->db = new Dbconnect();
				
		}
	function import()
		{
			$content = $this->getFile($this->Settings['url_file']);
			
			if($content) {
				$data = $this->parseData($content);
				$this->insertInDatabase($data);
			} else {
				$this->Current['error'] = true;
				$this->Current['error_msg'] = $this->getError(404);
			}
			
			
		}	
	function insertInDatabase($data)
		{
			$this->SetDbProperties();
			$sql_template = $this->createSQLTemplate();
			$sqldata = $this->parseDataTemplate($data,$sql_template);
			$this->ExecuteSQL($sqldata);			
			

		}
	function ExecuteSQL($sqldata)
		{
			$data = explode("\n",$sqldata);
			for($i=0; $i < sizeof($data); $i++)
				{
					$this->db->selectsql($data[$i]);
				}
			return true;
		}
	function createSqlTemplate()
		{
		
			$column = $this->getColumnNames();
			// create statement with fieldlist
			$fields = "";
			// starting at second column to avoid the primary key
			for($i=1; $i < sizeof($column); $i++)
				{
					$fields[] = '`'.$column[$i]['Field'].'`';
					$columns[] = "'"."{col_".($i-1).'}'."'";
				}
			$fieldlist = implode(",",$fields);
			$columnlist = implode(",",$columns);
			
			$sql_template = "INSERT INTO `".$this->Settings['mysql_table']."` (".$fieldlist.") VALUES(".$columnlist.");\n";
			
			return $sql_template;
		}
	function parseDataTemplate($data,$tpl)
		{
			$sql = "";
			for($i=0; $i < sizeof($data); $i++)
				{
					$sql_tmp = $tpl;
					for($j=0; $j < sizeof($data[$i]); $j++)
						{
							$sql_tmp = str_replace("{col_".$j."}",addslashes($data[$i][$j]),$sql_tmp);
						}
					$sql .= $sql_tmp;
				}
			return $sql;	
		}	
	function getColumnNames()
		{

			$column = $this->db->getColumnNames($this->Settings['mysql_table']);
			return $column; 

		}
		
	function setDbProperties()
		{
		
			$this->db->username = $this->Settings['mysql_user'];
			$this->db->password = $this->Settings['mysql_pass'];
			$this->db->database = $this->Settings['mysql_database'];
			$this->db->hostname = $this->Settings['mysql_hostname'];

		
		}
		
	function parseData($content)
		{
			$data = explode($this->Settings['line_delimiter'],$content);
			
			$result = "";
			
			for($i=0; $i < sizeof($data); $i++)
				{
					$column = explode($this->Settings['column_delimiter'],$data[$i]);
					if(sizeof($column) > 5) {
						$result[] = $column;
					}				
				}
			return $result;	
			
		}
	function getFile($url)
		{
			
			$content = file_get_contents($url);
			if($this->Settings['convert_encoding'])
				{
					$content = mb_convert_encoding($content,"UTF-8","ISO-8859-1");
				}

			if(!$content) {
				return false;
			}  else {
				return $content;			
			}
		
		}
	private function getError($err_num)
		{
			$err[404] = "No such file or directory";
			$err["default"] = "Unknown error";

			if(isset($err[$err_num])) {
				return $err[$err_num];
			} else {
				return $err['default'];
			}
		}	
}
