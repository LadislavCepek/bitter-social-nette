<?php

namespace App\Components;

use Nette;
use Nette\Application\UI\Control;
use App\Model\CommentManager;

class CommentControl extends Control
{
	/** CommentManager */
	private $commentManager;

	private $comment;

	public function __construct($comment, CommentManager $commentManager)
	{
		$this->commentManager = $commentManager;
		$this->comment = $comment;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '\templates\comment.latte');

		$this->template->comment = $this->comment;
		$this->template->isOwner = $this->isOwner();

		$this->template->render();
	}

	public function handleDelete()
	{
		$this->commentManager->delete($this->comment->id);

		$this->redirect('this');
	}

	private function isOwner()
	{
		$user = $this->presenter->user;
		if(!$user->isLoggedIn())
			return false;

		return $user->id == $this->comment->user->id;
	} 
}