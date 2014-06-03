<?php

namespace Relation;

abstract class RelationBase implements \IteratorAggregate {
  public $root;
  public $parent;

  public function __construct($parent, $root = null) {
    $this->parent = $parent;
    $this->root = $root ? $root : ($parent ? $parent->root : $parent);
  }

  function up() {
    return $this->parent;
  }

  function fieldName($f) {
    if(strpos($f, '.')) {
      return $this->q($f);
    } else {
      return $this->parent->fieldName($f);
    }
  }

  function q($f) {
    return $this->root->q($f);
  }

  function sub($or = ' OR ') {
    return $this->parent->sub($or);
  }

  function limit($limit, $offset = null) {
    return $this->parent->limit($limit, $offset);
  }

  function offset($offset) {
    return $this->parent->offset($offset);
  }

  function order($order) {
    return $this->parent->order($order);
  }

  function clearStmt() {
    return $this->parent->clearStmt();
  }

  function prepare($usingLimits = true) {
    return $this->parent->prepare($usingLimits);
  }

  function query($usingLimits = true) {
    return $this->parent->query($usingLimits);
  }

  function rowCount($usingLimits = true) {
    return $this->parent->rowCount($usingLimits);
  }

  function join($table, $type = 'INNER') {
    return $this->parent->join($table, $type);
  }

  function innerJoin($table) {
    return $this->join($table);
  }

  function outerJoin($table) {
    return $this->join($table, 'LEFT');
  }

  function leftJoin($table) {
    return $this->join($table, 'LEFT');
  }

  function rightJoin($table) {
    return $this->join($table, 'RIGHT');
  }

  function crossJoin($table) {
    return $this->join($table, 'CROSS');
  }

  function field($f) {
    return $this->parent->field($f);
  }

  function fields($f) {
    return $this->parent->fields($f);
  }

  function getIterator() {
    return $this->parent->getIterator();
  }

  function equals($k, $v, $type=null) {
    $this->parent->equals($k, $v, $type);
  }

  function notEquals($k, $v, $type=null) {
    $this->parent->notEquals($k, $v, $type);
  }

  function gt($field, $value, $type=null) {
    return $this->parent->gt($field, $value, $type);
  }

  function gte($field, $value, $type=null) {
    return $this->parent->gte($field, $value, $type);
  }

  function lt($field, $value, $type=null) {
    return $this->parent->lt($field, $value, $type);
  }

  function lte($field, $value, $type=null) {
    return $this->parent->lte($field, $value, $type);
  }

  function greaterThan($field, $value, $type=null) {
    return $this->parent->greaterThan($field, $value, $type);
  }

  function greaterThanEquals($field, $value, $type=null) {
    return $this->parent->greaterThanEquals($field, $value, $type);
  }

  function lessThan($field, $value, $type=null) {
    return $this->parent->lessThan($field, $value, $type);
  }

  function lessThanEquals($field, $value, $type=null) {
    return $this->parent->lessThanEquals($field, $value, $type);
  }
}