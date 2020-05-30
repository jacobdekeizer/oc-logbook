<?php

namespace Jacob\LogBook\Classes\Entities;

abstract class BaseEntity
{
    public function getData(): array
    {
        $data = [];

        $properties = get_object_vars($this);

        foreach ($properties as $name => $value) {
            if (is_array($value)) {
                $items = [];

                foreach ($value as $val) {
                    if ($val instanceof BaseEntity) {
                        $items[] = $val->getData();
                    }
                }

                $value = $items;
            } else if ($value instanceof BaseEntity) {
                $value = $value->getData();
            }

            $data[$name] = $value;
        }

        return $data;
    }
}
