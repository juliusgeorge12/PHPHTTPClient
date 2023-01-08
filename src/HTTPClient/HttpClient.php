<?php
 namespace src\HTTPClient;

use Exception;
use src\Exception\unknown_scheme;

 /**
   * @author Julius George
   * @version 1.0
   * @see https://www.rfc-editor/rfc/rfc9110
   * _____________________________________________________________
   * RFC9110 implementation  http client class
   *  this class contain all the functionalities for initiating 
   * a http client and sending http transaction
   * for now this class does not spport ssl or tls transaction
   * but it would be added soon
   * _____________________________________________________________
   * 
   */
 class HttpClient {
  
      /**
       * the http client version
       * 
       * 
       *  @var int 
       */
      const version = 1.0; 
      
      /**
       * default http port
       * 
       * @var int
       */
      const default_http_port = 80;

      /**
       * 
       * default https port
       * 
       * @var int 
       */

       const default_https_port = 433;

      /**
       * http ok status code
       * 
       * @var int
       */

       const response_ok = 200;

       /**
       * http permanent redirect status code
       * 
       * @var int
       */

       const redirect_permanent = 301;
      
       /**
       * http temporary redirect status code
       * 
       * @var int
       */

      const redirect_temporary = 302;

      /**
       * the maximum number of times the request should 
       * redirect
       * 
       */
      const max_redirect = 3;
      /**
       * the http scheme
       * 
       * @var array
       */
       const accepted_scheme = ["https" , "http"];

      /**
       * 
       * holds the http connection object
       * 
       */

      
       private $connection = null;

      /**
       * enable or disable sending of cookie;
       * options:
       * * `(false)` would disable the sending of cookie, default
       * * `(true)` would enable the sending of cookie with the request
       */
        
      private $should_send_cookie = false;

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
      private $should_debug = 0;

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

        private $debug_output = 'echo';
      
      /**
       * hold the custom headers that should be sent with the request
       * an array containing header=>value pair
       * @var array
       */

       private $custom_headers = [];
      
      /**
       * the data to send in the request
       * body
       * an array of [name=>the_name,value=>the_value,type=>type]... pair
       */
      
       private $body_data = [];

      
      /**
       * the raw http header
       * 
       */

       private $raw_header = null;

       /**
        * holds the response parser
        *
        */

         
      /**
       * hold the http request builder object
       * 
       */

      private $request_builder = null;

      /**
       * hold the http response parser
       * 
       * @var object
       */

       private $response_parser = null;

      /**
       * thr raw http 
       * 
       */
      
      /**
       * the http scheme
       * 
       */

       private $scheme = null;
        
      /**
       * the host
       * 
       */

      private $host = null;
      
       /**
       * the request path including the query string
       * 
       */

      private $path = null;

      /**
       * the request path excluding the query string
       * 
       */

       private $path_only = null;

      /**
       * the url query string
       * 
       */

      private $url_query_string = null;

      /**
       * the request method
       * 
       */

       private $method = null;


      /**
       * holds the raw http request
       * 
       * @var string
       * 
       */

       private $raw_http_request = null;

       
      /**
       * the response text
       * 
       * @var string
       */

       private $response_text =  null;

       /**
        * the response status code
        * @var int
        */
       
        private $status_code = null;
      
        /**
         * the response status text
         * 
         * @var string 
         */

         private $status_text = null;

      /**
       * set if the request is faked request
       * this is usually set when you just want to 
       * get the http request and you don't want the 
       * request to be sent
       * 
       */

       private $is_fake = false;

       /**
        *  the number of times the request
        * has been redirected
        * @var int
        */

        private $redirected_numbers = 0;

        /**
         * set if the client should output anything
         * 
         * @var bool
         */

         private $should_shutup = true;

         /**
          * the message mode to use when outputing message
          * echo by default
          * @var string
          */

          private $message_mode = "echo";

      public function __construct()
      {
        $this->connection = new client;
      }

      /**
       * create the client object and connect to the client
       */
      private function initialize_client()
      {  
        $this->connection->should_debug = $this->should_debug;
        $this->connection->debug_output = $this->debug_output;
        $this->connection->connect($this->host , $this->port);
        if(!$this->connection->connected()){
          throw new Exception($this->connection->last_error);
        }

      }
      /**
       * parse the url
       */
      private function initialize_url(string $url)
      {
        $url_parser = new url_parser($url);
        $url_parser->parse();
        $this->scheme = $url_parser->get_scheme();
       if(!in_array($this->scheme , self::accepted_scheme)){
          throw new Exception((new unknown_scheme())->message());
       }
       //set the condition to secure
       if($this->scheme === "https"){
       $this->connection->secure_connection = true;
          }
        $port = $url_parser->get_port();
        if(is_null($port)){
          if($this->scheme === "https"){
           $this->port = self::default_https_port;
          }
          if($this->scheme === "http"){
            $this->port = self::default_http_port;
           }
        }
         else {
          $this->port = $port;
        }
      if(empty($url_parser->get_query_string())){
          $query_string = "";
          } 
       else {
          $query_string = "?" . $url_parser->get_query_string();
        }
        $this->url_query_string = $query_string;
        $this->path_only = $url_parser->get_path();
        $path = $this->path_only . $query_string;
       $this->path = $path;
       $url_parser->get_host();
       $this->host = $url_parser->get_host();
      }

      /**
       * output a message
       * 
       * @param string $message
       */

       private function talk(string $message)
       {
             if($this->message_mode === "echo"){
                echo $message . "\r\n";
             }
             if($this->message_mode === "html"){
              echo "<p>" . $message . "</p>";
             }
       }

      /**
       * open a http request
       * 
       * @param string $url
       * @param string $method
       */
       
       public function open(string $url , string $method)
       {
        $this->request_builder = new request_builder;
        $this->response_parser = new response_parser;
        $this->method = $method;
        $this->initialize_url($url);
        $this->initialize_client();
        return $this;
       }

       
        /**
         * set the encoding type to multipart
         * this important when uploading file
         * 
         */
        public function enable_file_upload(){
          $this->request_builder->is_multipart = true;
          return $this;
        }


       /**
        * check if the url is 
        * an absolute path
        * 
        * @param string $url
        * @return bool
        */
       private function is_absolute_path($url)
       {
           $scheme = parse_url($url , PHP_URL_SCHEME);
           $host = parse_url($url , PHP_URL_HOST);
           if(is_null($scheme) && is_null($host)){
            return false;
           } else {
            return true;
           }
       }


       /**
        * returns  the full path from the relative path
        *
        * @param string $relative_path
        * @return string
        */
       private function get_full_path_from_relative_path(string $relative_path)
       {
          $path = trim($relative_path , "/");
          $relative_to_part = explode("/" , $this->path_only);
          array_pop($relative_to_part);
           $full_path = $this->scheme . "://" . $this->host;
          $full_path .= implode('/' , $relative_to_part) . "/" . $path;
          return $full_path;
       } 

       /**
        * turns an array into a cookie string
        * form which can be include with the http requests
        * @param $cookie $key=>$value pair
        * @return string
        */
        private function cookie_encode(array $cookie){
          $string = [];
          foreach($cookie as $key=>$value){
            array_push($string , trim($key) . "=" . trim($value));
          } 
          return implode(';', $string);
        }

        /**
         * set the request faking to be true
         * this means the request would not be sent
         * 
         */
        public function fake()
        {
          $this->is_fake = true;
          return $this;
        }

        /**
         * set if the client should echo 
         * messages like redirecting messages
         * false by default
         * @param bool $should_shutup false by default meaning
         * no message is outputed
         * @param string $mode html|echo whe set to html, html message would 
         * be outputed , when set to echo messages would
         * be outputed as plain text
         */

         public function silent(bool $should_shutup = true, string $message_mode = "echo")
         {
          $this->should_shutup = $should_shutup;
          $this->message_mode = $message_mode;
          return $this;
         }

         
        /**
         * set should send cookie to true
         * 
         */

         public function enable_cookie()
         {
           $this->should_send_cookie = true;
           return $this;
         }

         /**
          * sends the cookie
          * @param array $name=>$value pair
          */

        public function send_cookie(array $cookies)
        {
          if(!$this->should_send_cookie){
            throw new Exception('cookie is has not been enabled');
          }
          $cookie_string = $this->cookie_encode($cookies);
         $this->custom_headers["Cookie"] = $cookie_string;
         return $this;
        }

        /**
         * enable debugging
         * 
         * @param int $debug_leve;
         * * options:
         * * self::debug_off (`0`) no debug output , default
         * * self::debug_client (`1`) shows client -> server messages
         * * self::debug_server (`2`) shows client -> server and 
         * server -> client messages
         * * self::debug_connecttion shows connection , client -> server and
         * server -> client messages
         * * self::debug_lowlevel (`4`) shows all messages
         * * 
         * 
         * @param string $debug_output_fromat
         * * how debug output is handled
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
        public function enable_debug(int $debug_level, string $debug_out = 'echo')
        {
          $this->should_debug = $debug_level;
          $this->debug_output = $debug_out;
          return $this;
        }
        /**
         * set the custom request header
         * @param array $headers an array of name=>value pair
         * 
         */

         public function set_headers(array $headers)
         {
            if(array_key_exists('Cookie' , $headers)){
              unset($headers["cookie"]);
            }
            $this->custom_headers = array_merge($headers);
            return $this;
         }
        
         /**
          * set  the body data
          * @param array 2d array of [[name=>the_name,value=>value,type=>the_type(text or file)],...]
          */
       
          private function set_body_data(array $body_data)
          {
            $this->body_data = $body_data;
          }
          
        /**
         * build  http the request
         * 
         */

         private function build_request()
         { 
            $this->raw_http_request =  $this->request_builder
           ->set_method($this->method)
           ->set_host($this->host)
           ->set_url($this->path)
           ->build_body($this->body_data)
           ->build_headers($this->custom_headers)
           ->get();
          }

         /**
          * the request body data
          * 2d array of [[name1,value1],[name2,value2]...]
          *
          */
         public function send(array $body_data = [])
         {
           $this->set_body_data($body_data);
           $this->build_request();
           $this->send_request();
           return $this;
         }

         /**
          * returns the raw http request
          */
         public function get_raw_request()
         {
         return $this->raw_http_request;

         }

          /**
          * returns the raw http request
          */
          public function get_raw_response()
          {
            if($this->is_fake){
              throw new Exception("can not get raw http response because the request is being faked, hence no request is sent");
            } else {
                     return $this->raw_header . $this->response_text ;
            }
 
          }

          /**
           * returns the raw http header
           * 
           */

           private function get_raw_header()
           {
            return $this->raw_header;
           }
         /**
          * write the http request to the server
          *
          */

          private function write_request()
          {
            //write the request to the server
             $this->connection->write($this->raw_http_request);
             $this->parse_header();
             $this->fetch_response();
             $this->check_redirect();
             //$this->connection->close();
          }
          /**
           * 
           * send the http request
           */

           private function send_request()
           {
            //check if the request is being faked 
           //if true don't send the request to the server
            if(!$this->is_fake){
              $this->write_request();
              }
           
          }

          /**
           * get the header from the socket
           * 
           */

           private function fetch_header()
           {
               $header = " ";
               while(($buffer = $this->connection->read_line()))
               {
                if(rtrim($buffer) === ''){
                  break;
                }
                  $header .= $buffer;
               } 
                $this->raw_header = $header;
                return $header;
           }

         /**
          * parse the request header 
          *
          */

          private function parse_header()
          {
            $header = $this->fetch_header();
            $this->response_parser->parse_header($header);
            $this->status_code = $this->response_parser->get_status_code();
            $this->status_text = $this->response_parser->get_status_text();
           
          }
        /**
         * fetch the response
         * 
         */
        private function fetch_response()
        { 
         /**
          * we would proceed to read the response body even if it 
          * is a redirect, if not the remaining bytes we didn't read would 
          * be sent together with the new response which will give an
          * error 
          */
          $content_length = $this->response_parser->get("content-length");
          $encoding = $this->response_parser->get("transfer-encoding");
          $content_encoding = $this->response_parser->get("content-encoding");

          if(!$content_length && !$encoding){
            $this->read_body_until_null_byte();
          }
           if($content_length !== null && $content_length > 0){
            $this->read_body($content_length);
          }
          if($encoding !== null && $encoding === "chunked")
          {
            $this->chunk_read();
          }
            if(!is_null($content_encoding) && ($content_encoding === "gzip"))
               {
                $this->decode_gzip_response();
               }
        }

        /**
         * read the response body
         * 
         */

         private function read_body($length)
         {
          $body_length = $length;
          $data = "";
          while($body_length > 0){

            $data_read = $this->connection->read($length);
            $body_length -= strlen($data_read);
            $data .= $data_read;
          }
          $this->response_text = $data;
         }
      
         /**
          * use for reading the body when
          * there is neither transfer-encoding nor content-length
          * in the request header
          *
          */

          private function read_body_until_null_byte()
          {
            $data = '';
            while($buffer = $this->connection->read(1024)){
              $data .= $buffer;
              if(rtrim($buffer) == ''){
                break;
              }
            }
            $this->response_text = $data;

          }

          /**
           * read the body of the response in chunk
           */

           private function chunk_read()
           {
            $data = "";
               while((rtrim($start_marker = $this->connection->read_line()) !== "")){
                      $chunk_left = hexdec($start_marker);
                       if($chunk_left == 0){
                          break;
                                }
                        while($chunk_left > 0){
                           $buffer = $this->connection->read($chunk_left);
                           $chunk_left -= strlen($buffer);
                           $data .= $buffer;
                             }
                       $start_marker = $this->connection->read_line();
                        $start_marker = rtrim($start_marker);
                                                 }
                     $this->response_text = $data;
         
           }

        /**
         * decode a gzip response
         * 
         */
         private function decode_gzip_response()
         {
          $this->response_text = gzdecode($this->response_text);
         }

        /**
         * return the status code
         * @return int
         */
        public function status_code()
        {
          return $this->status_code;
        } 

        /**
         * 
         * return the status text
         * 
         * @return string
         */

         public function status_text()
         {
            return $this->status_text;
         }

         /**
          * check if the request is a redirect
          * 
          * 
          */

          private function check_redirect()
          {
              if(($this->status_code === self::redirect_permanent) || ($this->status_code === self::redirect_temporary))
                {
                  $path = $this->response_parser->get('location');
                  if($this->is_absolute_path($path)){
                        $url = $path;
                  } else {
                   $url = $this->get_full_path_from_relative_path($path);
                  }
                  if($this->redirected_numbers < self::max_redirect){
                    if(!$this->should_shutup){ $this->talk("redirecting..."); }
                    $this->redirect($url);
                  } else {
                    throw new Exception("redirected too many times");
                    exit;
                  }
                
                } 
               
          }

          /**
           * redirect the request
           * 
           */

           private function redirect(string $url)
           {
              $this->redirected_numbers++;
              $this->open($url , 'get');
              $this->send();
             
            }
        


          /**
           * get the response 
           * @return string
           */
          
           public function get_response_text()
           {
            return $this->response_text;
           }

           /**
            *  return a parse json response
            *  
            */

            public function get_json_response()
            {
               return json_decode($this->response_text);
            }

           public function get_response_header(){
            return $this->response_parser->get_response_headers();
           }
 }