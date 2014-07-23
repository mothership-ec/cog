<?php

namespace Message\Cog\Test\ValueObject;

use Message\Cog\ValueObject\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	const SORT_KEY   = 'key';
	const SORT_VALUE = 'value';

	/**
	 * Checks configure is called in __construct.
	 */
	public function testConfigureCalledInConstruct()
	{
		$mock = $this->getMockBuilder('\Message\Cog\ValueObject\Collection')
			->setMethods(array('_configure'))
			->disableOriginalConstructor()
			->getMock();

		$mock->expects($this->once())
			->method('_configure');

		$mock->__construct();
	}

	/**
	 *
	 *
	 */
	public function testSetKeyToCallable()
	{
		$collection = new Collection;
		$collection->setKey(function($item) {
			return $item['key']['thing'];
		});

		$value = [
			'key' => [
				'thing' => 'Hi there',
			],
			'test' => 'value',
		];

		$collection->add($value);

		$this->assertSame($value, $collection->get('Hi there'));
	}

	/**
	 * Check adding the key to an empty array doesn't throw an error.
	 */
	public function testSetKeyOnObjectValue()
	{
		$collection = new Collection();

		$this->assertSame($collection, $collection->setKey('key'));

		$object = (object) ['key' => 'hi'];

		$collection->add($object);

		$this->assertSame($object, $collection->get('hi'));
	}

	/**
	 * Checks setting a key on a non-empty collection throws the correct exception.
	 *
	 * @expectedException \LogicException
	 */
	public function testSetKeyOnNotEmptyThrowsException()
	{
		$item = [0 => "hello"];
		$collection = new Collection($item);

		$collection->setKey("key");
	}

	/**
	 * Checks setting a type on a non-empty collection throws the correct exception.
	 *
	 * @expectedException \LogicException
	 */
	public function testSetTypeOnNotEmptyThrowsException()
	{
		$item = [0 => "hello"];
		$collection = new Collection($item);

		$collection->setType("\DateTime");
	}

	/**
	 * Check using array notation throws the correct exception.
	 *
	 * @expectedException \BadMethodCallException
	 */
	public function testArrayAccessSettingThrowsException()
	{
		$collection = new Collection;

		$collection['hello'] = 'my thing';
	}

	/**
	 * Checks sort by key is the default.
	 */
	public function testDefaultSortingIsByKey()
	{
		$collection = new Collection;
		$collection->setKey('pos');
		$collection->add(['pos' => 3]);
		$collection->add(['pos' => 5]);
		$collection->add(['pos' => -1]);
		$collection->add(['pos' => 0]);

		$sorted = [
			['pos' => -1],
			['pos' => 0],
			['pos' => 3],
			['pos' => 5],
		];

		$this->assertSame($sorted, array_values($collection->all()));
	}

	/**
	 * Checks setting sort after adding some items triggers a re-sort.
	 */
	public function testCollectionIsResortedAfterSettingSort()
	{
		$collection = new Collection;
		$collection->setKey('pos');
		$collection->add(['pos' => 3]);
		$collection->add(['pos' => 5]);
		$collection->add(['pos' => -1]);
		$collection->add(['pos' => 0]);

		$collection->setSort(function($a, $b) {
			return ($a > $b) ? -1 : 1;
		}, self::SORT_KEY);

		$sorted = [
			['pos' => 5],
			['pos' => 3],
			['pos' => 0],
			['pos' => -1],
		];

		$this->assertSame($sorted, array_values($collection->all()));
	}


	/**
	 * Tests sort by value works correctly.
	 */
	public function testSortCollectionByValue()
	{
		$collection = new Collection;
		$collection->add(3);
		$collection->add(5);
		$collection->add(-1);
		$collection->add(0);

		$this->assertSame($collection,$collection->setSort(function($a, $b) {
			return ($a > $b) ? -1 : 1;
		}, self::SORT_VALUE));

		$sorted = [5,3,0,-1];

		$this->assertSame($sorted, array_values($collection->all()));
	}

	/**
	 * Tests calling setSort() with a "by" that is not valid.
	 *
	 * @expectedException \LogicException
	 */
	public function testSortingByNotKeyOrValueThrowsError()
	{
		$collection = new Collection;

		$collection->setSort(function($a, $b) {
			return ($a < $b) ? -1 : 1;
		}, 'testing');
	}

	/**
	 * Tests setting validation and adding item that does match.
	 */
	public function testValidationPasses()
	{
		$collection = new Collection;

		$this->assertSame($collection, $collection->addValidator(function($item) {
			if ($item == 'goodbye') {
				return false;
			}
		}));

		$this->assertSame($collection, $collection->add('hello'));
		$this->assertEquals(['hello'], $collection->all());
		$this->assertEquals(1, $collection->count());
		$this->assertCount(1, $collection);
	}

	/**
	 * Tests setting validation and adding item that doesn't match.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testValidationFailes()
	{
		$collection = new Collection;

		$collection->addValidator(function($item) {
			if ($item == 'hello') {
				return false;
			}
		});

		$collection->add('hello');

		$this->assertEquals(['hello'], $collection->all());
		$this->assertEquals(1, $collection->count());
		$this->assertCount(1, $collection);
	}

	/**
	 * Tests setting validation on a non-empty collection.
	 *
	 * @expectedException \LogicException
	 */
	public function testAddValidationOnNonEmptyCollectionThrowsError()
	{
		$collection = new Collection;
		$item = ['hello' => 'hi'];

		$collection->add($item);

		$collection->addValidator(function($item) {
			if ($item['hello'] == 'goodbye') {
				return false;
			}
		});
	}

	/**
	 * Checks counting items in an empty array is zero.
	 * Checks all() returns nothing.
	 */
	public function testEmptyArrayHasZeroCount()
	{
		$collection = new Collection([]);

		$this->assertEquals(0,$collection->count());
	 	$this->assertEquals([], $collection->all());
	}

	/**
	 * Checks counting items in an non-empty array is correct.
	 * Checks all() returns all values in an non-empty array.
	 */
	public function testInstantiateWithOneItem()
	{
		$values = [0 => "hello"];
		$collection = new Collection($values);

		$this->assertEquals(1,$collection->count());
		$this->assertEquals($values, $collection->all());

		foreach ($collection as $key => $item) {
			$this->assertEquals($values[$key], $item);
		}

		return $collection;
	}

	/**
	 * Checks adding and removing items works correctly.
	 *
	 * @depends testInstantiateWithOneItem
	 */
	public function testAddingAndRemovingItems(Collection $collection)
	{
		$values = [0 => "hello"];

		$collection->add('hello again');

		$this->assertEquals(array_merge($values, ['hello again']), $collection->all());
		$this->assertEquals(2, $collection->count());
		$this->assertCount(2, $collection);

		$this->assertSame($collection, $collection->remove(0));
		$this->assertEquals(1, $collection->count());
		$this->assertCount(1, $collection);
	}

	/**
	 * Check adding item to array with no key set.
	 */
	public function testArrayAccessWithDefaultZeroIndexedKeys()
	{
		$collection = new Collection;
		$item1 = ['hello' => 'hi'];

		$this->assertFalse(isset($collection[0]));

		$collection->add($item1);

		$this->assertTrue(isset($collection[0]));
		$this->assertSame($item1, $collection[0]);

		unset($collection[0]);

		$this->assertFalse(isset($collection[0]));
		$this->assertSame([], $collection->all());
	}

	/**
	 * Check adding multiple items are added and removed correctly.
	 */
	public function testArrayAccessWithCustomKeys()
	{
		$collection = new Collection;
		$item1 = ['hello' => 'hi'];
		$item2 = ['hello' => 'hey'];

		$collection->setKey('hello');

		$this->assertFalse(isset($collection['hi']));

		$collection->add($item1);
		$collection->add($item2);

		$this->assertTrue(isset($collection['hi']));
		$this->assertSame($item1, $collection['hi']);

		$this->assertTrue(isset($collection['hey']));
		$this->assertSame($item2, $collection['hey']);

		unset($collection['hi']);

		$this->assertFalse(isset($collection['hi']));

		unset($collection['hey']);

		$this->assertFalse(isset($collection['hi']));

		$this->assertSame([], $collection->all());
	}

	/**
	 * Checks removing an item which doesn't exist throws the correct exception.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testRemovingItemDoesntExist()
	{
		$item = [0 => "hello"];
		$collection = new Collection($item);

		$collection->remove(3);
	}

	/**
	 * Checks getting an item which doesn't exist throws the correct exception.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testGettingItemThatDoesntExist()
	{
		$item = [0 => "hello"];
		$collection = new Collection($item);

		$collection->get(3);
	}

	/**
	 * Checks adding an item with a key that already exists throws exception.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddingItemWithSameKeyThrowsException()
	{
		$collection = new Collection();
		$collection->setKey('hello');

		$item = ['hello' => 'hi'];

		$collection->add($item);
		$collection->add($item);
	}

	/**
	 * Checks adding an item with a different type throws the correct exception.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddItemTypeDoesntMatch()
	{
		$collection = new Collection();

		$this->assertSame($collection, $collection->setType("\DateTime"));

		$object = (object) ['key' => 'hi'];

		$collection->add($object);
	}

	/**
	 * Checks adding an object item with no key throws the correct exception.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddObjectItemDoesntHaveKey()
	{
		$collection = new Collection();
		$collection->setKey('hello');

		$object = (object) [NULL => 'hi'];

		$collection->add($object);
	}

	/**
	 * Checks adding an array item with no key throws the correct exception.
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testAddArrayItemDoesntHaveKey()
	{
		$collection = new Collection();
		$collection->setKey('hello');

		$item = [NULL => 'hi'];

		$collection->add($item);
	}

	/**
	 * Checks item must be an array or object when a key is set.
	 *
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Item must be array or object
	 */
	public function testAddingItemMustBeArrayOrOjectWhenHasKey()
	{
		$collection = new Collection;
		$collection->setKey('hello');

		$collection->add("hi");
	}

	public function testSerialization()
	{
		$collection = new Collection;

		$collection->setKey(function($item) {
			return $item['id'];
		});

		$collection->setSort(function($a, $b) {
			return ($a > $b) ? -1 : 1;
		}, $collection::SORT_KEY);

		$collection->addValidator(function($item) {
			return array_key_exists('id', $item);
		});

		$this->assertInstanceOf('Serializable', $collection);

		$serialized   = serialize($collection);
		$unserialized = unserialize($serialized);

		$item1 = ['id' => 1, 'hello' => 'word'];
		$item2 = ['id' => 2, 'hello' => 'word'];

		$unserialized->add($item1);
		$unserialized->add($item2);

		$this->assertSame($item1, $unserialized->get(1));
		$this->assertSame([2 => $item2, 1 => $item1], $unserialized->all());

		try {
			$unserialized->add(['hey' => 'i dont have an id!']);
		}
		catch (\InvalidArgumentException $e) {
			return true;
		}

		$this->fail('Validation did not fail after being serialized.');
	}
}