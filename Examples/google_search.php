<?php
/**
 * _______________________________________
 * 
 * @author julius george
 * this is file demonstrate how
 * to use the phpclient by simply 
 * use it to fetch search result
 * from google
 * _________________________________________
 */


 /* __________________________________________
   start by including the index.php file
    the index.php file would bootstrap
   the remaining files
 ____________________________________________
 */

 include  dirname(__DIR__) . "\\index.php" ;

use src\HTTPClient\HttpClient;

 $http = new HttpClient;
  //you can turn on debuging by saying $http->enable_debug(debug_level , debug_out)
 // the debug level is from 0 - 4, these are what each debug level mean
 //0 is no debug, turn debug this is set by default
 //1 shows client -> server messages
 //2 shows client -> server and server -> client messages
 //3 shows client -> server , server -> client and connection messages
 //4 lowlevel debug, shows all messages 

 //the debug output can take
 // echo, meaning the debug messages would be echo in plain text format this is appropriate
 //for command line/ terminal prompt testing , and this is set by default
 // html, the debug messages would be in html format, this is good when testing via browser
 // you might want to defined your own function to output your messages the way you want and so
 // you can pass in a function expecting these two parameters which are
 // $message and $level , it can be any name but the first parameter is the debug message
 // and the second parameter is the debug level 
 // e.g function($msg , $lev){
 //      echo "the debug message is " . $msg . " the debug level is "  . $lev; 
 // }
 //if you want to send cookie with your request first enable cookie
 // using $http->enable_cookie() this actually return the current object so as a result you can
 // another method it e.g $http->enable_cookie()->send_cookie(["name1"=>"value1","name2"=>"value2" ,...])
 // to send your cookie call the send_cookie method and pass in an associaive array of name => key pair
 //note you should call this method after calling enable_cookie() method and before calling the send() method
 // so this is how how to send your cookie
 // $http->send_cookie(["name1"=>"value1","name2"=>"value2" ,...])
 // to upload file you have to enable file upload using
 // enable_file_upload() method e.g 
 // $http->enable_file_upload()
 //now let's say you just want to inspect the raw http request 
 //and you don't want the request to be sent you can fake the 
 //request by calling the fake method e.g
 //$http->fake()
 //now after doing all these settings you can open a request and send your request and fecth the response
 // you should wrapp the remaining part (opening a request , sending the request , and fetching the response)
 //inside a try block so that all exception can be caught
 //this is because instead of echo an error done by the user an exception is thrown
 //e.g when the url of the conatin a protocol/scheme that is not http or https
 // i.e file:// or ftp://
 //another instance where exception is thrown is when the maximum numbers of files that can be uploaded
 //per request is exceed the number is usually 6.
 // an exception is also thrown when a file size exceed the maximum file size accepted 
 //the maximum file size allowed is 200MB
 // in this file we are going to be demonstrating sending get
 //request by sending a get request to google to fecth search result
 

 try {
 //open a new request
   $http->silent(false)->open('http://google.com?q=how+to+track+a+phone' , 'get')
  //send the request
  ->send();
  //get the response status code
    $response_code = $http->status_code();
  if($response_code == 200){
 //fecth the response
   $result = $http->get_response_text();
  echo $result;
  } else {
 //get the status text
   $status_text = $http->status_text();
   echo $response_code . " " . $status_text;
  }

 }
  catch(Exception $e){
        echo $e->getMessage();
  }