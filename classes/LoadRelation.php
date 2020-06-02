<?php

namespace Jacob\LogBook\Classes;

class LoadRelation
{
    private $name;
    private $withTrashed;

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
