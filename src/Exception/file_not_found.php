<?php
  namespace src\Exception;

use Exception;

  class file_not_found extends Exception {
        public function message(){
              return   "the file " . $this->getMessage() . " does not exist";
        }
  }