<?php

include_once('model/DataReport.class.php');
$dr = new DataReport();

//Check login et vérif cookie, conditionne la suite
$user = $dr->checkAuth($_REQUEST, $_COOKIE);
$data = $dr->execute($method, $arg);

echo($data);

 ?>