<?php

namespace Daveawb\Repos\Contracts;

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
     * @param string $terminator
     * @param array $args
     * @return mixed
     */
    public function getByTerminator($terminator, array $args = []);
}