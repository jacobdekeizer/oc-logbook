<?php

namespace Jacob\LogBook\Classes\Entities;

class Changes extends BaseEntity
{
    const TYPE_CREATED = 'created';
    const TYPE_UPDATED = 'updated';
    const TYPE_DELETED = 'deleted';

    protected $attributes = [
        'type',
        'changedAttributes',
    ];
}