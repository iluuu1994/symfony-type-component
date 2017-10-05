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
use Symfony\Component\Type\Type\Type;
use Symfony\Component\Type\Type\UnionType;

class ToStringTest extends TestCase
{
    /**
     * @dataProvider typeStringRepresentationPairDataProvider
     */
    public function testTypeToString(string $stringRepresentation, Type $type)
    {
        $this->assertEquals($stringRepresentation, (string) $type);
    }

    public function typeStringRepresentationPairDataProvider()
    {
        return [
            ['int[]', new ArrayType(new IntType())],
            ['bool', new BoolType()],
            ['DateTime', new ClassType('DateTime')],
            ['DateTime', new ClassType('\DateTime')],
            ['float', new FloatType()],
            ['Iterator & Countable', new IntersectionType(new ClassType('\Iterator'), new ClassType('\Countable'))],
            ['int', new IntType()],
            ['mixed', new MixedType()],
            ['null', new NullType()],
            ['(int)', new ParenthesesType(new IntType())],
            ['string', new StringType()],
            ['int | null', new UnionType(new IntType(), new NullType())],
        ];
    }
}
