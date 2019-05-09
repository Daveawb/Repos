<?php

namespace Classes;

use Daveawb\Repos\Criteria;
use Daveawb\Repos\Repository as BaseRepository;
use Illuminate\Database\Query\Builder;

/**
 * Class NameCriteria
 * @package Daveawb\Tests\Classes
 */
class NameCriteria extends Criteria {

    /**
     * Add criteria to the next query by the repository.
     *
     * @param Builder $model
     * @param BaseRepository|Repository $repository
     * @return mixed
     */
    public function apply($model, BaseRepository $repository)
    {
        return $model->where('first_name', '=', 'Wayne');
    }
}
