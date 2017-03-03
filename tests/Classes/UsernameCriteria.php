<?php

namespace Classes;

use Daveawb\Repos\Criteria;
use Daveawb\Repos\Repository as BaseRepository;
use Illuminate\Database\Query\Builder;

/**
 * Class UsernameCriteria
 * @package Daveawb\Tests\Classes
 */
class UsernameCriteria extends Criteria {

    /**
     * Add criteria to the next query by the repository.
     *
     * @param Builder $model
     * @param BaseRepository|Repository $repository
     * @return mixed
     */
    public function apply($model, BaseRepository $repository)
    {
        return $model->where('username', '=', 'daveawb');
    }
}