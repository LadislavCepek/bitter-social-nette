<?php

namespace App\Forms;

use Nette;
use Nette\Utils\Image;
use Nette\Application\UI\Form;
use Nette\Utils\FileSystem;
use Tracy\Debugger;
use App\Model;

class PictureFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\UserManager */
	private $userManager;

	public function __construct(FormFactory $factory, Model\UserManager $userManager)
	{
		$this->factory = $factory;
		$this->userManager = $userManager;
	}

	/** 
	* Create form
	* @param string
	* @param string
	* @param string
	* @param string
	* @param bool
	* @param callable
	* @return Form 
	**/
	public function create(string $id, string $hasPicture, callable $onSuccess, callable $onFailure)
	{
		$form = $this->factory->create();

		$form->addUpload('file', 'File');

		$form->addHidden('rectX', '');

		$form->addHidden('rectY', '');

		$form->addSubmit('send', 'Save');

		$form->onSuccess[] = function (Form $form, $values) use ($id, $hasPicture, $onSuccess, $onFailure)
		{				
			if(filesize($values->file) > 0)
			{
				$image = Image::fromFile($values->file);

				$imagePath = sprintf('profiles/%s/profile.png', $id);
				$renamePath = sprintf('profiles/%s/profile-temp.png', $id);
				
				if(!$hasPicture)
				{
					try
					{
						FileSystem::rename($imagePath, $renamePath);
					}
					catch(Nette\IOException $ex)
					{
						Debugger::log($ex);
						$onFailure();

						return;
					}
				}

				try
				{
					$image->resize(650, null);

					$image->crop($values->rectX, $values->rectY, 300, 300);

					$image->save($imagePath);
				}
				catch(Nette\InvalidArgumentException $ex)
				{
					Debugger::log($ex);

					// revert changes
					if(!$hasPicture)
					{
						FileSystem::rename($renamePath, $imagePath);
					}

					$onFailure();

					return;
				}

				if(!$hasPicture)
				{
					FileSystem::delete($renamePath);
				}
				
				$this->userManager->updatePicture($id, true);

				$onSuccess();
			}
			else
			{
				Debugger::log(sprintf('Zero filesize for %s', $this->user->id));
				$onFailure();
			}
		};

		$form->addProtection('Request time out');

		return $form;
	}
}
