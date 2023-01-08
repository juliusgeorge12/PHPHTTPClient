<?php
  namespace src\HTTPClient;

use Exception;
use src\Exception\tls_failure;

/**
   * @author Julius George
   * _____________________________________________________________
   * This contains the class for establishing a connection
   * to an http server
   * _____________________________________________________________
   * 
   */
  class client {
     
        /**
         * default http port
         * 
         * 
         * @var int
         */
        const default_port = 80;
        /*
         * default port for https connection
         * the http client would be built to support
         * https ssl/ttl later
         * 
         * 
         * @var int 
         */
        const default_secure_port = 443;
         
        /**
         * set the connection to secure
         * 
         * @var bool
         */

         public $secure_connection = false;
        /**
         * default timeout to wait for server
         * 
         * @var int
         */
         
         const timeout = 300;

          /**
           * maximum data to read
           * 
           * @var int
           */
           const max_read = 4096;
        /**
         * debug level to turn of debug
         * 
         * 
         * @var int
         */
        const debug_off = 0;
         
        /**
         * debug level to show client -> server messages
         * 
         * 
         * 
         * @var int
         */
       const debug_client = 1;

        /**
         * debug client level to show client -> server and server -> client messages
         *
         * @var int
         */
        const debug_server = 2;

        /**
         * debug level to show connection, client -> server and server -> client 
         * messge
         * 
         * @var int
         */
        const debug_connection = 3;

        /**
         * debug level to show all messages
         * 
         * 
         * @var int
         */
         const debug_lowlevel = 4;
          /**
           * sets the debug output level
           * options:
           * * self::debug_off (`0`) no debug output , default
           * * self::debug_client (`1`) shows client -> server messages
           * * self::debug_server (`2`) shows client -> server and 
           * server -> client messages
           * * self::debug_connecttion shows connection , client -> server and
           * server -> client messages
           * * self::debug_lowlevel (`4`) shows all messages
           */
         public $should_debug = self::debug_off;

          /**
           * how debug output is handled
           * options: 
           * * `echo` output plain text as it is 
           * appropriate for cli , default
           * * `html` output html formatted string 
           * * Alternatively you can pass in a callable expecting two
           * parameters: a message string and debug level
           * 
           * ```php
           * $this->debug_output = function($message , $level)
           * {
           *  echo "debug level $level : message $message"; 
           * };
           * ```
           */
           public $debug_output = 'echo';
          



           
            /**
             * last error encountered
             * 
             * @var string
             */
             
             public $last_error = null;

          

         /**
          * 
          * holds the http connection
          *
          * 
          *
          * @var null|resource
          */
          private $connection = null;
          
         
     private function debug(string $message , int $level = 0){
           if($this->should_debug < $level){
            return;
           }
           if(is_callable($message) && !in_array($this->debug_output , ["echo" , "html"])){
             call_user_func($this->debug_output , $message , $level);
             return;
           }
           switch ($this->debug_output) {
            case 'html':
                //Cleans up output a bit for a better looking, HTML-safe output
                echo gmdate('Y-m-d H:i:s'), ' ', htmlentities(
                    preg_replace('/[\r\n]+/', '', $message),
                    ENT_QUOTES,
                    'UTF-8'
                ), "<br>\n";
                break;
            case 'echo':
            default:
                //Normalize line breaks
                $str = preg_replace('/\r\n|\r/m', "\n", $message);
                echo gmdate('Y-m-d H:i:s'),
                "\t",
                    //Trim trailing space
                trim(
                    //Indent for readability, except for trailing break
                    str_replace(
                        "\n",
                        "\n                   \t                  ",
                        trim($str)
                    )
                ),
                "\n";
        }
        }
        
       
        /**
         * connects to an http server
         * @param string $host
         * @param int $port
         * @param int $timeout
         * @param array $options
         * 
         * @return bool
         */
        public function connect($host , $port = null ,$timeout = 30 , $options = []){
                  if($this->connected()){
                        $this->set_error('Client already connects to server');
                        return false;
                  }
                  if(is_null($port) || empty($port)){
                       if(!$this->secure_connection){
                        $port = self::default_port;
                     } else {
                        $port = self::default_secure_port;
                       //tls connection not supported for now
                     }
                  }
                  $conn_msg = "CONNECTION : client connecting to server at $host:$port";
                  $conn_msg .= ",timeout=$timeout , options=" ;
                  $conn_msg .= (count($options) > 0) ? var_export($options , true) : '[]'; 
                  $this->debug($conn_msg, self::debug_connection);
            
               $this->connection = $this->get_connection($host , $port , $timeout , $options);
               if($this->secure_connection){
                 echo 'tls connection not supported for now so can not proceed with the https request';
                 exit;
                 }
               if($this->connection === false){
                  //error already set
                  return false;
               }
               $this->debug('CONNECTION: connection opened and is ready for communication' ,
            self::debug_connection);
               return true;
        }
        /**
         * checks if the client has connected to server
         * 
         * @return bool
         * 
         */
        public function connected(){

            if (is_resource($this->connection)) {
                  $sock_status = stream_get_meta_data($this->connection);
                  if ($sock_status['eof']) {
                      //The socket is valid but we are not connected
                      $this->debug(
                          'HTTP  NOTICE: EOF caught while checking if connected',
                          self::debug_client
                      );
                      $this->close();
      
                      return false;
                  }
      
                  return true; //everything looks good
              }
      
              return false;
        }

        /**
         * connect to an http server
         * 
         * 
         * @return false|resource
         */
        private function get_connection($host , $port , $timeout , $options){
          static $stream_ok;
          //check if stream_socket_client function is enable or available
         if(null === $stream_ok){
          $stream_ok = function_exists('stream_socket_client');
         }
         $errno = 0;
         $errstr = '';
          if($stream_ok){
            $context = stream_context_create($options);
            set_error_handler([$this , 'error_handler']);
            $connection = stream_socket_client(
                  "$host:$port" ,
                   $errno,
                    $errstr ,
                     $timeout ,
                     STREAM_CLIENT_CONNECT,
                      $context
                  );
             restore_error_handler();           
          } else {
            //meaning stream_socket_client not supported fall back to
            //fsock_open()
             $this->debug("stream_socket_client not available" ,
              self::debug_connection);
              $connection = fsockopen($host , $port , $errno , $errstr);
              restore_error_handler();
          }
          //verify if the connection was really successful
           if(!is_resource($connection)){
            $this->set_error('Connection failed: '. $errstr , $errno);
            $this->debug("Failed to connect to Http Server : ERRO[$errno] : $errstr" 
            , self::debug_client);
            return false;
           }
         if (strpos(PHP_OS, 'WIN') !== 0) {
            $max = (int)ini_get('max_execution_time');
            //Don't bother if unlimited, or if set_time_limit is disabled
            if (0 !== $max && $timeout > $max && strpos(ini_get('disable_functions'), 'set_time_limit') === false) {
                @set_time_limit($timeout);
            }
            stream_set_timeout($connection, $timeout, 0);
        }
              return $connection;
        }

       /**
        * sets the last error
        * @param $error
        * @param $code
        */
        protected function set_error($error , $code = 0){
            if($code === 0){
          $this->last_error = 'Error:' . "  " . $error;
            } else {
                  $this->last_error = "Error[$code]:"  . "  " . $error;
            }
        }

        /**
         * handles connection error
         * @param int $errno
         * @param string $errmsg
         * @param string $errfile
         * @param int $errline
         * 
         */
        protected  function error_handler($errno , $errmsg , $errfile , $errline){
            $notice = 'Connection failed.';
            $this->set_error($errmsg , $errno);
            $this->debug(
                "$notice Error #$errno: $errmsg [$errfile line $errline]",
                self::debug_connection
            );
        }
        public function close(){
            fclose($this->connection);
            $this->connection = null;
        }
        /**
         * sends a tarnsaction to the server
         * 
         * @param $command
         */
        public function write(string $command){
            if($this->connection === null){
                  $this->debug("CONNECTION :  connection has not be made to client" , self::debug_connection);
              echo "connection has not been made connect method is expected to be called
               before writing and reading data \r\n";
               return;
            } else if($this->connection === false){
                  $this->debug("CONNECTION :  could not connect to server" , self::debug_connection);
              echo "can not write data because connection could not be made\r\n";
               return;
            }
            $this->debug("CLIENT -> SERVER : $command" , self::debug_client);
            $len = strlen($command);
            fwrite($this->connection , $command , $len);
        }

        /**
         * read reply from server
         * 
         * 
         * @return string
         */
         public function read($bytes = self::max_read){
              if($this->connection === null){
                  $this->debug("CONNECTION :  connection has not be made to client" , self::debug_connection);
              echo "connection has not been made connect method is expected to be called
               before writing and reading data \r\n";
               return;
            }
            else if($this->connection === false){
                  $this->debug("CONNECTION :  could not connect to server" , self::debug_connection);
              echo "can not read data because connection could not be made\r\n";
               return;
            }
               $this->debug("SERVER -> CLIENT : sending data" , self::debug_server);
        
              return @fread($this->connection , $bytes);
          } 

         /**
          * read line from the stream 
          *
          */

          public function read_line(){
            if($this->connection === null){
                $this->debug("CONNECTION :  connection has not be made to client" , self::debug_connection);
            echo "connection has not been made connect method is expected to be called
             before writing and reading data \r\n";
             return;
                       }
                else if($this->connection === false){
                $this->debug("CONNECTION :  could not connect to server" , self::debug_connection);
                 echo "can not read data because connection could not be made\r\n";
             return;
                }
            $this->debug("SERVER -> CLIENT : sending data" , self::debug_server);
            return @fgets($this->connection);
            }
        
      }
        
       
  