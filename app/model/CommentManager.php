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
			self::COLUMN_PHOTO => $postId,
			self::COLUMN_TEXT => $text,
		]);
	}

	public function get($postId)
	{
		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_POST_ID, $postId)->fetch();

		return $this->toObject($row);
	}

	/** 
 	* @param ActiveRow
 	* @return stdClass
	*/
	public function toObject(ActiveRow $row)
	{
		$user = $this->userManager->toObject($row->user);

		$comment = $row->toArray();
		unset($comment[self::COLUMN_USER_ID]);

		$comment['user'] = $user;

		return (object) $comment;
	}
}	