<?php

namespace websytnik\tree;

class RecursiveTreeBuilder
{
    private $array;
    private $key;
    private $parentKey;
    private $childrenKey;
    private $blank;

    public static function from(array $array) : self
    {
        return new self($array);
    }

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function setIndex(string $key, string $parentKey = 'parent_id') : self
    {
        $this->key = $key;
        $this->parentKey = $parentKey;
        return $this;
    }

    public function setChildrenKey(string $key) : self
    {
        $this->childrenKey = $key;
        return $this;
    }

    public function setBlank($array) : self
    {
        if (!is_array($array)) {
            $array = [$array];
        }

        $this->blank = $array;

        return $this;
    }

    public function create() : RecursiveTree
    {
        return new RecursiveTree($this->array, $this->key, $this->parentKey, $this->childrenKey, $this->blank);
    }
}
