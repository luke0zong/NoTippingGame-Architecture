import json
# from random import choice
import random
from hps.clients import SocketClient
from time import sleep
import argparse

HOST = 'localhost'
PORT = 5000
SLOW = False


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
        self.myWeight = dict()
        for i in range(1, int(self.num_weights) + 1):
            self.myWeight[i] = 1

    def play_game(self):
        response = {}
        while True:
            response = json.loads(self.client.receive_data())
            if 'game_over' in response and response['game_over'] == "1":
                print("Game Over!")
                exit(0)

            self.board_state = list(
                map(int, response['board_state'].strip().split(' ')))

            print(self.board_state)

            # sleeps 2 seconds
            if SLOW:
                sleep(2)

            if response['move_type'] == 'place':
                position, weight = self.place(self.board_state)
                self.client.send_data(
                    json.dumps({
                        "position": position,
                        "weight": weight
                    }))
            else:
                position = self.remove(self.board_state)
                self.client.send_data(json.dumps({"position": position}))

    def place(self, board_state):
        """
        Inputs:
        current_board_state - array of what weight is at a given position on the board

        Output:
        position (Integer), weight (Integer)
        """

        allPosition = []
        for key, value in self.myWeight.items():
            if value == 1:
                position = self.find_place_position(key, self.board_state)
                if position != -100:
                    allPosition.append((position - 30, key))
        if len(allPosition) == 0:
            choice = (0, 1)
        else:
            choice = random.choice(allPosition)
        self.myWeight[choice[1]] = 0
        print("Added: " + str(choice))
        return choice[0], choice[1]

    def remove(self, board_state):
        """
        Inputs:
        current_board_state - array of what weight is at a given position on the board

        Output:
        position (Integer)
        """
        allPossiblePosition = []
        for i in range(0, 61):
            if self.board_state[i] != 0:
                tempWeight = self.board_state[i]
                self.board_state[i] = 0
                if self.check_balance(self.board_state):
                    allPossiblePosition.append(i - 30)
                self.board_state[i] = tempWeight
        if len(allPossiblePosition) == 0:
            choice = 1
        else:
            choice = random.choice(allPossiblePosition)
        print("Removed:" + str(choice))
        return choice

    def check_balance(self, board_state):
        left_torque = 0
        right_torque = 0
        for i in range(0, 61):
            left_torque += (i - 30 + 3) * self.board_state[i]
            right_torque += (i - 30 + 1) * self.board_state[i]
        left_torque += 3 * 3
        right_torque += 1 * 3
        return left_torque >= 0 and right_torque <= 0

    def find_place_position(self, weight, board_state):
        for i in range(0, 61):
            if self.board_state[i] == 0:
                self.board_state[i] = weight
                if self.check_balance(self.board_state):
                    self.board_state[i] = 0
                    return i
                self.board_state[i] = 0
        return -100


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='')
    parser.add_argument('--first',
                        action='store_true',
                        default=False,
                        help='Indicates whether client should go first')
    parser.add_argument('--slow', action='store_true', default=False)
    parser.add_argument('--ip', type=str, default='localhost')
    parser.add_argument('--port', type=int, default=5000)
    parser.add_argument('--name', type=str, default="Random Client")

    args = parser.parse_args()

    HOST = args.ip
    PORT = args.port
    SLOW = args.slow

    player = NoTippingClient(args.name, args.first)
    player.play_game()
