<?php

// get data file
$config = parse_ini_file('conf/data.ini', TRUE);

if($_GET['code']){
  // keep only numbers and chars a-z
  $code = preg_replace('![^a-z0-9]!', '', $_GET['code']);

  $request2 = 'curl -X POST -H \'Authorization: Basic '.$config['authorization'].'\' -H '.
              '\'Content-Type: application/x-www-form-urlencoded\' -d "clientId='.$config['clientid'].'" -d "grant_type=authorization_code" '.
              '-d "redirect_uri=https%3A%2F%2Fwuza.ch%2Ffitbitm%2Fwrite.php" -d "code='.$code.'" '.
              'https://api.fitbit.com/oauth2/token';
  $result2  = exec($request2);
  $data2    = json_decode($result2, true);
  
  if($config['debug'] == 1){
    print "code = $code ... ";
    var_dump($data2);
  }
  
  $tooken = $data2['access_token'];
  if($tooken){
	file_put_contents($config['file'], $tooken);
  }

}

