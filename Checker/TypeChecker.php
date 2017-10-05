<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Type\Checker;

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

final class TypeChecker
{
    public function isSupertypeOf(Type $a, Type $b)
    {
        if ($a instanceof ArrayType && $b instanceof ArrayType) {
            return $this->isSupertypeOf($a->inner, $b->inner);
        }

        if ($a instanceof ClassType && $b instanceof ClassType) {
            if ($a->name === $b->name) {
                return true;
            }

            try {
                $aReflectionClass = new \ReflectionClass($a->name);
                $bReflectionClass = new \ReflectionClass($b->name);
            } catch (\ReflectionException $e) {
                return false;
            }

            return $aReflectionClass->isInterface()
                ? $bReflectionClass->implementsInterface($a->name)
                : $bReflectionClass->isSubclassOf($a->name);
        }

        if ($a instanceof MixedType) {
            return true;
        }

        if ($a instanceof ParenthesesType) {
            return $this->isSupertypeOf($a->inner, $b);
        } elseif ($b instanceof ParenthesesType) {
            return $this->isSupertypeOf($a, $b->inner);
        }

        if ($a instanceof UnionType && $b instanceof UnionType) {
            return ($this->isSupertypeOf($a->lhs, $b->lhs) && $this->isSupertypeOf($a->rhs, $b->rhs))
                || ($this->isSupertypeOf($a->lhs, $b->rhs) && $this->isSupertypeOf($a->rhs, $b->lhs))
                || ($this->isSupertypeOf($a->lhs, $b) && $this->isSupertypeOf($a->rhs, $b))
                || ($this->isSupertypeOf($a, $b->lhs) && $this->isSupertypeOf($a, $b->rhs));
        } elseif ($a instanceof UnionType) {
            return $this->isSupertypeOf($a->lhs, $b) || $this->isSupertypeOf($a->rhs, $b);
        } elseif ($b instanceof UnionType) {
            return $this->isSupertypeOf($a, $b->lhs) && $this->isSupertypeOf($a, $b->rhs);
        }

        if ($a instanceof IntersectionType && $b instanceof IntersectionType) {
            return ($this->isSupertypeOf($a->lhs, $b->lhs) || $this->isSupertypeOf($a->rhs, $b->rhs))
                && ($this->isSupertypeOf($a->lhs, $b->rhs) || $this->isSupertypeOf($a->rhs, $b->lhs))
                && ($this->isSupertypeOf($a->lhs, $b) || $this->isSupertypeOf($a->rhs, $b))
                && ($this->isSupertypeOf($a, $b->lhs) || $this->isSupertypeOf($a, $b->rhs));
        } elseif ($a instanceof IntersectionType) {
            return $this->isSupertypeOf($a->lhs, $b) && $this->isSupertypeOf($a->rhs, $b);
        } elseif ($b instanceof IntersectionType) {
            return $this->isSupertypeOf($a, $b->lhs) || $this->isSupertypeOf($a, $b->rhs);
        }

        return $this->checkPrimitiveTypeEquality($a, $b);
    }

    public function isSubtypeOf(Type $a, Type $b)
    {
        return $this->isSupertypeOf($b, $a);
    }

    public function isEqualTo(Type $a, Type $b)
    {
        return $this->isSupertypeOf($a, $b) && $this->isSubtypeOf($a, $b);
    }

    private function checkPrimitiveTypeEquality(Type $a, Type $b)
    {
        return ($a instanceof BoolType && $b instanceof BoolType)
            || ($a instanceof FloatType && $b instanceof FloatType)
            || ($a instanceof IntType && $b instanceof IntType)
            || ($a instanceof MixedType && $b instanceof MixedType)
            || ($a instanceof NullType && $b instanceof NullType)
            || ($a instanceof StringType && $b instanceof StringType);
    }
}
