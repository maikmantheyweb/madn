<?php
/**
 * Created by PhpStorm.
 * User: maik
 * Date: 21.10.16
 * Time: 21:26
 */

namespace models;

class Token
{
	public $belongsTo;
	public $id;
	public $totalSteps = 0;

	public function __construct($config)
	{
		$this->belongsTo = $config['player'];
		$this->id = $this->belongsTo->id . '-' . $config['id'];
	}

	public function isFinal()
	{
		return ($this->totalSteps > Game::FIELD_STEPS);
	}

	public static function getPlayerIdByTokenId($tokenID) {
		return substr($tokenID, 0, 1);
	}
}