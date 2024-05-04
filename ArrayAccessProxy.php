<?php

namespace MaxieSystems;

final readonly class ArrayAccessProxy implements \ArrayAccess
{
    public function __construct(
        private readonly object $subject,
        private readonly bool $readonly = true
    ) {
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->subject->$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->subject->$offset;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($this->readonly) {
            throw new \Error(EMessages::readonlyProperty($this, $offset));
        }
        $this->subject->$offset = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if ($this->readonly) {
            throw new \Error(EMessages::readonlyProperty($this, $offset));
        }
        unset($this->subject->$offset);
    }
}
