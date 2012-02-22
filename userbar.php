<?php
      if (empty($_SESSION['username'])){
      echo $steamloginbutton;
      }
      else {
      echo '<img src="' . $_SESSION['avatar'] . '" alt="avatar"/> ' . $_SESSION['username'] . ' || <a href="/stats" target="_self">stats</a> - <a href="/settings" target="_self">settings</a> - <a href="/logout.php" target="_self">logout</a>';
      }
?>
