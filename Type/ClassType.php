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

final class ClassType extends Type
{
    public $name;

    public function __construct($name)
    {
        if (strpos($name, '\\') === 0) {
            $name = substr($name, 1);
        }

        $this->name = $name;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
