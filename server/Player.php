<?php
class Player {
    public $WeightState;
    public $timeLeft;
    public $name;

    function __construct($name, $numberOfWeight) {
        // initialize player‘s available weight
        for($i = 1; $i <= $numberOfWeight; $i++) {
            $this->WeightState[$i] = true;
        }
        // time limit is 120 seconds per player
        $this->timeLeft = 120.0;
        $this->name = $name;
    }
}