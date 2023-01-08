<?php
 namespace src\HTTPClient;

use src\Blob\Blob;

  /**
   * help you builds your request body
   */
 class formData {
   
    /**
     * holds the formData
     * @var array
     */
    private $store_data = [];
    /**
     * append a text field to the formData
     * @param string name
     * @param any $value
     */
    public function append(string $name ,  $value){
         array_push($this->store_data , [$name , $value]);
         return $this;
    }
    /**
     * append a file to the formData
     * @param string $name
     * @param string $url the url of the file to upload
     */
    public function append_file(string $name , string $url){
        $this->append($name , new Blob($url));
        return $this;
   }
  
   /**
    * returns the formData
    */
    public function get(){
        return $this->store_data;
    }
 }