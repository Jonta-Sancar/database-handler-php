<?php
namespace Handlers;

class Auxiliaries{
  public static function arrayLength($array){
    return is_array($array) ? count($array) : 0;
  }

  public static function returnsFilteredString($string, $values = "abcdefghijklmnopqrstuvwxyz"){
    $string_in_array = str_split(str_replace("\n", " ", $string));

    $new_array = [];
    foreach($string_in_array as $char){
      if(mb_strpos($values, $char) !== false){
        array_push($new_array, $char);
      }
    }

    return implode('', $new_array);
  }
}