<?php
  namespace src\HTTPClient;


/**
   * _________________________________________________
   *  the url parser parse the url and converts it
   *  into the various parts
   * ___________________________________________________
   */


  class url_parser {
     
    /**
     * the url scheme
     * 
     * @var string
     */
   private $scheme;
   /**
    * the domain name
    *
    * @var string
    */

    private $domain;

    /**
     * url port
     * 
     * @var int
     */
     private $port;

     /**
      * url path
      *
      * @var string
      */
      private $path;
      /**
       * url query string
       * 
       * 
       * @var string
       */
      private $query_string;

      /**
       * the uri
       * 
       * @var string
       */
        private $url;

       
       public function __construct(string $url)
       {
        $this->url = $url;
       }

       /**
        * sets the url scheme
        * @param string $scheme
        */
       private function set_scheme(string $scheme){
        $this->scheme = $scheme;
       }

       /**
        * sets the url domain
        * @param string $domain
        *
        */
        private function set_domain(string $domain){
                $this->domain = $domain;
                   }

        /**
         * set the port
         * 
         * @param int $port
         */
        private function set_port(int $port){
                $this->port = $port;
        }

        /**
         * set the path
         * 
         * @param string $path
         */
        private function set_path(string $path){
                $this->path = $path;
        }

        /**
         * set the query string
         * 
         * @param string $string
         */
        private function set_query_string(string $string){
                $this->query_string = $string;
        }

        public function parse(){
                $url = $this->url;
                $scheme = (parse_url($url , PHP_URL_SCHEME) !== null) ? parse_url($url , PHP_URL_SCHEME) : ' ';
                $this->set_scheme($scheme);
                $host = (parse_url($url , PHP_URL_HOST) !== null) ? parse_url($url , PHP_URL_HOST) : ' ';
                $this->set_domain($host);
                $port = (parse_url($url , PHP_URL_PORT) !== null) ? parse_url($url , PHP_URL_PORT) : 0;
                $this->set_port($port);
                $path = (parse_url($url , PHP_URL_PATH) !== null) ? parse_url($url , PHP_URL_PATH) : '/';
                $this->set_path($path);
                $query = (parse_url($url , PHP_URL_QUERY) !== null) ? parse_url($url , PHP_URL_QUERY) : '';
                $this->set_query_string($query);
                return $this;
        }
       /**
        * return the url scheme
        *
        * @return string
        */
        public function get_scheme(){
                return $this->scheme;
        }
        /**
        * return the host/domain
        *
        * @return string
        */
        public function get_host(){
                return $this->domain;
        }
        /**
        * return the port
        *
        * @return int
        */
        public function get_port(){
                return $this->port;
        }
        /**
        * return the url path
        *
        * @return string
        */
        public function get_path(){
                return $this->path;
        }
        /**
        * return the query string
        *
        * @return string
        */
        public function get_query_string(){
                return $this->query_string;
        }
        

  }