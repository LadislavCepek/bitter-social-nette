<?php

namespace App\Model;

use Nette;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;

class PostManager extends BaseManager
{
	use Nette\SmartObject;

	const 
		TABLE_POSTS = 'posts',
		COLUMN_ID = 'id',
		COLUMN_TITLE = 'title',
		COLUMN_HEADLINE = 'headline',
		COLUMN_BODY = 'body',
		COLUMN_USER_ID = 'user_id',
		TABLE_LIKES = 'likes',
		COLUMN_POST_ID = 'post_id';

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

	public function create($post)
	{
		return $this->database->table(self::TABLE_POSTS)->insert(
		[
			self::COLUMN_TITLE => $post->title,
			self::COLUMN_HEADLINE => $post->headline,
			self::COLUMN_BODY => $post->body,
			self::COLUMN_USER_ID => $this->user->id,
		]);
	}

	public function edit($post)
	{
		$row = $this->database->table(self::TABLE_POSTS)->get($post->id);

		unset($post[self::COLUMN_ID]);

		$row->update($post);

		return $this->toObject($row);
	}

	public function delete($postId)
	{
		$this->database->table(self::TABLE_POSTS)->get($postId)->delete();
	}

	public function exists($postId)
	{
		$result = $this->database->table(self::TABLE_POSTS)->where(self::COLUMN_ID, $postId);

		if($result)
			return true;
		else
			return false;
	}

	public function get($postId)
	{
		$post = $this->database->table(self::TABLE_POSTS)->where(self::COLUMN_ID, $postId)->fetch();

		return $this->toObject($post);
	}

	public function getFromUser($userId)
	{
		$selection = $this->database->table(self::TABLE_POSTS);
		$rows = $selection->where(self::COLUMN_USER_ID, $userId)->fetchAll();

		$posts = array();

		foreach ($rows as $row)
		{
			$post = $this->toObject($row);
			array_push($posts, $post);
		}

		return $posts;
	}

	public function postCount($userId)
	{
		return $this->database->table(self::TABLE_POSTS)->where(self::COLUMN_USER_ID, $userId)->count();
	}

	public function toObject(ActiveRow $row)
	{
		$user = $this->userManager->toObject($row->user);

		$post = $row->toArray();
		unset($post[self::COLUMN_USER_ID]);

		$post['user'] = $user;

		$meta = array();
		$meta['liked'] = $this->isLikedByUser($row->id);
		$meta['likes'] = $this->likes($row->id);

		$post['meta'] = (object) $meta;

		return (object) $post;
	}

	public function like($postId)
	{
		$result = $this->database->table(self::TABLE_LIKES)->insert(
		[
			self::COLUMN_POST_ID => $postId,
			self::COLUMN_USER_ID => $this->user->id,
		]);

		if($result)
			return true;
		else
			return false;
	}

	public function dislike($postId)
	{
		$result = $this->getLikeSelection($postId, $this->user->id)->delete();

		if($result)
			return true;
		else
			return false;
	}

	public function isLikedByUser($postId)
	{
		$result = $this->getLikeSelection($postId, $this->user->id)->fetch();

		if($result)
			return true;
		else
			return false;
	}

	/** Calculates how many likes post have */
	public function likes($postId)
	{
		$selection = $this->database->table(self::TABLE_LIKES);
		return $selection->where(self::COLUMN_POST_ID, $postId)->count();
	}

	private function getLikeSelection($postId)
	{
		$selection = $this->database->table(self::TABLE_LIKES);
		return $selection->where(self::COLUMN_POST_ID . ' = ? && '. self::COLUMN_USER_ID . ' = ?', $postId, $this->user->id);
	}
}