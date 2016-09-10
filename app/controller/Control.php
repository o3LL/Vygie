<?php


include_once('app/models/DataModel.php');
$dm = new DataModel();

//Check login et vérif cookie, conditionne la suite
//$user = $dr->checkAuth($_REQUEST, $_COOKIE);
$data = $dm->execute($method, $arg);
 ?>