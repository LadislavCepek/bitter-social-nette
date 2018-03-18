<?php

namespace App\Presenters;

use Nette;
use App\Forms;
use App\Model;

class HomepagePresenter extends BasePresenter
{
	public function __construct()
	{
		
	}

	public function renderDefault()
	{
		if($this->user->isLoggedIn())
		{
		}
	}
}
