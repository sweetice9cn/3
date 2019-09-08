<?php

  include 'connect.php';
  include 'header1.php';
  if (isset($_SESSION['uid'])){
      echo '<p style="font-size:120%">Thanks, ' .$_SESSION['uname']. ', for using the Snickr.</p>';
      unset($_SESSION['uid']);
      unset($_SESSION['uname']);
      unset($_SESSION['unickname']);
      unset($_SESSION['uemail']);
      header("refresh:2; url = index.php" );
      exit();
  }


  // Destroy the session.
  session_destroy();

?>
