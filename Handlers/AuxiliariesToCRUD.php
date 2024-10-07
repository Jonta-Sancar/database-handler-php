<?php

namespace Handlers;

class AuxiliariesToCRUD {
  public static function returnProcessedData($v){
    if(empty($v) && is_string($v) && $v != 0){
      return null;
    } else {
      return $v;
    }
  }

  public static function checksIfIsArrayAndReturns(Array|String|Int|Float|Null $data, String $implode_separator = ", ") : String|Int|Float|Null {
    if(is_array($data)){
      return implode($implode_separator, $data);
    } else {
      return $data;
    }
  }

  public static function checksIfIsNotEmptyAndReturns($data, String $prefix = '') : String{
    $new_value = !empty($data) ? $prefix . $data : "";

    return $new_value;
  }

  public static function returnsValuesSyntax(Array $array) : String|Bool{
    if(is_numeric($array[0])){
      $values = false;
    } else {
      $new_array = array_map(function($v){
        return '?';
      }, $array);

      $values = implode(", ", $new_array);
    }

    return $values;
  }

  public static function prepareConditions(Array|Null $conditions, Bool $is_on = false) : String|Array|Bool{
    $GLOBALS["func_prepareconditions_is_on"] = $is_on;
    if(!empty($conditions)){
      if(is_array($conditions)){
        $processed_data = array_map(function($v){
          if (is_array($v)) {
            $column = $v[0];

            if($GLOBALS["func_prepareconditions_is_on"]){
              return "$column = ".$v[1];
            } else {
              return "$column = '".$v[1]."'";
            }
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

  public static function returnsOderBySyntax(String|Null $order_by, String|Null $order_direction) : String{
    $desc_array = [">", "DESC"];
  
    if(!empty($order_by)){
      $order_direction = array_search($order_direction, $desc_array) !== false ? " DESC " : " ASC ";
      $order_by = " ORDER BY " . $order_by . " " . $order_direction;
    } else {
      $order_by = "";
    }

    return $order_by;
  }

  public static function prepareLimitClause(String|Int|Null $limit_min, String|Int|Null $limit_max) : String{
    if(empty($limit_max) && (!empty($limit_min) && $limit_min != 0)){
      $limit = "LIMIT $limit_min";
    } else if ((!empty($limit_min) || $limit_min === 0) && (!empty($limit_max) || $limit_max === 0)){
      $limit = "LIMIT $limit_min, $limit_max";
    } else {
      $limit = "";
    }

    return $limit;
  }

  public static function prepareTable($table){
    if(is_array($table)){
      $new_table_value = [];

      foreach($table as $key => $values){
        if(is_array($values)){
          $on_txt = AuxiliariesToCRUD::prepareConditions($values, true);
  
          if($on_txt && !empty($on_txt)){
            array_push($new_table_value, $key . ' ON ' . $on_txt);
          } else {
            array_push($new_table_value, $key);
          }
        } else {
            array_push($new_table_value, $values);
        }
      }
    } else {
      $new_table_value = $table;
    }

    return $new_table_value;
  }
}