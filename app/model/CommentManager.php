<?php

namespace App\Model;

use Nette;
use Nette\Security\User;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

class CommentManager extends BaseManager
{
	use Nette\SmartObject;

	const
		COLUMN_ID = 'id',
		COLUMN_USER_ID = 'user_id',
		COLUMN_POST_ID = 'post_id',
		COLUMN_TEXT = 'text';

	/** 
	* @var User */
	private $user;

	/**
	* @var UserManager */
	private $userManager; 

	/**
	* @param Context
	* @param User
	* @param UserManager
	*/
	public function __construct(Context $database, User $user, UserManager $userManager)
	{
		parent::__construct($database, 'comments');
		$this->user = $user;
		$this->userManager = $userManager;
	}

	/**
	* @param string
	* @param string
	* @return void
	*/
	public function create(string $postId, string $text)
	{
		$table = $this->getTable();

		$id = $this->generateUniqueID($table);

		$this->getTable()->insert(
		[
			self::COLUMN_ID => $id,
			self::COLUMN_USER_ID => $this->user->id,
			self::COLUMN_POST_ID => $postId,
			self::COLUMN_TEXT => $text,
		]);
	}

	/**
	* @param string
	* @return stdObject
	*/
	public function get(string $commentID)
	{
		$row = $this->getTable()->where(self::COLUMN_ID, $commentID)->fetch();

		return $this->toObject($row);
	}

	/** 
	* Get all comments from post
	* @param string
	* @return array
	*/
	public function getFromPost(string $postId)
	{
		$rows = $this->getTable()->where(self::COLUMN_POST_ID, $postId)->fetchAll();

		$comments = array();

		foreach ($rows as $row) 
		{
			$comment = $this->toObject($row);
			array_push($comments, $comment);
		}

		return $comments;
	}

	/**
	* @param string
	*/
	public function delete(string $commentId)
	{
		$this->getTable()->get($commentId)->delete();
	}

	/** 
 	* @param 
 	* @return stdClass or false
	*/
	public function toObject($row)
	{
		parent::toObject($row);

		$user = $this->userManager->toObject($row->user);

		$comment = $row->toArray();
		unset($comment[self::COLUMN_USER_ID]);

		$comment['user'] = $user;

		return (object) $comment;
	}
}	