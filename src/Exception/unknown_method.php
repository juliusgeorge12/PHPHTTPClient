<?php
  namespace src\Exception;

use Exception;

  class unknown_method extends Exception {
        public function message(){
              return   "unkown request method";
        }
  }