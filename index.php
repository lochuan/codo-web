<?php
session_start();
if(isset($_SESSION['login'])){
    header("Location: home.php");
    exit;
}
require_once('views/index.html');
?>
