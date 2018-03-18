?php

namespace App\Components;

use Nette;
use App\Model\PostManager;

class PostFactory
{
	use Nette\SmartObject;

	/** App\Model\PostManager */
	private $postManager;

	public function __construct(PostManager $postManager)
	{
		$this->postManager = $postManager;
	}

	/**
	* @return PostControl
	*/
	public function create()
	{
		return new PostControl($this->postManager);
	}
}