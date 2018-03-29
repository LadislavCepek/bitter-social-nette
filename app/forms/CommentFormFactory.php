<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\CommentManager;

class CommentFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory **/
	private $factory;

	/** @var App\Model\CommentManager */
	private $commentManager;

	public function __construct(FormFactory $factory, CommentManager $commentManager)
	{
		$this->factory = $factory;
		$this->commentManager = $commentManager;
	}

	/** 
	* Create form
	* @param callable
	* @return Form 
	**/
	public function create($postId, callable $onSuccess)
	{
		$form = $this->factory->create();

		$form->addTextArea('comment', '')
			->setRequired('Please write a comment');

		$form->addSubmit('submit', 'Add comment');

		$form->onSuccess[] = function(Form $form, $values) use ($postId, $onSuccess)
		{
			$this->commentManager->create($postId, $values->comment);
			$onSuccess();
		};

		$form->addProtection('Request time out');

		return $form;
	}
}