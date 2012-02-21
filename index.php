<?php include 'steambutton.php'; ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
	<title>Team Fortress 2 Pickup</title>
	<link rel="stylesheet" type="text/css" href="style.css" media="screen" />
</head>

<body>
<div id="wrapper">
	<div id="header">
		<div id="welcome">
<div id="logo"></div>
		</div>
		<div id="steam">
		<?php 
		if (empty($_COOKIE['username'])){
      echo $steamloginbutton;
		}
		else {
      echo '<img src="' . $_COOKIE['avatar'] . '" alt="avatar"/> ' . $_COOKIE['username'] . ' || <a href="/stats" target="_self">stats</a> - <a href="/settings" target="_self">settings</a> - <a href="/logout.php" target="_self">logout</a>';
		}
		?>
		</div>
	</div>
	<div id="middle">
		<div id="left">
			<div id="news">
			2.19.2012 - haxing noobs
			</div>
			<div id="chat">
			falcro: i'm gey
			</div>
		</div>
		<div id="right">
			<div class="pug">
			6v6 Badlands
			<br />cp_badlands
			<br /><div class="flag"><img src="img/us.png" alt="us flag"/> US</div>
			15/18
			</div>
			<div class="pug">
			highlander lulz
			<br />cp_foundry
         <br /><div class="flag"><img src="img/eu.png" alt="eu flag"/> EU</div>
			</div>
			<div class="pug">
			falcro is faget
		</div>
	</div>
</div>
<div id="footer">
   <div id="copy">
   &copy;2012 TF2Pickup.com
   </div>
</div>
</div>
</body>
</html>
