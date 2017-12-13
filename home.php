<?php
session_start();
if(empty($_SESSION['login'])){
    header("Location: index.php");
    exit;
}
require_once('views/home.html');
?>
