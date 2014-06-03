<?php
namespace Relation;

class Field {
  public $table;
  public $exp;
  public $alias;

  public function __construct($table, $exp = null, $alias = null) {
    if($table && !$exp) {
      $exp = $table;
      $table = null;
    }
    if(strpos($name, ' ')) {
      $parts = explode(' ', $name);
      $name = $parts[0];
      $alias = $parts[1];
    }
    if(strpos($name, '.')) {
      // TODO: We don't have a way to find tables yet;
      $parts = explode('.', $name);
      $name = $parts[1];
    }
    $this->table = $table;
    $this->exp = $exp;
    $this->alias = $alias;
  }

  public static function isExpression($s) {
    if(strpos($s, ' ') !== false) {
      return true;
    }
    if(strpos($s, '(') !== false) {
      return true;
    }
    if(strpos($s, '`') !== false) {
      return true;
    }

    if(strpos($s, 'CURRENT_')) {
      return true;
    }

    return false;
  }

  public static function isFieldName($s) {
    if(strpos($s, ' ') !== false) {
      return false;
    }
    if(strpos($s, '.') !== false) {
      return true;
    }
    if(strpos($s, '`') !== false) {
      return true;
    }
    return false;
  }

  function fieldName() {
    if(static::isExpression($this->exp)) {
      return $this->exp;
    }
    return Select::q($this->exp);
  }

  function alias() {
    return $this->alias ? Select::q($this->alias) : $this->fieldName();
  }

  function asFrom() {
    $s = array($this->fieldName);
    if($this->alias) {
      $s[] = $this->alias;
    }
    return implode(' ', $s);
  }

  function __toString() {
    $s = array();
    if($this->table) {
      $s[] = $this->table->alias();
    }
    $s[] = $this->alias();
    return implode('.', $s);
  }
}