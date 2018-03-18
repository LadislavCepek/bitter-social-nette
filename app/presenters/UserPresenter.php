<?php 

namespace App\Presenters;

use Nette\Application\UI\Multiplier;
use App;
use App\Model\UserManager;
use App\Model\PostManager;
use App\Forms\PostFormFactory;
use App\Components\PostControl;

class UserPresenter extends BasePresenter
{
	/** @var App\Model\UserManager */
	private $userManager;

	/** @var App\Model\PostManager */
	private $postManager;

	/** @var App\Forms\PostFormFactory */
	private $postFormFactory;

	private $profile;

	public function __construct(UserManager $userManager, PostManager $postManager, PostFormFactory $postFormFactory)
	{
		$this->userManager = $userManager;
		$this->postManager = $postManager;
		$this->postFormFactory = $postFormFactory;
	}

	public function renderProfile($username)
	{
		$this->profile = $this->userManager->get($username);

		$this->template->profile = $this->profile;
		$this->template->isOwner = $this->isOwner($this->profile->id);

		$this->template->posts = $this->postManager->getFromUser($this->profile->id);
	}

	public function handleFollow()
	{

		if($this->isAjax())
		{
			$this->redrawControl('ajaxChange');
		}
	}

	protected function createComponentPost()
	{
		return new Multiplier(function($postId)
		{
			$post = $this->postManager->get($postId);
			return new PostControl($post, $this->postManager);
		});
	}
}