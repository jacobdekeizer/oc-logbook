<?php

namespace Jacob\LogBook\Classes\Entities;

class Attribute extends BaseEntity
{
    protected $column;
    protected $old;
    protected $new;

    public function __construct(string $column, $old, $new)
    {
        $this->column = $column;
        $this->old = $old;
        $this->new = $new;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getOld()
    {
        return $this->old;
    }

    public function getNew()
    {
        return $this->new;
    }
}
