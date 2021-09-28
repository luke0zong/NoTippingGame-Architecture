import json
from random import choice
from hps.clients import SocketClient
from time import sleep
import argparse

HOST = 'localhost'
PORT = 5000


class NoTippingClient(object):
    def __init__(self, name, is_first):
        self.first_resp_recv = False
        self.name = name
        self.client = SocketClient(HOST, PORT)
        self.client.send_data(
            json.dumps({
                'name': self.name,
                'is_first': is_first
            }))
        response = json.loads(self.client.receive_data())
        self.board_length = response['board_length']
        self.num_weights = response['num_weights']

    def play_game(self):
        # pass
        response = {}
        while True:
            response = json.loads(self.client.receive_data())
            if 'game_over' in response and response['game_over'] == "1":
                print("Game Over!")
                exit(0)

            self.board_state = list(
                map(int, response['board_state'].strip().split(' ')))

            if response['move_type'] == 'place':
                position, weight = self.place(board_state)
                self.client.send_data(
                    json.dumps({
                        "position": position,
                        "weight": weight
                    }))
            else:
                position = self.remove(board_state)
                self.client.send_data(json.dumps({"position": position}))

    def place(self, current_board_state):
        """
        PLACE YOUR PLACING ALGORITHM HERE

        Inputs:
        current_board_state - array of what weight is at a given position on the board

        Output:
        position (Integer), weight (Integer)
        """

        return 1, 1

    def remove(self, current_board_state):
        """
        PLACE YOUR REMOVING ALGORITHM HERE

        Inputs:
        current_board_state - array of what weight is at a given position on the board

        Output:
        position (Integer)
        """
        return 1


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='')
    parser.add_argument('--first',
                        action='store_true',
                        default=False,
                        help='Indicates whether client should go first')
    parser.add_argument('--ip', type=str, default='localhost')
    parser.add_argument('--port', type=int, default=5000)
    parser.add_argument('--name', type=str, default="Python Demo Client")
    args = parser.parse_args()

    HOST = args.ip
    PORT = args.port

    player = NoTippingClient(args.name, args.first)
    player.play_game()
