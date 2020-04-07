<?php
namespace App\lib\NestedSet;

use Illuminate\Database\Eloquent\Collection;

trait NestedSetTrait
{

    /**
     * Return a custom nested set collection
     *
     * @return NestedSetCollection
     */
    public function newCollection(array $models = [])
    {
        return new NestedSetCollection($models);
    }

    /**
     * Return childs collection
     *
     * @return Collection|NULL
     */
    abstract function getChildsList(): ?Collection;
}