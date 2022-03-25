<?php

namespace websytnik\tree;

class RecursiveTree
{
    private $_array;
    private $_key = 'id';
    private $_parentKey = 'parent_id';
    private $_childrenKey = 'children';
    private $_nullKey = 0;
    private $_blank;
    private $_items;
    private $_structure;

    public function __construct(array $array, ?string $key, ?string $parentKey, ?string $childrenKey, $blank)
    {
        $this->_array = $array;
        $this->_blank = $blank;

        if ($key) {
            $this->_key = $key;
        }

        if ($parentKey) {
            $this->_parentKey = $parentKey;
        }

        if ($childrenKey) {
            $this->_childrenKey = $childrenKey;
        }

        if ($blank !== null && in_array(0, $blank)) {
            $this->_blank = md5(rand(0, 1000));
        }
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function getItems() : array
    {
        if (is_null($this->_items)) {
            $array = [];

            foreach ($this->_array as $item) {
                if (is_array($item)) {
                    $id = $item[$this->_key];
                    $parentID = $item[$this->_parentKey];
                } elseif (is_object($item)) {
                    $idAttribute = $this->_key;
                    $parentIdAttribute = $this->_parentKey;
                    $id = $item->$idAttribute;
                    $parentID = $item->$parentIdAttribute;
                } else {
                    throw new Exception('Invalid item type');
                }

                $array[$id] = new RecursiveTreeItem($this, $id, $parentID, $item);
            }

            $this->_items = $array;
        }

        return $this->_items;
    }

    public function getItem($id) : ?RecursiveTreeItem
    {
        return $this->getItems()[$id] ?? null;
    }

    public function getStructure() : array
    {
        if (is_null($this->_structure)) {
            $array = [];

            foreach ($this->getItems() as $item) {
                $parentID = $item->getParentID();

                if ($this->isEmpty($parentID)) {
                    $parentID = $this->_nullKey;
                }

                if (!isset($array[$parentID])) {
                    $array[$parentID] = [];
                }

                $array[$parentID][$item->getID()] = $item;
            }

            $this->_structure = $array;
        }

        return $this->_structure;
    }

    public function getChildren($item) : array
    {
        if ($this->isEmpty($item)) {
            $item = $this->_nullKey;
        } elseif ($item instanceof RecursiveTreeItem) {
            $item = $item->getID();
        } elseif (is_array($item)) {
            $item = $item[$this->_key];
        } elseif (is_object($item)) {
            $attribute = $this->_key;
            $item = $item->$attribute;
        }
        
        return $this->getStructure()[$item] ?? [];
    }

    public function getParent($item) : ?RecursiveTreeItem
    {
        if (!is_object($item) && !is_array($item)) {
            $item = $this->getItem($item);
        }

        if ($this->isEmpty($item)) {
            return null;
        }

        if ($item instanceof RecursiveTreeItem) {
            $parentID = $item->getParentID();
        } elseif (is_array($item)) {
            $parentID = $item[$this->_parentKey];
        } elseif (is_object($item)) {
            $attribute = $this->_parentKey;
            $parentID = $item->$attribute;
        } else {
            return null;
        }

        return $this->getItem($parentID);
    }

    public function hasChildren($item) : bool
    {
        return count($this->getChildren($item)) > 0;
    }

    public function hasParent($item) : bool
    {
        return $this->getParent($item) !== null;
    }

    public function getTree(RecursiveTreeItem $item = null) : array
    {
        $array = [];

        foreach ($this->getChildren($item) as $child) {
            $i = $child->getValue();
            $hasChildren = $child->hasChildren();

            if ($hasChildren) {
                if (is_array($i)) {
                    $i[$this->_childrenKey] = $child->getTree();
                } else {
                    $attribute = $this->_childrenKey;
                    $i->$attribute = $child->getTree();
                }
            }

            $array[] = $i;
        }

        return $array;
    }

    public function getHtmlBuilder(RecursiveTreeItem $item = null)
    {
        return new RecursiveTreeHtmlBuilder($this, $item);
    }

    private function isEmpty($value) : bool
    {
        if (is_null($this->_blank)) {
            return empty($value);
        } else {
            return in_array($value, $this->_blank);
        }
    }
}
