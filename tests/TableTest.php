<?php
namespace Relation;

class TableTest extends \PHPUnit_Framework_TestCase {

  function testBasic() {
    $t = new Table('table');
    $this->assertEquals('`table`', (string)$t);
  }

  function testAliasExplict() {
    $t = new Table('table','t');
    $this->assertEquals('`table` `t`', (string)$t);
  }

  function testAliasImplict() {
    $t = new Table('table t');
    $this->assertEquals('`table` `t`', (string)$t);
  }

  function testDBExplict() {
    $t = new Table('table', 't', 'db');
    $this->assertEquals('`db`.`table` `t`', (string)$t);
  }


  function testDBImplict() {
    $t = new Table('db.table t');
    $this->assertEquals('`db`.`table` `t`', (string)$t);
  }

  function testFieldName() {
    $t = new Table('db.table t');
    $this->assertEquals('`t`.`field`', $t->fieldName('field'));
    $a = new Table('table');
    $this->assertEquals('`table`.`field`', $a->fieldName('field'));
  }

  function testAliasMethod() {
    $t = new Table('table', 't');
    $this->assertEquals('`t`', $t->alias());
    $t = new Table('table');
    $this->assertEquals('`table`', $t->alias());
  }

  function testAvoidDoubleQuoteing() {
    $t = new Table('`table`');
    $this->assertEquals('`table`', (string)$t);
    $t = new Table('`db`.`table` `t`');
    $this->assertEquals('`db`.`table` `t`', (string)$t);
    $t = new Table('db.`table` t');
    $this->assertEquals('`db`.`table` `t`', (string)$t);
  }

  function testSharding() {
    Table::$sharding = array('FOO' => 'bar');
    $t = new Table('FOO.table t');
    $this->assertEquals('`bar`.`table` `t`', (string)$t);
  }

}
