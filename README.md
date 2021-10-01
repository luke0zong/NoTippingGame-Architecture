# No Tipping Game

## Problem Statement

Given a uniform, flat board (made of a titanium alloy) **60 meters** long and weighing **3 kilograms**, consider it ranging from **-30** meters to **30** meters. So the center of gravity is at 0. We place two supports of equal heights at positions **-3** and **-1** and a **3 kilogram** block at position **-4**.

The No Tipping game is a two person game that works as follows: the two players each start with **k** blocks having weights **1 kg** through **k kg** where **2k is less than 50**. The first player places one block anywhere on the board, then the second player places one block anywhere on the board, and play alternates with each player placing one block until the second player places his or her last block. (The only allowable positions are on meter markers. No player may place one block above another one, so each position will have at most one block.) If after any play, the placement of a block causes the board to tip, then the player **who did that play loses**. Suppose that the board hasn't tipped by the time the last block is placed. Then the players remove one block at a time in turns. At each play, each player may remove a block placed by **any player** or **the initial block**. If the board tips following a removal, then the player **who removed the last block loses**.

As the game proceeds, the net torque **around each support** is calculated and displayed. The blocks, whether on the board or in the possession of the players, are displayed with their weight values. The torque is computed by **weight times the distance to each support**. **Clockwise is negative torque and counterclockwise is positive torque**. *You want the net torque on the left support to be non-positive and the net torque on the right support to be non-negative*.

Check link: https://cs.nyu.edu/courses/fall21/CSCI-GA.2965-001/notipping.html

## Starting Server

To begin the server, run:

```bash
php main.php <port> <number of weights>
```

This will create a socket for communication to and from the server at `hostname:port`.

## Playing the Game

```json
{
    "move_type":"place",
    "game_over":"1",
    "board_state":" 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 3 0 0 0 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0"
}
```

Every turn the player will be sent a **JSON object** containing the state of the game. It will include:

`move_type` - A flag to let the player know if they are in a stage to add weights to the board or remove weights from the board: `place` = adding weights, `remove` = removing weights.

`board_state` - The weights at each position on the board. '0' if the space is unoccupied and a weight can be placed there.

`game_over` - A flag to let the player know if the game has ended or not. 0 = The game is still going and the player must make a move, 1 = The game has ended.

To make a move, the player must send a string to the server containing the following depending on the stage of the game:

### Adding Weights

If the player is still adding weights (i.e. has weights left to add), he must send the following to the server:

```json
{
    "weight":1,
    "position":1
}
```

where `weight` is the weight in kg of the weight you'd like to add, and `position` is the position where you'd like to add it [-30, 30].

### Removing Weights

If the player is removing weights, he must send the following:

```json
{"position":1}
```

where `position` is the position of the weight to be removed in his turn.

### Illegal Moves

Making an illegal move will result in the player immediately losing the game. This includes:

* Placing the same weight twice
* Placing a weight on an occupied space
* Attempting to place a weight in a space not represented on the board (ie. x > 30 || x < -30)
* Attempting to remove a weight from an unoccupied space

## Client

When writing a client to interact with the server, the interaction works as follows:

* Connect to the server via `hostname:port`.

* Send greeting information to the server, eg: `{"name":"Client Name","is_first":false}`. Where `name` is your team name and `is_first` indicates weather your client should go first.

* Listen to the server and receive initial game information from the game, eg: `{"num_weights": 15,"board_length": 30}` where `num_weights` is your initial weight blocks (1 kg to k kg), `board_length` is the length of the game board (30 indicates the board goes from -30 to 30 meters).

* Continue listening to server. When it is your turn, the server will send the JSON describing the game state to you. You can then send your move per the instructions above.

You can find starter code for each language under `clients/<language>/` folders.
Read the `readme.md` for instructions.

## Random Strategy Test Client

`test_no_tipping.py` is included to test your algorithm against.

To run the test script, use the same host name and port number that was used for the php script. For example, you should use this command `python3 test_no_tipping.py --port XXXX --name TEST`. Therefore, your code should also accept host name and port number as an argument.


### requirements

`test_no_tipping.py` uses `SocketClient`, install `hps-nyu`:
```
pip install --user hps-nyu
```

### Description of random strategy

A Random Strategy should play the first or second player's game (depending on the command line). During AddMode, it should choose a random remaining block and place it as far left as possible so as to avoid tipping. During RemoveMode, it should examine all blocks on the board, determine which are will not cause tipping, and remove a random one of those.

## Localhost Server

Start the main server as a separate process by running:

```
php main.php <port> <number of weights> [-w]
```

`-w` is an optional command line argument which forces a 1 second pause between turns(this delay does not affect each clients allowed time to run).

You can also run an instance of the webserver for a visual depiction with the command `php -S <hostname:port>`. Make sure the port of the server and webserver are different.

For example:
```
php -S localhost:8000
````
You can view the running game from the index.html file (by going to `localhost:8000/index.html`.


Have both clients establish a connection to the server. If you are using test client you can do so with `python3 test_no_tipping.py --port XXXX --name TEST`.

## Received Code

| Team        | Received On | Test |
| ----------- | ----------- | ---- |
| Team Player | 10/01       | PASS |

## Contact Us
Yaowei Zong - yz7413@nyu.edu
Kevin Chang - tc3149@nyu.edu