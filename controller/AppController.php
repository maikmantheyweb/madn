<?php

namespace controller;

use models\Game;

class AppController
{
	public $config = [
		'playerCount' => 4,
		'resultCount' => 1300,
	];
	public function __construct($config = null)
	{
		if (!empty($config))
			$this->config = $config;
	}

	public function run()
	{
		$currentGame = new Game($this->config);
		$log = $currentGame->start();

		return $log;
	}
}