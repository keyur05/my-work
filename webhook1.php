<?php
include('functions.php');
error_reporting(E_ALL);
session_start();
$mysqli = mysqli_connect("localhost","root","","lop_survey");

// Check connection
if (mysqli_connect_errno()) 
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}
$list_id = $_GET['listid'];
$fname = $_GET['fname'];
$Email = $_GET['email'];
$sTodayDate = date("m/d/Y H:i:s");
$aResponse = array();
$aResubResponse = array();
$nResponse = '';
$getEncode = json_encode($_GET,true);

function getEmailDetail($list_id , $fname , $Email)
{
  global $mysqli;
  global $getEncode;
  global $sTodayDate;
  if($Email=='')
  {
      echo "email not found";
      exit;
  }
  //email validation code added SI
  
  $sUrl = 'http://www.xverify.com/services/emails/verify/?email='.$Email.'&type=json&apikey=1016031-647E5499&domain=xverify.com';
  $sResumeCurlHandler = curl_init();
  curl_setopt($sResumeCurlHandler, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($sResumeCurlHandler, CURLOPT_FOLLOWLOCATION, TRUE);
  curl_setopt($sResumeCurlHandler, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($sResumeCurlHandler, CURLOPT_URL, $sUrl);
  curl_getinfo($sResumeCurlHandler, CURLINFO_CONTENT_TYPE);
  $sResponseShareData = curl_exec($sResumeCurlHandler);
  $oResponseShareData = json_decode($sResponseShareData);

  //for HTTP Error code 
  $http_status = curl_getinfo($sResumeCurlHandler, CURLINFO_HTTP_CODE);
  curl_close($sResumeCurlHandler);
  if($oResponseShareData->email->status == 'invalid') 
  {  
    $sql = "INSERT INTO tbl_invalid_data (listid, email, firstname, request, response, created_date) VALUES('$list_id','$Email','$fname','$getEncode', '$sResponseShareData',NOW())";
    if (mysqli_query($mysqli,$sql))
    {  
        echo "Record inserted successfully.<br />";
    }
    else
    {
        echo("Error description: " . mysqli_error($mysqli));
    }

    exit;
  }
  if($oResponseShareData->email->status == 'valid')
  {  
    $request = array(
        "email" => $Email
      );

    $contact_response = post_request($request, 'https://api.ongage.net/'.trim($list_id).'/api/contacts/by_email/'.$Email, 'get');
    $aContactData = json_decode($contact_response);
    $sContactStatus = '';
    if($aContactData->metadata->error != true )
    {
      $sContactStatus = $aContactData->payload->ocx_status;
    }

    if($sContactStatus=="Unsubscribed")
    {
      $requestResub = array(
          "emails" =>array($Email),
          "overwrite"=> true, 
          );
      
      $requestResub["change_to"] = "resubscribe";
      $responseRawResub = post_request($requestResub, 'https://api.ongage.net/'.trim($list_id).'/api/v2/contacts/change_status', 'post');
      $rawResub = json_decode($responseRawResub);
      $nResubscribe = $rawResub->payload->success;
            
      $Resubscribe = array(
        "email" => $Email,
        "overwrite" => true
      );
            
      $Resubscribe['fields'] = array(
        "email" => $Email,
        "first_name" => $fname,
        "sunset_policy_date" => $sTodayDate
      );
            
      $responseRaw = post_request($Resubscribe, 'https://api.ongage.net/' . trim($list_id) . '/api/v2/contacts', 'post');    
      $responseRaw = json_decode($responseRaw);
      if($responseRaw->payload->updated == 1)
      {
        $sActionTaken = 'U';
      }
                  
    }
    else
    {
      $request = array(
          "email" => $Email,
          "overwrite" => true
        );
                
      $request['fields'] = array(
        "email" => $Email,
        "first_name" => $fname,
        "sunset_policy_date" => $sTodayDate
      );
      $responseRaw = post_request($request, 'https://api.ongage.net/' . trim($list_id) . '/api/v2/contacts', 'post');    
      $responseRaw = json_decode($responseRaw); 
      if($responseRaw->payload->created == 1)
      {
        $sActionTaken = 'C';
      }
      if($responseRaw->payload->updated == 1)
      {
        $sActionTaken = 'U';
      }
    }
    $sql = "INSERT INTO 
              tbl_valid_data 
              ( 
                listid,
                email,
                firstname,
                request,
                response,
                action_taken,
                created_date 
              )
           VALUES 
              (
                '$list_id',
                '$Email',
                '$fname',
                '$getEncode',
                '$sResponseShareData',
                '$sActionTaken',
                NOW()
              )";
    if (mysqli_query($mysqli,$sql))
    {  
        echo "Record inserted successfully.<br />";
    }
    else
    {
        echo("Error description: " . mysqli_error($mysqli));
    }
    exit;
  }   
}
echo getEmailDetail($list_id , $fname , $Email);