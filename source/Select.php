<?php

namespace Relation;

class Select extends RelationBase implements \IteratorAggregate {
  public $db;
  public $modelClass = false;
  public $fields = '*';
  public $table;
  protected $_where;
  protected $_joins;
  protected $_order;
  protected $_limit = false;
  protected $_offset = false;
  protected $_stmt = false;
  protected $_stmtNoLimit = false;
  protected $_stmtCount = false;
  protected $_stmtNoLimitCount = false;
  protected $_it;

  public function __construct($db, $modelClass, $parent, $root = null, $table = null, $fields = '*') {
    $this->modelClass = $modelClass;
    if(is_string($root)) {
      $table = $root;
      $root = null;
    }
    $this->db = $db;
    parent::__construct($parent, $root);
    $this->table = is_string($table) ? new Table($table) : $table;
    if(is_null($root)) {
      $root = $this;
    }
  }

  function limit($limit, $offset = null) {
    $this->_stmt = false;
    $this->_limit = $limit;
    if(!is_null($offset)) {
      $this->_offset = $offset;
    }
    return $this;
  }

  function offset($offset) {
    $this->_stmt = false;
    $this->_offset = $offset;
    return $this;
  }

  function sub($or = ' OR ') {
    if(!$this->_where) {
      $this->where(null);
      $this->_where->paste = $or;
      return $this->_where;
    }
    return $this->_where->sub($or);
  }

  function where($name, $value = null) {
    $this->clearStmt();
    if($this->_where) {
      if(is_null($value)) {
        $this->_where->addSegment($name);
      } else {
        $this->_where->equals($name, $value);
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

  function order($order) {
    $this->clearStmt();
    if(!$order || $order == ' ASC' || $order == ' DESC') return $this;
    if(!$this->_order) $this->_order = array();
    if(is_string($order)) {
      $this->_order[] = $order;
    } else if(is_array($order)) {
      $this->_order = array_merge($this->_order, $order);
    }
    return $this;
  }

  function __toString() {
    return $this->getString(true);
  }

  function q($v, $delimiter = "`") {
    $v = str_replace($delimiter, '', $v);
    if(stripos($v, '.') === false) {
      return $delimiter . $v . $delimiter;
    } else {
      $p = explode('.', $v);
      return $delimiter . implode($delimiter . '.' . $delimiter, $p) . $delimiter;
    }
  }

  function fieldName($f) {
    if(strpos($f, '.')) {
      return $this->q($f);
    } else {
      return $this->table->fieldName($f);
    }
  }

  function fields($fields) {
    $this->fields = $fields;
    return $this;
  }

  function field($f) {
    if($this->fields == '*') {
      $this->fields(array());
    }
    if(!is_array($this->fields)) {
      $this->fields(array($this->fields));
    }
    $this->fields[] = $f;
    return $this;
  }

  /**
   * Convert the select into various forms of strings.
   * @param  boolean $usingLimits true to include the limit clause
   * @param  boolean $count       true to yield a form with fields replaced with count(*)
   * @return string               A complete SQL String
   */
  function getString($usingLimits = true, $count = false) {
    $out = array('SELECT');
    $this->fieldsToString($out, $count);
    $out[] = 'FROM';
    $out[] = (string)$this->table;
    if($this->_joins) {
      $out[] = (string)implode(' ', $this->_joins);
    }
    if($this->_where) {
      $where = (string)$this->_where;
      if ($where) {
        $out[] = 'WHERE';
        $out[] = $where;
      }
    }
    if($this->_order && !$count) {
      $out[] = 'ORDER BY';
      $out[] = implode(', ', $this->_order);
    }
    if($usingLimits && !$count && $this->_limit) {
      $this->limitToString($out);
    }
    return implode(' ', $out);
  }

  protected function fieldsToString(&$out, $count) {
    if ($count) {
      $out[] = "COUNT(*)";
      return $out;
    }
    if(is_string($this->fields)) {
      $out[] = $this->fields;
    } else if(is_array($this->fields)) {
      $out[] = implode(', ', $this->fields);
    }
    return $out;
  }

  protected function limitToString(&$out) {
    $out[] = 'LIMIT';
    $out[] = ($this->_offset ? $this->_offset : 0) . ', ' . $this->_limit;
  }

  function clearStmt() {
    $this->_stmt = false;
    $this->_stmtNoLimit = false;
  }

  function prepare($usingLimits = true, $count = false) {
    $stmtName = $usingLimits ? "_stmt" : "_stmtNoLimit";
    if($count) $stmtName .= 'Count';
    $stmt = $this->$stmtName;
    if(!$stmt) {
      $stmt = $this->db->prepare($this->getString($usingLimits, $count));
      $this->$stmtName = $stmt;
    }
    return $stmt;
  }

  function query($usingLimits = true) {
    $stmt = $this->prepare($usingLimits);
    if($this->modelClass) {
      $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->modelClass);
    }
    $stmt->execute($this->_where ? $this->_where->parameters() : null);
    return $stmt;
  }

  function rowCount($usingLimits = false) {
    $stmt = $this->prepare($usingLimits);
    $stmt->execute($this->_where ? $this->_where->parameters() : null);
    return $stmt->rowCount();
  }

  function getIterator() {
    return new \IteratorIterator($this->query());
  }

  function join($table, $type = 'INNER') {
    $join = new Join($this, $table, $type);
    if(!$this->_joins) {
      $this->_joins = array();
    }
    $this->_joins[] = $join;
    return $join;
  }

  function parameters() {
    $out = array();
    if($this->_joins) {
      foreach ($this->_joins as $join) {
        $out += $join->_where->parameters();
      }
    }
    if($this->_where) {
      $out += $this->_where->parameters();
    }
    return $out ? $out : null;
  }

  function equals($k, $v, $type=null) {
    if(!$this->_where) {
      $this->where(null);
    }
    return $this->_where->equals($k, $v, $type);
  }
  function inArray($k, $v) {
    if(!$this->_where) {
      $this->where(null);
    }
    return $this->_where->inArray($k, $v);
  }
  function notEquals($k, $v, $type=null) {
    if(!$this->_where) {
      $this->where(null);
    }
    return $this->_where->notEquals($k, $v, $type);
  }
}