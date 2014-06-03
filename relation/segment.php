<?php
namespace Relation;

class Segment extends RelationBase {
  protected $_field;
  protected $_op;
  protected $_value;
  protected $_type;

  public function __construct($parent, $field, $value, $op = null, $type = null) {
    parent::__construct($parent);
    $this->_field  = $field;
    $this->_op = $op;
    $this->_type = $type;
    $this->_value = $value;
  }

  function includeValue() {
    $op = $this->op();
    return ($op!='IS NULL' && $op!='IS NOT NULL');
  }

  function op($convert = true) {
    $op =  $this->_op ? $this->_op : '=';
    if($convert && is_null($this->_value)) {
      if($op == '=') {
        return 'IS NULL';
      } else if($op == '<>') {
        return 'IS NOT NULL';
      }
    }
    return $op;
  }

  function bareField() {
    if(strpos($this->_field, '.')) {
      $parts = explode('.', $this->_field);
      return $parts[1];
    }
    return $this->_field;
  }

  public function addToParameters(&$ps) {
    if($this->includeValue()) {
      $ps[$this->bareField()] = SQLValue::to_string($this->_value, $this->_type);
    }
    return $ps;
  }

  public function __toString() {
    $str = $this->_field . ' ' . $this->op();
    if($this->includeValue()) {
      $str .= ' :' . $this->bareField();
    }
    return $str;
  }
}