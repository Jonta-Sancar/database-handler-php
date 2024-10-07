# database-handler-php - version: 1.8.0
A set of files that deliver database manipulation functionality

## Database
```SQL
  CREATE DATABASE IF NOT EXISTS `test_db_handler` COLLATE utf8mb3_general_ci;

  USE  `test_db_handler`;

  CREATE TABLE IF NOT EXISTS `people` (
    `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ref_db_handler` VARCHAR(25) NOT NULL,
    `name` varchar(100) NOT NULL,
    `job` varchar(100) DEFAULT NULL,
    `hobbie` varchar(100) DEFAULT NULL
  );

  INSERT INTO `test_db_handler`.`people` (`id`, `ref_db_handler`, `name`, `job`, `hobbie`) VALUES ('2', 'ZQABQ-RUVW-BZM45-LB6AT1NY', 'Default User', 'Be', 'Or not to be');

  CREATE TABLE IF NOT EXISTS `type_people` (
    `id` int NOT NULL AUTO_INCREMENT,
    `ref_db_handler` VARCHAR(25) NOT NULL,
    `name` varchar(100) DEFAULT NULL,
    PRIMARY KEY (`id`)
  );

  CREATE TABLE IF NOT EXISTS `type_has_people` (
    `id_type` int DEFAULT NULL,
    `id_person` int DEFAULT NULL,
    CONSTRAINT `type_has_people_ibfk_1` FOREIGN KEY (`id_type`) REFERENCES `type_people` (`id`),
    CONSTRAINT `type_has_people_ibfk_2` FOREIGN KEY (`id_person`) REFERENCES `people` (`id`)
  );
```

## import
```
composer require jonta-sancar/database-handler-php
```

## Test Values
```php
  <?php
    # connect_values.php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'test_db_handler');
```

# Connection
## executeSQL
```php
  <?php
    # connect_use_connection.php
    use Handlers\Connection;

    $PDO = new Connection(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    # execute_sql.php
      // $result = $PDO->executeSQL("INSERT INTO people (`id`, `ref_db_handler`, `name`) VALUES (?, ?, ?)", ...[1, 'SPABQ-LICY-ZZQ28-QK2DF2PA', 'Zerrai Mundo']);
    $result = $PDO->executeSQL("INSERT INTO people (`id`, `ref_db_handler`, `name`) VALUES (?, ?, ?)", 1, 'SPABQ-LICY-ZZQ28-QK2DF2PA', 'Zerrai Mundo');
    echo "<br>";
    var_dump($result);
    echo "<br>";

      // $result = $PDO->executeSQL("DELETE FROM people WHERE `id` = ?", ...[1]);
    $result = $PDO->executeSQL("DELETE FROM people WHERE `id` = ?", 1);
    echo "<br>";
    var_dump($result);
    echo "<br>";
```

# SQL_CRUD
## connection
```php
  <?php
    # connect_use_sql_crud.php
    use Handlers\SQL_CRUD;

    $crud = new SQL_CRUD(DB_HOST, DB_USER, DB_PASS, DB_NAME);
```

## Insert
```php
  <?php
    # crud.php
    $data_insert = [
      "id" => 20,
      "name" => "Claris Pector",
      "job" => "Web Developer",
      "hobbie" => 'soccer'
    ];

    $response = $crud->execInsert("people", $data_insert);
    var_dump($response);
```

## Select
```php
  <?php
    # crud.php
    $response = $crud->execSelect("people");
    var_dump($response);

    echo "<br><br>";

    $response = $crud->execSelect("people", null, null, null, 'id', '>');
    var_dump($response);
    
    echo "<br><br>";
   
    $tables = [
      " people P " => [],
      " type_has_people THP " => [["THP.id_person", "P.id"]],
      " type_people TP " => [["TP.id", "THP.id_type"]]
    ];

    $columns = [
      " COUNT(*) `repeat` ",
      " P.job ",
      " TP.name name_type "
    ];

    $conditions = [
      ['P.id', 20],
      " P.job LIKE '%doctor%' "
    ];

    $group_by = [
      " P.job ",
      " THP.id_type "
    ];

    $response = $crud->execSelect($tables, $columns, $conditions, $group_by, '`repeat`', '>', 0, 100);
    var_dump($response);
```

## Update
```php
  <?php
    # crud.php
    $data_update = [
      "job" => "doctor of codes"
    ];

    $conditions = [
      ['id', 20]
    ];

    $response = $crud->execUpdate("people", $data_update, $conditions);
    var_dump($response);
```

## Delete
```php
  <?php
    # crud.php
    $response = $crud->execDelete("people", [["id", 20]]);
    var_dump($response);
```

## Heritage
### Connection whith SQL functionality
```php
  <?php
    # execute_sql_SQL_CRUD.php
      // $result = $PDO->executeSQL("INSERT INTO people (`id`, `ref_db_handler`, `name`) VALUES (?, ?, ?)", ...[1, 'SPABQ-LICY-ZZQ28-QK2DF2PA', 'Zerrai Mundo']);
    $result = $crud->executeSQL("INSERT INTO people (`id`, `ref_db_handler`, `name`) VALUES (?, ?, ?)", 1, 'SPABQ-LICY-ZZQ28-QK2DF2PA', 'Zerrai Mundo');
    echo "<br>";
    var_dump($result);
    echo "<br>";

      // $result = $PDO->executeSQL("DELETE FROM people WHERE `id` = ?", ...[1]);
    $result = $crud->executeSQL("DELETE FROM people WHERE `id` = ?", 1);
    echo "<br>";
    var_dump($result);
    echo "<br>";
```

# CrudSpecificTable
## connection
```php
  <?php
    # connect_use_sql_crud.php
    use Handlers\CrudSpecificTable;
    
    $columns = [
      'id' => [
        'required'
      ],
      'ref' => [
        'listing',
        'required'
      ],
      'name' => [
        'title' => 'Name Title',
        'registration',
        'listing',
        'editing',
        'required'
      ],
      'job' => [
        'title' => 'Job Title',
        'registration',
        'listing',
        'editing',
        'required'
      ],
      'hobbie' => [
        'title' => 'Hobbie Title',
        'registration',
        'listing',
        'editing'
      ]
    ];

    $specific_crud = new CrudSpecificTable('people', $columns, DB_HOST, DB_USER, DB_PASS, DB_NAME);
```

## Insert
```php
  <?php
    # crud.php
    $data_insert = [
      "id" => 20,
      "name" => "Claris Pector",
      "job" => "Web Developer",
      "hobbie" => 'soccer'
    ];

    $response = $specific_crud->insert($data_insert);
    var_dump($response);
```

## Select
```php
  <?php
    # crud.php
    $response = $specific_crud->select();
    var_dump($response);

    echo "<br><br>";

    $response = $specific_crud->select(null, null, null, 'id', '>');
    var_dump($response);
```

## Update
```php
  <?php
    # crud.php
    $data_update = [
      "job" => "doctor of codes"
    ];

    $conditions = [
      ['id', 20]
    ];

    $response = $specific_crud->update($data_update, $conditions);
    var_dump($response);
```

## Delete
```php
  <?php
    # crud.php
    $response = $specific_crud->delete([["id", 20]]);
    var_dump($response);
```

## Returns columns
```php
  <?php
    $columns = $specific_crud->returnsAllColumnsOfTable();
    var_dump($columns);
    /**
     * id
     * ref
     * name
     * job
     * hobbie
    */

    $columns = $specific_crud->returnsAllColumnsOfTable(true);
    var_dump($columns);
    /**
     * id
     * ref
     * Name Title
     * Job Title
     * Hobbie Title
    */
   
    $columns = $specific_crud->returnsColumnsForRegistration();
    var_dump($columns);
    /**
     * name
     * job
     * hobbie
    */
   
    $columns = $specific_crud->returnsColumnsForEditing();
    var_dump($columns);
    /**
     * name
     * job
     * hobbie
    */
   
    $columns = $specific_crud->returnsColumnsForListing();
    var_dump($columns);
    /**
     * ref
     * name
     * job
     * hobbie
    */
   
    $columns = $specific_crud->returnsRequiredColumns();
    var_dump($columns);
    /**
     * id
     * ref
     * name
     * job
    */
   
    $column = $specific_crud->returnsTitleOfColumn('hobbie');
    var_dump($column);
    // Hobbie Title
   
    $column = $specific_crud->returnsColumnOfTitle('Hobbie Title');
    var_dump($column);
    // hobbie
```

## Return ref or id
```php
  <?php
    $id = $specific_crud->returnsIdByRef('ZQABQ-RUVW-BZM45-LB6AT1NY');
    var_dumo($id);
    // 2

    $ref = $specific_crud->returnsRefById(2);
    var_dump($ref);
    // ZQABQ-RUVW-BZM45-LB6AT1NY
```

## Heritage
### Execute SQL whith SQL functionality
```php
  <?php
    # execute_sql_crud_specific_table.php
      // $result = $PDO->executeSQL("INSERT INTO people (`id`, `ref_db_handler`, `name`) VALUES (?, ?, ?)", ...[1, 'SPABQ-LICY-ZZQ28-QK2DF2PA', 'Zerrai Mundo']);
    $result = $specific_crud->executeSQL("INSERT INTO people (`id`, `ref_db_handler`, `name`) VALUES (?, ?, ?)", 1, 'SPABQ-LICY-ZZQ28-QK2DF2PA', 'Zerrai Mundo');
    echo "<br>";
    var_dump($result);
    echo "<br>";

      // $result = $PDO->executeSQL("DELETE FROM people WHERE `id` = ?", ...[1]);
    $result = $specific_crud->executeSQL("DELETE FROM people WHERE `id` = ?", 1);
    echo "<br>";
    var_dump($result);
    echo "<br>";
```

### General SQL CRUD Functionality - CrudSpecificTable
#### Insert
```php
  <?php
    # crud.php
    $data_insert = [
      "id" => 20,
      "name" => "Claris Pector",
      "job" => "Web Developer",
      "hobbie" => 'soccer'
    ];

    $response = $specific_crud->execInsert("people", $data_insert);
    var_dump($response);
```

#### Select
```php
  <?php
    # crud.php
    $response = $specific_crud->execSelect("people");
    var_dump($response);

    echo "<br><br>";

    $response = $specific_crud->execSelect("people", null, null, null, 'id', '>');
    var_dump($response);
    
    echo "<br><br>";
   
    $tables = [
      " people P " => [],
      " type_has_people THP " => [["THP.id_person", "P.id"]],
      " type_people TP " => [["TP.id", "THP.id_type"]]
    ];

    $columns = [
      " COUNT(*) `repeat` ",
      " P.job ",
      " TP.name name_type "
    ];

    $conditions = [
      ['P.id', 20],
      " P.job LIKE '%doctor%' "
    ];

    $group_by = [
      " P.job ",
      " THP.id_type "
    ];

    $response = $specific_crud->execSelect($tables, $columns, $conditions, $group_by, '`repeat`', '>', 0, 100);
    var_dump($response);
```

#### Update
```php
  <?php
    # crud.php
    $data_update = [
      "job" => "doctor of codes"
    ];

    $conditions = [
      ['id', 20]
    ];

    $response = $specific_crud->execUpdate("people", $data_update, $conditions);
    var_dump($response);
```

#### Delete
```php
  <?php
    # crud.php
    $response = $specific_crud->execDelete("people", [["id", 20]]);
    var_dump($response);
```