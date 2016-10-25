<?php
/**
 * Created by PhpStorm.
 * User: maik
 * Date: 21.10.16
 * Time: 22:08
 */

namespace models;

use controller\ApiController;

class Dice
{
	public $results = [];
	public $currentResultKey = 0;
	public $config;

	/**
	 * Dice constructor.
	 *
	 * @param null $config
	 */
	public function __construct($config = null)
	{
		$this->config = $config;
		$randomizer = new ApiController($config);
		$this->results = $randomizer->getResults();

		if (empty($this->results)){
			$i = 1;
			while ($i < $this->config['resultCount']){
				$this->results[] = rand(1, 6);
				$i++;
			}
		}
	}

	public function role()
	{
		if ($this->currentResultKey > ($this->config['resultCount'] - 1)) {
			die('out of results');
		}

		$result = $this->results[$this->currentResultKey];
		$this->currentResultKey++;



		return $result;
	}
}