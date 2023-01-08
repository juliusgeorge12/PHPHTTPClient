<?php
  namespace src\HTTPClient;

use Exception;

 /**
  * Author julius George
  * * the http response parser class
  *
  */
  class response_parser {
  
  /** 
   * the delimiter for spliting the header and body
   * of the response
   * @var string
   */
   
   const http_component_separator = "\r\n\r\n";
   
  /**
   * the delimiter for splitting lines
   * 
   * @var string
   */

   const line_spliter = "\r\n";

  /**
   * holds the response header
   * @var string
   */
   private $response_header = null;
  /**
   * holds the response body
   * 
   * @var string
   */
   private $response_body = null;

   /**
    * holds the raw http response 
    * 
    * @var string
    */
    public $raw_response = null;
    /**
     * holds the response status code
     * @var string
     */

    private $response_code = null;

    /**
     * holds the http version sent by the server
     * @var string
     */

    private $version = null;

    /**
     * hold the response status text
     * 
     * @var string
     */

     private $status_text = null;

     /**
      * holds the header lines
      *
      * @var array 
      */

      private $header_lines = [];

     /**
      * holds the body lines
      *
      * @var array 
      */

      private $body_lines = [];
   /**
    * holds the parsed response headers
    * 
    * @var array 
    */
    private $parsed_headers = [];

    public function __construct()
    {  
        
    }

    /**
     * parse the http header
     * 
     */
    public function parse_header(string $http_res_header){
        $this->response_header = $http_res_header;
        $this->parse_header_status();
        $this->parse_headers();
    }
    

    /**
     * split the lines
     * @param string $data
     * 
     * @return array
     */

     private function split_lines(string $data)
     {
       return explode(self::line_spliter , $data);
     }
    
     /**
      * parse the first line and return the response code and text
      *
      */
      
      private function parse_header_status()
      {
         $this->header_lines = $this->split_lines($this->response_header);
         $first_line = $this->header_lines[0];
         $header_data = explode(" "  , $first_line , 4);
         if(count($header_data) < 4){
          throw new Exception("unkown http header: " . $this->response_header);
          exit;
         }
         array_shift($header_data);
         [$this->version , $this->response_code , $this->status_text] = $header_data;
        array_shift($this->header_lines);
         array_pop($this->header_lines);
      }

    
    /**
     * parse the response header and return each header value pair
     * 
     */

     private function parse_headers()
     {
      $lines = $this->header_lines;
      
        foreach($lines as $line){
              $parsed_line = explode(": " , $line);
               if(strtolower($parsed_line[0]) == "set-cookie"){
              $this->parsed_headers['set-cookie'][] = $parsed_line[1];
            } else {
             $this->parsed_headers[strtolower($parsed_line[0])] = $parsed_line[1];
            }
          }
     
     }
    
    
    /**
     * return the http status code
     * 
     * @return int
     */
     public function get_status_code()
     {
        return intval($this->response_code);
     }

     /**
     * return the http status text
     * 
     * @return string
     */
    public function get_status_text()
    {
       return $this->status_text;
    }

    /**
     * return the headers
     * 
     * @return array
     * 
     */

     public function get_response_headers()
     {
       return $this->parsed_headers;
     }

     /**
      * return the value of the header passed in
      * 
      * @param string $header
      * @return string
      */
     
      public function get(string $header)
      {
        return isset($this->parsed_headers[strtolower($header)]) ? $this->parsed_headers[strtolower($header)] : null;
        
      }

    /**
     * 
     * return the response text
     * 
     * @return array
     * 
     */

    public function get_response()
    {
      return $this->response_body;
    }
  } 