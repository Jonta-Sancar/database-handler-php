<?php
namespace Handlers;

use Handlers\SQL_CRUD;

class CrudSpecificTable extends SQL_CRUD{
  protected String $table;
  protected Array $columns;
  protected Array $columns_for_registration;
  protected Array $columns_for_listing;

  public function __construct(String $table, Array $columns, String $host = null, String $db_user = null, String $db_pass = null, String $db_name = null, String $db_drive = 'mysql') {
    $this->table = $table;
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

  public function tableInsert(Array $data) : Object{
    return $this->execInsert($this->table, $data);
  }

  public function tableSelect(String|Array|Null $columns = "*", Array|Null $conditions = null, Array|String|Null $group_by = null, Array|String|Null $order_by = null, String|Null $order_direction = "<", String|Int|Null $limit_min = 100, String|Int|Null $limit_max = null) : Object{
    return $this->execSelect($this->table, $columns, $conditions, $group_by, $order_by, $order_direction, $limit_min, $limit_max);
  }

  public function tableUpdate(Array $data, Array|String|Null $conditions = null) : Object{
    return $this->execUpdate($this->table, $data, $conditions);
  }

  public function tableDelete(Array|String|Null $conditions = null) : Object{
    return $this->execDelete($this->table, $conditions);
  }

  public function returnsAllColumnsOfTable(){
    return $this->columns;
  }

  public function returnsColumnsForRegistration(){
    return $this->columns_for_registration;
  }

  public function returnsColumnsForListing(){
    return $this->columns_for_listing;
  }
}