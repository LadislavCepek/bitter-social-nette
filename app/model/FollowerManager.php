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
		COLUMN_ID = 'id',
		COLUMN_FOLLOWING_ID = 'following_id',
		COLUMN_FOLLOWER_ID = 'follower_id';

	/**
	* @var User */
	private $user;

	/**
	* @var UserManager */
	private $userManager; 

	public function __construct(Context $database, User $user, UserManager $userManager)
	{
		parent::__construct($database, 'followers');
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
		$table = $this->getTable();
		$id = $this->generateUniqueId($table);

		$this->getTable()->insert(
		[
			self::COLUMN_ID => $id,
			self::COLUMN_FOLLOWING_ID => $followingID,
			self::COLUMN_FOLLOWER_ID => $this->user->id,
		]);
	}

	/** 
	* Delete follower row
	* @param string
	* @return void
	*/
	public function delete(string $followingID)
	{
		$selection = $this->getTable()->where(self::COLUMN_FOLLOWING_ID, $followingID)->where(self::COLUMN_FOLLOWER_ID, $this->user->id);
		$selection->delete();
	}

	/** 
	* Get user followers
  * @param string
  * @return array
  */
	public function getUserFollowers(string $followingID)
	{
		$rows = $this->getTable()->where(self::COLUMN_FOLLOWING_ID, $followingID)->select(self::COLUMN_FOLLOWER_ID)->fetchAll();

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
	public function getUserFollowing(string $followerID)
	{
		$rows = $this->getTable()->where(self::COLUMN_FOLLOWER_ID, $followerID)->select(self::COLUMN_FOLLOWING_ID)->fetchAll();

		$users = array();

		foreach ($rows as $row)
		{
			$user = $this->toObject($row->following);
			array_push($users, $user);
		}

		return $users;
	}

	/** 
 	* @param 
 	* @return stdClass or false
	*/
	public function toObject($row)
	{
		return $this->userManager->toObject($row);
	}
}	