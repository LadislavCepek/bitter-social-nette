<?php

namespace App\Model;

use Nette;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class LikeManager extends BaseManager
{
	use Nette\SmartObject;

	const 
		COLUMN_ID = 'id',
		COLUMN_USER_ID = 'user_id',
		COLUMN_POST_ID = 'post_id';

	/** @var User */
	private $user;

	/** @var UserManager */
	private $userManager;

	/**
	* @param Context
	* @param User
	* @param UserManager
	*/
	public function __construct(Context $database, User $user, UserManager $userManager)
	{
		parent::__construct($database, 'likes');
		$this->user = $user;
		$this->userManager = $userManager;
	}

	/** 
	*	@param string 
	* @return boolean
	*/
	public function create(string $postID)
	{
		$table = $this->getTable();

		$id = $this->generateUniqueID();

		$result = $table->insert(
		[
			self::COLUMN_ID => $id,
			self::COLUMN_USER_ID => $this->user->id,
			self::COLUMN_POST_ID => $postID,
		]);

		return $this->getBool($result);
	}

	/** 
	*	@param string 
	* @return boolean
	*/
	public function delete(string $postID)
	{
		$result = $this->getLikeSelection($postID, $this->user->id)->delete();

		return $this->getBool($result);
	}

	/** 
	*	@param string 
	* @return Nette\Database\Table\Selection
	*/
	private function getLikeSelection(string $postID)
	{
		return $this->getTable()->where(self::COLUMN_POST_ID, $postID)->where(self::COLUMN_USER_ID, $this->user->id);
	}
}