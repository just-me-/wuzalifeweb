<?php

// get data file
$config = parse_ini_file('conf/data.ini', TRUE);

$debug    = $config['debug'];
$version  = $config['version'];
$username = $config['username'];
$password = $config['password'];
$file 	  = $config['file'];

// cookie instand of short session
$accesskey = $config['loginaccesskey'];

$cards = array(
  array('icon' => 'account_box', 	'front' => 'Zähne am Mittag putzen', 	'back' => 'Check!', 'disabled' => 1),
  array('icon' => 'account_box', 	'front' => 'Zähne am Abend putzen', 	'back' => 'Check!'),
  array('icon' => 'face', 			'front' => 'Gesicht am Abend waschen', 	'back' => 'Check!'),
  array('icon' => 'directions_run',	'front' => 'Heute Sport getrieben', 	'back' => 'Check!', 'disabled' => 1),
  array('icon' => 'battery_full', 	'front' => 'Heute gesund gegessen?', 	'back' => 'Check!'),
  array('icon' => 'pets', 			'front' => 'Heute keine Nägel gekaut', 	'back' => 'Check!')
);

session_start();

// check login if given
// if correct - start access session 
if($_POST['username'] && $_POST['password']){
  if( ($_POST['username'] == $username) && ($_POST['password'] == $password) ) {
	// use a timespan of 5 week
	$remembering_timespan = time() + 5 * 7 * 24 * 60 * 60;
	setcookie('access', $accesskey, $remembering_timespan);
	$loggedin = 1;
  } else {
	setcookie('access', '', time()-3600);
  }
}

// show data/backend or login
$main = '';
if($_COOKIE['access'] == $accesskey || $loggedin == 1){
  
  // my personal generated request link
  // callback wuza.ch/fitbitm
  // access to: profile and weight
  // time: 31536000 (1 year)
  $request1 = 'https://www.fitbit.com/oauth2/authorize?response_type=code&client_id='.$config['clientid'].
              '&redirect_uri=https%3A%2F%2Fwuza.ch%2Ffitbitm%2Fwrite.php'.
              '&scope=profile%20weight'.
              '&expires_in=31536000';
			  
  // automatic write action if possible
  $homepage = file_get_contents($request1);

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
  <h1>'.$title." <a class=\"fitbitcode\" href=\"$request1\"><i class=\"material-icons\">update</i></a>".'</h1>
  <p>'.$body.'</p>
  ';
  
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
	
	<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
	<link rel="icon" type="image/png" href="favicon/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="favicon/favicon-16x16.png" sizes="16x16">
	<link rel="manifest" href="favicon/manifest.json">
	<link rel="mask-icon" href="favicon/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="favicon/favicon.ico">
	<meta name="msapplication-config" content="favicon/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">
    
    <title>WUZAlife</title>
    
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="data/fitbit.css?version=%22<?php echo $version; ?>%22" rel="stylesheet">
  </head>
  
  <body>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="data/jquery.flip.js"></script>
		
	<div class="main">
      <?php echo $main; ?>
	  
	  <?php
	  foreach($cards as $i => $card){
	  ?>
		
		<div id="<?php echo $i ?>" class="card <?php echo ($card['disabled'] ? 'disabled' : '') ?>"> 
		  <div class="front"> 
			<i class="material-icons"><?php echo $card['icon'] ?></i>
			<span><?php echo $card['front'] ?></span>
		  </div> 
		  <div class="back">
			<i class="material-icons">sentiment_very_satisfied</i>
			<span><?php echo $card['back'] ?></span>
		  </div> 
		</div>
		
	  <?php
	  }
	  ?>
	</div>
	
    <script src="data/fitbit.js?version=%22<?php echo $version; ?>%22"></script>
  </body>
</html>


