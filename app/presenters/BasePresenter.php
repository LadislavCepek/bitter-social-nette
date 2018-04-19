<?php

namespace App\Presenters;

use Nette;
use App;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var App\Components\HeaderFactory @inject */
	public $headerFactory;

	/** @var App\Forms\SearchFormFactory @inject */
	public $searchFormFactory;

	public function startup()
	{
		parent::startup();

		if(!$this->user->isLoggedIn() && $this->getName() != 'Sign')
			$this->redirect('Sign:in');
	}

	protected function isOwner($compare)
	{
		if(!$this->user->isLoggedIn())
			return false;

		return $this->user->id == $compare;
	}

	protected function createComponentHeader()
	{
		return $this->headerFactory->create($this->user, $this->searchFormFactory);
	}
}
