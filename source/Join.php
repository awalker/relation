<?php

namespace Relation;

class Join extends RelationBase {
  public $table;
  public $type;
  public $_where;

  public function __construct($parent, $table, $type = 'INNER') {
    parent::__construct($parent);
    $this->table = is_string($table) ? new Table($table) : $table;
    $this->type = $type;
  }

  public function on($name, $value = null) {
    if($value and (Field::isExpression($value) || Field::isFieldName($value)) ) {
      $name .= ' = ' . $value;
      $value = null;
    }
    if($this->_where) {
      if(is_null($value)) {
        $this->_where->addSegement($name);
      } else {
        $this->_where->addSegement(array($name, $value));
      }
    } else {
      if(is_null($value)) {
        $this->_where = new Where($this, $this->root, $name);
      } else {
        $this->_where = new Where($this, $this->root, array($name => $value));
      }
    }
    return $this->_where;
  }

  public function __toString() {
    $out = array($this->type, 'JOIN', (string)$this->table, 'ON', (string)$this->_where);
    return implode(' ', $out);
  }
}