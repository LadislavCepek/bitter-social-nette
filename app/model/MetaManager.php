<?php

namespace App\Model;

use Nette;
use Nette\Database\Context;

class MetaManager
{
	use Nette\SmartObject;

	const 
		TABLE_POSTS = 'posts',
		TABLE_FOLLOWERS = 'followers',
		COLUMN_USER_ID = 'user_id',
		COLUMN_FOLLOWER_ID = 'follower_id';

	/**
	* @var Context */
	protected $database;

	public function __construct(Context $database)
	{
		$this->database = $database;
	}

	/**
	* Count user posts
	* @param string
	* @return int
	*/ 
	public function postCount(string $userID)
	{
		return $this->database->table(self::TABLE_POSTS)->where(self::COLUMN_USER_ID, $userID)->count();
	}

	/**
	* Count user followers
	* @param string
	* @return int
	*/ 
	public function followersCount($userID)
	{
		return $this->database->table(self::TABLE_FOLLOWERS)->where(self::COLUMN_USER_ID, $userID)->count();
	}

	/**
	* Count how many users this user is following
	* @param string
	* @return int
	*/ 
	public function followingCount($userID)
	{
		return $this->database->table(self::TABLE_FOLLOWERS)->where(self::COLUMN_FOLLOWER_ID, $userID)->count();
	}
}