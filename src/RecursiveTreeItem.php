<?php

namespace websytnik\tree;

class RecursiveTreeItem
{
    private $_recursiveTree;
    private $_id;
    private $_parentID;
    private $_value;

    public function __construct(RecursiveTree $recursiveTree, $id, $parentID, $value)
    {
        $this->_recursiveTree = $recursiveTree;
        $this->_id = $id;
        $this->_parentID = $parentID;
        $this->_value = $value;
    }

    public function __get($name)
    {
        if (is_array($this->_value)) {
            return $this->_value[$name];
        } elseif (is_object($this->_value)) {
            return $this->_value->$name;
        } else {
            return null;
        }
    }

    public function __debugInfo() : array
    {
        return (array) $this->getValue();
    }

    public function getID()
    {
        return $this->_id;
    }

    public function getParentID()
    {
        return $this->_parentID;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function getChildren() : array
    {
        return $this->_recursiveTree->getChildren($this);
    }

    public function getParent() : ?RecursiveTreeItem
    {
        return $this->_recursiveTree->getParent($this);
    }

    public function hasChildren() : bool
    {
        return $this->_recursiveTree->hasChildren($this);
    }

    public function hasParent() : bool
    {
        return $this->_recursiveTree->hasParent($this);
    }

    public function getTree() : array
    {
        return $this->_recursiveTree->getTree($this);
    }
}
