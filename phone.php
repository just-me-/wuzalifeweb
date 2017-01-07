<?php

// get data file
$config = parse_ini_file('conf/data.ini', TRUE);

$debug    = $config['debug'];
$version  = $config['version'];
$username = $config['username'];
$password = $config['password'];
$file 	  = $config['file'];

session_start();

// check login if given
// if correct - start access session 
if($_POST['username'] && $_POST['password']){
  if( ($_POST['username'] == $username) && ($_POST['password'] == $password) ) {
    $_SESSION['access'] = 1; 
  }
}

// show data/backend or login
$main = ''; 
if($_SESSION['access'] == 1){
  
  // my personal generated request link
  // callback wuza.ch/fitbitm
  // access to: profile and weight
  // time: 31536000 (1 year)
  $request1 = 'https://www.fitbit.com/oauth2/authorize?response_type=code&client_id='.$config['clientid'].
              '&redirect_uri=https%3A%2F%2Fwuza.ch%2Ffitbitm%2Fwrite.php'.
              '&scope=profile%20weight'.
              '&expires_in=31536000';

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
  
	$feedback = ($y_fat_kg > $t_fat_kg) ? '<i class="material-icons">sentiment_very_satisfied</i> <i class="material-icons">thumb_up</i>' : '<i class="material-icons">sentiment_very_dissatisfied</i> <i class="material-icons">thumb_down</i>';
  
	$body = "<b>".number_format(($y_fat_kg - $t_fat_kg), 2)." Fett verloren </b><br>";
	$body .= "Heute: &nbsp;&nbsp;&nbsp;".number_format($t_weight, 1)." kg und ".number_format($t_fat, 1)."% Fett.<br>";
	$body .= " Gestern: ".number_format($y_weight, 1)." kg und ".number_format($y_fat, 1)."% Fett.";
	$title = $feedback." ".number_format($t_weight, 1)."kg";
  
  
	// there is a problem
	if($t_weight == 0 && $y_weight == 0){
	  $title = "No Fitbit Data";
	  $body  = "Check your script";
	}
  } else {
	$main .= "<p>No tooken!</p>";
  }
  
  $main .= '
  <h1>'.$title.'</h1>
  <p>'.$body.'</p>
  ';
  $main .= "<a class=\"fitbitcode\" href=\"$request1\"><i class=\"material-icons\">update</i></a>";
  
} else {
  // show login page
  $main .= '
  <form action="phone.php" method="post" class="login">
    <input name="username" type="text" placeholder="Benutzername">
    <input name="password" type="password" placeholder="Passwort">
    <button type="submit" name="login">Anmelden</button>
  </form>
  ';
}

?>


<!doctype html>
<html lang="de">
  
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0">
    
    <title>Fitbit</title>
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="data/fitbit.css?version=%22<?php echo $version; ?>%22" rel="stylesheet">
  </head>
  
  <body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		
	<div class="main">
      <?php echo $main; ?>
	</div>
	
    <script src="data/fitbit.js?version=%22<?php echo $version; ?>%22"></script>
  </body>
</html>


