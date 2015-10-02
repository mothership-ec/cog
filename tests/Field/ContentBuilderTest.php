<?php

namespace Message\Cog\Test\Field;

use Message\Cog\Field;
use Mockery as m;

class ContentBuilderTest extends \PHPUnit_Framework_TestCase
{
	public function testBuildBasicContent()
	{
		$builder = new Field\ContentBuilder;

		$factory = m::mock('\\Message\\Cog\\Field\\Factory')
			->shouldReceive('getIterator')
			->zeroOrMoreTimes()
			->andReturn(new \ArrayIterator([
				'field_1' => m::mock('\\Message\\Cog\\Field\\BaseField')
					->shouldReceive('setValue')
					->once()
					->with('value_1')

					->getMock(),
				'field_2' => m::mock('\\Message\\Cog\\Field\\BaseField')
					->shouldReceive('setValue')
					->once()
					->with('value_2')

					->getMock()
			]))

			->getMock()
		;

		$row1 = [
			'field' => 'field_1',
			'value' => 'value_1'
		];

		$row2 = [
			'field' => 'field_2',
			'value' => 'value_2'
		];

		$content = $builder->buildContent($factory, [
			'' => [
				(object) $row1,
				(object) $row2
			]
		]);

		$this->assertEquals(2, $content->count());
	}

	public function testBuildGroupContent()
	{
		$builder = new Field\ContentBuilder;
		$hidden = m::mock('\\Message\\Cog\\Field\\Type\\Hidden')->makePartial();

		$group1 = m::mock('\\Message\\Cog\\Field\\Group')
			->shouldReceive('isRepeatable')
			->once()
			->andReturn(false)

			->shouldReceive('add')
			->once()
			->with($hidden)
			->passthru()

			->shouldReceive('getIdentifierField')
			->zeroOrMoreTimes()
			->passthru()

			->shouldReceive('get')
			->with('_sequence')
			->andReturn($hidden)

			->getMock()
		;

		$factory = m::mock('\\Message\\Cog\\Field\\Factory')
			->shouldReceive('getIterator')
			->zeroOrMoreTimes()
			->andReturn(new \ArrayIterator([
				'group_1' => $group1
			]))

			->shouldReceive('getField')
			->once()
			->andReturn($hidden)

			->getMock()
		;

		$row1 = [
			'field'    => 'field_1',
			'value'    => 'value_1',
			'sequence' => '0'
		];

		$row2 = [
			'field'    => 'field_1',
			'value'    => 'value_2',
			'sequence' => '1'
		];

		$content = $builder->buildContent($factory, [
			'group_1' => [
				(object) $row1,
				(object) $row2
			]
		]);

		$this->assertEquals(1, $content->count());
		$this->assertEquals($group1, $content->group_1);
		$this->assertTrue($content->group_1 instanceof Field\Group);
	}

	public function testBuildRepeatableContent()
	{
		$builder = new Field\ContentBuilder;
		$hidden = m::mock('\\Message\\Cog\\Field\\Type\\Hidden')->makePartial();

		$group1 = m::mock('\\Message\\Cog\\Field\\Group')
			->shouldReceive('isRepeatable')
			->once()
			->andReturn(true)

			->shouldReceive('add')
			->once()
			->with($hidden)
			->passthru()

			->shouldReceive('getIdentifierField')
			->zeroOrMoreTimes()
			->passthru()

			->shouldReceive('get')
			->with('_sequence')
			->andReturn($hidden)

			->getMock()
		;

		$factory = m::mock('\\Message\\Cog\\Field\\Factory')
			->shouldReceive('getIterator')
			->zeroOrMoreTimes()
			->andReturn(new \ArrayIterator([
				'group_1' => $group1
			]))

			->shouldReceive('getField')
			->once()
			->andReturn($hidden)

			->getMock()
		;

		$row1 = [
			'field'    => 'field_1',
			'value'    => 'value_1',
			'sequence' => '0'
		];

		$row2 = [
			'field'    => 'field_1',
			'value'    => 'value_2',
			'sequence' => '1'
		];

		$content = $builder->buildContent($factory, [
			'group_1' => [
				(object) $row1,
				(object) $row2
			]
		]);

		$this->assertEquals(1, $content->count());
		$this->assertEquals(2, $content->group_1->count());
		$this->assertTrue($content->group_1 instanceof Field\RepeatableContainer);
	}

	public function testBuildPassedContent()
	{
		$builder = new Field\ContentBuilder;
		$content = new Field\Content;

		$factory = m::mock('\\Message\\Cog\\Field\\Factory')
			->shouldReceive('getIterator')
			->zeroOrMoreTimes()
			->andReturn(new \ArrayIterator([
				'field_1' => m::mock('\\Message\\Cog\\Field\\BaseField')
					->shouldReceive('setValue')
					->once()
					->with('value_1')

					->getMock(),
				'field_2' => m::mock('\\Message\\Cog\\Field\\BaseField')
					->shouldReceive('setValue')
					->once()
					->with('value_2')

					->getMock()
			]))

			->getMock()
		;

		$row1 = [
			'field' => 'field_1',
			'value' => 'value_1'
		];

		$row2 = [
			'field' => 'field_2',
			'value' => 'value_2'
		];

		$builder->buildContent($factory, [
			'' => [
				(object) $row1,
				(object) $row2
			]
		], $content);

		$this->assertEquals(2, $content->count());
	}

	public function testBuildPassedContentString()
	{
		$builder = new Field\ContentBuilder;

		$factory = m::mock('\\Message\\Cog\\Field\\Factory')
			->shouldReceive('getIterator')
			->zeroOrMoreTimes()
			->andReturn(new \ArrayIterator([
				'field_1' => m::mock('\\Message\\Cog\\Field\\BaseField')
					->shouldReceive('setValue')
					->once()
					->with('value_1')

					->getMock(),
				'field_2' => m::mock('\\Message\\Cog\\Field\\BaseField')
					->shouldReceive('setValue')
					->once()
					->with('value_2')

					->getMock()
			]))

			->getMock()
		;

		$row1 = [
			'field' => 'field_1',
			'value' => 'value_1'
		];

		$row2 = [
			'field' => 'field_2',
			'value' => 'value_2'
		];

		$content = $builder->buildContent($factory, [
			'' => [
				(object) $row1,
				(object) $row2
			]
		], '\\Message\\Cog\\Field\\Content');

		$this->assertEquals(2, $content->count());
	}
}