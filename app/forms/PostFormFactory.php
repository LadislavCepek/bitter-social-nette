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
	public function create($postId, $content, $image, $article, callable $onSuccess)
	{
		$form = $this->factory->create();

		$requiredMessage = 'Please write something';

		$form->addHidden('id', '')
			->setValue($postId);

		$form->addTextArea('content', '')
			->setRequired($requiredMessage)
			->setValue($content)
			->addRule(Form::MAX_LENGTH, 'Post is too long', 255);

		$form->addText('image', '')->setValue($image)
			->setRequired(false)
			->addRule(Form::MAX_LENGTH, 'Image link too long', 120);

		$form->addCheckbox('hidden', 'Make private?');

		$form->addHidden('article', '')->setValue($article)
			->setHtmlAttribute('id', 'article-editor-value');			

		$form->addSubmit('submit', 'Post')
			->setHtmlAttribute('id', 'editor-button');

		$form->onSuccess[] = function(Form $form, $values) use ($onSuccess)
		{
			if($values->id == null)
				$row = $this->postManager->create($values);
			else
				$row = $this->postManager->edit($values);

			$onSuccess($row->id);
		};

		$form->addProtection('Request time out');

		return $form;
	}
}