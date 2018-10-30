<?php

namespace Jacob\LogBook\Classes\Entities;

class Attribute extends BaseEntity
{
    protected $attributes = [
        'column',
        'old',
        'new',
    ];
}