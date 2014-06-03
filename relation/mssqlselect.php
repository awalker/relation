<?php
namespace Relation;

/**
 * A stupid hack to get Select working with MS-SQL 2012
 */
class MSSqlSelect extends Select //implements \IteratorAggregate
{

  /**
   * Returns MS-SQL 2012 style limit/offset which is actually offset/fetch
   */
  protected function limitToString(&$out) {
    $out[] = 'OFFSET';
    $out[] = $this->_offset ? $this->_offset : 0;
    $out[] = 'ROWS';

    $out[] = 'FETCH NEXT';
    $out[] = $this->_limit;
    $out[] = 'ROWS ONLY';
  }

  function q($v, $delimiter = "") {
    if(stripos($v, '.') === false) {
      return $delimiter . $v . $delimiter;
    } else {
      $p = explode('.', $v);
      return $delimiter . implode($delimiter . '.' . $delimiter, $p) . $delimiter;
    }
  }


  function getString($usingLimits = true, $count = false) {
    $str = parent::getString($usingLimits, $count);
    $str = str_replace('`', '', $str);
    return $str;
  }

  function rowCount($usingLimits = false) {
    $stmt = $this->prepare($usingLimits, true);
    $stmt->execute($this->_where ? $this->_where->parameters() : null);
    $stmt->setFetchMode(\PDO::FETCH_COLUMN, 0);
    return $stmt->fetch();
  }
}
