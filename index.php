<?php
session_start();
if(isset($_SESSION['login']) && $_SESSION['login'] == true){
    header("Location: home.php");
    exit;
}
require_once('views/index.html');
?>
