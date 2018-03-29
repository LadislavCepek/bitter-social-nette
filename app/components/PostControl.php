<?php

namespace App\Components;

use Nette;
use Nette\Application\UI\Control;
use App\Model\PostManager;

class PostControl extends Control
{
	/** 
	* @var PostManager */
	private $postManager;

	private $post;

	public function __construct($post, PostManager $postManager)
	{
		$this->postManager = $postManager;
		$this->post = $post;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '\templates\post.latte');

		$this->template->post = $this->post;
		$this->template->isOwner = $this->isOwner();

		$this->template->render();
	}

	public function handleLike()
	{
		if(!$this->post->meta->liked)
		{
			$result = $this->postManager->like($this->post->id);
			if($result)
			{
				$this->post->meta->liked = true;
				$this->post->meta->likes++;
			}
		}
		else
		{
			$result = $this->postManager->dislike($this->post->id);
			if($result)
			{
				$this->post->meta->liked = false;
				$this->post->meta->likes--;
			}
		}

		if($this->presenter->isAjax())
		{
			$this->redrawControl();
		}
		else
		{
			$this->redirect('this');
		}
	}

	public function handleDelete()
	{
		$this->postManager->delete($this->post->id);

		$this->redirect('this');
	}

	private function isOwner()
	{
		$user = $this->presenter->user;
		if(!$user->isLoggedIn())
			return false;

		return $user->id == $this->post->user->id;
	} 
}