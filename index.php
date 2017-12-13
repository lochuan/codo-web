<?php
session_start();
if(!empty($_SESSION['login'])){
    header("Location: home.php");
    exit;
}
require_once('views/index.html');
?>
