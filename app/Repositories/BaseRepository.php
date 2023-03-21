<?php namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected $model;
    protected $queryBuilder;

    public function __construct($model)
    {
        $this->model = $model;
        $this->queryBuilder = $model;
    }

    public function __call($function, $arguments)
    {
        $this->queryBuilder = call_user_func_array([$this->queryBuilder, $function], $arguments);
        return $this;
    }

    public function resetQueryBuilder()
    {
        $this->queryBuilder = $this->model->newInstance();
    }

    public function getNewModelInstance(array $attributes = array())
    {
        return $this->model->newInstance($attributes);
    }

    public function getQueryBuilderAndReset()
    {
        $q = clone $this->queryBuilder;
        $this->resetQueryBuilder();
        return $q;
    }

    //used when you want to keep current conditions for old query, but create a new branch from now to add new conditions
    public function getQueryBuilderCopy()
    {
        return clone $this->queryBuilder;
    }


    function all()
    {
        return $this->model->all();
    }

    /**
     * returns the model found with additional options
     *
     * @param int $id
     * @param mixed $related - the relations to be load with eager loading
     *
     * @return mixed
     */
    public function find($id, array $related = null)
    {
        if (!is_null($related)) {
            return $this->model->with($related)->findOrFail($id);
        } else {
            return $this->model->findOrFail($id);
        }
    }

    /**
     * @param $ids
     * @param array $columns
     * @return mixed
     */
    public function findMany($ids, $columns = ['*'])
    {
        return $this->model->findMany($ids, $columns);
    }

    /**
     * create a new model
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * update the model
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update($id, array $data)
    {
        $model = $this->findWithTrashed($id);
        if (!$model->fill($data)->save()) {
            return false;
        }
        return $model;
    }

    public function findWithTrashed($id)
    {
        $model = $this->where('id', $id);

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model))) {
            $model = $model->withTrashed();
        }

        return $model->first();
    }

    /**
     * @param $key
     * @param $value
     * @return $this|BaseRepository
     */
    public function filterBy($key, $value)
    {
        if (!$value) return $this;

        return $this->where($key, $value);
    }

    /**
     * @param $key
     * @param $value
     * @return $this|BaseRepository
     */
    public function filterByLike($key, $value)
    {
        if (!$value) return $this;

        return $this->where($key, 'like', '%' . $value . '%');
    }

    /**
     * update the model by object
     *
     * @param Model $obj
     * @param array $data
     * @return Model
     */
    public function updateObject(Model $obj, array $data)
    {
        $obj->fill($data)->save();

        return $obj;
    }

    /**
     * delete the model
     *
     * @param mixed $id
     * @return Model
     */
    public function deleteById($id)
    {
        return $this->model->find($id)->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function restoreById($id)
    {
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this->model))) {
            return $this->model->withTrashed()->where('id', $id)->restore();
        } else {
            return $this->model->where('id', $id)->update('deleted_at', null);
        }
    }

    /**
     * returns the first model found by conditions
     *
     * @param string $key
     * @param mixed $value
     * @param string $operator
     * @return Model
     */
//    public function getFirstBy($key, $value, $operator = '=')
//    {
//        return $this->model->where($key, $operator, $value)->first();
//    }

    /**
     * returns all models found by conditions
     *
     * @param string $key
     * @param mixed $value
     * @param string $operator
     * @return Collection
     */
//    public function getAllBy($key, $value, $operator = '=')
//    {
//        return $this->model->where($key, $operator, $value)->get();
//    }


    /**
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = ['*'])
    {
        $return = $this->queryBuilder->get($columns);
        $this->resetQueryBuilder();
        return $return;
    }

    public function first()
    {
        $return = $this->queryBuilder->first();
        $this->resetQueryBuilder();
        return $return;
    }

    public function count()
    {
        $return = $this->queryBuilder->count();
        $this->resetQueryBuilder();
        return $return;
    }

    public function delete()
    {
        $return = $this->queryBuilder->delete();
        $this->resetQueryBuilder();
        return $return;
    }

    public function updateAll(array $values)
    {
        $return = $this->queryBuilder->update($values);
        $this->resetQueryBuilder();
        return $return;
    }

    public function paginate($limit = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $return = $this->queryBuilder->paginate($limit, $columns, $pageName, $page);
        $this->resetQueryBuilder();
        return $return;
    }

    public function lists($column, $key = null)
    {
        $return = $this->queryBuilder->lists($column, $key);
        $this->resetQueryBuilder();
        return $return;
    }

    /**
     * @param $relation int|array
     * @return $this
     */
    public function with($relations)
    {
        $this->queryBuilder = $this->queryBuilder->with($relations);
        return $this;
    }

    public function where($field, $operand = NULL, $value = NULL)
    {
        $this->queryBuilder = $this->queryBuilder->where($field, $operand, $value);
        return $this;
    }

    public function selectRaw($expression, array $bindings = [])
    {
        $this->queryBuilder = $this->queryBuilder->selectRaw($expression, $bindings);
        return $this;
    }

    public function whereRaw($query, array $bindings = [])
    {
        $this->queryBuilder = $this->queryBuilder->whereRaw($query, $bindings);
        return $this;
    }

    public function orWhere($field, $operand = NULL, $value = NULL)
    {
        $this->queryBuilder = $this->queryBuilder->orWhere($field, $operand, $value);
        return $this;
    }

    public function orWhereHas($relation, \Closure $callback, $operator = '>=', $count = 1)
    {
        $this->queryBuilder = $this->queryBuilder->orWhereHas($relation, $callback, $operator, $count);
        return $this;
    }

    public function whereHas($relation, \Closure $callback, $operator = '>=', $count = 1)
    {
        $this->queryBuilder = $this->queryBuilder->WhereHas($relation, $callback, $operator, $count);
        return $this;
    }

    public function orderBy($field, $order = 'ASC')
    {
        if (!$field) {
            return $this;
        }

        $this->queryBuilder = $this->queryBuilder->orderBy($field, $order);
        return $this;
    }

    public function orderByRaw($sql, $bindings = [])
    {
        $this->queryBuilder = $this->queryBuilder->orderByRaw($sql, $bindings);
        return $this;
    }

    public function skip($value)
    {
        $this->queryBuilder = $this->queryBuilder->skip($value);
        return $this;
    }

    public function offset($value)
    {
        return $this->skip($value);
    }

    public function take($value)
    {
        $this->queryBuilder = $this->queryBuilder->take($value);
        return $this;
    }

    public function limit($value)
    {
        return $this->take($value);
    }

    /**
     * @param $condition
     * @param $orderByRaw
     * @return $this
     */
    public function orderByRawIfCondition($condition, $orderByRaw)
    {
        if (!$condition) return $this;

        return $this->orderByRaw($orderByRaw);
    }
}
