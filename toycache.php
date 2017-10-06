#!/usr/bin/php
<?php

/**
 * Remote variable cache storage.
 * Class ToyCache
 */
class ToyCache
{
    protected $connections = [];

    protected $address = '0.0.0.0';

    protected $port = 9500;

    protected $sock;

    public function __construct()
    {
        $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    public function __destruct()
    {
        socket_close($this->sock);
    }

    public function stop()
    {
        foreach ($this->connections as $conn) {
            socket_close($conn);
        }

        socket_close($this->sock);
    }

    /**
     * Starts the server
     */
    public function start()
    {
        try {
            // Make address and port reusable
            socket_setopt($this->sock, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_setopt($this->sock, SOL_SOCKET, SO_REUSEPORT, 1);

            // Bind Address and port to listen clients connections
            socket_bind($this->sock, $this->address, $this->port);
            socket_listen($this->sock);
            socket_set_nonblock($this->sock);

            // Main loop
            while (true) {

                $connection = socket_accept($this->sock);

                if ($connection == false) {
                    usleep(100);
                } elseif ($connection > 0) {

                    $this->connections[] = $connection;

                    socket_getpeername($connection, $ip);
                    echo "Server: New client connected ", $ip, "\n";

                    $this->handleClient($connection);
                } else {
                    echo 'Error:' . socket_strerror($connection);
                }
            }

        } catch (Exception $e) {
            echo 'Error: ', $e->getMessage();
        }
    }

    /**
     * Fork process to handle client interaction.
     * @param $connection
     */
    public function handleClient($connection)
    {
        $storage = [];

        $pid = pcntl_fork();

        if ($pid == -1) { // Fork error

            echo "Error: Failure to handle client";
            socket_close($connection);

        } elseif ($pid == 0) { // Child process

            while (true) {

                while($buf = @socket_read($connection, 1024, PHP_NORMAL_READ))
                    if($buf = trim($buf))
                        break;

                if (false == $buf) {
                    socket_getpeername($connection, $ip);
                    echo "Server: Client disconnection ", $ip, "\n";
                    break;
                } else {

                    $command = substr($buf, 0, strpos($buf, ' ', 0));

                    if (empty($command)) {
                        $command = $buf;
                    } else {
                        $args = explode(' ', substr($buf, strpos($buf, ' ')+1));
                    }

                    switch ($command) {

                        case 'SET':

                            echo 'Server: SET on key: ', $args[0],' value: ', $args[1], "\n";
                            $storage[$args[0]] = $args[1];
                            break;
                        case 'GET':

                            if (!isset($storage[$args[0]])) {
                                $value = null;
                            } else {
                                $value = $storage[$args[0]];
                            }

                            echo 'Server: GET on key: ', $args[0],' value: ', $value, "\n";


                            socket_write($connection, $value, strlen($value));


                            break;
                        case 'FLUSH':
                            $storage = null;
                            $storage = [];
                            break;
                    }

                    echo 'Command: ', $command, ' Message: ', $buf, "\n";
                }
            }

        } else { // parent

            socket_close($connection);
        }
    }
}


$server = new ToyCache();
$server->start();