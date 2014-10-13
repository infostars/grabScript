<?php

namespace greevex\gss\objects;

class Queue
{
    private $items;

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function append($item)
    {
        $this->items[] = $item;
    }

    public function getNext()
    {
        return array_shift($this->items);
    }
}