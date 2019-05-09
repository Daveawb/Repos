<?php

namespace Daveawb\Repos;

use Daveawb\Repos\Contracts\AllowCriteria;
use Daveawb\Repos\Contracts\AllowTerminators;
use Daveawb\Repos\Contracts\RepositoryStandards;
use Daveawb\Repos\Exceptions\RepositoryException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class Repository
 * @package Daveawb\Repos
 */
abstract class Repository implements RepositoryStandards, AllowCriteria, AllowTerminators {

    const NO_FLUSH = 0;
    const FLUSH = 1;

    /**
     * @var Model|Builder
     */
    protected $model;

    /**
     * @var Container
     */
    protected $app;

    /**
     * @var Collection
     */
    protected $criteria;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * On construct create the model
     * @param Container $app
     * @param Collection $criteria
     * @throws BindingResolutionException
     */
    public function __construct(Container $app, Collection $criteria)
    {
        $this->app = $app;
        $this->model = $this->newModel();
        $this->criteria = $criteria;
    }

    /**
     * Retrieve all models
     *
     * @param array $columns
     * @return EloquentCollection
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function findAll(array $columns = ['*'])
    {
        $this->applyCriteria();

        return $this->model->get($columns);
    }

    /**
     * Find models where $column === $id
     *
     * @param string $field
     * @param mixed $id
     * @param array $columns
     * @return Model
     * @throws RepositoryException
     * @throws BindingResolutionException
     */
    public function findBy($field, $id, array $columns = ['*'])
    {
        $this->applyCriteria();

        $model = $this->model->where($field, $id)->first($columns);

        if ( ! $model )
            throw new RepositoryException("Model does not exist.");

        return $model;
    }

    /**
     * @param $id
     * @param array $columns
     * @return Model
     * @throws RepositoryException
     * @throws BindingResolutionException
     */
    public function findById($id, array $columns = ['*'])
    {
        return $this->findBy($this->model->getKeyName(), $id, $columns);
    }

    /**
     * Find models using $method as the terminator
     *
     * @param $method
     * @param array $columns
     * @return EloquentCollection|Model
     * @throws RepositoryException
     * @throws BindingResolutionException
     * @deprecated Will be removed in version 1.0.0 use withBuilder() instead
     */
    public function findByMethod($method, array $columns = ['*'])
    {
        $this->applyCriteria();

        $result = $this->model->{$method}($columns);

        if ( ! $result )
            throw new RepositoryException("Method {{$method}} does not exist on the model.");

        return $result;
    }

    /**
     * Persist a new set of data
     *
     * @param array|callable $data
     * @param int $flush
     * @return Model|Builder
     * @throws BindingResolutionException
     */
    public function create($data, $flush = self::FLUSH)
    {
        if ($flush === self::FLUSH) {
            $this->flushModel();
        }

        if (is_callable($data)) {
            $model = $this->model;

            call_user_func($data, $model);

            $model->push();
        } else {
            $model = $this->model->create($data);
        }

        return $model;
    }

    /**
     * Update a model where $column === $id
     *
     * @param array $data
     * @param $field
     * @param $id
     * @return Model
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function update(array $data, $field, $id)
    {
        $this->applyCriteria();

        /** @var Model $model */
        $model = $this->model->where($field, $id)->first();

        $model->fill($data)->save();

        return $model;
    }

    /**
     * Update a model passed in. Returns a boolean indicating whether the
     * model has been modified or not.
     *
     * @param array $data
     * @param Model $model
     * @return bool
     */
    public function updateModel(array $data, Model $model)
    {
        $model = $model->fill($data);

        return $model->isDirty() && $model->update($data);
    }

    /**
     * Paginate results
     *
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param null $page
     * @return LengthAwarePaginator
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function paginate($perPage = 10, array $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->applyCriteria();

        return $this->model->paginate($perPage, $columns, $pageName, $page);
    }

    /**
     * Delete a model where $column === $id
     *
     * @param $field
     * @param $id
     * @return bool|null
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function delete($field, $id)
    {
        $this->applyCriteria();

        return $this->model->where($field, $id)->delete();
    }

    /**
     * Make a new model and attach it to the repository
     *
     * @param null $override
     *
     * @return Model|mixed
     * @throws BindingResolutionException
     */
    public function newModel($override = null)
    {
        $model = $this->model();

        if ($override) $model = $override;

        $class = '\\'.ltrim($model, '\\');

        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->app->make($class);
    }

    /**
     * Get the models namespaced class name
     *
     * @return string
     */
    abstract public function model();

    /**
     * @param bool $status
     * @return $this
     */
    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * @param Criteria $criteria
     * @param array $args
     * @return $this
     * @throws RepositoryException
     */
    public function getByCriteria($criteria, array $args = [])
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->model = $this->criteriaFactory($criteria, $args)
            ->apply($this->model, $this);

        return $this;
    }

    /**
     * @param string $criteria
     * @param array $args
     * @return $this
     */
    public function pushCriteria($criteria, array $args = [])
    {
        $this->criteria->push([$criteria, $args]);

        return $this;
    }

    /**
     * @return $this
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function applyCriteria()
    {
        // Clear out any previous modifications
        $this->flushModel();

        if($this->skipCriteria === true)
            return $this;

        foreach($this->getCriteria() as $criteria)
        {
            list($class, $args) = $criteria;

            $this->getByCriteria($class, $args);
        }

        return $this;
    }

    /**
     * Retrieve results using a terminator expression.
     * This special type of criteria will return a
     * result set rather than modifying the model/builder.
     *
     * @param string $terminator
     * @param array $args
     * @return mixed
     * @throws RepositoryException
     */
    public function getByTerminator($terminator, array $args = [])
    {
        return $this->criteriaFactory($terminator, $args)->apply($this->model, $this);
    }

    /**
     * Criteria factory method
     *
     * @param $class
     * @param array $args
     * @return Criteria
     * @throws RepositoryException
     */
    private function criteriaFactory($class, array $args)
    {
        $criteria = new $class(...$args);

        if ( ! $criteria instanceof Criteria) {
            throw new RepositoryException("{$class} is not an instance of Criteria");
        }

        return $criteria;
    }

    /**
     * Flush the repositories model and replace with a fresh one
     * @throws BindingResolutionException
     */
    public function flushModel()
    {
        $this->model = $this->newModel();
    }

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @return Repository
     * @throws BindingResolutionException
     */
    public function newInstance()
    {
        return new static($this->app, new Collection());
    }
}
