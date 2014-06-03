<?php
namespace Relation;

class Table {
  public $db;
  public $name;
  public $alias;

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
    if($db == 'SECDB') {
      $db = SECDB;
    }
    $this->db = $db;
  }

  function fieldName($field) {
    $alias = $this->alias ? $this->alias : $this->name;
    return Select::q($alias) . '.' . Select::q($field);
  }

  function alias(){
    return $this->alias ? Select::q($this->alias) : Select::q($this->name);
  }

  function __toString() {
    $s = '';
    if($this->db) {
      $s .= Select::q($this->db) . '.';
    }
    $s .= Select::q($this->name);
    if($this->alias) {
      $s .= ' ' . Select::q($this->alias);
    }
    return $s;
  }
}