<?php

namespace Classes;

use Daveawb\Repos\Repository as BaseRespository;
use Daveawb\Repos\Terminator;

/**
 * Class UsernameTerminator
 * @package Daveawb\Tests\Classes
 */
class UsernameTerminator extends Terminator{

    /**
     * Get data using the models current state.
     *
     * @param                   $model
     * @param BaseRespository   $repository
     *
     * @return mixed
     */
    public function apply($model, BaseRespository $repository)
    {
        return $model->where('username', 'daveawb')->first();
    }
}