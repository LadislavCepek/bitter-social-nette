<?php

namespace App\Service;

use Nette;
use Elasticsearch\ClientBuilder;

class SearchService
{
	use Nette\SmartObject;

	const
		ID            = 'id',
		USER_INDEX    = 'users',
		USER_FULLNAME = 'fullname',
		USER_USERNAME = 'username';
		
	/** @var ClientBuilder */
	protected $client;

	public function __construct()
	{
		$this->client = ClientBuilder::create()->build();
	}

	/**
	* Searches for user
	* @param string
	* @return object
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
			$result =
			[
				self::ID => $hit['_id'],
				self::USER_FULLNAME => $hit['_source'][self::USER_FULLNAME],
				self::USER_USERNAME => $hit['_source'][self::USER_USERNAME]
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
	* @return object
	*/
	public function indexUser($id, $firstname, $lastname, $username)
	{
		$array = [$firstname, $lastname];
		$fullname = join(' ', $array);

		$params = 
		[
			'index'  => self::USER_INDEX,
			'type'   => 'data',
			self::ID => $id,
			'body'   => 
			[
				self::USER_FULLNAME => $fullname,
				self::USER_USERNAME => $username
			]
		];

		return $this->client->index($params);
	}



}	