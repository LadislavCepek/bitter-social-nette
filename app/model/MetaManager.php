<?php

namespace App\Model;

use Nette;
use Nette\Database\Context;

class MetaManager
{
	use Nette\SmartObject;

	const 
		TABLE_POSTS = 'posts',
		COLUMN_USER_ID = 'user_id';

	/** @var Context */
	protected $database;

	public function __construct(Context $database)
	{
		$this->database = $database;
	}

	public function postCount($userId)
	{
		return $this->database->table(self::TABLE_POSTS)->where(self::COLUMN_USER_ID, $userId)->count();
	}
}