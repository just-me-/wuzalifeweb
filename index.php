<?php

// get data file
$config = parse_ini_file('conf/data.ini', TRUE);

$debug = $config['debug'];
$file  = $config['file'];

$tooken = file_get_contents($file);
if($tooken){

  $today = date("Y-m-d");

  $result3 = exec('curl -H "Authorization: Bearer '.$tooken.'" '
  .'https://api.fitbit.com/1/user/-/body/log/weight/date/'.$today.'/1d.json');

  $data = json_decode($result3, true);
  if($debug == 1){
    var_dump($data);
  }

  $y_fat = $data['weight'][0]['fat'];
  $y_weight = $data['weight'][0]['weight'];
  $y_fat_kg = $y_weight*$y_fat/100;

  $t_fat = $data['weight'][1]['fat'];
  $t_weight = $data['weight'][1]['weight'];
  $t_fat_kg = $t_weight*$t_fat/100;

  $feedback = ($y_fat_kg > $t_fat_kg) ? ':-)' : ':-(';

  $body = "Heute: ".number_format($t_weight, 1)." kg und ".number_format($t_fat, 1)."% Fett.";
  $body .= " Gestern: ".number_format($y_weight, 1)." kg und ".number_format($y_fat, 1)."% Fett.";
  $title = $feedback." ".number_format($t_weight, 1)."kg (".number_format(($y_fat_kg - $t_fat_kg), 2)." Fett verloren)";


  // there is a problem
  if($t_weight == 0 && $y_weight == 0){
    $title = "No Fitbit Data";
    $body  = "Check your script";
  }

  // send push
  exec('curl --header \'Access-Token: '.$config['pushbullet'].'\' \
       --header \'Content-Type: application/json\' \
       --data-binary \'{"body":"'.$body.'","title":"'.$title.'","type":"note"}\' \
       --request POST \
       https://api.pushbullet.com/v2/pushes');
  
  echo '1';

} else {
  print "No tooken...";
}
