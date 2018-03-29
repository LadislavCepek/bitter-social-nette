<?php

namespace App\Components;

use Nette;
use Nette\Security\User;
use Nette\Application\UI\Control;
use App\Forms\SearchFormFactory;

class HeaderControl extends Control
{
	/** @var User */
	private $user;

	/** @var SearchFormFactory */
	private $searchFormFactory;

	public function __construct(User $user, SearchFormFactory $searchFormFactory)
	{
		$this->user = $user;
		$this->searchFormFactory = $searchFormFactory;
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '\templates\header.latte');
		$this->template->user = $this->user;
		$this->template->render();
	}

	protected function createComponentSearchForm()
	{
		return $this->searchFormFactory->create(function($search)
		{
			$this->presenter->redirect('Search:list', $search);
		});
	}
}