<?php
include 'Player.php';

class Board
{
    public $player;
    public $maxWeight;
    public $boardState;
    private $boardLength;
    public $currentState;
    public $currentTurn;
    private $boardWeight;
    private $currentPut;
    public $gameOver;
    public $boardColor;
    public $leftTorque;
    public $rightTorque;

    /**
     * This function is used to check the balance, and check whether it is over or not
     * 
     * @return bool true means game is over, false means not over
     */
    public function isGameOver()
    {
        $this->calculateTorque();
        return $this->leftTorque < 0 || $this->rightTorque > 0;
    }

    function calculateTorque()
    {
        $leftTorque = 0;
        $rightTorque = 0;
        for ($i = -$this->boardLength; $i <= $this->boardLength; $i++) {
            $leftTorque += ($i + 3) * $this->boardState[$i];
            $rightTorque += ($i + 1) * $this->boardState[$i];
        }
        // add information about the board weight, now let's do 3
        $leftTorque += 3 * $this->boardWeight;
        $rightTorque += 1 * $this->boardWeight;
        $this->leftTorque = $leftTorque;
        $this->rightTorque = $rightTorque;
    }


    /**
     * 
     * @return string the information for players
     */
    function generateSendingString()
    {
        return json_encode($this->generateSendingJSON());
    }

    /**
     * 
     * @return the information for players
     */
    function generateSendingJSON()
    {
        $game_state = ['move_type' => $this->currentState, 'game_over' => ($this->gameOver ? "1" : "0")];

        $s = "";
        for ($i = -$this->boardLength; $i <= $this->boardLength; $i++) {
            $s = $s . " " . $this->boardState[$i];
        }
        $game_state['board_state'] = $s;

        // if game over, send the winner
        if ($this->gameOver) {
            $game_state['winner'] = $this->player[3 - $this->currentTurn]->name;
        }

        return $game_state;
    }

    /**
     * Print out the information about which player wins the game
     * 
     * @param $gameOverReason the reason to print out to screen
     */
    public function setGameOver($gameOverReason)
    {
        $this->gameOver = true;
        echo "\n[GAME OVER] $gameOverReason.\n";
        echo "            Winner is [" . $this->player[3 - $this->currentTurn]->name . "]\n";
    }


    /**
     * The move function puts weight on position, and using isGameOver to check the balance
     * 
     * @param $weight the weight that currentUser want to move
     * @param $position the position that the currentUser want to place
     */
    public function move($weight, $position)
    {
        if ($position < -$this->boardLength || $position > $this->boardLength || $this->boardState[$position] != 0) {
            $this->setGameOver("Wrong position from " . $this->player[$this->currentTurn]->name);
        } else if (!$this->player[$this->currentTurn]->WeightState[$weight]) {
            $this->setGameOver("Wrong weight from " .  $this->player[$this->currentTurn]->name);
        } else {
            $this->boardState[$position] = $weight;
            $this->boardColor[$position] = $this->currentTurn;
            $this->player[$this->currentTurn]->WeightState[$weight] = false;

            if ($this->isGameOver()) {
                $this->setGameOver("Tipping by " . $this->player[$this->currentTurn]->name);
                echo "[GAME] " . "Left Torque: [" . $this->leftTorque . "]  Right Torque: [" . $this->rightTorque . "]\n";
            } else {
                echo "[PLAYER] " . $this->player[$this->currentTurn]->name . " put weight $weight on position $position; still balanced\n";
                echo "[GAME] " . "Left Torque: [" . $this->leftTorque . "]  Right Torque: [" . $this->rightTorque . "]\n";
                $this->currentPut++;
                if ($this->currentPut == $this->boardLength * 2 + 1 || $this->currentPut == 2 * $this->maxWeight) {
                    $this->currentState = "remove";
                    echo "[GAME] Stage Change: remove\n";
                }

                $this->currentTurn = 3 - $this->currentTurn;
            }
        }
    }


    /**
     * Remove funciton remove the weight from position, check if there is no weight.
     * 
     * @param $position the position that player want to remove weight.
     */
    public function remove($position)
    {
        if ($position < -$this->boardLength || $position > $this->boardLength || $this->boardState[$position] == 0) {
            $this->setGameOver("Wrong position from " . $this->player[$this->currentTurn]->name . "\n");
        } else {
            $weight = $this->boardState[$position];
            $this->boardState[$position] = 0;
            if ($this->isGameOver()) {
                $this->setGameOver("Tipping by " . $this->player[$this->currentTurn]->name . "\n");
                echo "[GAME] " . "Left Torque: [" . $this->leftTorque . "]  Right Torque: [" . $this->rightTorque . "]\n";
            } else {
                echo "[GAME] " . $this->player[$this->currentTurn]->name . " removes weight $weight on position $position; still balanced\n";
                echo "[GAME] " . "Left Torque: [" . $this->leftTorque . "]  Right Torque: [" . $this->rightTorque . "]\n";
                $this->currentTurn = 3 - $this->currentTurn;
            }
        }
    }

    /**
     * Give player turn and the time he consumed, update the time of him.
     * 
     * @param $turn
     * @param $time
     */
    public function updateTime($turn, $time)
    {
        $this->player[$turn]->timeLeft -= $time;

        if ($this->player[$turn]->timeLeft <= 0) {
            $this->setGameOver("[PLAYER] " . $this->player[$turn]->timeLeft . " has ran out of time");
        } else {
            echo "[PLAYER] " . $this->player[$turn]->name . " has " . $this->player[$turn]->timeLeft . " seconds left\n";
        }
    }

    function __construct($boardLength, $numberOfWeight, $boardWeight, $player1, $player2)
    {
        if ($boardLength <= 3 || $numberOfWeight <= 0) {
            throw new Exception("[GAME] Not proper initialization parameter $boardLength $numberOfWeight");
        }

        $this->gameOver = false;
        $this->currentPut = 0;
        $this->currentState = "place";
        $this->currentTurn = 1;
        $this->boardWeight = $boardWeight;
        $this->maxWeight = $numberOfWeight;
        $this->boardLength = $boardLength;

        // initialize the board from -boardLength to boardLength
        for ($i = -$this->boardLength; $i <= $this->boardLength; $i++) {
            $this->boardState[$i] = 0;
            $this->boardColor[$i] = 0;
        }

        $this->player[1] = new Player($player1, $numberOfWeight);
        $this->player[2] = new Player($player2, $numberOfWeight);

        // place initial block at -4
        $this->boardState[-4] = 3;

        // calculate the initial torque
        $this->calculateTorque();
    }
}
