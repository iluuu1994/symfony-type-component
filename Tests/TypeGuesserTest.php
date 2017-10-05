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
use Symfony\Component\Type\Checker\TypeChecker;
use Symfony\Component\Type\Guesser\TypeGuesser;
use Symfony\Component\Type\Type\ArrayType;
use Symfony\Component\Type\Type\BoolType;
use Symfony\Component\Type\Type\ClassType;
use Symfony\Component\Type\Type\FloatType;
use Symfony\Component\Type\Type\IntType;
use Symfony\Component\Type\Type\MixedType;
use Symfony\Component\Type\Type\NullType;
use Symfony\Component\Type\Type\ParenthesesType;
use Symfony\Component\Type\Type\StringType;
use Symfony\Component\Type\Type\UnionType;

class TypeGuesserTest extends TestCase
{
    /** @var TypeGuesser */
    private $typeGuesser;

    protected function setUp()
    {
        $typeChecker = new TypeChecker();
        $this->typeGuesser = new TypeGuesser($typeChecker);
    }

    public function testNullGuesser()
    {
        $this->assertInstanceOf(NullType::class, $this->typeGuesser->guess(null));
    }

    public function testIntegerGuesser()
    {
        $this->assertInstanceOf(IntType::class, $this->typeGuesser->guess(1));
        $this->assertInstanceOf(IntType::class, $this->typeGuesser->guess(0));
        $this->assertInstanceOf(IntType::class, $this->typeGuesser->guess(42));
        $this->assertInstanceOf(IntType::class, $this->typeGuesser->guess(123));
        $this->assertInstanceOf(IntType::class, $this->typeGuesser->guess(-123));
        $this->assertInstanceOf(IntType::class, $this->typeGuesser->guess(PHP_INT_MAX));
        $this->assertInstanceOf(IntType::class, $this->typeGuesser->guess(PHP_INT_MIN));
    }

    public function testObjectGuesser()
    {
        $this->assertInstanceOf(ClassType::class, $this->typeGuesser->guess(new \DateTime()));
        $this->assertInstanceOf(ClassType::class, $this->typeGuesser->guess(new \Exception()));
        $this->assertInstanceOf(ClassType::class, $this->typeGuesser->guess(new \stdClass()));
        $this->assertInstanceOf(ClassType::class, $this->typeGuesser->guess(new \SplStack()));
    }

    public function testFloatGuesser()
    {
        $this->assertInstanceOf(FloatType::class, $this->typeGuesser->guess(0.0));
        $this->assertInstanceOf(FloatType::class, $this->typeGuesser->guess(10.123));
        $this->assertInstanceOf(FloatType::class, $this->typeGuesser->guess(-9152.1));
        $this->assertInstanceOf(FloatType::class, $this->typeGuesser->guess(M_PI));
        $this->assertInstanceOf(FloatType::class, $this->typeGuesser->guess(INF));
        $this->assertInstanceOf(FloatType::class, $this->typeGuesser->guess(-INF));
    }

    public function testStringGuesser()
    {
        $this->assertInstanceOf(StringType::class, $this->typeGuesser->guess(''));
        $this->assertInstanceOf(StringType::class, $this->typeGuesser->guess('Foo'));
        $this->assertInstanceOf(StringType::class, $this->typeGuesser->guess('Bar'));
    }

    public function testBoolGuesser()
    {
        $this->assertInstanceOf(BoolType::class, $this->typeGuesser->guess(true));
        $this->assertInstanceOf(BoolType::class, $this->typeGuesser->guess(false));
    }

    public function testMixed()
    {
        // Currently, resources are the only types not supported by the type guesser
        // Once it is supported, the mixed type can be remove from the type guesser
        $resource = curl_init();
        $this->assertInstanceOf(MixedType::class, $this->typeGuesser->guess($resource));
    }

    public function testEmptyArrayGuesser()
    {
        $type = new ArrayType(
            new MixedType()
        );
        $this->assertEquals($type, $this->typeGuesser->guess([]));
    }

    public function testArrayGuesser()
    {
        $type = new ArrayType(
            new IntType()
        );
        $this->assertEquals($type, $this->typeGuesser->guess([1, 2, 3]));

        $type = new ArrayType(
            new UnionType(
                new UnionType(
                    new IntType(),
                    new NullType()
                ),
                new StringType()
            )
        );
        $this->assertEquals($type, $this->typeGuesser->guess([1, 2, null, 'foo', null]));

        $type = new ArrayType(
            new ClassType('Exception')
        );
        $this->assertEquals($type, $this->typeGuesser->guess([new \LogicException(), new \Exception()]));
    }
}
