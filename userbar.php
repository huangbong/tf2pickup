<?php
if (isset($_SESSION['username'])) {
?>
    <img src="<?php echo $_SESSION['avatar']; ?>" alt="avatar"/>
    <?php echo $_SESSION['username']; ?> ||
    <a href="/stats" target="_self">stats</a>
  - <a href="/settings" target="_self">settings</a>
  - <a href="/logout.php" target="_self">logout</a>
<?php
}
?>
