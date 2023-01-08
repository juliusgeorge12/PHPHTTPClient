<?php
 /**
 * _______________________________________
 * 
 * @author julius george
 * this  file demonstrate how
 * to use the phpclient
 * to upload file 
 * _________________________________________
 */

use src\HTTPClient\HttpClient;

use src\HTTPClient\formData;

 /* __________________________________________
   start by including the index.php file
    the index.php file would bootstrap
   the remaining files
 ____________________________________________
 */

include  dirname(__DIR__) . "\\index.php" ;
 
//create the httclient object

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
  $http->enable_file_upload();
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
 // in this file we are going to be demonstrating the uploading file using
 //the httpclient
  
 //to upload we have to open a post request
 //the open method return the object enabling us to call
 //another method on it e.g $http->open(url,method)->send()
 //that way we don't have to do
 //$http->open(url,method);
 //$http->send()

  try {

  $http->open('http://localhost/home.php' , 'post');

  //send the request passing a 2d array of the form
  //[[name1,value1],[name2,value2],...]
  //to make things easier you can just create a new form
  //data and append the data to it and call the get method of
  //the formData object to get the form data 
  //and pass this data to the send method
  //instead of manually passing [[name1,value1],[name2,value2],...]
  //to the send method
  //we are going to be using the formData here
  //use the formData class you have to add
  //use src\HTTPClient\formData;
  //to the top
  //if your text editor is smart it will added it for you
  //if you don't want to add it to the top then instead of doing
  // $form_data = new formData;
  //do this instead
  //$form_data = new src\HTTPClient\formData;
 $form_data = new formData; 
  //now append the data you  want to send by doing
  //$form_data->append(name,value)
  //to append a file you have to pass in 
  // a blob object as the value e.g
  //$form_data->append('my_pic', new Blob(the url of the file))
  // or you can do
  //$form_data->append_file('my_pic' , the url of the file)
  // i can call append or append_file on append or append_file
  //because these two methods return the object
  // you can see that, that is what i am doing below 
  $form_data->append('fname' , 'julius')
  ->append('lname' , 'George')
  ->append('age' , 19)
  ->append_file('my_pic' , __DIR__ . "\images\glass.png");
  //to upload more than one file set all their names to the same name and add [] to the name
  //e.g ->append_file('my_pic[]' , filepath1)
  //->append_file('my_pic[]' , filepath2)
  //remember i told you can use append() to append files too
  //->append('my_pic[]' , new Blob(filepath3))
  //get the array to be passed into the send method 
  //using the get method of the formData object
  $data = $form_data->get();
 
  $http->send($data);
  //get the response status code

  $status_code = $http->status_code();
  if($status_code == 200) {
  $response = $http->get_response_text();
 //if you are expecting a json response
 //and you want it to be parsed you can use
 // get_json_response method instead this method
 // return a parsed json object e.g
 // $response = $http->get_json_response()
 //if you want to get the raw http response sent
 //by the server use the get_raw_response method
 //e.g $raw_response = $http->get_raw_response()
 //if you are faking the request and just want 
 //to see the raw http request do
 // $raw_req = $http->get_raw_request()
  echo $response; 
  } else {
    echo $http->status_code() . " " . $http->status_text();
  }


 

  } catch(Exception $e)
  {
    echo $e->getMessage();
  }