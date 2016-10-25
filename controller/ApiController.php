<?php

namespace controller;

class ApiController
{
	public $postData = [
		'jsonrpc' => '2.0',
		'method' => 'generateIntegers',
		'params' =>
			[
				'apiKey' => '00000000-0000-0000-0000-000000000000',
				'n' => 100,
				'min' => 1,
				'max' => 6,
				'replacement' => true,
				'base' => 10,
			],
		'id' => 36471,
	];

	public function __construct($config = null)
	{
		$this->postData['params']['n'] = $config['resultCount'] ?? 100;
	}

	public function getResults()
	{
		return $this->getResultFromApi();
	}

	/**
	 * @return mixed
	 */
	protected function getResultFromApi()
	{
		$data_string = json_encode($this->postData);

		// Setup cURL
		$ch = curl_init('https://api.random.org/json-rpc/1/invoke');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			[
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string),
			]
		);

		$response = curl_exec($ch);

		if ($response === false) {
			die(curl_error($ch));
		}

		$responseData = json_decode($response, true);

		return $responseData['result']['random']['data'];
	}
}

