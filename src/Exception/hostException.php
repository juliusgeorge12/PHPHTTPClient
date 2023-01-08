<?php
  namespace src\Exception;

use Exception;

  class hostException extends Exception {
        public function message(){
              return   "host and method can not be empty";
        }
  }