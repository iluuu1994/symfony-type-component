<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Type\Parser;

final class Token
{
    const KIND_WHITESPACE = 0;
    const KIND_SYMBOL = 1;
    const KIND_KEYWORD = 2;
    const KIND_IDENTIFIER = 3;

    public $kind;
    public $lexeme;

    public function __construct($kind, $lexeme)
    {
        $this->kind = $kind;
        $this->lexeme = $lexeme;
    }

    public function __toString()
    {
        return $this->lexeme;
    }
}
