<?php

include("./Handlers/SQL_CRUD.php");
include("./Handlers/Connection.php");
use Handlers\Connection;
use Handlers\SQL_CRUD;

$PDO = new Connection("localhost", "root", "1234", "teste");

$crud = new SQL_CRUD();
$response = $crud->SQL_select("pessoas");

var_dump($response);
// var_dump($PDO->executeSQL($response['SQL'], ...$response['VALUES']));