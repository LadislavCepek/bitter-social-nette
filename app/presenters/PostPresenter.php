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
	private $content = '';
	private $image = '';
	private $article = '';

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

		$this->template->post = $this->post;
		$this->template->isOwner = $this->isOwner($this->post->user->id);

		$this->template->comments = $this->commentManager->getFromPost($postId);
	}

	public function renderEdit($postId)
	{
		if($postId)
		{
			$post = $this->postManager->get($postId);

			if(!$post)
				$this->redirect('Homepage:');
			
			$user = $post->user;

			if($this->user->id != $user->id)
				$this->redirect('User:profile ' . $user->username);
			
			$this->postId = $postId; 

			$this->content = $post->content;
			$this->image = $post->image;
			$this->article = $post->article;
		}
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

		$this->flashMessage('Post has been deleted');

		$this->redirect('User:profile');
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
		return $this->postFormFactory->create($this->postId, $this->content, $this->image, $this->article, function($postId)
		{
			$this->flashMessage('Post has been added');

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