<?php

class GameController
{
    private $socket;
    private $resources;
    public $player1 = "Player 1";
    public $player2 = "Player 2";
    private $observed = false;

    function __construct($address, $port)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_bind($this->socket, $address, $port);
    }

    function createConnection($numOfWeights, $board_length, $observed)
    {
        socket_listen($this->socket);
        $initial_message = json_encode(['num_weights' => $numOfWeights, 'board_length' => $board_length]);

        echo "[SERVER] Waiting for Player [1] ...\n";
        while (true) {
            if ($this->resources[1] = socket_accept($this->socket)) {
                echo "         Socket accepted...\n";
                socket_set_nonblock($this->resources[1]);
                echo "         Waiting for message to come...\n";
                $data = json_decode($this->recv(1));
                $this->player1 = $data->name;
                $this->send(1, $initial_message);
                echo "         Connection from [" . $this->player1 . "], established\n";
                break;
            }
        }

        echo "[SERVER] Waiting for Player [2] ...\n";
        while (true) {
            if ($this->resources[2] = socket_accept($this->socket)) {
                echo "         Socket accepted...\n";
                socket_set_nonblock($this->resources[2]);
                echo "         Waiting for message to come...\n";
                $data = json_decode($this->recv(2));
                $this->player2 = $data->name;
                $this->send(2, $initial_message);
                echo "         Connection from [" . $this->player2 . "], established\n";
                break;
            }
        }

        // second connection is the first to start
        if ($data->is_first == 1) {
            $tmp = $this->resources[1];
            $this->resources[1] = $this->resources[2];
            $this->resources[2] = $tmp;

            $tmp = $this->player1;
            $this->player1 = $this->player2;
            $this->player2 = $tmp;
        }

        // connect to observer
        if ($observed) {
            echo "[SERVER] Connecting to Observer ...\n";
            if ($this->resources[0] = socket_accept($this->socket)) {
                echo "         Socket accepted...\n";

                // extra communication to identify client (see comment below for more details on websocket exchange)
                $identification = socket_read($this->resources[0], 5000);
                if (strpos($identification, "Sec-WebSocket-Key:") !== false) {
                    preg_match('#Sec-WebSocket-Key: (.*)\r\n#', $identification, $matches);
                    $key = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
                    $headers = "HTTP/1.1 101 Switching Protocols\r\n";
                    $headers .= "Upgrade: websocket\r\n";
                    $headers .= "Connection: Upgrade\r\n";
                    $headers .= "Sec-WebSocket-Version: 13\r\n";
                    $headers .= "Sec-WebSocket-Accept: $key\r\n\r\n";
                    socket_write($this->resources[0], $headers, strlen($headers));
                }


                socket_set_nonblock($this->resources[0]);
                echo "         Send game information...\n";
                $gameInfo = json_encode(['num_weights' => $numOfWeights, 'board_length' => $board_length, 'player1' => $this->player1, 'player2' => $this->player2]);
                // $this->send(0, $gameInfo);
                // socket_write($this->resources[0], chr(129) . chr(strlen($gameInfo)) . $gameInfo);
                $this->sendObserver($gameInfo);
                echo "         Connection to observer established!\n";
                $this->observed = true;
            }
        }
    }

    function closeConnection()
    {
        socket_close($this->resources[1]);
        socket_close($this->resources[2]);
        if ($this->observed) {
            // close observer connection
            socket_close($this->resources[0]);
        }
        socket_close($this->socket);
    }

    function send($player, $string)
    {
        echo "[SERVER] Sending to [" . $player . "] ...\n";
        echo "         Message: " . $string . "\n";
        socket_write($this->resources[$player], "$string\n");
    }

    function sendObserver($string)
    {
        socket_write($this->resources[0], chr(129) . chr(strlen($string)) . $string);
    }

    function recvMove($player)
    {
        $data = json_decode($this->recv($player));
        $player = $player == '1' ? $this->player1 : $this->player2;

        echo "\n[SERVER] Received move from [" . $player . "]\n";
        return $data;
    }

    function recv($player)
    {
        while (true) {
            $data = socket_read($this->resources[$player], 1024, PHP_BINARY_READ);
            if ($data != "") {
                return $data;
            }
        }
    }
}
