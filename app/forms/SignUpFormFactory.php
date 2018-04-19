<?php

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


class SignUpFormFactory
{
	use Nette\SmartObject;

	const PASSWORD_MIN_LENGTH = 8;

	/** @var FormFactory */
	private $factory;

	/** @var Model\SignManager */
	private $signManager;


	public function __construct(FormFactory $factory, Model\SignManager $signManager)
	{
		$this->factory = $factory;
		$this->signManager = $signManager;
	}


	/** 
	* Create form
	* @param callable
	* @return Form 
	**/
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();
		$form->addText('firstname', 'First name:')
			->setRequired('Please enter your first name');

		$form->addText('lastname', 'Last name:')
			->setRequired('Please enter your last name');

		$form->addText('username', 'Username:')
			->setRequired('Please enter your username');

		$form->addEmail('email', 'E-mail:')
			->setRequired('Please enter your e-mail');

		$form->addPassword('password', 'Password:')
			->setOption('description', sprintf('at least %d characters', self::PASSWORD_MIN_LENGTH))
			->setRequired('Please create a password')
			->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

		$form->addSelect('gender', 'Gender:', 
		[
			'ma' => 'Male',
			'fe' => 'Female',		
		]);
		$form['gender']->setDefaultValue('ma');

		$form->addSubmit('send', 'Sign up');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess)
		{
			try 
			{
				$this->signManager->add($values->firstname, $values->lastname, $values->username,
																$values->email, $values->password, $values->gender);
			} 
			catch (Model\DuplicateNameException $e) 
			{
				$form->addError('Username or email is already taken');
				return;
			}
			
			$onSuccess();
		};

		$form->addProtection('Request time out');

		return $form;
	}
}
