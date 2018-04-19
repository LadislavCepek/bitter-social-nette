<?php

namespace App\Service;

use Nette;
use Elasticsearch\ClientBuilder;

class SearchService
{
	use Nette\SmartObject;

	const
		ID            	 = 'id',
		USER_INDEX    	 = 'users',
		USER_FULLNAME 	 = 'fullname',
		USER_USERNAME 	 = 'username',
		USER_GENDER 		 = 'gender',
		USER_HAS_PICTURE = 'hasPicture';
		
	/** @var ClientBuilder */
	protected $client;

	public function __construct()
	{
		$this->client = ClientBuilder::create()->build();
	}

	/**
	* Searches for user
	* @param string
	* @return array
	*/
	public function search($search)
	{
		$params = 
		[
			'index' => self::USER_INDEX,
			'type'  => 'data',
			'body'  => 
			[
				'query' =>
				[
					'bool' =>
					[
						'should' =>
						[
							'match' =>
							[
								self::USER_FULLNAME => $search
							],
							'match' =>
							[
								self::USER_USERNAME => $search
							]
						]
					]
				]
			]
		];
		
		$hits = $this->client->search($params)['hits']['hits'];

		$results = array();

		foreach ($hits as $hit) 
		{
			$id = $hit['_id'];
			$fullname = $hit['_source'][self::USER_FULLNAME];
			$username = $hit['_source'][self::USER_USERNAME];

			$hasPicture = $hit['_source'][self::USER_HAS_PICTURE];

			$picture = "";
			
			if($hasPicture == true)
			{
				$picture = sprintf('%s/profile.png', $id);
			}
			else
			{
				$gender = $hit['_source'][self::USER_GENDER];

				$picture = sprintf('default/%s.png', $gender);
			}

			$result =
			[
				self::ID => $id,
				self::USER_FULLNAME => $fullname,
				self::USER_USERNAME => $username,
				'picture' => $picture
			];

			array_push($results, (object) $result);
		}

		return $results;
	}

	/**
	*	Index user to search engine
	* @param string
	* @param string
	* @param string
	* @param string
	* @param string
	* @return array
	*/
	public function indexUser(string $id, string $firstname, string $lastname, string $username, string $gender)
	{
		$array = [$firstname, $lastname];
		$fullname = join(' ', $array);

		$params = 
		[
			'index'  => self::USER_INDEX,
			'type'   => 'data',
			'id' => $id,
			'body'   => 
			[
				self::USER_FULLNAME => $fullname,
				self::USER_USERNAME => $username,
				self::USER_GENDER => $gender,
				self::USER_HAS_PICTURE => false
			]
		];

		return $this->client->index($params);
	}

	/**
	*	Update user in search engine
	* @param string
	* @param string
	* @param string
	* @param string
	* @param string
	* @return array
	*/
	public function updateUser(string $id, string $firstname, string $lastname, string $gender)
	{
		$array = [$firstname, $lastname];
		$fullname = join(' ', $array);

		$params = 
		[
			'index'  => self::USER_INDEX,
			'type'   => 'data',
			'id' => $id,
			'body'   => 
			[
				'doc' =>
				[
					self::USER_FULLNAME => $fullname,
					self::USER_GENDER => $gender,
				]
			]

		];

		return $this->client->update($params);
	}

	/**
	*	Update user picture in search engine
	* @param string
	* @param string
	* @return array
	*/
	public function updateUserPicture(string $id, string $hasPicture)
	{
		\Tracy\Debugger::barDump($hasPicture, 'hase');
		$params = 
		[
			'index'  => self::USER_INDEX,
			'type'   => 'data',
			'id' => $id,
			'body'   => 
			[
				'doc' =>
				[
					self::USER_HAS_PICTURE => $hasPicture == 0 ? false : true, 
				]
			]

		];

		return $this->client->update($params);
	}

	/**
	* @param string 
	* @return object
	*/
	public function getUser(string $id)
	{
		$params =
		[
			'index' => self::USER_INDEX,
			'type' => 'data',
			'id' => $id
		];

		$user = $this->client->get($params);
		$source = $user['_source'];

		$result =
		[
			self::ID => $user['_id'],
			self::USER_FULLNAME => $source[self::USER_FULLNAME],
			self::USER_USERNAME => $source[self::USER_USERNAME],
			self::USER_GENDER => $source[self::USER_GENDER],
			'hasPicture' => $source[self::USER_HAS_PICTURE]
		];

		return (object) $result;
	}
}	