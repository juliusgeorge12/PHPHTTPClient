<?php
  namespace src\Blob;

use Exception;

  /**
   * A blob object
   * 
   */

  class Blob implements Blob_interface {

     /**
      * the url of the blob
      *
      *  @var string
      */

      private $url;

      /**
       * the size of the blob
       * 
       * @var int
       */

       private $size;

       /**
        * the mime type of the blob
        *  
        * @var string
        */

        private $mime_type;

        /**
         * hold the file status
         * 
         * @var bool
         */

         private $file_exist = false;

        /**
         * the file path to the blob
         * 
         * @param string $path
         */
        public function __construct(string $path)
        {
               $path = str_replace("\\" , '/' , $path);
                $this->url = $path;
                $this->initialize();
        }

        /**
         * calls the private methods
         * that store the size and mime type
         */

         private function initialize()
         {
                // cehck if the file exists
                $this->is_valid();

                //store the mime type
                $this->store_mime_type();

                // store the file size
                $this->read_size();
         }

        /**
         * read the size of the blob and store it
         * 
         */

         private function read_size()
         {
          $this->size = filesize($this->url);
         }

         /**
          * store the mime type 
          * of the blob
          *
          */

          private function store_mime_type()
          {
                $this->mime_type = mime_content_type($this->url);
                
          }

         /**
          * check if the file really exists
          * 
          * @return bool
          */

          private function is_valid()
          {
                if(file_exists($this->url))
                {
                        $this->file_exist = true;
                }
                else 
                {
                    $this->file_exist = false;
                    throw new Exception("the file " . $this->url . " does not exists");
                }
          }

          /**
           * 
           * get the mime type
           * 
           * @return string
           */

          public function get_mime(): string
          {
                return $this->mime_type;
          }

          /**
           * return the size of the blob
           * 
           * @return int
           */
         
           public function get_size(): int
           {
                return $this->size;
           }

           /**
            * return the location of the blob 
            *
            * @return string
            */

            public function get_url(): string
            {
                return $this->url;
            }

  }