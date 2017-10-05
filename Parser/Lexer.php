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

/**
 * @internal
 */
final class Lexer
{
    private $context;

    public function __construct($inputStream)
    {
        // str_split unfortunately converts '' to ['']
        $characterStream = strlen($inputStream) > 0
            ? str_split($inputStream)
            : [];

        $this->context = new Context($characterStream);
    }

    public function lex()
    {
        $tokens = [];

        while ($token = $this->nextToken()) {
            array_push($tokens, $token);
        }

        $currentInput = $this->context->currentInput();
        assert($this->context->isEOF(), "Expected EOF, got `$currentInput`");

        return $tokens;
    }

    private function nextToken()
    {
        if ($this->context->isEOF()) {
            return null;
        }

        return $this->lexWhitespace()
            ?? $this->lexSymbols()
            ?? $this->lexKeyword()
            ?? $this->lexIdentifier();
    }

    private function lexWhitespace()
    {
        $lexeme = $this->context->consumeWhile(function ($character) {
            return $character === ' ';
        });

        if (count($lexeme) === 0) {
            return null;
        }

        return new Token(Token::KIND_WHITESPACE, join('', $lexeme));
    }

    private function lexSymbols()
    {
        $this->context->pushScope();

        $symbols = [',', ':', '(', ')', '[', ']', '\\', '|', '&', '?'];

        foreach ($symbols as $symbol) {
            if ($this->context->consume($symbol)) {
                return new Token(Token::KIND_SYMBOL, $symbol);
            }
        }

        return null;
    }

    private function lexKeyword()
    {
        $this->context->pushScope();

        $keywords = ['bool', 'float', 'int', 'mixed', 'null', 'string'];

        foreach ($keywords as $keyword) {
            if ($lexeme = $this->context->consumeSequence(str_split($keyword))) {
                // Make sure the keyword is not directly followed by any characters
                // E.g. "integer" --> Identifier, not "int" + "eger"
                if ($this->lexIdentifier() !== null) {
                    $this->context->reject();
                    continue;
                }

                return new Token(Token::KIND_KEYWORD, $keyword);
            }
        }

        return null;
    }

    private function lexIdentifier()
    {
        $this->context->pushScope();

        $firstChar = $this->context->consumeIf(function ($character) {
            return ctype_alpha($character) || $character === '_';
        });

        if ($firstChar === null) {
            $this->context->reject();

            return null;
        }

        $remainder = $this->context->consumeWhile(function ($character) {
            return ctype_alpha($character) || ctype_digit($character) || $character === '_';
        });

        $lexeme = join('', array_merge([$firstChar], $remainder));

        return new Token(Token::KIND_IDENTIFIER, $lexeme);
    }
}
