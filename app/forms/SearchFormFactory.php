<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;

class SearchFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory **/
	private $factory;

	public function __construct(FormFactory $factory)
	{
		$this->factory = $factory;
	}

	/** 
	* Create form
	* @param callable
	* @return Form 
	**/
	public function create(callable $onSuccess)
	{
		$form = $this->factory->create();

		$form->addTextArea('search', '');

		$form->addSubmit('submit', 'Search');

		$form->onSuccess[] = function(Form $form, $values) use ($onSuccess)
		{
			$onSuccess($values->search);
		};

		$form->addProtection('Request time out');

		return $form;
	}
}