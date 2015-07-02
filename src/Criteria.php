<?php

namespace Daveawb\Repos;

/**
 * Class Criteria
 * @package Daveawb\Repos
 */
abstract class Criteria {

    /**
     * Add criteria to the next query by the repository.
     *
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    abstract public function apply($model, Repository $repository);
}