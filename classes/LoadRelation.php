<?php

namespace Jacob\LogBook\Classes;

class LoadRelation
{
    private string $name;
    private bool $withTrashed;

    public function __construct(string $name, bool $withTrashed)
    {
        $this->name = $name;
        $this->withTrashed = $withTrashed;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isWithTrashed(): bool
    {
        return $this->withTrashed;
    }
}
