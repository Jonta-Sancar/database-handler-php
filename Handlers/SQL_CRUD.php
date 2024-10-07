<?php
namespace Handlers;

use Handlers\ReturnFormat;
use Handlers\Connection;

use Exception;

class SQL_CRUD extends Connection{
  protected function SQL_insert(String $table, Array $data) : Array|Bool{
    if(is_array($data)){
      $processed_data = array_map(function($v){return AuxiliariesToCRUD::returnProcessedData($v);}, $data);
  
      $array_keys = array_keys($processed_data);
      
      $values = AuxiliariesToCRUD::returnsValuesSyntax($array_keys);
  
      $columns = implode("`, `", $array_keys);
  
      $sql = "INSERT INTO `$table`(`$columns`) VALUES ($values);";
      return ["SQL" => $sql, "VALUES" => $processed_data];
    } else {
      return false;
    }
  }
  
  protected function SQL_select(String|Array $table, String|Array|Null $columns = "*", Array|Null $conditions = null, Array|String|Null $group_by = null, Array|String|Null $order_by = null, String|Null $order_direction = "<", String|Int|Null $limit_min = null, String|Int|Null $limit_max = null) : String|Bool{
    try{
      $columns_txt    = AuxiliariesToCRUD::checksIfIsArrayAndReturns($columns)  ?? '*';
      $conditions_txt = AuxiliariesToCRUD::prepareConditions($conditions);
      $group_by_txt   = AuxiliariesToCRUD::checksIfIsArrayAndReturns($group_by);
      $order_by_txt   = AuxiliariesToCRUD::checksIfIsArrayAndReturns($order_by);
  
      $conditions_txt = AuxiliariesToCRUD::checksIfIsNotEmptyAndReturns($conditions_txt, " WHERE ");
      $group_by_txt   = AuxiliariesToCRUD::checksIfIsNotEmptyAndReturns($group_by_txt, " GROUP BY ");
  
      $order_by_txt   = AuxiliariesToCRUD::returnsOderBySyntax($order_by_txt, $order_direction);

      $limit          = AuxiliariesToCRUD::prepareLimitClause($limit_min, $limit_max);

      $table_prepared = AuxiliariesToCRUD::prepareTable($table);

      $table_txt      = AuxiliariesToCRUD::checksIfIsArrayAndReturns($table_prepared, " INNER JOIN ");
  
      return "SELECT $columns_txt FROM $table_txt $conditions_txt $group_by_txt $order_by_txt $limit;";
    } catch (Exception $e){
      return false;
    }    
  }
  
  protected function SQL_update(String $table, Array $data, Array|String|Null $conditions = null) : Array|Bool{
    if(is_array($data) && $conditions && !empty($conditions)){
      $processed_data = array_map(function($v){return AuxiliariesToCRUD::returnProcessedData($v);}, $data);
      $conditions_txt = AuxiliariesToCRUD::prepareConditions($conditions);
  
      $conditions_txt = AuxiliariesToCRUD::checksIfIsNotEmptyAndReturns($conditions_txt, " WHERE ");

      $columns_values = [];

      foreach($processed_data as $k => $v){
        $column_value = "`$k` = ?";
        array_push($columns_values, $column_value);
      }
  
      $columns_values_txt = AuxiliariesToCRUD::checksIfIsArrayAndReturns($columns_values);
  
      $sql = "UPDATE `$table` SET $columns_values_txt $conditions_txt;";
      return ["SQL" => $sql, "VALUES" => $processed_data];
    } else {
      return false;
    }
  }
  
  protected function SQL_delete(String $table, Array|String|Null $conditions = null) : String|Bool{
    $conditions_prepared = AuxiliariesToCRUD::prepareConditions($conditions);
  
    $conditions_txt = AuxiliariesToCRUD::checksIfIsNotEmptyAndReturns($conditions_prepared, " WHERE ");

    return "DELETE FROM $table $conditions_txt";
  }

  public function execInsert(String $table, Array $data) : Object{
    try{
      $ref = $this->returnsItemRef($table);
      if($ref !== false){
        $data['ref_db_handler'] = $ref;
      }

      $response = $this->SQL_insert($table, $data);
  
      if($response !== false){
        return $this->executeSQL($response['SQL'], ...$response["VALUES"]);
      } else {
        return new ReturnFormat(false, "SQL could not be mounted. Please check the data provided and try again", null, $response);
      }
    } catch (Exception $e) {
      return new ReturnFormat(false, $e->getMessage(), null, false);
    }
  }

  public function execSelect(String|Array $table, String|Array|Null $columns = "*", Array|Null $conditions = null, Array|String|Null $group_by = null, Array|String|Null $order_by = null, String|Null $order_direction = "<", String|Int|Null $limit_min = 100, String|Int|Null $limit_max = null) : Object{
    try{
      $response = $this->SQL_select($table, $columns, $conditions, $group_by, $order_by, $order_direction, $limit_min, $limit_max);

      if($response !== false){
        return $this->executeSQL($response);
      } else {
        return new ReturnFormat(false, "SQL could not be mounted. Please check the data provided and try again", null, $response);
      }
    } catch (Exception $e) {
      return new ReturnFormat(false, $e->getMessage(), null, false);
    }
  }

  public function execUpdate(String $table, Array $data, Array|String|Null $conditions = null) : Object{
    if(!empty($conditions)){
      try{
        $response = $this->SQL_update($table, $data, $conditions);
  
        if($response !== false){
          return $this->executeSQL($response['SQL'], ...$response['VALUES']);
        } else {
          return new ReturnFormat(false, "SQL could not be mounted. Please check the data provided and try again", null, $response);
        }
      } catch (Exception $e) {
        return new ReturnFormat(false, $e->getMessage(), null, false);
      }
    } else {
      return new ReturnFormat(false, "The operation was blocked as it would have been applied to all records in the table", null, false);
    }
  }

  public function execDelete(String $table, Array|String|Null $conditions = null) : Object{
    if(!empty($conditions)){
      try{
        $response = $this->SQL_delete($table, $conditions);
  
        if($response !== false){
          return $this->executeSQL($response);
        } else {
          return new ReturnFormat(false, "SQL could not be mounted. Please check the data provided and try again", null, $response);
        }
      } catch (Exception $e) {
        return new ReturnFormat(false, $e->getMessage(), null, false);
      }
    } else {
      return new ReturnFormat(false, "The operation was blocked as it would have been applied to all records in the table", null, false);
    }
  }
}