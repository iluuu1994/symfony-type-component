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
final class Context
{
    private $inputStream;
    private $cursor = 0;
    private $cursors = [];

    public function __construct($inputStream)
    {
        $this->inputStream = $inputStream;
    }

    public function consume($expectedInput)
    {
        if (strtolower($this->currentInput()) != $expectedInput) {
            return false;
        }

        $this->cursor += 1;

        return true;
    }

    public function consumeIf(callable $closure)
    {
        $currentInput = $this->currentInput();

        if ($currentInput === null) {
            return null;
        }

        if ($closure($currentInput)) {
            $this->cursor += 1;

            return $currentInput;
        }

        return null;
    }

    public function consumeWhile(callable $closure)
    {
        $this->pushScope();

        while (true) {
            if (!$this->consumeIf($closure)) {
                break;
            }
        }

        return $this->accept();
    }

    public function consumeSequence($sequence)
    {
        $this->pushScope();

        foreach ($sequence as $input) {
            if (!$this->consume($input)) {
                $this->reject();

                return false;
            }
        }

        return $this->accept();
    }

    public function currentInput()
    {
        if ($this->isEOF()) {
            return null;
        }

        return $this->inputStream[$this->cursor];
    }

    public function pushScope()
    {
        array_push($this->cursors, $this->cursor);
    }

    public function accept()
    {
        $lastCursor = $this->cursors[count($this->cursors) - 1];
        $accepted = array_slice($this->inputStream, $lastCursor, $this->cursor - $lastCursor);

        array_pop($this->cursors);

        return $accepted;
    }

    public function reject()
    {
        $this->cursor = array_pop($this->cursors);
    }

    public function isEOF()
    {
        return $this->cursor >= count($this->inputStream);
    }
}
