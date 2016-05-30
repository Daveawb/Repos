<?php

namespace Classes;

use Daveawb\Repos\Criteria;
use Daveawb\Repos\Repository as BaseRespository;

/**
 * Class UsernameCriteria
 * @package Daveawb\Tests\Classes
 */
class UsernameCriteria extends Criteria {

    /**
     * Add criteria to the next query by the repository.
     *
     * @param $model
     * @param BaseRespository|Repository $repository
     * @return mixed
     */
    public function apply($model, BaseRespository $repository)
    {
        return $model->where('username', '=', 'daveawb');
    }
}