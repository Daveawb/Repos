<?php

namespace Daveawb\Repos\Contracts;

use Daveawb\Repos\Criteria;

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
     * @param Criteria $criteria
     * @return $this
     */
    public function getByCriteria(Criteria $criteria);

    /**
     * @param Criteria $criteria
     * @return $this
     */
    public function pushCriteria(Criteria $criteria);

    /**
     * @return $this
     */
    public function  applyCriteria();
}