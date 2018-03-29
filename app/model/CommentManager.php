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
		TABLE_NAME = 'comments',
		COLUMN_USER = 'user_id',
		COLUMN_POST = 'post_id',
		COLUMN_TEXT = 'text';

	/** @var User */
	private $user;

	/** @var UserManager */
	private $userManager; 

	public function __construct(Context $database, User $user, UserManager $userManager)
	{
		parent::__construct($database);
		$this->user = $user;
		$this->userManager = $userManager;
	}

	public function create($postId, $text)
	{
		$this->database->table(self::TABLE_NAME)->insert(
		[
			self::COLUMN_USER => $this->user->id,
			self::COLUMN_POST => $postId,
			self::COLUMN_TEXT => $text,
		]);
	}

	public function get($postId)
	{
		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_POST, $postId)->fetch();

		return $this->toObject($row);
	}

	public function getFromPost($postId)
	{
		$rows = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_POST, $postId)->fetchAll();

		$comments = array();

		foreach ($rows as $row) 
		{
			$comment = $this->toObject($row);
			array_push($comments, $comment);
		}

		return $comments;
	}

	public function delete($commentId)
	{
		$this->database->table(self::TABLE_NAME)->get($commentId)->delete();
	}

	/** 
 	* @param ActiveRow
 	* @return stdClass
	*/
	public function toObject(ActiveRow $row)
	{
		$user = $this->userManager->toObject($row->user);

		$comment = $row->toArray();
		unset($comment[self::COLUMN_USER]);

		$comment['user'] = $user;

		return (object) $comment;
	}
}	