<?php
session_start();
if(isset($_SESSION['login']) && $_SESSION['login'] == false){
    header("Location: index.php");
    exit;
}
require_once('views/home.html');
?>
