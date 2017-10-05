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

final class TypeParser
{
    public function parse(string $type)
    {
        $lexer = new Lexer($type);
        $tokens = $lexer->lex();

        // Remove whitespace from tokens
        $tokens = array_values(array_filter($tokens, function ($token) {
            return $token->kind !== Token::KIND_WHITESPACE;
        }));

        assert(count($tokens) > 0, 'Unexpected non-whitespace tokens.');

        $parser = new Parser($tokens);

        return $parser->parse();
    }
}
