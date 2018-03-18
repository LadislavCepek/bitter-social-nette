<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

/**
 * Users management.
 */
class UserManager extends BaseManager implements Nette\Security\IAuthenticator
{
	use Nette\SmartObject;

	const
		TABLE_NAME = 'users',
		COLUMN_ID = 'id',
		COLUMN_FIRSTNAME = 'firstname',
		COLUMN_LASTNAME = 'lastname',
		COLUMN_USERNAME = 'username',
		COLUMN_PASSWORD_HASH = 'hash',
		COLUMN_EMAIL = 'email',
		COLUMN_GENDER = 'gender',
		COLUMN_ROLE = 'role',
		COLUMN_HAS_PICTURE = 'hasPicture',
		HASH_COST = 12;

	/** @var MetaManager */
	private $metaManager;

	public function __construct(Context $database, MetaManager $metaManager)
	{
		parent::__construct($database);
		$this->metaManager = $metaManager;
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($email, $password) = $credentials;

		$row = $this->database->table(self::TABLE_NAME)
			->where(self::COLUMN_EMAIL, $email)->fetch();

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

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);

		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
	}

	/**
	 * Adds new user.
	 * @param  string
	 * @param  string
	 * @param  string
	 * @param  string
	 * @return void
	 * @throws DuplicateNameException
	 */
	public function add($firstname, $lastname, $username, $email, $password, $gender)
	{
		try 
		{
			$this->database->table(self::TABLE_NAME)->insert(
			[
				self::COLUMN_FIRSTNAME => $firstname,
				self::COLUMN_LASTNAME => $lastname,
				self::COLUMN_USERNAME => $username,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password, ['cost' => self::HASH_COST]),
				self::COLUMN_EMAIL => $email,
				self::COLUMN_GENDER => $gender,
			]);
		} 
		catch (Nette\Database\UniqueConstraintViolationException $e)
		{
			throw new DuplicateNameException;
		}
	}

	/** 
	* Get user data
 	* @param string
 	* @return stdClass
	*/
	public function get($username)
	{
		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_USERNAME, $username)->fetch();

		return $this->toObject($row);
	}

	/** 
 	* @param ActiveRow
 	* @return stdClass
	*/
	public function toObject(ActiveRow $row)
	{
		$user = $row->toArray();
		unset($user[self::COLUMN_PASSWORD_HASH]);

		if($user[self::COLUMN_HAS_PICTURE] == 0)
		{
			$user[self::COLUMN_HAS_PICTURE] = false;
			$user['picture'] = $user[self::COLUMN_GENDER] == 'ma' ? 'default/male.png' : 'default/female.png';
		}
		else
		{
			$user[self::COLUMN_HAS_PICTURE] = true;
			$user['picture'] = $user[self::COLUMN_USERNAME] . '/profile.png';
		}

		$meta = array();
		$meta['posts'] = $this->metaManager->postCount($row->id);

		$user['meta'] = (object) $meta;

		return (object) $user;
	}
}



class DuplicateNameException extends \Exception
{
}
