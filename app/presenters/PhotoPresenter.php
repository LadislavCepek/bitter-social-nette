<?php

namespace App\Presenters;

use App\Forms\CommentFormFactory;
use App\Model;

class PhotoPresenter extends BasePresenter
{
	/* @var App\Model\UnsplashManager */
	private $unsplash;

	/** @var App\Model\PhotoManager */
	private $photoManager;

	/** @var App\Model\CommentManager */
	private $commentManager;

	/** @var App\Forms\CommentFormFactory */
	private $factory;

	private $photo = null;

	public function __construct()
	{

	}

	/* Show grid of photos */
	public function renderGrid()
	{
		$photos = $this->unsplash->getPhotos();

		$this->template->photos = $photos;
	}

	/* Show detail of one photo */
	public function renderDetail($id)
	{
		$this->template->user = $this->user;
		$this->template->comments = $this->commentManager->get($id);

		if($this->photo == null)
		{
			$this->photo = $this->unsplash->getPhoto($id);
		}

		//$photo = $this->formatMetaValues($photo);
		$this->template->photo = $this->photo;
		$this->template->liked = $this->photoManager->isLikedByUser($id, $this->user->id);
	}

	public function createComponentCommentForm()
	{		
		$id = $this->getParameter('id');

		return $this->factory->create($id, function()
		{
			$this->redirect('this');
		});
	}

	public function handleLike()
	{	
		$photoId = $this->getParameter('id');
		$userId = $this->user->id;
		$liked = $this->photoManager->isLikedByUser($photoId, $userId);

		if(!$liked)
			$this->photoManager->like($photoId, $userId);
		else
			$this->photoManager->dislike($photoId, $userId);
	}

	private function formatMetaValues($photo)
	{
		$values = $photo->meta;
		$photo->meta->likes = number_format($values->likes);

		if(isset($photo->meta->views))
			$photo->meta->views = number_format($values->views);

		if(isset($photo->meta->downloads))
			$photo->meta->downloads = number_format($values->downloads);

		return $photo;
	}
}