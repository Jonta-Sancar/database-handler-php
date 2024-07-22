<?php
namespace Handlers;

use Handlers\SQL_CRUD;

class CrudSpecificTable extends SQL_CRUD{
  protected String $table;
  protected Array $columns = [];
  protected Array $columns_for_registration = [];
  protected Array $columns_for_editing = [];
  protected Array $columns_for_listing = [];

  public function __construct(String $table, Array $columns, String $db_host = null, String $db_user = null, String $db_pass = null, String $db_name = null, String $db_drive = 'mysql') {
    $this->table = $table;

    parent::__construct($db_host, $db_user, $db_pass, $db_name, $db_drive);

    foreach ($columns as $column => $properties) {
      array_push($this->columns, $column);

      if(array_search("registration", $properties) !== false) {
        array_push($this->columns_for_registration, $column);
      }
      
      if(array_search("editing", $properties) !== false){
        array_push($this->columns_for_editing, $column);
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

  public function returnsColumnsForEditing(){
    return $this->columns_for_editing;
  }

  public function returnsColumnsForListing(){
    return $this->columns_for_listing;
  }

  public function returnsIdByRef($ref)
  {
    $conditions = [
      ["ref_db_handler", $ref]
    ];

    $response = $this->execSelect($this->table, 'id', $conditions);

    return $response->result != false ? $response->result[0]['id'] : false;
  }

  public function returnsRefById($id)
  {
    $conditions = [
      ["id", $id]
    ];

    $response = $this->execSelect($this->table, 'ref_db_handler', $conditions);

    return $response->result != false ? $response->result[0]['ref_db_handler'] : false;
  }
}