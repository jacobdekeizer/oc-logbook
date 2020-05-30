<?php

namespace Jacob\LogBook\Classes\Entities;

class Changes extends BaseEntity
{
    public const TYPE_CREATED = 'created';
    public const TYPE_UPDATED = 'updated';
    public const TYPE_DELETED = 'deleted';

    protected $type;
    protected $changedAttributes;

    /**
     * @param Attribute[] $changedAttributes
     */
    public function __construct(string $type, ?array $changedAttributes = null)
    {
        $this->type = $type;
        $this->changedAttributes = $changedAttributes;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isTypeCreated(): string
    {
        return $this->type === self::TYPE_CREATED;
    }

    public function isTypeUpdated(): string
    {
        return $this->type === self::TYPE_UPDATED;
    }

    public function isTypeDeleted(): string
    {
        return $this->type === self::TYPE_DELETED;
    }

    /**
     * @return Attribute[]|null
     */
    public function getChangedAttributes(): ?array
    {
        return $this->changedAttributes;
    }
}
