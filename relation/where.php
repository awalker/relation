<?php

namespace Relation;

class Where extends RelationBase {
  protected $_segments;
  public $paste = ' AND ';
  public $op = '=';
  public $prefix = '';
  public $suffix = '';
  protected $_parameters;

  public function __construct($parent, $root = null, $start = null) {
    if(is_string($root) || is_array($root)) {
      $start = $root;
      $root = null;
    }
    parent::__construct($parent, $root);
    $this->setSegments($start);
  }

  function setSegments($segments) {
    $this->_segments = array();
    if(is_null($segments)) {
      return;
    }
    $this->addSegments($segments);
    return $this;
  }

  function addSegment($value) {
    $this->_segments[] = $value;
    return $this;
  }

  function andValue($value) {
    $this->paste = ' AND ';
    return $this->addSegment($value);
  }

  function equals($field, $value, $type=null) {
    return $this->addSegment(new Segment($this, $field, $value, '=', $type));
  }

  function gt($field, $value, $type=null) {
    return $this->greaterThan($field, $value, $type);
  }

  function gte($field, $value, $type=null) {
    return $this->greaterThanEquals($field, $value, $type);
  }

  function lt($field, $value, $type=null) {
    return $this->lessThan($field, $value, $type);
  }

  function lte($field, $value, $type=null) {
    return $this->lessThanEquals($field, $value, $type);
  }

  function greaterThan($field, $value, $type=null) {
    return $this->addSegment(new Segment($this, $field, $value, '>', $type));
  }

  function greaterThanEquals($field, $value, $type=null) {
    return $this->addSegment(new Segment($this, $field, $value, '>=', $type));
  }

  function lessThan($field, $value, $type=null) {
    return $this->addSegment(new Segment($this, $field, $value, '<', $type));
  }

  function lessThanEquals($field, $value, $type=null) {
    return $this->addSegment(new Segment($this, $field, $value, '=<', $type));
  }

  function isNull($field) {
    return $this->addSegment(new Segment($this, $field, null, 'IS NULL'));
  }

  function isNotNull($field) {
    return $this->addSegment(new Segment($this, $field, null, 'IS NOT NULL'));
  }

  function notEquals($field, $value, $type=null) {
    return $this->addSegment(new Segment($this, $field, $value, '<>', $type));
  }

  function like($field, $value) {
    return $this->addSegment(new Segment($this, $field, $value, 'LIKE'));
  }

  function inArray($field, $a) {
    // TODO: Add some checking
    return $this->addSegment( $field .' IN (' . implode(',', $a) . ')');
  }

  function orValue($value) {
    $this->paste = ' OR ';
    return $this->addSegment($value);
  }

  function sub($paste = ' OR ') {
    $sub = new Where($this, $this->root, null);
    $sub->prefix = '(';
    $sub->suffix = ')';
    $sub->paste = $paste;
    $this->addSegment($sub);
    return $sub;
  }

  function setParameters(array $ps) {
    $this->_parameters = $ps;
    return $this;
  }

  function addToParameters(&$ps) {
    $mine = $this->parameters();
    $ps = array_merge($ps, $mine);
    return $ps;
  }

  function parameters() {
    $ps = array();
    foreach ($this->_segments as $key => $value) {
      if(is_object($value)) {
        $ps = $value->addToParameters($ps);
      }
    }
    if(!is_null($this->_parameters)) {
      $ps = array_merge($ps, $this->_parameters);
    }
    return $ps;
  }

  function addParameter($name, $value) {
    if(!$this->_parameters) {
      $this->_parameters = array();
    }
    $this->_parameters[$name] = $value;
    return $this;
  }

  function addSegments($segments) {
    if($segments && is_string($segments)) {
      $this->addSegment($segments);
    } else if($segments && is_array($segments)) {
      foreach ($segments as $key => $value) {
        if(is_numeric($key)) {
          $this->addSegment($value);
        } else {
          $this->equals($key, $value);
        }
      }
    } else if($segments && is_object($segments)) {
      $this->addSegment($segments);
    }
    return $this;
  }

  function __toString() {
    $segments = array();
    foreach ($this->_segments as $key => $value) {
      $segments[] = (string)$value;
    }
    return $this->prefix . implode($this->paste, $segments) . $this->suffix;
  }
}