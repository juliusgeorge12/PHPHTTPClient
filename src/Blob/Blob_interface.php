<?php
 namespace src\Blob;

 interface Blob_interface {

        /**
         * the constructor method
         * 
         * @param string $url the path to  file
         */
        public function __construct(string $url);

        /**
         * get the mime type of the blob
         * 
         * @return string
         */

         public function get_mime(): string;
         
        /**
         * get the size of the blob
         * 
         * @return int
         */

         public function get_size(): int;

         /**
          * return the url of the blob
          *  
          * @return string 
          */

          public function get_url(): string;


 }