<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Database\Context;
use Nette\Database\Table;
use Nette\Utils\FileSystem;
use Tracy\Debugger;
use App\Service\SearchService;

/**
 * Sign-in and Sing-up management.
 */
class SignManager extends BaseManager implements Nette\Security\IAuthenticator
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
		COLUMN_HAS_PICTURE = 'hasPicture',
		HASH_COST = 12,
		PROFILES_DIRECTORY = 'profiles/';

	/** @var SearchService */
	private $searchService;

	public function __construct(Context $database, SearchService $searchService)
	{
		parent::__construct($database, 'users');
		$this->searchService = $searchService;
	}

	/**
	 * Performs an authentication.
	 * @param array
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;

		$row = $this->getTable()->where(self::COLUMN_EMAIL, $email)->fetch();

		if (!$row) 
		{
			throw new Nette\Security\AuthenticationException('The email is incorrect.', self::IDENTITY_NOT_FOUND);
		} 
		elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) 
		{
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		} 
		elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) 
		{
			$row->update(
			[
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password, ['cost' => self::HASH_COST]),
			]);
		}

		$array = $row->toArray();
		unset($array[self::COLUMN_PASSWORD_HASH]);
		unset($array[self::COLUMN_ROLE]);

		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $array);
	}

	/**
	 * Adds new user
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return void
	 * @throws DuplicateNameException
	 */
	public function add(string $firstname, string $lastname, string $username, string $email, string $password, string $gender)
	{
		$table = $this->getTable();

		$id = $this->generateUniqueID($table);	

		try
		{
			$this->getTable()->insert(
			[
				self::COLUMN_ID => $id,
				self::COLUMN_FIRSTNAME => $firstname,
				self::COLUMN_LASTNAME => $lastname,
				self::COLUMN_USERNAME => $username,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password, ['cost' => self::HASH_COST]),
				self::COLUMN_EMAIL => $email,
				self::COLUMN_GENDER => $gender,
			]);

			$this->createUserDirectory($id);
		} 
		catch (Nette\Database\UniqueConstraintViolationException $e)
		{
			throw new DuplicateNameException;
			return;
		}

		try 
		{
			$this->searchService->indexUser($id, $firstname, $lastname, $username, $gender);	
		}
		catch(Exception $ex)
		{
			Debugger:log($ex);

			$this->delete($id);

			return;
		}
	}

	/**
	 * Update user
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param bool
	 * @return void
	 */
	public function update(string $id, string $firstname, string $lastname, string $gender)
	{
		$default = $this->searchService->getUser($id);

		try
		{
			$this->searchService->updateUser($id, $firstname, $lastname, $gender);
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
				self::COLUMN_FIRSTNAME => $firstname,
				self::COLUMN_LASTNAME => $lastname,
				self::COLUMN_GENDER => $gender,
				self::COLUMN_HAS_PICTURE => $hasPicture ? 1 : 0
			]);
		}
		catch(Nette\Database\DriverException $ex)
		{
			Debugger::log($ex);

			/* Revert search update */
			try
			{
				$names = explode(' ', $default->fullname);

				$this->searchService->updateUser($id, $names[0], $names[1], $default->gender);
			}
			catch(Exception $ex)
			{
				Debugger::log($ex);
			}
		}	
	}

	public function delete(string $id)
	{
		$this->getTable()->where(self::COLUMN_ID, $id)->delete();
	}

	public function updatePassword(string $email, string $password)
	{
		$row = $this->getTable()->where(self::COLUMN_EMAIL, $email)->fetch(); 

		\Tracy\Debugger::barDump($row, 'row');

		/*$row->update(
		[
			self::COLUMN_PASSWORD_HASH => Passwords::hash($password, ['cost' => self::HASH_COST]),
		]);*/
	}

	/**
	* Creates directory for user files
	* @param string
	* @return void
	* @throws Nette\IOException
	*/
	public function createUserDirectory(string $userPath)
	{
		$directory = self::PROFILES_DIRECTORY . $userPath;

		try
		{
			FileSystem::createDir($directory);
		}
		catch(Nette\IOException $ex)
		{
			Debugger::log($ex);
		}
	}
}

class DuplicateNameException extends \Exception
{
}
