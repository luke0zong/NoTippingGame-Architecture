
/**
 * A socket based client for the ``hps.servers.SocketServer`` class
 * Game: No Tipping
 * https://cs.nyu.edu/courses/fall21/CSCI-GA.2965-001/notipping.html
 */

import java.awt.*;
import java.io.*;
import java.net.*;

// for json
import com.alibaba.fastjson.JSONObject;

class Pair<T, U> {
    public T first;
    public U second;

    public Pair(T first, U second) {
        this.first = first;
        this.second = second;
    }
}

public class Client {
    /**
     * A client class for ``hps.servers.SocketServer``
     *
     */
    public String host;
    public int port;
    public Socket socket;

    private BufferedReader input = null;
    private PrintWriter output = null;

    private int boardLength;
    private int numWeights;

    // ! TODO: Please change this to your team name
    public String name = "Java Client";
    public boolean isFirst = false;

    public Client(String host, int port, boolean first) throws IOException {
        /**
         * @param host: The hostname of the server
         * @param port: The port of the server
         */
        this.host = host;
        this.port = port;
        this.isFirst = first;

        System.out.println("[INFO] Crating connection to server ...");
        System.out.println("       Client Name: " + name);

        try {
            socket = new Socket(host, port);

            // Client takes input from socket
            input = new BufferedReader(new InputStreamReader(socket.getInputStream()));

            // And also sends its output to the socket
            output = new PrintWriter(socket.getOutputStream(), true);

            // socket.connect(new InetSocketAddress(host, port), 1000);

        } catch (IOException e) {
            System.out.println("[ERROR] Could not connect to server");
            System.out.println("[ERROR] " + e.getMessage());
            System.exit(1);
        }

    }

    public void play_game() throws IOException {

        System.out.println("[INFO] Connected to server");
        System.out.println("[INFO] Sending greeting to server ...");

        // Send greeting json to server, name and is_first
        JSONObject greeting = new JSONObject();
        greeting.put("name", name);
        greeting.put("is_first", isFirst);

        this.send_data(greeting.toJSONString());

        // get initial game state from server
        String initialGameState = this.receive_data();
        System.out.println("[INFO] Received initial game state from server");
        System.out.println("[INFO] Parsing game state ...");

        // store game state from json, num_weights and board_length
        JSONObject gameState = JSONObject.parseObject(initialGameState);
        this.numWeights = gameState.getInteger("num_weights");
        this.boardLength = gameState.getInteger("board_length");

        System.out.println("[INFO] Starting game ...");
        JSONObject response = new JSONObject();

        while (true) {
            System.out.println("[INFO] Received message:");
            String res = this.receive_data();

            response = JSONObject.parseObject(res);

            if (response.containsKey("game_over") && response.getBoolean("game_over")) {
                System.out.println("[GAME] Game over!");
                System.exit(0);
            }

            // get current board state
            String boardState = response.getString("board_state");
            // TODO: parse board state here

            if (response.getString("move_type").equals("place")) {
                System.out.println("[GAME] Stage: Place");
                Pair<Integer, Integer> move = this.place(boardState);

                // send move to server
                JSONObject moveJson = new JSONObject();
                moveJson.put("position", move.first);
                moveJson.put("weight", move.second);
                this.send_data(moveJson.toJSONString());

            } else {
                System.out.println("[GAME] Stage: Remove");
                int position = this.remove(boardState);

                // send move to server
                JSONObject moveJson = new JSONObject();
                moveJson.put("position", position);
                this.send_data(moveJson.toJSONString());
            }
        }
    }

    // ! TODO: implement this method
    public Pair<Integer, Integer> place(String boardState) {
        /**
         * @param boardState: The current board state
         * @return: The position and weight to place
         */

        // PLACE YOUR PLACING ALGORITHM HERE

        // Inputs:
        // board_state - array of what weight is at a given position on the board

        // Output:
        // position (Integer), weight (Integer)

        return new Pair<Integer, Integer>(1, 1);
    }

    // ! TODO: implement this method
    public int remove(String boardState) {
        /**
         * @param boardState: The current board state
         * @return: The position to remove
         */

        // PLACE YOUR REMOVING ALGORITHM HERE

        // Inputs:
        // board_state - array of what weight is at a given position on the board

        // Output:
        // position (Integer)

        return 1;
    }

    public void send_data(String data) throws IOException {
        /**
         * Send data to the server
         *
         * @param data: The data to send to the server.
         */
        System.out.println("[INFO] Sending data to server ...");
        System.out.println("       " + data);
        output.println(data);
    }

    public String receive_data() throws IOException {
        /**
         * Receive data from the server
         *
         * @return The data received as a String from server.
         */

        String str = input.readLine();
        System.out.println("[INFO] Received data from server ...");
        System.out.println("       " + str);
        return str;
    }

    public void close_socket() throws IOException {
        /**
         * Close the connection
         */
        socket.close();
    }

    public static void main(String[] args) throws IOException {
        /**
         * @param args: The hostname and port of the server
         */

        int PORT = 5000;
        String HOST = "127.0.0.1";
        boolean FIRST = false;

        if (args.length == 0) {
            System.out.println("[INFO] Using default ip: localhost, port: 5000");
        } else {
            HOST = args[0];
            PORT = Integer.parseInt(args[1]);
            FIRST = Boolean.parseBoolean(args[2]);
            System.out.println("[INFO] Using ip: " + HOST + ", port: " + PORT);
        }

        Client client = new Client(HOST, PORT, FIRST);
        client.play_game();
    }
}
