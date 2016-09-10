<?php

include '../../vendor/autoload.php';


// router init
$router = new AltoRouter();
$router->setBasePath('/alert');


/*
* Route map settings
*/
$router->map('GET|POST','/', function(  ){
    $method = "index";
    $arg['format'] = 'html';
    $arg['view'] = 'index';
    include_once 'Control.php';
});

$router->map('GET|POST','/admin/', function(  ){
    $method = "admin";
    $arg['format'] = 'html';
    $arg['view'] = 'admin';
    include_once 'Control.php';
});

$router->map('POST','/[i:id]/edit', function( $id ){
    $method = "edit";
    include_once 'Control.php';
});

$router->map('POST','/[i:id]/update/', function(  ){
    $method = "update";
    include_once 'Control.php';
});

$router->map('POST','/[i:id]/delete/', function(  ){
    $method = "delete";
    include_once 'Control.php';
});

$router->map('GET|POST','/login/', function( ){
    $method = "login";
    $arg['view'] = 'login';
    $arg['format'] = 'html';
    include_once 'Control.php';
});

$router->map('GET|POST','/logout/', function( ){
    $method = "logout";
    $arg['view'] = 'logout';
    $arg['format'] = 'html';
    include_once 'Control.php';
});

$router->map('GET|POST','/contact/', function(){
    $method = "contact";
    $arg['format'] = 'html';
    $arg['view'] = 'contact';
    include_once 'Control.php';

});

//matching
$match = $router->match();

// do we have a match?
if( $match && is_callable( $match['target'] ) ) {
	call_user_func_array( $match['target'], $match['params'] );
} else {
    $method = "404";
    $arg['format'] = 'html';
    $arg['view'] = '404';
    include_once 'Control.php';
}