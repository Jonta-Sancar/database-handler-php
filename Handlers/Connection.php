<?php

namespace Handlers;

use Exception;
use Handlers\Auxiliaries;

class ReturnFormat
{
  public $result;
  public String|Null $result_error;
  public String|Bool|Null $ref_log;
  public String|Bool|Null $sql;

  function __construct($result, String|Null $result_error, String|Null $ref_log, String|Bool|Null $sql)
  {
    $this->result       = $result;
    $this->result_error = $result_error;
    $this->ref_log      = $ref_log;
    $this->sql          = $sql;
  }
}

class Connection
{
  public $logged_user = null;

  public $connection = null;
  public $connection_error = null;

  public $sql_exec_result = null;
  public $sql_exec_result_error = null;

  protected String $db_host;
  protected String $db_user;
  protected String $db_pass;
  protected String $db_name;
  protected String $db_drive;

  public function __construct(String $host = null, String $db_user = null, String $db_pass = null, String $db_name = null, String $db_drive = 'mysql')
  {
    $this->db_host  = $host;
    $this->db_user  = $db_user;
    $this->db_pass  = $db_pass;
    $this->db_name  = $db_name;
    $this->db_drive = $db_drive;
  }

  /**
   * Sets the logged in user.
   *
   * @param mixed $user The user to be set as the logged user.
   * @return void
   */
  public function setUser($user){
    $this->logged_user = $user;
  }

  /**
   * Function to establish a connection to the database.
   *
   * @throws Exception when connection fails
   * @return PDO|bool The database connection object or false on failure
   */
  private function connect()
  {
    try {
      $PDO = new \PDO("$this->db_drive:host=$this->db_host;dbname=$this->db_name;charset=utf8mb4", $this->db_user, $this->db_pass);

      $this->connection = $PDO;
    } catch (Exception $e) {
      $this->connection = false;
      $this->connection_error = $e->getMessage();
    }

    return $this->connection;
  }

  /**
   * Function to close the database connection.
   *
   */
  private function closeConnection()
  {
    $this->connection = null;
    $this->connection_error = null;
  }
  
  /**
   * Executes an SQL query with optional parameters and returns a ReturnFormat object.
   *
   * @param string $sql The SQL query to execute.
   * @param mixed ...$values Optional parameters to bind to the query.
   * @throws Exception when the connection fails or the query execution fails.
   * @return Object A ReturnFormat object containing the result of the query execution. The SQL result: SELECT => fetchAll; INSERT => lastInsertId; DELETE or UPDATE => rowCount.
   */
  public function executeSQL(String $sql, ...$values): Object
  {
    $sql_exec_result = null;
    $sql_exec_result_error = null;

    $values = array_values($values);
    $this->connect();
    
    $sql_comand = strtolower(explode(" ", trim($sql, " "))[0]);
    $sql_comand = Auxiliaries::returnsFilteredString($sql_comand);
    
    if ($this->connection) {
      try {
        $stmt = $this->prepareAndSetValues($sql, $values);
        $stmt->execute();

        $sql_exec_result = $this->returnsResult($stmt, $sql_comand);

        $sql_exec_result_error = null;
      } catch (Exception $e) {
        $sql_exec_result = false;
        $sql_exec_result_error = $e->getMessage();
      }
    } else {
      $sql_exec_result = false;
      $sql_exec_result_error = "Connection is null.";
    }

    $this->sql_exec_result = $sql_exec_result;
    $this->sql_exec_result_error = $sql_exec_result_error;

    $ref_log = $this->checkSqlResultAndReturnsRefLog($sql);

    $this->closeConnection();

    return new ReturnFormat($sql_exec_result, $sql_exec_result_error, $ref_log, $sql);
  }

  /**
   * Prepares a statement with the given SQL and sets values for the parameters.
   *
   * @param string $sql The SQL query to prepare.
   * @param array $values The values to bind to the parameters.
   * @throws Exception when an error occurs during preparation.
   * @return PDOStatement The prepared statement with values bound.
   */
  private function prepareAndSetValues($sql, $values){
    $stmt = $this->connection->prepare($sql);

    foreach ($values as $k => $v) {
      $stmt->bindParam($k+1, $values[$k]);
    }

    return $stmt;
  }
  
  /**
   * A function that determines the result based on the SQL command type.
   *
   * @param PDOStatement $stmt executed
   * @param string $sql_comand description
   * @throws Exception when there is an issue with the SQL execution.
   * @return The result based on the SQL command type.
   */
  private function returnsResult($stmt, $sql_comand){
    if ($sql_comand == 'select') {
      $sql_exec_result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } else if($sql_comand == 'insert'){
      $sql_exec_result = $this->connection->lastInsertId();
    } else {
      $sql_exec_result = $stmt->rowCount();
    }

    return $sql_exec_result;
  }

  /**
   * Checks the SQL result and returns the reference log.
   *
   * This function connects to the database, checks if the SQL execution result is false, and if so,
   * it checks the log table and inserts an error log into the logs_db_handler table. The function
   * returns the reference of the log entry if the SQL execution result is false, otherwise it returns null.
   *
   * @param string $sql The SQL query to be executed.
   * @return string|null The reference of the log entry if the SQL execution result is false, otherwise null.
   */
  private function checkSqlResultAndReturnsRefLog($sql){
    $this->connect();
    $ref_log = null;

    if ($this->sql_exec_result === false) {
      $this->checkLogTable();
      
      $SQL = "INSERT INTO logs_db_handler (ref, erro, auxiliar) VALUES (?,?,?)";
      $stmt = $this->connection->prepare($SQL);

      $error = !empty($this->sql_exec_result_error) ? $this->sql_exec_result_error : "undefined";
      
      $ref = $this->returnsItemRef("logs_db_handler");
      $values_log = [$ref, $error, $sql . "||| user - " . $this->logged_user];
      foreach ($values_log as $k => $v) {
        $stmt->bindParam($k+1, $values_log[$k]);
      }

      $stmt->execute();

      $ref_log = $ref;
    }

    return $ref_log;
  }

  /**
   * Checks if the logs_db_handler table exists in the database and creates it if it doesn't.
   *
   * @throws PDOException if there is an error with the database connection.
   * 
   */
  private function checkLogTable(){
    $SQL = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
    $stmt = $this->connection->prepare($SQL);
    $stmt->bindParam(1, $this->db_name);
    $log_table = "logs_db_handler";
    $stmt->bindParam(2, $log_table);
    $stmt->execute();

    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    if(count($result) <= 0){
      $SQL = "CREATE TABLE logs_db_handler (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        ref VARCHAR(25) NOT NULL,
        erro TEXT NULL,
        auxiliar TEXT NULL
        )";

      $stmt = $this->connection->prepare($SQL);
      $stmt->execute();
    }
  }

  /* REF HANLER */
  /**
   * Returns a unique reference for the given table.
   *
   * @param string $table The name of the table.
   * @return string|false The unique reference or false if the table does not have a 'ref' column.
   */
  public function returnsItemRef($table){
    $SQL = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?";
    $this->connect();
    $stmt = $this->connection->prepare($SQL);
    $stmt->bindParam(1, $this->db_name);
    $stmt->bindParam(2, $table);
    $stmt->execute();

    $response = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $columns = array_map(function($v){
      return $v['COLUMN_NAME'];
    }, $response);

    if(array_search("ref", $columns) !== false){
      $characters = str_split("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ");
      $ref_format = str_split('#####-####-#####-########');
  
      $ref = false;
      $find_ref = false;
      $count = 0;
      while(!$find_ref && $count<10000){
        $ref = '';

        foreach($ref_format as $ref_format_char){
          if($ref_format_char == '#'){
            $ref .= $characters[array_rand($characters, 1)];
          }else{
            $ref .= $ref_format_char;
          }
        }
  
        $find_ref = $this->checkIfRefCanBeUsed($table, $ref);
        if($find_ref === false){
          $ref = false;
        }
        $count++;
      }
      return $ref;
    } else {
      return false;
    }
  }

  /**
   * Checks if a reference can be used in a specified table.
   *
   * @param string $table The table name to check.
   * @param string $ref The reference to check for.
   * @return bool Returns true if the reference can be used, false otherwise.
   */
  private function checkIfRefCanBeUsed($table, $ref){
    $SQL = "SELECT * FROM $table WHERE ref = ?";
    $stmt = $this->connection->prepare($SQL);
    $stmt->bindParam(1, $ref);
    $stmt->execute();
    $response = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    return !(bool)Auxiliaries::arrayLength($response);
  }
  /* REF HANLER \. */
}
