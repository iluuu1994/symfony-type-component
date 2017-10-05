<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Type\Type;

final class UnionType extends Type
{
    public $lhs;
    public $rhs;

    public function __construct(Type $lhs, Type $rhs)
    {
        $this->lhs = $lhs;
        $this->rhs = $rhs;
    }

    public function __toString()
    {
        $lhs = (string) $this->lhs;
        $rhs = (string) $this->rhs;

        return "$lhs | $rhs";
    }
}
