<?php
function post_request($request, $link, $method)
{
    $request_json = json_encode($request);

    $c = curl_init();
    $link = $link;

    switch($method)
    {
        case "post":
                curl_setopt($c, CURLOPT_URL, $link);
                curl_setopt($c, CURLOPT_POST, TRUE);
                curl_setopt($c, CURLOPT_POSTFIELDS, $request_json);
              
                // Set the result output to be a string.
                curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
                break;
        case "put":
                curl_setopt($c, CURLOPT_URL, $link);
                curl_setopt($c, CURLOPT_PUT, TRUE);
                $temp = tmpfile();
                fwrite($temp, $request_json);
                fseek($temp, 0);
                curl_setopt($c, CURLOPT_INFILE, $temp);
                curl_setopt($c, CURLOPT_INFILESIZE, strlen($request_json));
                break;
        case "get":
                if ( ! empty($request))
                {
                    $link .= '?' . http_build_query($request);
                }
                curl_setopt($c, CURLOPT_URL, $link);
                break;

    }

    $headers = array(
        'X_USERNAME:pratik',
        'X_PASSWORD:Support123!@#',
        'X_ACCOUNT_CODE:lop_solutions',
    );

    curl_setopt($c, CURLOPT_HTTPHEADER, array_merge(array(
        // Overcoming POST size larger than 1k wierd behaviour
        // @link  http://www.php.net/manual/en/function.curl-setopt.php#82418
        'Expect:'), $headers
    ));

    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $response_raw = curl_exec($c);
    
    if ($method == 'put')
    {
        fclose($temp); // this removes the file
    }

    $errno =  curl_errno ( $c );
    $result = json_decode($response_raw);

    // if (strpos($link, 'api/list_fields/') === FALSE)
    // {
    //     echo "link [$method] :<b>$link</b><br/>";
    //     echo "json request: ".json_encode($request)."<br/>";
    //     echo "-----------------------------------<br/>";
    //     echo "json reponse: " .$response_raw;
    // }

    if (empty($errno))
    {
        if ( ! empty($result->payload->errors))
        {
            $errno = 500;
        }
    }
    if ( ! empty($errno))
    {
    header("HTTP/1.0 " . $errno);
    }
    return $response_raw;
}
?>