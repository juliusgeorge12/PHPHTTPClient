<?php
  namespace src\Exception;

use Exception;

  class max_file_exceeded extends Exception {
        public function message(){
              return   "the maximum file szie of 50MB exceeded";
        }
  }