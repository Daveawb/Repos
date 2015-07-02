<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 14/05/15
 * Time: 14:52
 */

namespace Daveawb\Repos;

/**
 * Class Terminator
 * @package Daveawb\Repos
 */
abstract class Terminator {

    /**
     * Get data using the models current state.
     *
     * @param $model
     * @param Repository $repository
     * @return mixed
     */
    abstract public function apply($model, Repository $repository);
}