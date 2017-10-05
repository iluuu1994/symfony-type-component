<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Type\Guesser;

use Symfony\Component\Type\Checker\TypeChecker;
use Symfony\Component\Type\Type\ArrayType;
use Symfony\Component\Type\Type\BoolType;
use Symfony\Component\Type\Type\ClassType;
use Symfony\Component\Type\Type\FloatType;
use Symfony\Component\Type\Type\IntType;
use Symfony\Component\Type\Type\MixedType;
use Symfony\Component\Type\Type\NullType;
use Symfony\Component\Type\Type\StringType;
use Symfony\Component\Type\Type\Type;
use Symfony\Component\Type\Type\UnionType;

final class TypeGuesser
{
    private $typeChecker;

    public function __construct(TypeChecker $typeChecker)
    {
        $this->typeChecker = $typeChecker;
    }

    public function guess($value)
    {
        if (is_array($value)) {
            $type = array_reduce($value, function ($accumulator, $element) {
                $elementType = $this->guess($element);

                return $accumulator !== null
                    ? $this->getCommonSupertype($accumulator, $elementType)
                    : $elementType;
            });

            return new ArrayType($type ?? new MixedType());
        }

        if (is_bool($value)) {
            return new BoolType();
        }

        if (is_object($value)) {
            return new ClassType(get_class($value));
        }

        if (is_float($value)) {
            return new FloatType();
        }

        if (is_int($value)) {
            return new IntType();
        }

        if (is_null($value)) {
            return new NullType();
        }

        if (is_string($value)) {
            return new StringType();
        }

        return new MixedType();
    }

    private function getCommonSupertype(Type $a, Type $b)
    {
        if ($this->typeChecker->isSupertypeOf($a, $b)) {
            return $a;
        }

        if ($this->typeChecker->isSupertypeOf($b, $a)) {
            return $b;
        }

        return new UnionType($a, $b);
    }
}
