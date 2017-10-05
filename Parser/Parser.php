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

/**
 * @internal
 */
final class Parser
{
    private $context;

    public function __construct($tokens)
    {
        $this->context = new Context($tokens);
    }

    public function parse()
    {
        $type = $this->parseType();

        $currentInput = $this->context->currentInput();
        assert($this->context->isEOF(), "Unexpected token `$currentInput`");

        return $type;
    }

    private function parseType($allowPostParsing = true)
    {
        $type = $this->parsePrimitiveType()
            ?? $this->parseClassType()
            ?? $this->parseNullableType()
            ?? $this->parseParenthesesType();

        // Some types don't allow post-parsing.
        // `?Foo | Bar` should be parsed as `(?Foo) | Bar` instead of `?(Foo | Bar)`.
        if ($allowPostParsing) {
            $type = $type !== null
                ? $this->postParseType($type)
                : null;
        }

        return $type;
    }

    private function parsePrimitiveType()
    {
        if (null !== $currentToken = $this->context->currentInput()) {
            if ($currentToken->kind !== Token::KIND_KEYWORD) {
                return null;
            }
        }

        if ($this->context->consumeIf(function ($token) {
            return strtolower($token->lexeme) === 'bool';
        })) {
            return new BoolType();
        }

        if ($this->context->consumeIf(function ($token) {
            return strtolower($token->lexeme) === 'int';
        })) {
            return new IntType();
        }

        if ($this->context->consumeIf(function ($token) {
            return strtolower($token->lexeme) === 'string';
        })) {
            return new StringType();
        }

        if ($this->context->consumeIf(function ($token) {
            return strtolower($token->lexeme) === 'float';
        })) {
            return new FloatType();
        }

        if ($this->context->consumeIf(function ($token) {
            return strtolower($token->lexeme) === 'null';
        })) {
            return new NullType();
        }

        if ($this->context->consumeIf(function ($token) {
            return strtolower($token->lexeme) === 'mixed';
        })) {
            return new MixedType();
        }

        return null;
    }

    private function parseClassType()
    {
        $leadingBackslash = $this->context->consume(new Token(Token::KIND_SYMBOL, '\\'));
        $identifier = $this->context->consumeIf(function ($token) {
            return $token->kind === Token::KIND_IDENTIFIER;
        });

        if (!$leadingBackslash && $identifier === null) {
            return null;
        }

        assert($identifier !== null, 'Expected type after namespace symbol `\\');

        $parts = [$identifier->lexeme];

        while ($this->context->consume(new Token(Token::KIND_SYMBOL, '\\'))) {
            $identifier = $this->context->consumeIf(function ($token) {
                return $token->kind === Token::KIND_IDENTIFIER;
            });
            assert($identifier !== null);

            $parts[] = $identifier->lexeme;
        }

        return new ClassType(join('\\', $parts));
    }

    private function parseNullableType()
    {
        if (!$this->context->consume(new Token(Token::KIND_SYMBOL, '?'))) {
            return null;
        }

        $inner = $this->parseType(false);
        assert($inner !== null, 'Expected type after nullable type symbol `?`');

        return new UnionType($inner, new NullType());
    }

    private function parseParenthesesType()
    {
        if (!$this->context->consume(new Token(Token::KIND_SYMBOL, '('))) {
            return null;
        }

        $inner = $this->parseType();
        assert($inner !== null, 'Expected type after opening parenthesis symbol `(`');

        $closingParen = $this->context->consume(new Token(Token::KIND_SYMBOL, ')'));
        assert($closingParen, 'Expected closing parenthesis symbol `)`');

        return new ParenthesesType($inner);
    }

    private function postParseUnionType($type)
    {
        if (!$this->context->consume(new Token(Token::KIND_SYMBOL, '|'))) {
            return null;
        }

        $rhs = $this->parseType();
        assert($rhs !== null, 'Expected type after union symbol `|`');

        return new UnionType($type, $rhs);
    }

    private function postParseIntersectionType($type)
    {
        if (!$this->context->consume(new Token(Token::KIND_SYMBOL, '&'))) {
            return null;
        }

        $rhs = $this->parseType();
        assert($rhs !== null, 'Expected type after union symbol `&`');

        return new IntersectionType($type, $rhs);
    }

    private function postParseArrayType($type)
    {
        if (!$this->context->consume(new Token(Token::KIND_SYMBOL, '['))) {
            return null;
        }

        $closingBracket = $this->context->consume(new Token(Token::KIND_SYMBOL, ']'));
        assert($closingBracket, 'Expected closing bracket symbol `]`');

        return new ArrayType($type);
    }

    private function postParseType($type)
    {
        if ($type !== null && !$this->context->isEOF()) {
            $postParseType = $this->postParseUnionType($type)
                ?? $this->postParseIntersectionType($type)
                ?? $this->postParseArrayType($type);

            if ($postParseType !== null) {
                return $this->postParseType($postParseType);
            }
        }

        return $type;
    }
}
