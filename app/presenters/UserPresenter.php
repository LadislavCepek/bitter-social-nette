<?php 

namespace App\Presenters;

use Nette\Application\UI\Multiplier;
use App;
use App\Model\UserManager;
use App\Model\PostManager;
use App\Model\FollowerManager;
use App\Forms\PostFormFactory;
use App\Components\PostControl;

class UserPresenter extends BasePresenter
{
	/**
	* @var UserManager */
	private $userManager;

	/**
	* @var PostManager */
	private $postManager;

	/**
	* @var FollowerManager */
	private $followerManager;

	/**
	* @var PostFormFactory */
	private $postFormFactory;

	private $profile;

	public function __construct(UserManager $userManager, PostManager $postManager, FollowerManager $followerManager, PostFormFactory $postFormFactory)
	{
		$this->userManager = $userManager;
		$this->postManager = $postManager;
		$this->followerManager = $followerManager;
		$this->postFormFactory = $postFormFactory;
	}

	public function renderProfile($username)
	{
		$this->profile = $this->userManager->get($username);

		$this->template->profile = $this->profile;
		$this->template->isOwner = $this->isOwner($this->profile->id);

		$this->template->posts = $this->postManager->getFromUser($this->profile->id);

		$this->template->followers = $this->followerManager->getUserFollowers($this->profile->id);
		\Tracy\Debugger::barDump($this->template->followers, 'followers');
		\Tracy\Debugger::barDump($this->followerManager->getUserFollowing($this->profile->id), 'following');
	}

	public function handleFollow()
	{
		$username = $this->getParameter('username');
		$id = $this->userManager->getID($username);

		$this->followerManager->create($id);

		if($this->isAjax())
		{
			$this->redrawControl('ajaxChange');
		}
	}

	/**
	* @return Multiplier
	*/
	protected function createComponentPost()
	{
		return new Multiplier(function($postId)
		{
			$post = $this->postManager->get($postId);
			return new PostControl($post, $this->postManager);
		});
	}
}