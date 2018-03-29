<?php

namespace App\Components;

use Nette;
use Nette\Security\User;
use App\Forms\SearchFormFactory;

class HeaderFactory
{
	use Nette\SmartObject;

	/**
	* Create header component
	* @param User
	* @return HeaderControl
	*/
	public function create(User $user, SearchFormFactory $searchFormFactory)
	{
		return new HeaderControl($user, $searchFormFactory);
	}
}