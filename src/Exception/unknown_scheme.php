<?php
  namespace src\Exception;

use Exception;

  class unknown_scheme extends Exception {
        public function message(){
              return   "unknown http scheme";
        }
  }