<?php

namespace Handlers;

use Exception;

class Connection {
  public $connection = null;
  public $connection_error = null;

  public $sql_exec_result = null;
  public $sql_exec_result_error = null;

  protected $db_host  = null;
  protected $db_user  = null;
  protected $db_pass  = null;
  protected $db_name  = null;
  protected $db_drive = null;

  public function __construct($host = null, $db_user = null, $db_pass = null, $db_name = null, $db_drive = 'mysql') {
    $this->db_host = $host;
    $this->db_user = $db_user;
    $this->db_pass = $db_pass;
    $this->db_name = $db_name;
    $this->db_drive = $db_drive;
  }

  private function connect() {
    try {
      $PDO = new \PDO("$this->db_drive:host=$this->db_host;dbname=$this->db_name", $this->db_user, $this->db_pass);

      $this->connection = $PDO;
    } catch(Exception $e) {
      $this->connection = false;
      $this->connection_error = $e->getMessage();
    }

    return $this->connection;
  }

  private function closeConnection(){
    $this->connection = null;
    $this->connection_error = null;
  }

  public function executeSQL($sql, ...$values) {
    $this->connect();

    if($this->connection){
      try {

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);

        $sql_comand = strtolower(explode(" ", $sql)[0]);

        if($sql_comand == 'select'){
          $this->sql_exec_result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } else {
          $this->sql_exec_result = $stmt;
        }
      } catch (Exception $e) {
        $this->sql_exec_result = false;
        $this->sql_exec_result_error = $e->getMessage();
      }
    } else {
      $this->sql_exec_result = false;
      $this->sql_exec_result_error = "Connection is null.";
    }

    $this->closeConnection();

    return $this->sql_exec_result;
  }
}