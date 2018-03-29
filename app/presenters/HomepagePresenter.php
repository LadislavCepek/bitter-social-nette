<?php

namespace App\Presenters;

use Nette;
use App\Model\UserManager;

class HomepagePresenter extends BasePresenter
{
	private $userManager;

	public function __construct(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}

	public function renderDefault()
	{
	}
}
