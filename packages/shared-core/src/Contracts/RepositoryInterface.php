<?php

namespace Shared\Core\Contracts;

interface RepositoryInterface
{
    public function all(array $columns = ['*']);
    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null);
    public function find($id, array $columns = ['*']);
    public function findBy(string $field, $value, array $columns = ['*']);
    public function findWhere(array $where, array $columns = ['*']);
    public function findWhereFirst(array $where, array $columns = ['*']);
    public function create(array $data);
    public function update(array $data, $id);
    public function delete($id);
    public function with(array $relations);
    public function where(string $column, $operator = null, $value = null, string $boolean = 'and');
    public function orderBy(string $column, string $direction = 'asc');
    public function search(string $query, array $columns);
}
