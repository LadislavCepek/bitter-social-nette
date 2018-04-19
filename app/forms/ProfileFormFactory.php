<?php

namespace App\Forms;

use Nette;
use Nette\Utils\Image;
use Nette\Application\UI\Form;

use App\Model;

class ProfileFormFactory
{
	use Nette\SmartObject;

	/** @var FormFactory */
	private $factory;

	/** @var Model\SignManager */
	private $signManager;

	public function __construct(FormFactory $factory, Model\SignManager $signManager)
	{
		$this->factory = $factory;
		$this->signManager = $signManager;
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
	public function create(string $id, string $firstname, string $lastname, string $gender, bool $hasPicture, callable $onSuccess)
	{
		$form = $this->factory->create();

		$form->addText('firstname', 'First name:')
			->setValue($firstname)
			->setRequired('Please enter your first name');

		$form->addText('lastname', 'Last name:')
			->setValue($lastname)
			->setRequired('Please enter your last name');

		$form->addSelect('gender', 'Gender:', 
		[
			'ma' => 'Male',
			'fe' => 'Female',
		]);

		$form->addUpload('file', 'File');

		\Tracy\Debugger::barDump($gender, 'default gender');
		$form['gender']->setDefaultValue($gender);

		$form->addSubmit('send', 'Save');

		$form->onSuccess[] = function (Form $form, $values) use ($id, $onSuccess)
		{	
			$this->signManager->update($id, $values->firstname, $values->lastname, $values->gender, false);
			
			if(filesize($values->file) > 0)
			{
				$image = Image::fromFile($values->file);

				$savePath = sprintf('profiles/%s/profile.png', $id);

				try
				{
					$image->save($savePath);
				}
				catch(Nette\InvalidArgumentException $ex)
				{
					Debugger::log($ex);
				}
				
			}

			$onSuccess();
		};

		$form->addProtection('Request time out');

		return $form;
	}
}
