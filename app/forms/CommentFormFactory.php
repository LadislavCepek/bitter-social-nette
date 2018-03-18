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
	* @return form 
	**/
	public function create($postId, callable $onSuccess)
	{
		$form = $this->factory->create();

		$form->addTextArea('comment', '')
			->setRequired('Please enter text');

		$form->addSubmit('send', 'Add comment');

		$form->onSuccess[] = function(Form $form, $values) use ($postId, $onSuccess)
		{
			$this->commentManager->create($postId, $values->comment);
			$onSuccess();
		};

		return $form;
	}
}