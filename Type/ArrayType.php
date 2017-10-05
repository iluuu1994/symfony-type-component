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

final class ArrayType extends Type
{
    public $inner;

    public function __construct(Type $inner)
    {
        $this->inner = $inner;
    }

    public function __toString()
    {
        $inner = (string) $this->inner;

        return $inner.'[]';
    }
}
