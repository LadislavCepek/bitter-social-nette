<?php

namespace App\Model;

use Nette;
use Nette\Security\User;
use Nette\Database;

abstract class BaseManager
{
	use Nette\SmartObject;

	/** @var Context */
	protected $database;

	/** @var string */
	private $tableName;

	public function __construct(Database\Context $database, string $tableName)
	{
		$this->database = $database;
		$this->tableName = $tableName;
	}

	/** 
	* Convert user data to object
 	* @param 
 	* @return false or void
	*/
	public function toObject($row)
	{
		if($row == false)
			return false;
	}

	/**
	* @param string Table name
	* @return Database\Table\Selection
	*/
	protected function getTable(string $name = null)
	{
		$tableName = $this->tableName;

		if($name != null)
			$tableName = $name;

		return $this->database->table($tableName);
	}

	/** 
	* Get bool from database result
	* @param 
	* @return bool
	*/	
	protected function getBool($activeRow)
	{
		if($activeRow)
			return true;
		else
			return false;
	}

	/**
	* Checks if generated id is unique
	* @param Table\Selection
	* @return string
	*/
	protected function generateUniqueID()
	{
		$table = $this->getTable();
		$isUnique = false;
		$id;

		while(!$isUnique)
		{
			$id = $this->generateRandomID();
			$result = $table->get($id);

			if($result == false)
				$isUnique = true;
			else
				$isUnique = false;
		}	
		
		return $id;
	}

	/**
	* @param int Length of generated string
	* @return string
	*/
	private function generateRandomID(int $length = 23)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$endIndex = strlen($characters) - 1;
		$result = '';

		for($i = 0; $i < $length; $i++)
		{
			$result .= $characters[rand(0, $endIndex)]; 
		}

		return $result;
	}
}	