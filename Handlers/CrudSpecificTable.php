<?php
namespace Handlers;

use Handlers\SQL_CRUD;

class CrudSpecificTable extends SQL_CRUD{
  private Array $columns;
  private Array $columns_for_registration;
  private Array $columns_for_listing;

  public function __construct(String $tabela, Array $columns, String $host = null, String $db_user = null, String $db_pass = null, String $db_name = null, String $db_drive = 'mysql') {
    $this->db_host = $host;
    $this->db_user = $db_user;
    $this->db_pass = $db_pass;
    $this->db_name = $db_name;
    $this->db_drive = $db_drive;

    foreach ($columns as $column => $properties) {
      array_push($this->columns, $column);

      if(array_search("registration", $properties) !== false) {
        array_push($this->columns_for_registration, $column);
      }
      
      if(array_search("listing", $properties) !== false){
        array_push($this->columns_for_listing, $column);
      }
    }
  }
}