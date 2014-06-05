<?php
namespace Relation;

class SegmentTest extends \PHPUnit_Framework_TestCase {
  function testBasic() {
    $s = new Segment(null, 'field', 'value');
    $this->assertNotNull($s);
  }

  function testBasicString() {
    $s = new Segment(null, 'field', 'value');
    $this->assertEquals('field = :field', (string)$s);
  }

  function testTableStringEquals() {
    $params = array();
    $s = new Segment(null, 'table.field', 'value');
    $s->addToParameters($params);
    $this->assertEquals('table.field = :field', (string)$s);
    $this->assertEquals('value', $params['field']);
    $s = new Segment(null, 'table.field', null);
    $this->assertEquals('table.field IS NULL', (string)$s);
  }

  function testTableStringNotIncludeValue() {
    $s = new Segment(null, 'table.field', 'value', 'IS NULL');
    $this->assertEquals('table.field IS NULL', (string)$s);
  $s = new Segment(null, 'table.field', null, 'IS NULL');
  $this->assertEquals('table.field IS NULL', (string)$s);
  }

  function testStringNotEqual() {
    $s = new Segment(null, 'table.field', 'value', '<>');
    $this->assertEquals('table.field <> :field', (string)$s);
    $s = new Segment(null, 'table.field', null, '<>');
    $this->assertEquals('table.field IS NOT NULL', (string)$s);
  }
}