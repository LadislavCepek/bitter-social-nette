<?php

namespace App\Model;

use Nette;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Security\User;
use Nette\Utils;

class PostManager extends BaseManager
{
	use Nette\SmartObject;

	const 
		COLUMN_ID = 'id',
		COLUMN_CONTENT = 'content',
		COLUMN_IMAGE = 'image',
		COLUMN_ARTICLE = 'article',
		COLUMN_USER_ID = 'user_id',
		COLUMN_POST_ID = 'post_id',
		COLUMN_VISIBLE = 'visible';

	/** @var User */
	private $user;

	/** @var UserManager */
	private $userManager;

	/** @var MetaManager */
	private $metaManager;

	/**
	* @param Context
	* @param User
	* @param UserManager
	* @param MetaManager
	*/
	public function __construct(Context $database, User $user, UserManager $userManager, MetaManager $metaManager)
	{
		parent::__construct($database, 'posts');
		$this->user = $user;
		$this->userManager = $userManager;
		$this->metaManager = $metaManager;
	}

	/**
	* @param Utils\ArrayHash
	* @return ActiveRow
	*/
	public function create(Utils\ArrayHash $post)
	{
		$table = $this->getTable();

		$id = $this->generateUniqueID($table);

		return $this->getTable()->insert(
		[
			self::COLUMN_ID => $id,
			self::COLUMN_CONTENT => $post->content,
			self::COLUMN_IMAGE => $post->image,
			self::COLUMN_ARTICLE => $post->article,
			self::COLUMN_USER_ID => $this->user->id,
			self::COLUMN_VISIBLE => $post->hidden ? 0 : 1,
		]);
	}

	/**
	* @param Utils\ArrayHash
	* @return stdObject
	*/
	public function edit(Utils\ArrayHash $post)
	{
		$row = $this->getTable()->get($post->id);

		$row->update(
		[
			self::COLUMN_CONTENT => $post->content,
			self::COLUMN_IMAGE => $post->image,
			self::COLUMN_ARTICLE => $post->article,
			self::COLUMN_VISIBLE => $post->hidden ? 0 : 1,
		]);

		return $this->toObject($row);
	}

	/**
	* @param string
	* @return void
	*/
	public function delete(string $postID)
	{
		$this->getTable()->get($postID)->delete();
	}

	/**
	* @param string
	* @param bool
	* @return void
	*/
	public function setVisible(string $postID, bool $isVisible)
	{
		$value = $isVisible ? 1 : 0;
		$this->getTable()->where(self::COLUMN_ID, $postID)->update([self::COLUMN_VISIBLE => $value]);
	}

	/** 
	* Does post exist
	*	@param string 
	* @return boolean
	*/
	public function exists(string $postID)
	{
		$result = $this->getTable()->where(self::COLUMN_ID, $postID);

		if($result)
			return true;
		else
			return false;
	}

	/** 
	*	@param string 
	* @return stdObject
	*/
	public function get(string $postID)
	{
		$post = $this->getTable()->where(self::COLUMN_ID, $postID)->fetch();

		return $this->toObject($post);
	}

	/** 
	* Get all posts from user
	*	@param string 
	* @return array
	*/
	public function getFromUser(string $userID)
	{
		$rows = $this->getTable()->where(self::COLUMN_USER_ID, $userID)->fetchAll();

		$posts = array();

		foreach ($rows as $row)
		{
			$post = $this->toObject($row);
			array_push($posts, $post);
		}

		return $posts;
	}

	/**
	* Get posts for users feed
	* @param string
	* @return array
	*/
	public function getFeed(string $userID)
	{
		$query = 'SELECT p.*, f.following_id, f.follower_id FROM posts p INNER JOIN followers f ON (f.following_id = p.user_id) WHERE f.follower_id = ? ORDER BY p.created DESC';
		return $this->database->query($query, $userID)->fetchAll();
	}

	/** 
	*	@param 
	* @return stdObject or false
	*/
	public function toObject($row)
	{
		parent::toObject($row);

		$user = $this->userManager->toObject($row->user);

		$post = $row->toArray();
		unset($post[self::COLUMN_USER_ID]);

		$article = Utils\Html::el('');
		$article->setHtml($post[self::COLUMN_ARTICLE]);

		$content = Utils\Html::el('');
		$content->setHtml($post[self::COLUMN_CONTENT]);

		$post[self::COLUMN_ARTICLE] = $article;
		$post[self::COLUMN_CONTENT] = $content;

		$post['user'] = $user;

		$meta = $this->metaManager->getPostMeta($post['id'], $this->user->id);

		$post['meta'] = $meta;

		return (object) $post;
	}
}