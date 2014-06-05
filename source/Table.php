<?php
namespace Relation;

/**
 * Represents a table and parses a description of a table into this object.
 */
class Table {
  public $db;
  public $name;
  public $alias;
  public static $sharding = array();

  public function __construct($name, $alias = null, $db = null) {
    if(strpos($name, ' ')) {
      $parts = explode(' ', $name);
      $name = $parts[0];
      $alias = $parts[1];
    }
    if(strpos($name, '.')) {
      $parts = explode('.', $name);
      $name = $parts[1];
      $db = $parts[0];
    }
    $this->name = $name;
    $this->alias = $alias;
    if(array_key_exists($db, static::$sharding)) {
      $db = static::$sharding[$db];
    }
    $this->db = $db;
  }

  // FIXME: use the q method in Select when able.
  /**
   * @codeCoverageIgnore
   */
  static function q($v, $delimiter = "`") {
    $v = str_replace($delimiter, '', $v);
    if(stripos($v, '.') === false) {
      return $delimiter . $v . $delimiter;
    } else {
      $p = explode('.', $v);
      return $delimiter . implode($delimiter . '.' . $delimiter, $p) . $delimiter;
    }
  }


  function fieldName($field) {
    $alias = $this->alias ? $this->alias : $this->name;
    return static::q($alias) . '.' . static::q($field);
  }

  function alias(){
    return $this->alias ? static::q($this->alias) : static::q($this->name);
  }

  function __toString() {
    $s = '';
    if($this->db) {
      $s .= static::q($this->db) . '.';
    }
    $s .= static::q($this->name);
    if($this->alias) {
      $s .= ' ' . static::q($this->alias);
    }
    return $s;
  }
}