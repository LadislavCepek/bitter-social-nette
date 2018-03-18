<?php

namespace App\Components;

use Nette;
use Nette\Security\User;
use Nette\Application\UI\Control;

class HeaderControl extends Control
{
	/** @var Nette\Security\User */
	private $user;

	public function __construct(User $user)
	{
		$this->user = $user; 
	}

	public function render()
	{
		$this->template->setFile(__DIR__ . '\templates\header.latte');
		$this->template->user = $this->user;
		$this->template->render();
	}
}