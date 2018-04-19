<?php

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;

class PasswordFormFactory
{
	use Nette\SmartObject;

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
	* @param string
	* @param string
	* @param string
	* @param callable
	* @return Form 
	**/
	public function create(string $password, callable $onSuccess)
	{
		$form = $this->factory->create();

		$form->addPassword('password', 'Password:')
			->setOption('description', sprintf('at least %d characters', self::PASSWORD_MIN_LENGTH))
			->setRequired('Please create a password')
			->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

		$form->addSubmit('send', 'Save');

		$form->onSuccess[] = function (Form $form, $values) use ($id, $onSuccess)
		{	
			$this->signManager->updatePassword($values->password);
			
			$onSuccess();
		};

		$form->addProtection('Request time out');

		return $form;
	}
}
