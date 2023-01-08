<?php 
 namespace src\HTTPClient;

use src\Exception\file_not_found;

  /**
   *  a file reader for reading file
   * 
   */
 class fileReader {
        /**
         * hold the file data
         *  @var string|null $file_data 
         */
       private $file_data = null;

         /**
         * the file path
         * 
         * @var string
         */

        private $path = null;

         public function __construct(string $path)
         {
               if(!file_exists($path)){
                throw new file_not_found($path);
               } else {
                $this->path = $path;
               }
               return $this;
         }
        
         /**
          * gets the file type
          * @return string 
          */
         public function get_mime_type()
         {
                return mime_content_type($this->path);
                return $this;
         }

         /**
          * get the size of the file
          * 
          * @return int
          */

          public function get_size()
          {
            return filesize($this->path);
            return $this;
          }
          /**
           * reads the file
           * 
           */

           public function read(){
                $this->file_data = file_get_contents($this->path);
                return $this;
           }

         /**
          * returns the file data
          * @return string
          */

          public function get()
          {
                return $this->file_data;
          }

 }