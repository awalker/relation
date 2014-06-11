<?php
namespace Relation;

class SelectTest extends \PHPUnit_Framework_TestCase {

  function testBasicConstruction() {
    $select = new Select(null, null, null);
    $this->assertNotNull($select);
    return $select;
  }

  /**
   * @depends testBasicConstruction
   */
  function testQ($select) {
    $this->assertEquals('`foo`', $select->q('foo'));
    $this->assertEquals('|foo|', $select->q('foo', '|'));
  }

  /**
   * @depends testBasicConstruction
   */
  function testAvoidDoubleQuoting($select) {
    $this->assertEquals("`foo`", $select->q('`foo`'));
    $this->assertEquals("|foo|", $select->q('|foo|', '|'));
  }

  function testLimit() {
    $select = new Select(null, null, null, null, 't');
    $select->limit(30);
    $this->assertEquals('SELECT * FROM `t` LIMIT 0, 30', (string)$select);
    $select->limit(30, 60);
    $this->assertEquals('SELECT * FROM `t` LIMIT 60, 30', (string)$select);
    $select->offset(90);
    $this->assertEquals('SELECT * FROM `t` LIMIT 90, 30', (string)$select);
  }

  function testOrder() {
    $select = new Select(null, null, null, null, 't');
    $select->order('foo');
    $this->assertEquals('SELECT * FROM `t` ORDER BY foo', (string)$select);
    $select->order('bar ASC');
    $this->assertEquals('SELECT * FROM `t` ORDER BY foo, bar ASC', (string)$select);
  }

  /**
   * @depends testOrder
   */
  function testOrderArray() {
    $select = new Select(null, null, null, null, 't');
    $select->order(array('foo ASC','bar DESC'));
    $this->assertEquals('SELECT * FROM `t` ORDER BY foo ASC, bar DESC', (string)$select);
  }

  /**
   * @depends testOrder
   */
  function testOrderLazyInput() {
    $select = new Select(null, null, null, 't');
    $select->order(' ASC');
    $this->assertEquals('SELECT * FROM `t`', (string)$select);
    $select->order(' DESC');
    $this->assertEquals('SELECT * FROM `t`', (string)$select);
  }

  function testFields() {
    $select = new Select(null, null, null, 't');
    $select
    ->fields('t.*')
    ->field('u.name');
    $this->assertEquals('SELECT t.*, u.name FROM `t`', (string)$select);
  }

  function testFieldArray() {
    $select = new Select(null, null, null, 't');
    $select
    ->fields(array('t.steve', $select->fieldName('id')))
    ->field($select->fieldName('u.name'));
    $this->assertEquals('SELECT t.steve, `t`.`id`, `u`.`name` FROM `t`', (string)$select);
  }

  function testSub() {
    $select = new Select(null, null, null, 't');
    $select->sub()->equals('foo', 1)->gt('boo', 3);
    $select->sub(' GLUE ')->lt('baz', 5)->lte('faz', 7);
    $this->assertEquals("SELECT * FROM `t` WHERE foo = :foo OR boo > :boo OR (baz < :baz GLUE faz <= :faz)", (string)$select);
  }

  /**
   * @depends testLimit
   * @depends testOrder
   */
  function testComplex() {
    $select = new Select(null, null, null, 't');
    $select
    ->where('bar', 2)
    ->isNull('baz')
    ->sub()->equals('goo','boo')->isNotNull('qoo')
    ->order('foo ASC')
    ->leftJoin('user u')->on('u.id = t.user_id')->gte('u.type', 5)
    ->field('u.name')
    ->field('t.steve')
    ->limit(3,6);
    $this->assertEquals('SELECT u.name, t.steve FROM `t` LEFT JOIN `user` `u` ON u.id = t.user_id AND u.type >= :type WHERE bar = :bar AND baz IS NULL AND (goo = :goo OR qoo IS NOT NULL) ORDER BY foo ASC LIMIT 6, 3', (string)$select);

    $this->assertEquals(array('bar'=>2, 'goo'=>'boo', 'type'=>5), $select->parameters());
  }

  function testDryForwardEquals() {
    $select = new Select(null, null, null, 't');
    $select->equals('foo', 'bar');
    $select->where('4=1');
    $select->where('goo', 'bar');
    $this->assertEquals('SELECT * FROM `t` WHERE foo = :foo AND 4=1 AND goo = :goo', (string)$select);
  }

  function testDryForwardNotEquals() {
    $select = new Select(null, null, null, 't');
    $select->notEquals('foo', 'bar');
    $this->assertEquals('SELECT * FROM `t` WHERE foo <> :foo', (string)$select);
  }

  function testDryForwardInArray() {
    $select = new Select(null, null, null, 't');
    $select->inArray('foo', array(1, 2, 3));
    $this->assertEquals("SELECT * FROM `t` WHERE foo IN (1,2,3)", (string)$select);
  }

  function testRowCount() {
    $pdo = $this->getMock('PDO', array('prepare'), array('sqlite::memory:'));
    $pdostmt = $this->getMock('PDOStatement');

    $pdostmt->expects($this->once())
    ->method('execute');
    $pdostmt->expects($this->once())
    ->method('rowCount')
    ->will($this->returnValue(1));

    $pdo->expects($this->once())
    ->method('prepare')
    ->with($this->equalTo('SELECT * FROM `t`'))
    ->will($this->returnValue($pdostmt));

    $select = new Select($pdo, null, null, 't');
    $select->limit(3,5);
    $r = $select->rowCount();
    $this->assertEquals(1, $r);
  }

  function testPrepare() {
    $pdo = $this->getMock('PDO', array('prepare'), array('sqlite::memory:'));
    $pdostmt = $this->getMock('PDOStatement');

    $pdo->expects($this->once())
    ->method('prepare')
    ->with($this->equalTo('SELECT * FROM `t` LIMIT 5, 3'))
    ->will($this->returnValue($pdostmt));

    $select = new Select($pdo, null, null, 't');
    $select->limit(3,5);
    return $select->prepare();
  }

  function testPrepareCount() {
    $pdo = $this->getMock('PDO', array('prepare'), array('sqlite::memory:'));
    $pdostmt = $this->getMock('PDOStatement');

    $pdo->expects($this->once())
    ->method('prepare')
    ->with($this->equalTo('SELECT COUNT(*) FROM `t`'))
    ->will($this->returnValue($pdostmt));

    $select = new Select($pdo, null, null, 't');
    $select->limit(3,5);
    return $select->prepare(false, true);
  }

  function testGetIterator() {
    $pdo = $this->getMock('PDO', array('prepare'), array('sqlite::memory:'));
    $pdostmt = $this->getMock('PDOStatement');

    $pdostmt->expects($this->once())
    ->method('execute')
    ->with($this->equalTo(array('foo'=>5)))
    ->will($this->returnValue(true));

    $pdo->expects($this->once())
    ->method('prepare')
    ->with($this->equalTo('SELECT * FROM `t` WHERE foo = :foo'))
    ->will($this->returnValue($pdostmt));

    $select = new Select($pdo, 'stdClass', null, 't');
    $select->where('foo', 5);
    $out = array();
    // Can't really mock Traversable :(
    foreach($select as $obj) {
      $out[] = $obj;
    }
    $this->assertEquals(array(), $out);
  }

  function testEmptyWhere() {
    $select = new Select(null, null, null, 't');
    $select
    ->limit(3, 3)
    ->where(null);
    $this->assertEquals('SELECT * FROM `t` LIMIT 3, 3', (string)$select);
    $this->assertNull($select->parameters());
  }

}
