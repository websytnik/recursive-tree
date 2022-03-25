<?php

namespace websytnik\tree;

class RecursiveTreeHtmlBuilder
{
    const TYPE_TREE = 0;
    const TYPE_LIST = 10;
    const LINK_MODE_ALL = 0;
    const LINK_MODE_CHILDREN_ONLY = 10;

    private $recursiveTree;
    private $recursiveTreeItem;

    private $parentTag = 'ul';
    private $itemTag = 'li';
    private $parentTagAttributes;
    private $itemTagAttributes;
    private $labelAttribute = 'name';
    private $type = self::TYPE_TREE;
    private $link;
    private $linkTagAttributes;
    private $linkMode = self::LINK_MODE_ALL;
    private $listItemPrefix = '';

    public function __construct(RecursiveTree $recursiveTree, ?RecursiveTreeItem $recursiveTreeItem = null)
    {
        $this->recursiveTree = $recursiveTree;
        $this->recursiveTreeItem = $recursiveTreeItem;
    }

    public function setParentTag(string $tag) : self
    {
        $this->parentTag = $tag;
        return $this;
    }

    public function setItemTag(string $tag) : self
    {
        $this->itemTag = $tag;
        return $this;
    }

    public function setItemTagAttributes(array $array) : self
    {
        $this->itemTagAttributes = $array;
        return $this;
    }

    public function setParentTagAttributes(array $array) : self
    {
        $this->parentTagAttributes = $array;
        return $this;
    }

    public function setLabelAttribute(string $name) : self
    {
        $this->labelAttribute = $name;
        return $this;
    }

    public function setType(int $type) : self
    {
        $this->type = $type;
        return $this;
    }

    public function setLink($link) : self
    {
        $this->link = $link;
        return $this;
    }

    public function setLinkTagClass($class) : self
    {
        $this->linkTagClass = $class;
        return $this;
    }

    public function setLinkMode($mode) : self
    {
        $this->linkMode = $mode;
        return $this;
    }

    public function setListItemPrefix(string $string) : self
    {
        $this->listItemPrefix = $string;
        return $this;
    }

    public function create() : ?string
    {
        if ($this->type == self::TYPE_TREE) {
            return $this->getItemTreeHtml($this->recursiveTreeItem);
        } elseif ($this->type == self::TYPE_LIST) {
            return $this->getItemListHtml($this->recursiveTreeItem);
        }

        return null;
    }

    public function createSelect() : string
    {

    }

    private function getItemTreeHtml(?RecursiveTreeItem $recursiveTreeItem) : string
    {
        $content = '';

        foreach ($this->recursiveTree->getChildren($recursiveTreeItem) as $child) {
            $labelAttribute = $this->labelAttribute;
            $hasChildren = $child->hasChildren();

            if ($this->link && ($this->linkMode == self::LINK_MODE_ALL || !$hasChildren)) {
                $itemInner = $this->makeTag('a', $child->$labelAttribute, $this->linkTagAttributes + [
                    'href' => str_replace(':id', $child->getID(), $this->link)
                ]);
            } else {
                $itemInner = $child->$labelAttribute;
            }

            if ($hasChildren) {
                $itemInner .= $this->getItemTreeHtml($child);
            }

            $content .= $this->makeTag($this->itemTag, $itemInner, $this->itemTagAttributes);

        }

        return $this->makeTag($this->parentTag, $content, $this->parentTagAttributes);
    }

    private function getItemListHtml(?RecursiveTreeItem $recursiveTreeItem, $depth = 0) : string
    {
        $content = '';

        foreach ($this->recursiveTree->getChildren($recursiveTreeItem) as $child) {
            $labelAttribute = $this->labelAttribute;
            $label = str_repeat($this->listItemPrefix, $depth) . $child->$labelAttribute;

            if ($this->link) {
                $itemInner = $this->makeTag('a', $label, $this->linkTagAttributes + [
                    'href' => str_replace(':id', $child->getID(), $this->link)
                ]);
            } else {
                $itemInner = $label;
            }

            $content .= $this->makeTag($this->itemTag, $itemInner, $this->itemTagAttributes);

            if ($child->hasChildren()) {
                $content .= $this->getItemListHtml($child, $depth + 1);
            }
        }

        if ($depth == 0) {
            return $this->makeTag($this->parentTag, $content, $this->parentTagAttributes);
        }

        return $content;
    }

    private function makeTag($tag, $content, $attributes = null) : string
    {
        if (is_array($attributes)) {
            $attributesString = '';

            foreach ($attributes as $attributeName => $attributeValue) {
                $attributesString .= ' ' . $attributeName . '=' . '"' . $attributeValue . '"';
            }
        } else {
            $attributesString = '';
        }

        $html = '<' . $tag;

        if ($attributesString) {
            $html .= $attributesString;
        }

        $html .= '>';
        $html .= $content;
        $html .= '</' . $tag . '>';

        return $html;
    }
}
