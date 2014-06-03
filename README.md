# Relation

A set of classes for PHP loosely inspired by ActiveRelation.

Relation is a chain-able API for creating SELECT queries. It grew from my desire
to clean up controller code that applied optional filters and sorting to SQL.

Is is currently rather specific to my needs and only fully supports
MySQL and partially supports MSSql 2012 (in a very quick, dirty, buggy, in-secure
fashion). It uses PDO.

## Apology

Firstly, sorry. This was written quickly for a single specific project. It has limited
support for other databases and mostly only supports MySql. Some methods and properties
are poorly named and the code formatting is probably inconsistent and does not follow
other project's guidelines (I lose no sleep over that). Things are not properly documented.

In future versions I'd like to refactor and make it easier to support other DB providers.

## Examples

For best results, create model methods that return a pre-configured ```Select``` object

    class Foo extends Model {
      // other code ...

      public static function select() {
        return new \Relation\Select(static::getConnection(), get_called_class(), null, null, 'table_foo');
      }
    }

So then, in other code, you could:

    foreach (\Foo::select()->where('is_active', 1) as $foo) {
      print_r($foo);
    }

Or get more complex:

    $select = \Foo->fields(array(
      'table_foo.*',
      'a.name bar_name',
      'b.name baz_name',
      'b.short_name short_baz_name'
      ))
    ->where('is_active', 1);
    $select->leftJoin('bar a')->on('table_foo.bar_id', 'a.id')
    $select->leftJoin('baz b')->on('table_foo.baz_id', 'b.id')

    if (request('filter') == 'fresh') {
      $select->greaterThanEquals('created_on', new \DateTime('yesterday')->format('Y-m-d'))
    }

    if (request('sorted') == 1) {
      $select->order('table_foo.name');
    }

    foreach ($select as $foo) {
      print_r($foo);
    }

## Roadmap

* Add Document
* Refactor to allow support for more DB providers