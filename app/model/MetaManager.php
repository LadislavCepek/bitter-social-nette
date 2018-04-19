<?php

namespace App\Model;

use Nette;
use Nette\Database;

class MetaManager extends BaseManager
{
	use Nette\SmartObject;

	const 
		TABLE_POSTS = 'posts',
		TABLE_LIKES = 'likes',
		TABLE_FOLLOWERS = 'followers',
		COLUMN_ID = 'id',
		COLUMN_USER_ID = 'user_id',
		COLUMN_POST_ID = 'post_id',
		COLUMN_FOLLOWING_ID = 'following_id',
		COLUMN_FOLLOWER_ID = 'follower_id';

	/**
	* @param Database\Context
	* @param User
	*/ 
	public function __construct(Database\Context $database)
	{
		parent::__construct($database, '');
	}

	/**
	* @param string User ID
	* @return array
	*/ 
	public function getUserMeta(string $userID, string $followerID)
	{
		$posts = $this->postCount($userID);

		$meta =
		[
			'posts' => $posts,
			'followers' => $this->followersCount($userID),
			'following' => $this->followingCount($userID),
			'isFollowed' => $this->isFollowed($userID, $followerID)
		];

		return (object) $meta;
	}

	/**
	* @param string Post ID
	* @param string User ID
	* @return array
	*/ 
	public function getPostMeta(string $postID, string $userID)
	{
		$meta =
		[
			'likes' => $this->likesCount($postID),
			'isLikedByUser' => $this->isLikedByUser($postID, $userID)
		];

		return (object) $meta;
	}

	/**
	* Count user posts
	* @param string User ID
	* @return int
	*/ 
	private function postCount(string $userID)
	{
		return $this->getTable(self::TABLE_POSTS)->where(self::COLUMN_USER_ID, $userID)->count();
	}

	/**
	* Count user followers
	* @param string User ID
	* @return int
	*/ 
	private function followersCount(string $userID)
	{
		return $this->getTable(self::TABLE_FOLLOWERS)->where(self::COLUMN_FOLLOWING_ID, $userID)->count();
	}

	/**
	* Count how many users this user is following
	* @param string User ID
	* @return int
	*/ 
	private function followingCount(string $userID)
	{
		return $this->getTable(self::TABLE_FOLLOWERS)->where(self::COLUMN_FOLLOWER_ID, $userID)->count();
	}

	/**
	* Is user followed by currently logged user?
	* @param string User ID
	* @param string Follower ID
	* @return boolean
	*/ 
	private function isFollowed(string $userID, string $followerID)
	{
		$result = $this->getTable(self::TABLE_FOLLOWERS)->select(self::COLUMN_ID)->where(self::COLUMN_FOLLOWING_ID, $userID)->where(self::COLUMN_FOLLOWER_ID, $followerID)->fetch();

		return $this->getBool($result);
	}

	/** 
	*	@param string Post ID
	*	@param string User ID
	* @return boolean
	*/
	private function isLikedByUser(string $postID, string $userID)
	{
		$table = $this->getTable(self::TABLE_LIKES);
		$selection = $table->where(self::COLUMN_POST_ID, $postID)->where(self::COLUMN_USER_ID, $userID);
		$result = $selection->fetch();

		return $this->getBool($result);
	}

	/** 
	* Calculates how many likes post have 
	*	@param string Post ID
	* @return int
	*/
	private function likesCount(string $postID)
	{
		$table = $this->getTable(self::TABLE_LIKES);
		return $table->where(self::COLUMN_POST_ID, $postID)->count();
	}
}