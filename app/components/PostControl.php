<?php

namespace App\Components;

use Nette;
use Nette\Application\UI\Control;
use App\Model;

class PostControl extends Control
{
	/** @var Model\PostManager */
	private $postManager;

	/** @var Model\LikeManager */
	private $likeManager;

	private $post;

	public function __construct($post, Model\PostManager $postManager, Model\LikeManager $likeManager)
	{
		$this->postManager = $postManager;
		$this->likeManager = $likeManager;
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
		if(!$this->post->meta->isLikedByUser)
		{
			$result = $this->likeManager->create($this->post->id);
			if($result)
			{
				$this->post->meta->isLikedByUser = true;
				$this->post->meta->likes++;
			}
		}
		else
		{
			$result = $this->likeManager->delete($this->post->id);
			if($result)
			{
				$this->post->meta->isLikedByUser = false;
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

		$this->presenter->flashMessage('Post has been deleted');

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