<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Multiplier;
use App\Model;
use App\Components\PostControl;

class HomepagePresenter extends BasePresenter
{
	/** @var Model\PostManager */
	private $postManager;

	/** @var Model\LikeManager */
	private $likeManager;

	/** @var array */
	private $posts;

	public function __construct(Model\PostManager $postManager, Model\LikeManager $likeManager)
	{
		$this->postManager = $postManager;
		$this->likeManager = $likeManager;
		$this->posts = array();
	}

	public function renderDefault()
	{
		if($this->user->isLoggedIn())
		{
			$this->posts = $this->postManager->getFeed($this->user->id);
		}

		$this->template->posts = $this->posts;

	}

	/**
	* @return Multiplier
	*/
	protected function createComponentPost()
	{
		return new Multiplier(function($postID)
		{
			$post = $this->postManager->get($postID);
			return new PostControl($post, $this->postManager, $this->likeManager);
		});
	}
}
