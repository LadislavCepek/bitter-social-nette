<?php

namespace App\Model;

use Nette;
use Nette\Security\User;
use Nette\Database\Context;
use Nette\Database\Table;

abstract class BaseManager
{
	use Nette\SmartObject;

	/** @var Context */
	protected $database;

	public function __construct(Context $database)
	{
		$this->database = $database;
	}

	/** 
	* Convert user data to object
 	* @param Table\ActiveRow
 	* @return stdClass
	*/
	abstract public function toObject(Table\ActiveRow $row);

	/**
	* Checks if generated id is unique
	* @param Table\Selection
	* @return string
	*/
	protected function generateUniqueID(Table\Selection $table)
	{
		$isUnique = false;
		$id;

		while(!$isUnique)
		{
			$id = uniqid('', true);
			$result = $table->get($id);

			if($result == false)
				$isUnique = true;
			else
				$isUnique = false;
		}	
		
		return $id;
	}
}	