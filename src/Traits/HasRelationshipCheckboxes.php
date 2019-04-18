<?php

namespace Fourstacks\NovaCheckboxes\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HasRelationshipCheckboxes
{
    public function saveWithRelations(array $relationNames, array $options)
    {
        $relationData = \array_reduce($relationNames, function(array $carrier, string $relationName) {
            // tags -> tagsNova
            $relationNovaName = \sprintf("%sNova", $relationName);

            if (isset($this->attributes[$relationNovaName])) {
                $carrier[$relationName] = $this->attributes[$relationNovaName];
                unset($this->attributes[$relationNovaName]);
            }

            return $carrier;
        }, []);

        $result = parent::save($options);

        foreach($relationData as $relationName => $relationIds) {
            $this->{$relationName}()->sync($relationIds);
        }

        return $result;
    }

    /**
     * Parse a collection of models into checkbox data
     *
     * @param string $relation
     * @param string $idKey id column name
     * @return array
     */
    public function modelsToCheckboxData(string $relation, string $idKey = ''): array
    {
        if($idKey === '') {
            $idKey = sprintf('%s.id', Str::plural($relation));
        }

        return $this->{$relation}()->get([$idKey])->reduce(
            function (array $carrier, Model $model): array {
                $carrier[$model->id] = true;

                return $carrier;
            },
            []
        );
    }
}
