<?php
ob_start();

# server name
$sName = "srv1327.hstgr.io";
# user name
$uName = "u306273205_QuickChat";
# password
$pass = "Weblux@@1122";

# database name
$db_name = "u306273205_QuickChat";

#creating database connection
try {
  $conn = new PDO(
    "mysql:host=$sName;dbname=$db_name",
    $uName,
    $pass
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo "Connection failed : " . $e->getMessage();
}
