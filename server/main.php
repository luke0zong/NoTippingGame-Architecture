<?php
// ini_set('display_errors', 'On');
// ini_set('display_startup_errors', true);
// error_reporting(E_ALL);

include "GameController.php";
include "Board.php";
include "Draw.php";

// check command line arguments
if ($argc < 3 || !is_numeric($argv[1]) || !is_numeric($argv[2])) {
    echo ("[ERROR] Please provide port number and number of weights\n");
    echo ("[ERROR] Example command: php main.php 5000 15\n");
    exit(-1);
}

// start server
echo ("[SERVER] Starting server at localhost:$argv[1] with $argv[2] blocks each\n");

$numOfWeights = (int)$argv[2];
$board_length = 30;
$slow = false;
$observed = false;

foreach ($argv as $arg) {
    if ($arg == "-w") {
        $slow = true;
        echo "[SERVER] Slowing down game...\n";
    }
    if ($arg == "-o") {
        $observed = true;
        echo "[SERVER] Observing game...\n";
    }

}
$myController = new GameController("localhost", $argv[1]);
$myController->createConnection($numOfWeights, $board_length, $observed);
$myGame = new Board($board_length, $numOfWeights, 3, $myController->player1, $myController->player2);
while (!$myGame->gameOver) {
    echo "----------------------------------------------------------------\n";

    $sendingString = $myGame->generateSendingJSON();

    echo "[GAME] Stage: " . $sendingString['move_type'] . "\n";

    $board_indices = "";
    $board_state_output = "";
    // $first_pos = "";
    for ($i = -$board_length; $i <= $board_length; $i++) {
        $next_pos = " " . $i;
        $next_value = " " . (string) $myGame->boardState[$i];
        while (strlen($next_value) < strlen($next_pos)) {
            $next_value = " " . $next_value;
        }

        while (strlen($next_pos) < strlen($next_value)) {
            $next_pos = " " . $next_pos;
        }

        $board_indices = $board_indices . $next_pos;
        $board_state_output = $board_state_output . $next_value;
    }
    echo "[GAME] Board Status: \n";
    echo $board_indices . "\n";
    echo $board_state_output . "\n\n";
    echo "[GAME] Torque over left post at -3: [" . $myGame->leftTorque . "]\n";
    echo "       Torque over right post at -1: [" . $myGame->rightTorque . "]\n\n";
    draw($myGame, false);


    // send game state to current player
    $myController->send($myGame->currentTurn, $myGame->generateSendingString());
    $time1 = microtime(true);
    // receive move from current player
    $move = $myController->recvMove($myGame->currentTurn);
    $time2 = microtime(true);
    // update current's time
    $myGame->updateTime($myGame->currentTurn, $time2 - $time1);

    if ($myGame->gameOver) {
        break;
    }

    if ($myGame->currentState == "place") {
        echo "[PLAYER] Placing weight " . $move->weight . " at position " . $move->position . "\n";
        $myGame->move((int)$move->weight, (int)$move->position);
    } else {
        echo "[PLAYER] Removing weight from position " . $move->position . "\n";
        $myGame->remove((int)$move->position);
    }

    if ($slow) {
        sleep(1); // sleep for a second
    }
}

// Game over, send final message to players
$myController->send(1, $myGame->generateSendingString());
$myController->send(2, $myGame->generateSendingString());
draw($myGame, true);
$myController->closeConnection();
