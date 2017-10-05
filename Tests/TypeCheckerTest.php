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

class TypeCheckerTest extends TestCase
{
    /** @var TypeChecker */
    private $typeChecker;

    protected function setUp()
    {
        $this->typeChecker = new TypeChecker();
    }

    public function testArrayChecker()
    {
        $this->assertTrue($this->typeChecker->isSubtypeOf(new ArrayType(new IntType()), new ArrayType(new IntType())));
        $this->assertTrue($this->typeChecker->isSubtypeOf(new ArrayType(new IntType()), new ArrayType(new MixedType())));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new ArrayType(new IntType()), new ArrayType(new FloatType())));

        $this->assertTrue($this->typeChecker->isSupertypeOf(new ArrayType(new IntType()), new ArrayType(new IntType())));
        $this->assertTrue($this->typeChecker->isSupertypeOf(new ArrayType(new MixedType()), new ArrayType(new IntType())));
        $this->assertFalse($this->typeChecker->isSupertypeOf(new ArrayType(new FloatType()), new ArrayType(new IntType())));

        $this->assertTrue($this->typeChecker->isEqualTo(new ArrayType(new IntType()), new ArrayType(new IntType())));
        $this->assertFalse($this->typeChecker->isEqualTo(new ArrayType(new IntType()), new ArrayType(new MixedType())));
        $this->assertFalse($this->typeChecker->isEqualTo(new ArrayType(new IntType()), new ArrayType(new FloatType())));
    }

    public function testBoolChecker()
    {
        $this->assertTrue($this->typeChecker->isSubtypeOf(new BoolType(), new BoolType()));
        $this->assertTrue($this->typeChecker->isSupertypeOf(new BoolType(), new BoolType()));
        $this->assertTrue($this->typeChecker->isEqualTo(new BoolType(), new BoolType()));
        $this->assertTrue($this->typeChecker->isSubtypeOf(new BoolType(), new MixedType()));
        $this->assertTrue($this->typeChecker->isSubtypeOf(new BoolType(), new ParenthesesType(new BoolType())));
        $this->assertTrue($this->typeChecker->isSubtypeOf(new BoolType(), new UnionType(new IntType(), new BoolType())));

        $this->assertFalse($this->typeChecker->isSubtypeOf(new BoolType(), new ArrayType(new BoolType())));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new BoolType(), new NullType()));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new BoolType(), new ClassType('\DateTime')));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new BoolType(), new FloatType()));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new BoolType(), new IntersectionType(new FloatType(), new IntType())));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new BoolType(), new IntType()));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new BoolType(), new StringType()));
    }

    public function testClassChecker()
    {
        $dateTimeInterface = new ClassType('\DateTimeInterface');
        $dateTime = new ClassType('\DateTime');
        $splStack = new ClassType('\SplStack');
        $splDoublyLinkedListInterfaces = new IntersectionType(
            new ClassType('\Iterator'),
            new IntersectionType(
                new ClassType('\Countable'),
                new ClassType('\ArrayAccess')
            )
        );

        $this->assertTrue($this->typeChecker->isSubtypeOf($dateTime, $dateTimeInterface));
        $this->assertTrue($this->typeChecker->isSubtypeOf($splStack, $splDoublyLinkedListInterfaces));
        $this->assertTrue($this->typeChecker->isSupertypeOf($dateTimeInterface, $dateTime));
        $this->assertTrue($this->typeChecker->isSupertypeOf($splDoublyLinkedListInterfaces, $splStack));

        $this->assertTrue($this->typeChecker->isEqualTo($dateTimeInterface, $dateTimeInterface));
        $this->assertTrue($this->typeChecker->isEqualTo($dateTime, $dateTime));
        $this->assertTrue($this->typeChecker->isEqualTo($splStack, $splStack));
        $this->assertFalse($this->typeChecker->isEqualTo($dateTime, $dateTimeInterface));
        $this->assertFalse($this->typeChecker->isEqualTo($splStack, $splDoublyLinkedListInterfaces));
        $this->assertFalse($this->typeChecker->isEqualTo($dateTimeInterface, $dateTime));
        $this->assertFalse($this->typeChecker->isEqualTo($splDoublyLinkedListInterfaces, $splStack));
    }

    public function testNullChecker()
    {
        $this->assertTrue($this->typeChecker->isSubtypeOf(new NullType(), new NullType()));
        $this->assertTrue($this->typeChecker->isSupertypeOf(new NullType(), new NullType()));
        $this->assertTrue($this->typeChecker->isEqualTo(new NullType(), new NullType()));
        $this->assertTrue($this->typeChecker->isSubtypeOf(new NullType(), new MixedType()));
        $this->assertTrue($this->typeChecker->isSubtypeOf(new NullType(), new ParenthesesType(new NullType())));
        $this->assertTrue($this->typeChecker->isSubtypeOf(new NullType(), new UnionType(new IntType(), new NullType())));

        $this->assertFalse($this->typeChecker->isSubtypeOf(new NullType(), new ArrayType(new NullType())));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new NullType(), new BoolType()));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new NullType(), new ClassType('\DateTime')));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new NullType(), new FloatType()));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new NullType(), new IntersectionType(new FloatType(), new IntType())));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new NullType(), new IntType()));
        $this->assertFalse($this->typeChecker->isSubtypeOf(new NullType(), new StringType()));
    }

    public function testInvalidClass()
    {
        $class1 = new ClassType('Foo');
        $class2 = new ClassType('Bar');

        $this->assertFalse($this->typeChecker->isSubtypeOf($class1, $class2));
    }

    /**
     * @dataProvider parenthesesDataProvider
     */
    public function testParentheses($type1, $type2)
    {
        $this->assertTrue($this->typeChecker->isEqualTo($type1, $type2));
    }

    public function parenthesesDataProvider()
    {
        return [
            [
                new ParenthesesType(new IntType()),
                new ParenthesesType(new IntType()),
            ],
            [
                new IntType(),
                new ParenthesesType(new IntType()),
            ],
            [
                new ParenthesesType(new IntType()),
                new IntType(),
            ],
            [
                new ParenthesesType(new ParenthesesType(new IntType())),
                new ParenthesesType(new IntType()),
            ],
        ];
    }

    /**
     * @dataProvider unionTypeDataProvider
     */
    public function testUnionType($type1, $type2)
    {
        $this->assertTrue($this->typeChecker->isSubtypeOf($type1, $type2));
    }

    public function unionTypeDataProvider()
    {
        return [
            [
                new NullType(),
                new UnionType(
                    new IntType(),
                    new NullType()
                )
            ],
            [
                new NullType(),
                new UnionType(
                    new NullType(),
                    new IntType()
                )
            ],
            [
                new NullType(),
                new UnionType(
                    new IntType(),
                    new UnionType(
                        new StringType(),
                        new NullType()
                    )
                )
            ],
            [
                new UnionType(
                    new IntType(),
                    new NullType()
                ),
                new UnionType(
                    new IntType(),
                    new UnionType(
                        new StringType(),
                        new NullType()
                    )
                )
            ],
            [
                new UnionType(
                    new NullType(),
                    new IntType()
                ),
                new UnionType(
                    new IntType(),
                    new UnionType(
                        new StringType(),
                        new NullType()
                    )
                )
            ],
            [
                new UnionType(
                    new UnionType(
                        new StringType(),
                        new NullType()
                    ),
                    new IntType()
                ),
                new UnionType(
                    new NullType(),
                    new UnionType(
                        new IntType(),
                        new StringType()
                    )
                )
            ],
        ];
    }

    /**
     * @dataProvider intersectionTypeDataProvider
     */
    public function testIntersectionType($type1, $type2)
    {
        $this->assertTrue($this->typeChecker->isSubtypeOf($type1, $type2));
    }

    public function intersectionTypeDataProvider()
    {
        return [
            [
                new ClassType('DomainException'),
                new IntersectionType(
                    new ClassType('LogicException'),
                    new ClassType('Exception')
                )
            ],
            [
                new IntersectionType(
                    new ClassType('LogicException'),
                    new ClassType('Exception')
                ),
                new IntersectionType(
                    new ClassType('LogicException'),
                    new ClassType('Exception')
                )
            ],
            [
                new IntersectionType(
                    new ClassType('LogicException'),
                    new ClassType('Exception')
                ),
                new IntersectionType(
                    new ClassType('Exception'),
                    new ClassType('LogicException')
                )
            ],
            [
                new IntersectionType(
                    new ClassType('LogicException'),
                    new ClassType('Exception')
                ),
                new IntersectionType(
                    new ClassType('Exception'),
                    new ClassType('LogicException')
                )
            ],
        ];
    }
}
