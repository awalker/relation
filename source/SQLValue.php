<?php
namespace Relation;

class SQLValue {
  // try to guess the value's type
  public static function type($v) {
    if(is_null($v)) {return 'null';}
    if(is_string($v)) {return 'string';}
    if(is_numeric($v)) {return 'numeric';}
    if(is_bool($v)) {return 'bool';}
    if(is_object($v)) {
      switch(get_class($v)) {
        case 'DateTime':
          return 'date';
          break;
        default:
          return 'object';
          break;
      }
    }
  }

  public static function from_bool($v) {
    return $v ? 1 : 0;
  }

  public static function from_date($v) {
    return $v ? $v->format('Y-m-d H:i:s') : NULL;
  }

  public static function to_string($v, $type = null) {
    if(!$type) $type = static::type($v);
    switch($type) {
      case "string":
      case "numeric":
        return (string)$v;
        break;
      case "null":
        return null;
        break;
      default:
        $meth = "from_" . $type;
        return static::$meth($v);
    }
  }
}