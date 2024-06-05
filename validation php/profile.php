<?php session_start();
  echo "hello " .$_SESSION['user_name']; 
  echo "your email is ".$_SESSION['email'];
  ?>