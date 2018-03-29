<?php 

namespace App\Presenters;

use Nette\Application\UI\Multiplier;
use Nette\Utils\Html;

use App;
use App\Model\CommentManager;
use App\Model\PostManager;
use App\Forms\PostFormFactory;
use App\Forms\CommentFormFactory;
use App\Components\PostControl;
use App\Components\CommentControl;

class PostPresenter extends BasePresenter
{
	/** @var CommentManager */
	private $commentManager;

	/** @var PostManager */
	private $postManager;

	/** @var PostFormFactory */
	private $postFormFactory;

	/** @var CommentFormFactory */
	private $commentFormFactory;

  private $postId = null;
	private $title = '';
	private $headline = '';
	private $body = '';

	private $post;

	public function __construct(CommentManager $commentManager, PostManager $postManager,
															PostFormFactory $postFormFactory, CommentFormFactory $commentFormFactory)
	{
		$this->commentManager = $commentManager;
		$this->postManager = $postManager;
		$this->postFormFactory = $postFormFactory;
		$this->commentFormFactory = $commentFormFactory;
	}

	public function renderDetail($postId)
	{
		$this->post = $this->postManager->get($postId);

		$html = Html::el('div');
		$html->setHtml($this->post->body);

		$this->template->post = $this->post;
		$this->template->html = $html;
		$this->template->isOwner = $this->isOwner($this->post->user->id);

		$this->template->comments = $this->commentManager->getFromPost($postId);
	}

	public function renderEdit($postId)
	{
		if(!$this->user->isLoggedIn())
		{
			$this->redirect('Sign:in');
			return;
		}

		if($postId)
		{
			$post = $this->postManager->get($postId);

			if(!$post)
				$this->redirect('Homepage:');
			
			$user = $post->user;

			if($this->user->id != $user->id)
				$this->redirect('User:profile ' . $user->username);
			
			$this->postId = $postId; 

			$this->title = $post->title;
			$this->headline = $post->headline;
			$this->body = $post->body;
		}
	}


	public function handleLike()
	{

	}

	protected function createComponentComment()
	{
		return new Multiplier(function($commentId)
		{
			$comment = $this->commentManager->get($commentId);
			return new CommentControl($comment, $this->commentManager);
		});
	}

	protected function createComponentPostForm()
	{
		return $this->postFormFactory->create($this->postId, $this->title, $this->headline, $this->body, function($postId)
		{
			$this->redirect('Post:detail', array('postId' => $postId));
		});
	}

	protected function createComponentCommentForm()
	{
		$id = $this->getParameter('postId');

		return $this->commentFormFactory->create($id, function()
		{
			$this->redirect('this');
		});
	}
}