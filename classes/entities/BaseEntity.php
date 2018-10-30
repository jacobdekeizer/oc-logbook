<?php

namespace Jacob\LogBook\Classes\Entities;

class BaseEntity
{
    /**
     * @var array List of all the attributes of this model
     */
    protected $attributes = [];

    /**
     * @var array Storage of all attribute data
     */
    protected $attributesData = [];

    public function __construct(array $attributes = [])
    {
        $this->fillFromArray($attributes);
    }

    /**
     * Fill data from array
     * @param array $attributes
     */
    protected function fillFromArray(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->__set($key, $value);
        }
    }

    /**
     * Get data from attribute, child entity or nested entity
     *
     * @param string $key
     * @return null|string|array
     */
    public function __get($key)
    {
        if (isset( $this->attributesData[$key] )) {
            return $this->attributesData[$key];
        }

        return null;
    }
    /**
     * Set data for attribute, child entity or nested entity
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        if ($this->attributeExists($key)) {
            $this->attributesData[$key] = $value;
        }
    }

    /**
     * Check if an attribute key exists
     *
     * @param $key
     * @return bool
     */
    public function attributeExists($key)
    {
        return in_array($key, $this->attributes);
    }

    /**
     * Get all current data set in this model
     * @return array
     */
    public function getData()
    {
        $result = [];
        foreach ($this->attributes as $attribute) {
            $result[$attribute] = $this->$attribute;
        }
        return $result;
    }
}
