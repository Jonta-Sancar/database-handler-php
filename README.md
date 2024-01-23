# database-handler-php
A set of files that deliver database manipulation functionality

## Connection
```php
  use Handlers\Connection;

  $PDO = new Connection(DB_HOST, DB_USER, DB_PASS, DB_NAME);
```

## Connection whith SQL functionality
```php
  use Handlers\SQL_CRUD;

  $crud = new SQL_CRUD(DB_HOST, DB_USER, DB_PASS, DB_NAME);
```

## General SQL CRUD Functionality
### Insert
```php
  $data_insert = [
    "id" => 20,
    "nome" => "Claris Pector",
    "profissao" => "Desenvolvedor",
    "sexo" => 'M'
  ];

  $response = $crud->execInsert("pessoas", $data_insert);
  var_dump($response);
```

### Select
```php
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
```

### Update
```php
  $data_update = [
    "profissao" => "Pedreiro de Código"
  ];

  $conditions = [
    ['id', 20]
  ];

  $response = $crud->execUpdate("pessoas", $data_update, $conditions);
  var_dump($response);
```

### Delete
```php
  $response = $crud->execDelete("pessoas", [["id", 20]]);
  var_dump($response);
```