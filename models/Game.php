<?php
namespace models;

class Game
{
	const FIELD_STEPS = 40;
	public $currentPlayer;
	public $currentResult;
	public $currentRound;
	public $positions = [];
	public $player = [];
	public $tokens = [];
	public $finishedTokens = [];
	public $log = [];
	public $dice;
	public $gameFinished = false;

	/**
	 * Game constructor.
	 *
	 * @param null $config
	 */
	public function __construct($config = null)
	{
		$positionOnBoard = 1;
		$playerCount = $config['playerCount'] ?? 4;

		while ($positionOnBoard <= $playerCount) {
			$player = new Player($positionOnBoard);
			$this->player[] = $player;
			$this->setTokens($player);
			$positionOnBoard++;
		}

		$this->dice = new Dice($config);
	}

	/**
	 * @return array
	 */
	public function start()
	{
		$this->currentRound = 1;
		while (!$this->gameFinished) {
			$this->addToLog("Round $this->currentRound:");
			$this->playRound();
			$this->currentRound++;
		}

		$this->addToLog("End of game.");

		return $this->log;
	}

	protected function playRound()
	{
		foreach ($this->player as $player) {
			$this->currentPlayer = $player;
			do {
				$this->currentResult = $this->dice->role();
				$this->addToLog("$player->name roles a $this->currentResult.");
				$this->moveToken();
			} while ($this->currentResult == 6);
		}

		if (count($this->finishedTokens) == (count($this->player) * 4)) {
			$this->gameFinished = true;
		}
	}

	protected function setTokens(Player $player)
	{
		$i = 1;
		while ($i <= 4) {
			$this->tokens[] = new Token(['player' => $player, 'id' => $i]);
			$i++;
		}
	}

	protected function moveToken()
	{
		$token = $this->choseToken();

		if ($token) {
			$tokenId = substr($token->id, 2);
			if (empty($this->positions[$token->id]) && !in_array($this->getStartPositionOfPlayer(), $this->positions)) {
				$this->positions[$token->id] = $this->getStartPositionOfPlayer();
				$this->addToLog($this->currentPlayer->name . " sets token $tokenId on start field.");
			} else {
				$this->calculateAndSetPosition($token);
				$this->addToLog(
					$this->currentPlayer->name . " moves token $tokenId to position " . $this->positions[$token->id]
				);
			}
		} else {
			$this->addToLog($this->currentPlayer->name . " can't move a token.");
		}
	}

	/**
	 * @return Token|null
	 */
	protected function choseToken()
	{
		$selectedToken = null;

		foreach ($this->tokens as $token) {
			if (!isset($this->positions[$token->id])) {
				$token->totalSteps = 0;
			}

			if ($token->belongsTo == $this->currentPlayer && !in_array($token->id, $this->finishedTokens)) {

				$newTokenCanBePlaced = (!isset($this->positions[$token->id]) && $this->currentResult == 6 && !in_array(
						$this->getStartPositionOfPlayer(),
						$this->positions
					)
				);
				$tokenCanBeMoved = (isset($this->positions[$token->id]) && $this->possiblePositionIsFreeFor(
						$token
					));

				if ($newTokenCanBePlaced) {
					return $token;
				} else {
					if ($this->enemyTokenCouldBeBeatenBy($token)) {
						return $token;
					}

					if ($tokenCanBeMoved) {
						if (isset($selectedToken)) {
							$selectedToken = ($this->positions[$selectedToken->id] < $this->positions[$token->id]) ? $selectedToken : $token;
						} else {
							$selectedToken = $token;
						}
					}
				}
			}
		}

		return $selectedToken;
	}

	/**
	 * @return int
	 */
	protected function getStartPositionOfPlayer()
	{
		return $this->currentPlayer->id * 10 - 9;
	}

	/**
	 * @return int
	 */
	protected function getEndPositionOfPlayer()
	{
		return $this->calculatePosition($this->getStartPositionOfPlayer(), self::FIELD_STEPS - 1);
	}

	/**
	 * @param Token $token
	 *
	 * @return bool
	 */
	protected function possiblePositionIsFreeFor(Token $token)
	{
		$possiblePosition = $this->getPossiblePositionFor($token);
		$otherToken = array_flip($this->positions)[$possiblePosition];

		if (isset($otherToken) && in_array($otherToken, $this->finishedTokens)) {
			return true;
		}

		return !isset($otherToken);
	}

	/**
	 * @param Token $token
	 *
	 * @return bool
	 */
	protected function enemyTokenCouldBeBeatenBy(Token $token)
	{
		$possiblePosition = $this->getPossiblePositionFor($token);

		$flippedArr = array_flip($this->positions);

		$enemyTokenID = $flippedArr[$possiblePosition];
		$notOwnToken = Token::getPlayerIdByTokenId($enemyTokenID) != $this->currentPlayer->id;

		return (isset($enemyTokenID) && $notOwnToken && !in_array($enemyTokenID, $this->finishedTokens));
	}

	/**
	 * @param Token $token
	 */
	protected function calculateAndSetPosition(Token $token)
	{
		$possiblePosition = $this->getPossiblePositionFor($token);
		$enemyTokenID = array_flip($this->positions)[$possiblePosition];
		$enemyPlayerId = Token::getPlayerIdByTokenId($enemyTokenID);

		if (isset($enemyTokenID) && ($enemyPlayerId != $this->currentPlayer->id)) {

			unset($this->positions[$enemyTokenID]);

			$this->addToLog($this->currentPlayer->name . " beats token of player $enemyPlayerId.");
		}

		$this->positions[$token->id] = $this->calculatePosition($this->positions[$token->id], $this->currentResult);
		$token->totalSteps += $this->currentResult;

		if ($token->totalSteps > self::FIELD_STEPS && !in_array($token->id, $this->finishedTokens)) {
			$this->finishedTokens[] = $token->id;
		}
	}

	/**
	 * @param $start
	 * @param $steps
	 *
	 * @return mixed
	 */
	protected function calculatePosition($start, $steps)
	{
		$position = $start + $steps;
		if ($position > self::FIELD_STEPS) {
			$position = $position - self::FIELD_STEPS;
		}

		return $position;
	}

	/**
	 * @param Token $token
	 *
	 * @return mixed|null
	 */
	protected function getPossiblePositionFor(Token $token)
	{
		$possiblePosition = null;
		if (isset($this->positions[$token->id])) {
			$possiblePosition = $this->calculatePosition($this->positions[$token->id], $this->currentResult);
		}

		return $possiblePosition;
	}

	protected function addToLog($message)
	{
		$this->log[$this->currentRound][] = $message;
	}
}