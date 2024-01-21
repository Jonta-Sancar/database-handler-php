<?php

include_once("./Handlers/Connection.php");
include_once("./Handlers/SQL_CRUD.php");
use Handlers\Connection;
use Handlers\SQL_CRUD;

$PDO = new Connection("localhost", "root", "1234", "teste");

$crud = new SQL_CRUD("localhost", "root", "1234", "teste");


echo "<h3>INSERT</h3>";

// INSERT
  $data_insert = [
    "id" => 20,
    "nome" => "Claris Pector",
    "profissao" => "Desenvolvedor",
    "sexo" => 'M'
  ];

  $response = $crud->execInsert("pessoas", $data_insert);
  var_dump($response);
// INSERT \ END

echo "<br><br>";
echo "<h3>SELECT</h3>";

// SELECT 
  $response = $crud->execSelect("pessoas");
  var_dump($response);

  echo "<br><br>";

  $response = $crud->execSelect("pessoas", null, null, null, 'id', '>');
  var_dump($response);
  
  echo "<br><br>";

  $tables = [
    " pessoas P ",
    " tipo_has_pessoas THP ON THP.id_pessoa=P.id ",
    " tipo_pessoas TP ON TP.id=THP.id_tipo "
  ];

  $columns = [
    " COUNT(*) repeticao ",
    " P.profissao ",
    " TP.nome nome_tipo "
  ];

  $conditions = [
    ['P.id', 20],
    " P.profissao LIKE '%pedreiro%' "
  ];

  $group_by = [
    " P.profissao ",
    " THP.id_tipo "
  ];

  $response = $crud->execSelect($tables, $columns, $conditions, $group_by, 'repeticao', '>', 0, 100);
  var_dump($response);
// SELECT \ END

echo "<br><br>";
echo "<h3>UPDATE</h3>";

// UPDATE 
  $data_update = [
    "profissao" => "Pedreiro de Código"
  ];

  $conditions = [
    ['id', 20]
  ];

  $response = $crud->execUpdate("pessoas", $data_update, $conditions);
  var_dump($response);
// UPDATE \ END

echo "<br><br>";
echo "<h3>DELETE</h3>";

// DELETE 
  $response = $crud->execDelete("pessoas", [["id", 20]]);
  var_dump($response);
// DELETE \ END

// var_dump($PDO->executeSQL($response['SQL'], ...$response['VALUES']));