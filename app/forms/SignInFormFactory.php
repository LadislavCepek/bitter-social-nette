<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignInFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var User */
	private $user;


	public function __construct(FormFactory $factory, User $user)
	{
		$this->factory = $factory;
		$this->user = $user;
	}


	/** 
	* Create form
	* @param callable
	* @return Form 
	**/
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();
		$form->addText('email', 'Email:')
			->setRequired('Please enter your email.');

		$form->addPassword('password', 'Password:')
			->setRequired('Please enter your password.');

		$form->addCheckbox('remember', 'Keep me signed in');

		$form->addSubmit('send', 'Sign in');

		$form->onSuccess[] = function (Form $form, $values) use ($onSuccess) 
		{
			try 
			{
				$this->user->setExpiration($values->remember ? '14 days' : '20 minutes');
				$this->user->login($values->email, $values->password);
			} 
			catch (Nette\Security\AuthenticationException $e) 
			{
				$form->addError('The email or password you entered is incorrect.');
				return;
			}
			$onSuccess();
		};

		$form->addProtection('Request time out');

		return $form;
	}
}
