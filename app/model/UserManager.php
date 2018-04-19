<?php

namespace App\Model;

use Nette;
use Nette\Security\User;
use Nette\Database;
use App\Service\SearchService;

/**
 * Users management.
 */
class UserManager extends BaseManager
{
	use Nette\SmartObject;

	const
		COLUMN_ID = 'id',
		COLUMN_FIRSTNAME = 'firstname',
		COLUMN_LASTNAME = 'lastname',
		COLUMN_USERNAME = 'username',
		COLUMN_PASSWORD_HASH = 'hash',
		COLUMN_EMAIL = 'email',
		COLUMN_GENDER = 'gender',
		COLUMN_ROLE = 'role',
		COLUMN_HAS_PICTURE = 'hasPicture';

	/** @var MetaManager */
	private $metaManager;

	/** @var SearchService */
	private $searchService;

	private $user;

	public function __construct(Database\Context $database, MetaManager $metaManager, User $user, SearchService $searchService)
	{
		parent::__construct($database, 'users');
		$this->metaManager = $metaManager;
		$this->user = $user;
		$this->searchService = $searchService;
	}

	/** 
	* Get user data
 	* @param string
 	* @return stdClass
	*/
	public function getByID(string $id)
	{
		$row = $this->getTable()->where(self::COLUMN_ID, $id)->fetch();

		return $this->toObject($row, true);
	}

	/** 
	* Get user data
 	* @param string
 	* @return stdClass
	*/
	public function getByUsername(string $username)
	{
		$row = $this->getTable()->where(self::COLUMN_USERNAME, $username)->fetch();

		return $this->toObject($row, true);
	}

	/** 
	* Get user ID
 	* @param string
 	* @return string
	*/
	public function getID(string $username)
	{
		$row = $this->getTable()->where(self::COLUMN_USERNAME, $username)->select('id')->fetch();

		return $row->id;
	}

	/** 
	* Update user picture
 	* @param string
 	* @param string
 	* @return void
	*/
	public function updatePicture(string $id, string $hasPicture)
	{
		$default = $this->searchService->getUser($id);

		try
		{
			$this->searchService->updateUserPicture($id, $hasPicture);
		}
		catch(Exception $ex)
		{
			Debugger::log($ex);
			return;
		}	

		try
		{
			$this->getTable()->where(self::COLUMN_ID, $id)->update(
			[
				self::COLUMN_HAS_PICTURE => $hasPicture ? 1 : 0
			]);
		}
		catch(Nette\Database\DriverException $ex)
		{
			Debugger::log($ex);

			/* Revert search update */
			try
			{
				$this->searchService->updateUserPicture($id, $default->hasPicture);
			}
			catch(Exception $ex)
			{
				Debugger::log($ex);
			}
		}
	}

	/** 
	* Convert user data to object
 	* @param Table\ActiveRow
 	* @param bool Include meta data
 	* @return stdClass
	*/
	public function toObject($row, bool $getMeta = false)
	{
		parent::toObject($row);

		$user = $row->toArray();
		unset($user[self::COLUMN_PASSWORD_HASH]);

		if($user[self::COLUMN_HAS_PICTURE] == 0)
		{
			$user[self::COLUMN_HAS_PICTURE] = false;
			$user['picture'] = sprintf('default/%s.png', $user[self::COLUMN_GENDER]);
		}
		else
		{
			$user[self::COLUMN_HAS_PICTURE] = true;
			$user['picture'] = sprintf('%s/profile.png', $user[self::COLUMN_ID]);
		}

		if($getMeta)
		{
			$user['meta'] = $this->metaManager->getUserMeta($user['id'], $this->user->id);
		}

		return (object) $user;
	}
}