<?php
namespace Handlers;

use Handlers\SQL_CRUD;

class CrudSpecificTable extends SQL_CRUD{
  protected String $table;
  protected Array $columns = []; // this propertie can be a method, using SQL
  protected Array $columns_for_registration = [];
  protected Array $columns_for_editing = [];
  protected Array $columns_for_listing = [];
  protected Array $required_columns = [];

  public function __construct(String $table, Array $columns, String $db_host, String $db_user, String $db_pass, String $db_name, String $db_drive = 'mysql') {
    $this->table = $table;

    parent::__construct($db_host, $db_user, $db_pass, $db_name, $db_drive);

    foreach ($columns as $column => $properties) {
      $column_title = !empty($properties['title']) ? $properties['title'] : $column;
      $this->columns[$column] = $column_title;

      if(array_search("registration", $properties) !== false) {
        array_push($this->columns_for_registration, $column);
      }
      
      if(array_search("editing", $properties) !== false){
        array_push($this->columns_for_editing, $column);
      }
      
      if(array_search("listing", $properties) !== false){
        array_push($this->columns_for_listing, $column);
      }
      
      if(array_search("required", $properties) !== false){
        array_push($this->required_columns, $column);
      }
    }
  }

  public function insert(Array $data) : Object{
    return parent::execInsert($this->table, $data);
  }

  public function select(String|Array|Null $columns = "*", Array|Null $conditions = null, Array|String|Null $group_by = null, Array|String|Null $order_by = null, String|Null $order_direction = "<", String|Int|Null $limit_min = 100, String|Int|Null $limit_max = null) : Object{
    return parent::execSelect($this->table, $columns, $conditions, $group_by, $order_by, $order_direction, $limit_min, $limit_max);
  }

  public function update(Array $data, Array|String|Null $conditions = null) : Object{
    return parent::execUpdate($this->table, $data, $conditions);
  }

  public function delete(Array|String|Null $conditions = null) : Object{
    return parent::execDelete($this->table, $conditions);
  }

  public function returnsAllColumnsOfTable(Bool $title = false){
    if($title){
      $columns = array_values($this->columns);
    } else {
      $columns = array_keys($this->columns);
    }

    return $columns;
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

  public function returnsRequiredColumns(){
    return $this->required_columns;
  }

  public function returnsTitleOfColumn(String $column){
    return $this->columns[$column];
  }

  public function returnsColumnOfTitle(String $title){
    $column = false;

    foreach ($this->columns as $key => $value) {
      if($value == $title){
        $column = $key;
        break;
      }
    }

    return $column;
  }

  public function returnsIdByRef(String $ref)
  {
    $conditions = [
      ["ref_db_handler", $ref]
    ];

    $response = $this->execSelect($this->table, 'id', $conditions);

    return $response->result != false ? $response->result[0]['id'] : false;
  }

  public function returnsRefById(String|Int $id)
  {
    $conditions = [
      ["id", $id]
    ];

    $response = $this->execSelect($this->table, 'ref_db_handler', $conditions);

    return $response->result != false ? $response->result[0]['ref_db_handler'] : false;
  }
}