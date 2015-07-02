<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 14/05/15
 * Time: 14:53
 */

namespace Daveawb\Repos\Contracts;

use Daveawb\Repos\Terminator;

/**
 * Interface AllowTerminators
 * @package Daveawb\Repos\Contracts
 */
interface AllowTerminators {
    /**
     * Retrieve results using a terminator expression.
     * This special type of criteria will return a
     * result set rather than modifying the model/builder.
     *
     * @param Terminator $terminator
     *
     * @return mixed
     */
    public function findByTerminator(Terminator $terminator);
}