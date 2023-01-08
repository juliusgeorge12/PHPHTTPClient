<?php
  namespace src\Exception;

use Exception;

  class max_upload_exceeded extends Exception {
        public function message(){
              return   "the maximum number of files that can be uploaded via a single request is 6";
        }
  }