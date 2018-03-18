<?php

namespace App\Model;

use App\Data\PhotoData;

use Crew\Unsplash\HttpClient;
use Crew\Unsplash\Photo;

class UnsplashManager
{
	public function __construct()
	{
		HttpClient::init([
			'applicationId' => '8b345793d4c529e487de6b37c5114dc50038dc2e9c3116576d63c916a9a74afb',
			'utmSource' => 'GrizzlyGallery'
		]);
	}

	public function getPhotos()
	{
		$response = Photo::all();
		//var_dump($response);

		$photos = array();

		foreach($response as $item)
		{
			//var_dump($item);
			array_push($photos, new PhotoData($item));
		}

		return $photos;
	}

	public function getPhoto($id)
	{
		$response = Photo::find($id);
		//var_dump($response);
		$photo = new PhotoData($response);
		return $photo;
	}
}