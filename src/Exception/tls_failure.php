<?php
  namespace src\Exception;

use Exception;

  class tls_failure extends Exception {
        public function message(){
              return   "tls handshake unsuccessful";
        }
  }