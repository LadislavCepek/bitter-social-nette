<?php

namespace App\Model;

use Nette;
use Nette\Security\User;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

abstract class BaseManager
{
	use Nette\SmartObject;

	/** @var Context */
	protected $database;

	public function __construct(Context $database)
	{
		$this->database = $database;
	}

	abstract public function toObject(ActiveRow $row);
}	