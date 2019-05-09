<?php

namespace Classes;

use Daveawb\Repos\Repository as BaseRespository;
use Daveawb\Repos\Terminator;
use Illuminate\Database\Query\Builder;

/**
 * Class NameTerminator
 * @package Daveawb\Tests\Classes
 */
class NameTerminator extends Terminator{

    public static $FIRST_NAME;

    /**
     * Get data using the models current state.
     *
     * @param Builder           $model
     * @param BaseRespository   $repository
     *
     * @return mixed
     */
    public function apply($model, BaseRespository $repository)
    {
        return $model->where('first_name', '=', static::$FIRST_NAME)->first();
    }
}
