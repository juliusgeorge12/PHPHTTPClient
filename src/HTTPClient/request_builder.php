<?php
  namespace src\HTTPClient;

use Exception;
use src\Blob\Blob_interface;
use src\Exception\hostException;
use src\Exception\max_file_exceeded;
use src\Exception\unknown_method;

  /**
   * ___________________________________________________
   * this is the http request builder class, this class 
   * contains the methods and functions used in creating 
   * a complete http request boody which is sent to the 
   * server
   * __________________________________________________
   * 
   */
  class request_builder {
     
  /**
   * http version used
   * 
   * @var string
   */
    const http_version = 'HTTP/1.1';
      
  /**
   * user agent
   * 
   * 
   * @var string
   */

    const  user_agent = "JuliusGeorgePHPHttpClient/1.0";

  /**
   * the maximum file size to upload
   * 
   *
   * @var int
   */
  
  /**
   * the maximum size of file to upload per file per request
   * which is 200MB
   * 
   * @var int 
   */
    const max_file_size = 1024 * 1024 * 200;

  /**
   * the maximum number of files to upload per request
   * 
   * @var int
   */

   const max_files = 6;

   /**
    * the content type form for sending post request without a file
    * 
    * @var string
    */
          
   const post_default = "x-www-form-urlencoded";
            
    /**
     * default length for sending post data
     * 
     * @var int 
     *
     */

    const post_max_data = 1024;

    /**
     * the default character set
     * 
     * 
     * @var string
     */

    const default_charset = "utf-80";

    /**
     * unique boundary prefix
     * 
     * @var string
     */
     const uniq_bound_prefix = "---JuliusGeorgeHttpClient";

    /**
     * 
     * the accepted result format
     * 
     * @var string
     */

     const accept = "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*";
     
         
     /**
      * unique boundary for sending multipart/form-data 
      *
      *
      * @var string
      */
 
      private $unique_boundary = null;

     /**
      * the content type for sending form data with file upload
      * 
      * @var null|string
      */

       private $file_upload_data = null;

     /**
      * HTTP line break constant
      * 
      * @var string
      */
        
      const LE = "\r\n";

    /**
     * the accepted request methods
     * 
     * @var array
     */

     const accepted_methods = ["POST" , "GET" , "PUT" , "PATCH" , "DELETE"];

    
      /**
       * sets the character encoding , if not set
       * the default charset (utf-80) would be used
       * 
       * @var string 
       */

      public $charset = null;

      /**
        * sets if multipart encoding should be used for post request
        * 
        * @var bool 
        */
        
        public $is_multipart = false;

         
      /**
       * holds the request body
       * 
       * 
       * @var string
       */
          
       private $body = '';

      /**
       * holds the request
       *
       * @var string 
       */
           
       private $request = '';

      /**
       * holds some of the heads which would always be include
       *  with the request
       * 
       * @var array
       */
          
       const headers = ["Host" , "Connection" , "Accept" , "User-Agent" , "Accept-Encoding" , "Content-Type" , "Content-Length"];
           
      /**
       * holds the request headers
       *
       * @var string
       */
           
        private $request_header = '';

      /**
       * the request method
       * @var string 
       */
         
       private $method = '';
          
     /**
      * 
      * the request url
      * 
      */
          
       private $url;

       /**
        * the http host
        *
        * @var string
        */
          
        private $host = '';
           
        /**
         * sets the file upload content type
         * 
         * 
         * @return void
         */

          private function set_file_upload_content_type(){
                $this->set_unique_boundary();
               $this->file_upload_data= "multipart/form-data;boundary=" . $this->unique_boundary;
               return $this;
              }

          /**
           * sets the unique boundary
           *
           *
           */
               
           private function set_unique_boundary(){
                  $this->unique_boundary = self::uniq_bound_prefix . $this->random_string();
                }
            
          /**
           * return a random string
           * 
           * @return string
           */
            
           private  function random_string(){
                return base64_encode(random_bytes(10));
               }
              
        /**
         * set the host
         * 
         * @return object
         */
            
          public function set_host(string $host){
                  $this->host = $host; 
                  return $this;
                }
              
        /**
         * set method
         * 
         *
         */
               
          public function set_method(string $method){
                  $method = strtoupper($method);
                  if(!in_array($method , self::accepted_methods)){
                    throw new Exception("unknown request method: the method $method is not known");
                  }
                  $this->method = $method;
                  return $this;
                }

        /**
         * set the request url
         */
              
         public function set_url(string $url = "/"){
                  $this->url = $url;
                  return $this;
                }
               
        /**
         * build the request header
         * @param array $headers an associative array of header => value pair
         */
              
          public function build_headers($headers = []){
              
            if(empty($this->host) && empty($this->method)){
                 throw new Exception((new hostException())->message());
                }
            if(empty($this->url)){
                  $this->set_url();
                  }
            $this->request_header .= $this->method . " " . $this->url;
            $this->request_header .= " " . self::http_version . self::LE;
            $this->request_header .=  self::headers[0] . ': ' . $this->host . self::LE;
            $this->request_header .= self::headers[1] . ': Keep-alive' . self::LE;
            if((!$this->is_multipart) && $this->method === "POST"){
               $this->request_header .= self::headers[5] . ': ' . self::post_default . self::LE;
               $this->request_header .= self::headers[6] . ': ' . $this->get_body_len() . self::LE;
             }
            if($this->is_multipart && $this->method === "POST"){
             $this->request_header .= self::headers[6] . ': ' . $this->file_upload_data . self::LE;
             $this->request_header .= self::headers[5] . ': ' . $this->get_body_len() . self::LE;
           }
            $this->request_header .= self::headers[2];
            $this->request_header .= ': ' . self::accept . self::LE;
            $this->request_header .= self::headers[3] . ': ' . self::user_agent . self::LE;
            $this->request_header .= self::headers[4] . ': ' . 'gzip , deflate' . self::LE;
            if(!empty($headers)){
             foreach($headers as $header=>$value){
              $this->request_header .= $header . ': ' . $value . self::LE;
             }
             }
            $this->request_header .= self::LE;
                return $this;
               }
        /**
         * get the length of the request body
         * 
         * @return int
         */

         private function get_body_len(){
          return strlen($this->body);
         }

        /**
         * builds the request body
         * 
         * @param array $data an associative containing a key => value pair
         */
         public function build_body($param = []){
         if(!empty($param)){
          
          if(!($this->is_multipart && ($this->method == 'POST'))){
              $query_string = [];
              foreach($param as $query_param){
                $query_string[$query_param[0]] = $query_param[1];
              }
              $data = http_build_query($query_string);
            } 
          else {
            $this->set_file_upload_content_type();
            $number_of_files = 0;
            $boundary = "--" . $this->unique_boundary;
            $multipart_header = "Content-Disposition: form-data;";
            foreach($param as $post_data){
              $value = $post_data[1];
              if($value instanceof Blob_interface){
                $number_of_files++;
                       }
                 }
           if($number_of_files > self::max_files){
            throw new Exception((new max_file_exceeded())->message());
             }
           $data = "";
           foreach($param as $post_data){
            $value = $post_data[1];
            $name = $post_data[0];
            if($value instanceof Blob_interface){
               if($value->get_size() > self::max_file_size)
               {
                 throw new Exception((new max_file_exceeded())->message());
                }
              $file = new fileReader($value->get_url());
              $data .= $boundary . self::LE;
              $data .= $multipart_header . ' name="' . $name . '"; filename="';
              $data .= $value->get_url() . '"' . self::LE;
              $data .= "Content-Type: " . $value->get_mime() . self::LE . self::LE;
              $data .= $file->read()->get() . self::LE;
            } else {
            $data .= $boundary . self::LE;
            $data .= $multipart_header . ' name="' . $name . '"' . self::LE . self::LE;
            $data .= $value . self::LE;
            }
            }
           $data .= $boundary;
          }
         $this->body .= $data;
         }
          return $this;
         }

        /**
         * returns the full http request
         * 
         */
          public function get(){
            if($this->method !== "GET"){
            $this->request = $this->request_header . $this->body;
            } else {
              $this->request = $this->request_header;
            }
            $this->request_header = '';
            $this->body = '';
            return $this->request;
          }

  }