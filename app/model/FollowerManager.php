<?php

namespace App\Model;

use Nette;
use Nette\Security\User;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

class FollowerManager extends BaseManager
{
	use Nette\SmartObject;

	const
		TABLE_NAME = 'followers',
		COLUMN_ID = 'id',
		COLUMN_USER_ID = 'user_id',
		COLUMN_FOLLOWER_ID = 'follower_id';

	/**
	* @var User */
	private $user;

	/**
	* @var UserManager */
	private $userManager; 

	public function __construct(Context $database, User $user, UserManager $userManager)
	{
		parent::__construct($database);
		$this->user = $user;
		$this->userManager = $userManager;
	}

	/** 
	* Create follower row
	* @param string
	* @return void
	*/
	public function create(string $followingID)
	{
		$table = $this->database->table(self::TABLE_NAME);

		$id = $this->generateUniqueId($table);

		$table->insert(
		[
			self::COLUMN_ID => $id,
			self::COLUMN_USER_ID => $followingID,
			self::COLUMN_FOLLOWER_ID => $this->user->id,
		]);
	}

	/** 
	* Get user followers
  * @param string
  * @return array
  */
	public function getUserFollowers(string $userID)
	{
		$rows = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_USER_ID, $userID)->select('follower_id')->fetchAll();

		$users = array();

		foreach ($rows as $row)
		{
			$user = $this->toObject($row->follower);
			array_push($users, $user);
		}

		return $users;
	}

	/** 
	* Get users which this user follows
  * @param string
  * @return array
  */
	public function getUserFollowing(string $userID)
	{
		$rows = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_FOLLOWER_ID, $userID)->select('user_id')->fetchAll();

		$users = array();

		foreach ($rows as $row)
		{
			$user = $this->toObject($row->user);
			array_push($users, $user);
		}

		return $users;
	}

	public function isUserFollowing()
	{

	}

	public function delete($ID)
	{
		
	}

	/** 
 	* @param ActiveRow
 	* @return stdClass
	*/
	public function toObject(ActiveRow $row)
	{
		return $this->userManager->toObject($row);
	}
}	