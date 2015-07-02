<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 11/05/15
 * Time: 14:27
 */

namespace Classes;

use Daveawb\Repos\Exceptions\RepositoryException;

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