<?php

namespace App\Presenters;

use Nette;
use App\Service\SearchService;

class SearchPresenter extends BasePresenter
{
	private $searchService;

	public function __construct(SearchService $searchService)
	{
		$this->searchService = $searchService;
	}

	public function renderList($search)
	{
		$this->template->users = $this->searchService->search($search);
	}
}
