<?php

namespace Handlers;

include_once(__DIR__ . "/./Connection.php");

use Handlers\Connection;

use Exception;

class SQL_CRUD extends Connection{

  private function returnProcessedData($v){
    if(empty($v) && is_string($v) && $v != 0){
      return "NULL";
    } else {
      return $v;
    }
  }

  private function checksIfIsArrayAndReturns($data, $implode_separator = ", "){
    if(is_array($data)){
      return implode($implode_separator, $data);
    } else {
      return $data;
    }
  }

  private function checksIfIsNotEmptyAndReturns($data, $prefix = ''){
    $new_value = !empty($data) ? $prefix . $data : "";

    return $new_value;
  }

  private function returnsValuesSyntax($array){
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

  private function prepareConditions($conditions){
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

  private function returnsOderBySyntax($order_by, $order_direction){
    $desc_array = [">", "DESC"];
  
    if(!empty($order_by)){
      $order_direction = array_search($order_direction, $desc_array) !== false ? " DESC " : " ASC ";
      $order_by = " ORDER BY " . $order_by . " " . $order_direction;
    } else {
      $order_by = "";
    }

    return $order_by;
  }

  private function prepareLimitClause($limit_min, $limit_max){
    if(empty($limit_max) && (!empty($limit_min) && $limit_min != 0)){
      $limit = "LIMIT $limit_min";
    } else if ((!empty($limit_min) || $limit_min === 0) && (!empty($limit_max) || $limit_max === 0)){
      $limit = "LIMIT $limit_min, $limit_max";
    } else {
      $limit = "";
    }

    return $limit;
  }
  
  private function SQL_insert($table, $data){
    if(is_array($data)){
      $processed_data = array_map(function($v){return $this->returnProcessedData($v);}, $data);
  
      $array_keys = array_keys($processed_data);
      
      $values = $this->returnsValuesSyntax($array_keys);
  
      $columns = implode("`, `", $array_keys);
  
      $sql = "INSERT INTO `$table`(`$columns`) VALUES ($values);";
      return ["SQL" => $sql, "VALUES" => $processed_data];
    } else {
      return false;
    }
  }
  
  private function SQL_select($table, $columns = "*", $conditions = null, $group_by = null, $order_by = null, $order_direction = "<", $limit_min = null, $limit_max = null){
    try{
      $columns_txt    = $this->checksIfIsArrayAndReturns($columns)  ?? '*';
      $conditions_txt = $this->prepareConditions($conditions);
      $group_by_txt   = $this->checksIfIsArrayAndReturns($group_by);
      $order_by_txt   = $this->checksIfIsArrayAndReturns($order_by);
  
      $conditions_txt = $this->checksIfIsNotEmptyAndReturns($conditions_txt, " WHERE ");
      $group_by_txt   = $this->checksIfIsNotEmptyAndReturns($group_by_txt, " GROUP BY ");
  
      $order_by_txt   = $this->returnsOderBySyntax($order_by_txt, $order_direction);

      $limit          = $this->prepareLimitClause($limit_min, $limit_max);

      $table_txt      = $this->checksIfIsArrayAndReturns($table, " INNER JOIN ");
  
      return "SELECT $columns_txt FROM $table_txt $conditions_txt $group_by_txt $order_by_txt $limit;";
    } catch (Exception $e){
      return false;
    }    
  }
  
  private function SQL_update($table, $data, $conditions = false){
    if(is_array($data) && $conditions && !empty($conditions)){
      $processed_data = array_map(function($v){return $this->returnProcessedData($v);}, $data);
      $conditions_txt = $this->prepareConditions($conditions);
  
      $conditions_txt = $this->checksIfIsNotEmptyAndReturns($conditions_txt, " WHERE ");

      $columns_values = [];

      foreach($processed_data as $k => $v){
        $column_value = "`$k` = ?";
        array_push($columns_values, $column_value);
      }
  
      $columns_values_txt = $this->checksIfIsArrayAndReturns($columns_values);
  
      $sql = "UPDATE `$table` SET $columns_values_txt $conditions_txt;";
      return ["SQL" => $sql, "VALUES" => $processed_data];
    } else {
      return false;
    }
  }
  
  private function SQL_delete($table, $conditions = null){
    $conditions_prepared = $this->prepareConditions($conditions);
  
    $conditions_txt = $this->checksIfIsNotEmptyAndReturns($conditions_prepared, " WHERE ");

    return "DELETE FROM $table $conditions_txt";
  }

  public function execInsert($table, $data){
    try{
      $response = $this->SQL_insert($table, $data);
  
      if($response !== false){
        $this->executeSQL($response['SQL'], ...$response["VALUES"]);

        return [
          "SQL" => $response['SQL'],
          "RESULT" => $this->sql_exec_result,
        ];
      } else {
        return [
          "SQL" => $response['SQL'],
          "RESULT" => false,
        ];
      }
    } catch (Exception $e) {
      return [
        'query_status'=> false,
        'message' => $e->getMessage()
      ];
    }
  }

  public function execSelect($table, $columns = "*", $conditions = null, $group_by = null, $order_by = null, $order_direction = "<", $limit_min = 100, $limit_max = null){
    try{
      $response = $this->SQL_select($table, $columns, $conditions, $group_by, $order_by, $order_direction, $limit_min, $limit_max);

      if($response !== false){
        $this->executeSQL($response);

        return [
          "SQL" => $response,
          "RESULT" => $this->sql_exec_result
        ];
      } else {

        return [
          "SQL" => $response,
          "RESULT" => false
        ];
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
        $this->executeSQL($response['SQL'], ...$response['VALUES']);

        return [
          "SQL" => $response['SQL'],
          "RESULT" => $this->sql_exec_result
        ];
      } else {
        return [
          "SQL" => $response['SQL'],
          "RESULT" => false
        ];
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
        $this->executeSQL($response);

        return [
          "SQL" => $response,
          "RESULT" => $this->sql_exec_result,
        ];
      } else {
        return [
          "SQL" => $response['SQL'],
          "RESULT" => false,
        ];
      }
    } catch (Exception $e) {
      return [
        'query_status'=> false,
        'message' => $e->getMessage()
      ];
    }
  }
}