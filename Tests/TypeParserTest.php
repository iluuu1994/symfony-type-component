<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Type\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Type\Parser\TypeParser;
use Symfony\Component\Type\Type\ArrayType;
use Symfony\Component\Type\Type\BoolType;
use Symfony\Component\Type\Type\ClassType;
use Symfony\Component\Type\Type\FloatType;
use Symfony\Component\Type\Type\IntersectionType;
use Symfony\Component\Type\Type\IntType;
use Symfony\Component\Type\Type\MixedType;
use Symfony\Component\Type\Type\NullType;
use Symfony\Component\Type\Type\ParenthesesType;
use Symfony\Component\Type\Type\StringType;
use Symfony\Component\Type\Type\UnionType;

class TypeParserTest extends TestCase
{
    /** @var TypeParser */
    private $typeParser;

    protected function setUp()
    {
        $this->typeParser = new TypeParser();
    }

    public function testPrimitiveTypes()
    {
        $this->assertInstanceOf(BoolType::class, $this->typeParser->parse('bool'));
        $this->assertInstanceOf(IntType::class, $this->typeParser->parse('int'));
        $this->assertInstanceOf(StringType::class, $this->typeParser->parse('string'));
        $this->assertInstanceOf(FloatType::class, $this->typeParser->parse('float'));
        $this->assertInstanceOf(NullType::class, $this->typeParser->parse('null'));
        $this->assertInstanceOf(MixedType::class, $this->typeParser->parse('mixed'));
    }

    public function testPrimitiveTypesCaseInsensitivity()
    {
        $this->assertInstanceOf(BoolType::class, $this->typeParser->parse('Bool'));
        $this->assertInstanceOf(IntType::class, $this->typeParser->parse('Int'));
        $this->assertInstanceOf(StringType::class, $this->typeParser->parse('String'));
        $this->assertInstanceOf(FloatType::class, $this->typeParser->parse('Float'));
        $this->assertInstanceOf(NullType::class, $this->typeParser->parse('Null'));
        $this->assertInstanceOf(MixedType::class, $this->typeParser->parse('Mixed'));
    }

    public function testNullableTypes()
    {
        $expected = new UnionType(
            new IntType(),
            new NullType()
        );
        $this->assertEquals($expected, $this->typeParser->parse('?int'));

        $expected = new UnionType(
            new UnionType(
                new IntType(),
                new NullType()
            ),
            new FloatType()
        );
        $this->assertEquals($expected, $this->typeParser->parse('?int | float'));
    }

    public function testParenthesesTypes()
    {
        $expected = new ParenthesesType(new IntType());
        $this->assertEquals($expected, $this->typeParser->parse('(int)'));
    }

    public function testArrayTypes()
    {
        $expected = new ArrayType(
            new IntType()
        );
        $this->assertEquals($expected, $this->typeParser->parse('int[]'));

        $expected = new UnionType(
            new IntType(),
            new ArrayType(
                new FloatType()
            )
        );
        $this->assertEquals($expected, $this->typeParser->parse('int | float[]'));
    }

    public function testUnionTypes()
    {
        $expected = new UnionType(
            new IntType(),
            new UnionType(
                new FloatType(),
                new StringType()
            )
        );
        $this->assertEquals($expected, $this->typeParser->parse('int | float | string'));
    }

    public function testIntersectionTypes()
    {
        $expected = new IntersectionType(
            new ClassType('Iterator'),
            new IntersectionType(
                new ClassType('Countable'),
                new ClassType('ArrayAccess')
            )
        );
        $this->assertEquals($expected, $this->typeParser->parse('Iterator & Countable & ArrayAccess'));
    }

    /**
     * @dataProvider invalidTypesDataProvider
     * @expectedException \AssertionError
     */
    public function testAssertionErrorForUnknownTypes($invalidType)
    {
        $this->typeParser->parse($invalidType);
    }

    public function invalidTypesDataProvider()
    {
        return [
            ['~'],
            ['\\\\Foo'],
            [''],
            [' '],
            ['(A | B'],
            ['A | B)'],
            ['A |'],
            ['| B'],
            ['A &'],
            ['& B'],
            ['int['],
            ['\DateTime\\'],
            ['0'],
            ['0Foo'],
            ['\0Foo'],
        ];
    }

    /**
     * @dataProvider classTypesDataProvider
     */
    public function testClassTypes($classType)
    {
        $this->assertInstanceOf(ClassType::class, $this->typeParser->parse($classType));
    }

    public function classTypesDataProvider()
    {
        return [
            ['Foo'],
            ['\Foo'],
            ['Foo\Bar'],
            ['\Foo\Bar'],
            ['Foo\Bar\Baz'],
            ['\Foo\Bar\Baz'],
            ['\Foo2'],
            ['\Foo_'],
            ['\_Foo_\_Bar_'],
        ];
    }

    /**
     * @dataProvider identifiersStartingWithKeywordsDataProvider
     */
    public function testIdentifiersStartingWithKeywords($identifier)
    {
        $identifier = $this->typeParser->parse($identifier);

        $this->assertInstanceOf(ClassType::class, $identifier);
        $this->assertEquals($identifier, $identifier->name);
    }

    public function identifiersStartingWithKeywordsDataProvider()
    {
        return [
            ['intelligent'],
            ['floating'],
            ['nullable'],
        ];
    }
}
