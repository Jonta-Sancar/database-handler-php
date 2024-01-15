<?php

namespace Handlers;

use Exception;

class SQL_CRUD {

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
  
  public function SQL_insert($table, $data){
    if(is_array($data)){
      $processed_data = array_map(function($v){return $this->returnProcessedData($v);}, $data);
  
      $array_keys = array_keys($processed_data);
  
      if(is_numeric($array_keys[0]) && !is_string($array_keys[0])){
        $values = implode(", ", array_map(function($v){return $this->returnValuesAsASymbol($v);}, $array_keys));
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
  
  public function SQL_select($table, $columns = "*", $conditions = null, $group_by = null, $order_by = null, $order_direction = "<", $limit_min = null, $limit_max = null){
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
  
      return "SELECT $columns_txt FROM $table $conditions_txt $group_by_txt $order_by_txt $limit;";
    } catch (Exception $e){
      return false;
    }    
  }
  
  public function SQL_update($table, $data){}
  
  public function SQL_delete($table, $conditions){}
}