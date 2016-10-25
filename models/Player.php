<?php
/**
 * Created by PhpStorm.
 * User: maik
 * Date: 21.10.16
 * Time: 21:27
 */

namespace models;

class Player
{
	public $name;
	public $id;
	public $finished;

	public function __construct($position)
	{
		$this->name = 'Player ' . $position;
		$this->finished = false;
		$this->id = $position;
	}
}