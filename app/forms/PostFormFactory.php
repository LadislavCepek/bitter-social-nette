<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\PostManager;

class PostFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var App\Model\PostManager */
	private $postManager;

	public function __construct(FormFactory $factory, PostManager $postManager)
	{
		$this->factory = $factory;
		$this->postManager = $postManager;
	}

	/** 
	* Create form
	* @param callable
	* @return Form 
	**/
	public function create($postId, $title, $headline, $body, callable $onSuccess)
	{
		$form = $this->factory->create();

		$requiredMessage = 'Please write something';

		$form->addHidden('id', '')
			->setValue($postId);

		$form->addTextArea('title', 'Title:')
			->setRequired($requiredMessage)
			->setValue($title);

		$form->addTextArea('headline', 'Headline:')
			->setRequired($requiredMessage)
			->setValue($headline);

		$form->addHidden('body', '')
			->setHtmlAttribute('id', 'editor-body')
			->setRequired($requiredMessage)
			->setValue($body);

		$form->addSubmit('submit', 'Post')
			->setHtmlAttribute('id', 'editor-button');

		$form->onSuccess[] = function(Form $form, $post) use ($onSuccess)
		{
			if($post->id == null)
				$row = $this->postManager->create($post);
			else
				$row = $this->postManager->edit($post);

			$onSuccess($row->id);
		};

		$form->addProtection('Request time out');

		return $form;
	}
}