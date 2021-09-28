# Java Client

## requirement

Place `fastjson-1.2.78.jar` in the same directory as `Client.java`.


## args

Include all args or none.

- `<host>` : host to connect to, default 'localhost'

- `<port>` : port to connect to, default `5000`

- `<bool:first>` : indicates whether client should go first

## script

- compile : `javac -cp fastjson-1.2.78.jar Client.java`

- run: `java -cp fastjson-1.2.78.jar:. Client`