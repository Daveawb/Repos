<?php

namespace Daveawb\Repos\Contracts;

/**
 * Interface AllowCriteria
 * @package Daveawb\Repos\Contracts
 */
interface AllowCriteria {

    /**
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true);

    /**
     * @return mixed
     */
    public function getCriteria();

    /**
     * @param string $criteria
     * @param array $args
     * @return $this
     */
    public function getByCriteria($criteria, array $args = []);

    /**
     * @param string $criteria
     * @param array $args
     * @return $this
     */
    public function pushCriteria($criteria, array $args = []);

    /**
     * @return $this
     */
    public function  applyCriteria();
}