<?php

namespace Classes;

/**
 * Class Repository
 * @package Daveawb\Tests\Classes
 */
class Repository extends \Daveawb\Repos\Repository {

    /**
     * Get the models namespaced class name
     *
     * @return string
     */
    public function model()
    {
        return 'Classes\User';
    }
}