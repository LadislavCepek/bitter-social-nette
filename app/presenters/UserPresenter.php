<?php 

namespace App\Presenters;

use Nette\Application\UI\Multiplier;
use App;
use App\Model;
use App\Forms;
use App\Components\PostControl;

class UserPresenter extends BasePresenter
{
	/** @var Model\UserManager */
	private $userManager;

	/** @var Model\PostManager */
	private $postManager;

	/** @var Model\LikeManager */
	private $likeManager;

	/** @var Model\FollowerManager */
	private $followerManager;

	/** @var Forms\ProfileFormFactory */
	private $profileFormFactory;

	/** @var Forms\PictureFormFactory */
	private $pictureFormFactory;

	private $profile;

	public function __construct(Model\UserManager $userManager, Model\PostManager $postManager, Model\LikeManager $likeManager, Model\FollowerManager $followerManager,
	 Forms\ProfileFormFactory $profileFormFactory, Forms\PictureFormFactory $pictureFormFactory)
	{
		$this->userManager = $userManager;
		$this->postManager = $postManager;
		$this->likeManager = $likeManager;
		$this->followerManager = $followerManager;
		$this->profileFormFactory = $profileFormFactory;
		$this->pictureFormFactory = $pictureFormFactory;
	}

	public function renderProfile(string $username = null)
	{
		if($username = null)
			$username = $this->user->getIdentity()->username;

		$this->profile = $this->userManager->getByUsername($username);

		$this->template->profile = $this->profile;
		$this->template->isOwner = $this->isOwner($this->profile->id);

		$this->template->posts = $this->postManager->getFromUser($this->profile->id);
	}

	public function renderEdit(string $username)
	{

		$this->profile = $this->userManager->getByUsername($username);

		$this->template->profile = $this->profile;
		$this->template->isOwner = $this->isOwner($this->profile->id);
	}

	public function renderEditPicture(string $username)
	{
		$this->profile = $this->userManager->getByUsername($username);

		$this->template->profile = $this->profile;
		$this->template->isOwner = $this->isOwner($this->profile->id);
	}

	public function handleFollow()
	{
		$username = $this->getParameter('username');
		$id = $this->userManager->getID($username);

		$this->followerManager->create($id);

		if($this->isAjax())
		{
			$this->redrawControl('follow');
			$this->redrawControl('meta');
		}
		else
		{
			$this->redirect('this');
		}
	}

	public function handleUnfollow()
	{
		$username = $this->getParameter('username');
		$id = $this->userManager->getID($username);

		$this->followerManager->delete($id);

		if($this->isAjax())
		{
			$this->redrawControl('follow');
			$this->redrawControl('meta');
		}
		else
		{
			$this->redirect('this');
		}
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

	protected function createComponentProfileForm()
	{
		$profile = $this->user->getIdentity();	

		$id = $this->user->id;
		$firstname  = $profile->firstname;
		$lastname 	= $profile->lastname;
		$gender 	  = $profile->gender;
		$hasPicture = $profile->hasPicture == 0 ? false : true;

		return $this->profileFormFactory->create($id, $firstname, $lastname, $gender, $hasPicture, 
		function()
		{
			$this->flashMessage('Update successful');
			$this->redirect('this');
		});
	}

	protected function createComponentPictureForm()
	{
		$id = $this->user->id;
		$hasPicture = $this->user->getIdentity()->hasPicture == 0 ? false : true;

		return $this->pictureFormFactory->create($id, $hasPicture,
		function()
		{
			$this->flashMessage('Update successful', 'success');
			$this->redirect('User:profile');
		}, 
		function()
		{
			$this->flashMessage('Update failed', 'danger');
			$this->redirect('this');
		});
	}
}