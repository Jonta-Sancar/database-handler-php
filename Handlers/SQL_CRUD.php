<?php

namespace Handlers;

include_once(__DIR__ . "/./Connection.php");

use Handlers\Connection;

use Exception;

class SQL_CRUD extends Connection{

  private static function returnProcessedData($v){
    if(empty($v) && is_string($v) && $v != 0){
      return "NULL";
    } else {
      return $v;
    }
  }

  private static function returnValuesAsASymbol($v){
    return '?';
  }

  private static function prepareConditions($conditions){
    if(!empty($conditions)){
      if(is_array($conditions)){
        $processed_data = array_map(function($v){
          if (is_array($v)) {
            $column = $v[0];
            return "$column = '".$v[1]."'";
          } else {
            return $v;
          }
        },$conditions);
    
        return implode(" AND ", $processed_data);
      } else {
        return $conditions;
      }
    } else {
      return false;
    }
  }
  
  private function SQL_insert($table, $data){
    if(is_array($data)){
      $processed_data = array_map(function($v){return $this->returnProcessedData($v);}, $data);
  
      $array_keys = array_keys($processed_data);
  
      if(is_numeric($array_keys[0])){
        return false;
      } else {
        $values = ":" . implode(", :", $array_keys);
      }
  
      $columns = implode("`, `", $array_keys);
  
      $sql = "INSERT INTO `$table`(`$columns`) VALUES ($values);";
      return ["SQL" => $sql, "VALUES" => $processed_data];
    } else {
      return false;
    }
  }
  
  private function SQL_select($table, $columns = "*", $conditions = null, $group_by = null, $order_by = null, $order_direction = "<", $limit_min = null, $limit_max = null){
    try{
      $columns_txt    = is_array($columns) ? implode(', ', $columns): $columns;
      $conditions_txt = $this->prepareConditions($conditions);
      $group_by_txt   = is_array($group_by) ? implode(', ', $group_by): $group_by;
      $order_by_txt   = is_array($order_by) ? implode(', ', $order_by): $order_by;
  
      if(empty($columns_txt)){
        $columns_txt = '*';
      }
  
      if(!empty($conditions_txt)){
        $conditions_txt = " WHERE " . $conditions_txt;
      }
  
      if(!empty($group_by_txt)){
        $group_by_txt = " GROUP BY " . $group_by_txt;
      }
  
      if(!empty($order_by_txt)){
        $order_direction = $order_direction == '>' ? " DESC " : " ASC ";
        $order_by_txt = " ORDER BY " . $order_by_txt . " " . $order_direction;
      }
  
      if(empty($limit_max) && (!empty($limit_min) && $limit_min != 0)){
        $limit = "LIMIT $limit_min";
      } else if ((!empty($limit_min) || $limit_min === 0) && (!empty($limit_max) || $limit_max === 0)){
        $limit = "LIMIT $limit_min, $limit_max";
      } else {
        $limit = "";
      }

      if(is_array($table)){
        $table_txt = implode(' INNER JOIN ', $table);
      } else {
        $table_txt = $table;
      }
  
      return "SELECT $columns_txt FROM $table_txt $conditions_txt $group_by_txt $order_by_txt $limit;";
    } catch (Exception $e){
      return false;
    }    
  }
  
  private function SQL_update($table, $data, $conditions = null){
    if(is_array($data)){
      $processed_data = array_map(function($v){return $this->returnProcessedData($v);}, $data);
      $conditions_txt = $this->prepareConditions($conditions);
  
      if(!empty($conditions_txt)){
        $conditions_txt = " WHERE " . $conditions_txt;
      }

      $columns_values = [];

      foreach($processed_data as $k => $v){
        $column_value = "`$k` = :$k";
        array_push($columns_values, $column_value);
      }
  
      $columns_values_txt = implode(", ", $columns_values);
  
      $sql = "UPDATE `$table` SET $columns_values_txt $conditions_txt;";
      return ["SQL" => $sql, "VALUES" => $processed_data];
    } else {
      return false;
    }
  }
  
  private function SQL_delete($table, $conditions = null){
    $conditions_txt = $this->prepareConditions($conditions);
  
    if(!empty($conditions_txt)){
      $conditions_txt = " WHERE " . $conditions_txt;
    }

    $sql = "DELETE FROM $table $conditions_txt";

    return $sql;
  }

  public function execInsert($table, $data){
    try{
      $response = $this->SQL_insert($table, $data);
  
      if($response !== false){
        $this->executeSQL($response['SQL'], $response["VALUES"]);

        return $this->sql_exec_result;
      } else {
        return false;
      }
    } catch (Exception $e) {
      return [
        'query_status'=> false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function execSelect($table, $columns = "*", $conditions = null, $group_by = null, $order_by = null, $order_direction = "<", $limit_min = null, $limit_max = null){
    try{
      $response = $this->SQL_select($table, $columns, $conditions, $group_by, $order_by, $order_direction, $limit_min, $limit_max);

      if($response !== false){
        $this->executeSQL($response);

        return $this->sql_exec_result;
      } else {
        return false;
      }
    } catch (Exception $e) {
      return [
        'query_status'=> false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function execUpdate($table, $data, $conditions = null){
    try{
      $response = $this->SQL_update($table, $data, $conditions);

      if($response !== false){
        $this->executeSQL($response['SQL'], $response['VALUES']);

        return $this->sql_exec_result;
      }
    } catch (Exception $e) {
      return [
        'query_status'=> false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function execDelete($table, $conditions = null){
    try{
      $response = $this->SQL_delete($table, $conditions);

      if($response !== false){
        $this->executeSQL($response[''], $response['']);

        return $this->sql_exec_result;
      }
    } catch (Exception $e) {
      return [
        'query_status'=> false,
        'message' => $e->getMessage()
      ];
    }
  }
}