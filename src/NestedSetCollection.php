<?php
namespace App\lib\NestedSet;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NestedSetCollection extends Collection
{

    private $total;

    private $parent_column;

    private $children_name = 'items';

    private $nest_left;

    private $nest_right;

    private $pointer = 2;

    public function __construct($items = [])
    {
        parent::__construct($items);
        $this->parent_column = 'parent';
        $this->total = count($items);
    }

    /**
     * Travers the tree and define nested set properties for elements in collection
     *
     * @return \App\lib\NestedSet\NestedSetCollection
     */
    public function buildNestedSet()
    {
        $this->nest_left = 1;
        // $this->nest_right = $this->total * 2;

        $this->findFirstLevel();

        $this->where('nest_level', 1)->each(function ($root) {
            $this->travers($root);
        });

        $this->nest_right = $this->pointer;
        $this->pointer = 2;

        return $this;
    }

    /**
     * Search for root level elements.
     * And mark them as 1st nest level
     */
    protected function findFirstLevel()
    {
        $this->each(function ($model) {
            if ($model->nest_level) {
                unset($model->nest_level);
            }

            if ($this->where('id', $model->{$this->parent_column})
                ->isEmpty()) {
                $model->nest_level = 1;
            }
        });
    }

    /**
     * Travers the tree and mark nested set properties
     *
     * @param Model $parent
     */
    protected function travers(Model $parent)
    {
        $parent->nest_left = $this->pointer;
        $this->pointer ++;

        $this->where($this->parent_column, $parent->id)->each(function ($child) {
            $this->travers($child);
        });

        $parent->nest_right = $this->pointer;
        $this->pointer ++;
    }

    /**
     * Discover childs of current elements in collection.
     * If child not in collection it will be added
     *
     * @param int $n
     *            the depth at wich childs would be traced. If 0 - depth is not limited
     * @return \App\lib\NestedSet\NestedSetCollection
     */
    public function traversTreeToNsLevel(int $n = 0)
    {
        $this->findFirstLevel();

        $this->where('nest_level', 1)->each(function ($root) use ($n) {
            $this->getChilds($root, $n);
        });

        return $this;
    }

    protected function getChilds(Model $parent, $n)
    {
        $level = $parent->nest_level + 1;
        if ($n == 0 || $level <= $n) {
            $parent->getChildsList()->each(function ($child) use ($n, $parent) {
                $child->nest_level = $parent->nest_level + 1;
                if ($this->where('id', $child->id)
                    ->isEmpty()) {
                    $this->add($child);
                }

                $this->getChilds($child, $n);
            });
        }
    }
}