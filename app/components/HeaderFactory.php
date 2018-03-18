<?php

namespace App\Components;

use Nette;

class HeaderFactory
{
	use Nette\SmartObject;

	/**
	* @return HeaderControl
	*/
	public function create($user)
	{
		return new HeaderControl($user);
	}
}