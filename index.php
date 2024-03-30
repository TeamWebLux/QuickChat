<?php 
ob_start();
ini_set('display_errors', '1');
require "./Router/router.php";
include "./Public/Pages/Chat/app/ajax/getMessage.php";

?>