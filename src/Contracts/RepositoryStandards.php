<?php

namespace Daveawb\Repos\Contracts;

use Daveawb\Repos\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator;

/**
 * Interface RepositoryStandards
 * @package Daveawb\Repos\Contracts
 */
interface RepositoryStandards {

    /**
     * Retrieve all models
     *
     * @param array $columns
     * @return Collection
     */
    public function findAll(array $columns = ['*']);

    /**
     * Find models where $column === $id
     *
     * @param $field
     * @param $id
     * @param array $columns
     * @return Model
     * @throws RepositoryException
     */
    public function findBy($field, $id, array $columns = ['*']);

    /**
     * Find models using $method as the terminator
     *
     * @param $method
     * @param array $columns
     * @return Collection | Model
     * @throws RepositoryException
     */
    public function findByMethod($method, array $columns = ['*']);

    /**
     * Persist a new set of data
     *
     * @param callable|array $data
     * @return Model
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
     * @return AbstractPaginator
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
     * @return Model
     */
    public function newModel($override = null);

    /**
     * Flush the repositories model and replace with a fresh one
     *
     * @return void
     */
    public function flushModel();

    /**
     * Get the model or builder in its current state
     *
     * @return Builder|Model
     */
    public function getModel();

    /**
     * Set a model on the repository
     *
     * @param Model $model
     */
    public function setModel(Model $model);

    /**
     * Get a fresh instance of the repository.
     *
     * @return mixed
     */
    public function newInstance();

}
