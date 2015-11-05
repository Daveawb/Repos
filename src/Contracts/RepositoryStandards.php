<?php

namespace Daveawb\Repos\Contracts;

use Daveawb\Repos\Exceptions\RepositoryException;

/**
 * Interface RepositoryStandards
 * @package Daveawb\Repos\Contracts
 */
interface RepositoryStandards {

    /**
     * Retrieve all models
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAll(array $columns = ['*']);

    /**
     * Find models where $column === $id
     *
     * @param $field
     * @param $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model
     * @throws RepositoryException
     */
    public function findBy($field, $id, array $columns = ['*']);

    /**
     * Find models using $method as the terminator
     *
     * @param $method
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection | \Illuminate\Database\Eloquent\Model
     * @throws RepositoryException
     */
    public function findByMethod($method, array $columns = ['*']);

    /**
     * Persist a new set of data
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($data);

    /**
     * Update a model where $column === $id
     *
     * @param array $data
     * @param $field
     * @param $id
     * @return bool|int
     */
    public function update(array $data, $field, $id);

    /**
     * Paginate results
     *
     * @param int $perPage
     * @param array $columns
     * @return \Illuminate\Pagination\AbstractPaginator
     */
    public function paginate($perPage = 10, array $columns = ['*']);

    /**
     * Delete a model where $column === $id
     *
     * @param $field
     * @param $id
     * @return bool|null
     */
    public function delete($field, $id);

    /**
     * Make and return a new model
     *
     * @param null $override
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function newModel($override = null);

}