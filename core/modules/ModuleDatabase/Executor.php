<?php

namespace ModuleDatabase;

use \PDO;
use \PDOStatement;


class Executor
{
    private $table = null;
    private $dbh = null;

    private $components = [
        "where" => [],
        "having" => [],
        "order" => [],
        "join" => [],
        "group" => [],
        "fields" => null,
        "limit" => null,
        "offset" => null
    ];

    public function __construct(PDO $dbh, string $table)
    {
        $this->table = $table;
        $this->dbh = $dbh;
    }

    private function _execute(string $query, array $params): PDOStatement
    {
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    private function _condition(string $type, string $field, string $sign, $value = null, bool $native): array
    {
        if ($value === null) {
            $value = $sign;
            $sign = "=";
        }
        if (!is_int($value) && $value[0] != ":" && $value != "?" && !$native)
            $value = $this->dbh->quote($value);
        return [$type, $this->_toString($field), $sign, $value];
    }

    private function _toString(string $field): string
    {
        return "`" . str_replace(".", "`.`", $field) . "`";
    }

    private function _select(): string
    {
        $fields = $this->components["fields"] === null ? "*" : implode(", ", $this->components["fields"]);
        $query = "SELECT {$fields} FROM `{$this->table}`";
        if (!empty($this->components["join"])) {
            foreach ($this->components["join"] as $join) {
                $query .= " {$join[0]} JOIN `{$join[1]}` ON {$join[2]} ";
            }
        }
        if (!empty($this->components["where"])) {
            $query .= " WHERE ";
            foreach ($this->components["where"] as $where) {
                $query .= " {$where[0]} ";
                if (count($where) > 1) {
                    $query .= "{$where[1]} {$where[2]} {$where[3]}";
                }
            }
        }
        if (!empty($this->components["having"])) {
            $query .= " HAVING ";
            foreach ($this->components["having"] as $having) {
                $query .= $having[0];
                if (count($having) > 1) $query .= "( {$having[1]} {$having[2]} {$having[3]})";
            }
        }
        if (!empty($this->components["order"])) {
            $query .= " ORDER BY " . implode(",", array_map(function ($elem) {
                    return " {$elem[1]} {$elem[0]} ";
                }, $this->components["order"]));
        }
        if (!empty($this->components["limit"])) {
            $query .= " LIMIT {$this->components["limit"]} ";
        }
        if (!empty($this->components["offset"])) {
            $query .= " OFFSET {$this->components["offset"]} ";
        }
        return $query;
    }

    private function _whereGroup(callable $where, $type = null): self
    {
        if ($type !== null) $this->components["where"][] = [$type];
        $this->components["where"][] = ["("];
        $where($this);
        $this->components["where"][] = [")"];
        return $this;
    }
    //->whereGroup(function($q){
    //  $q->where("id",7);
    //  $q->orWhere("id",12);
    //})
    private function _join(string $table, string $field_far, string $field = "id", string $current_table = null, string $type = "INNER"): self
    {
        $current_table = $current_table ? $current_table : $this->table;
        $field = "`{$current_table}`.`{$field}`";
        $field_far = "`{$table}`.`{$field_far}`";
        $on = "({$field}={$field_far})";
        $this->components["join"][] = [$type, $table, $on];
        return $this;
    }

    public function insert(array $data): int
    {
        $names = array_keys($data);
        $query = "INSERT INTO `{$this->table}` (`"
            . implode("`,`", $names)
            . "`) VALUES (:"
            . implode(",:", $names)
            . ")";
        $this->_execute($query, $data);
        return $this->dbh->lastInsertId();
    }

    public function getAllWhere(string $where = "1", array $data = [])
    {
        return $this->_execute("SELECT * FROM `{$this->table}` WHERE {$where}", $data)->fetchAll();
    }

    public function deleteWhere(string $where, array $data = []): void
    {
        $this->_execute("DELETE FROM `{$this->table}` WHERE {$where}", $data);
    }

    public function deleteById(int $id): void
    {
        $this->deleteWhere("id=?", [$id]);
    }

    public function getFirstWhere(string $where, array $data = [])
    {
        return $this->_execute("SELECT * FROM `{$this->table}` WHERE {$where}", $data)->fetch();

    }

    public function getElementById(int $id)
    {
        return $this->getFirstWhere("id=?", [$id]);
    }

    public function updateById(int $id, array $data): void
    {
        $_data = array_map(function ($elem) {
            return "`{$elem}`=:{$elem}";
        }, array_keys($data));
        $query = "UPDATE `{$this->table}` SET " . implode(", ", $_data) . " WHERE id={$id}";
        $this->_execute($query, $data);
    }

    public function countOfWhere(string $where = "1", array $data = []):?int
    {
        return (int)$this->_execute("SELECT COUNT(*) FROM `{$this->table}` WHERE {$where}", $data)->fetchColumn();
    }

    public function join(string $table, string $field_far, string $field = "id", string $cur_table = null): self
    {
        return $this->_join($table, $field_far, $field, $cur_table);
    }

    public function joinLeft(string $table, string $field_far, string $field = "id", string $cur_table = null): self
    {
        return $this->_join($table, $field_far, $field, $cur_table, "LEFT");
    }

    public function joinRight(string $table, string $field_far, string $field = "id", string $cur_table = null): self
    {
        return $this->_join($table, $field_far, $field, $cur_table, "RIGHT");
    }

    public function where(string $field, string $sign, string $value = null, bool $native = false): self
    {
        $this->components["where"][] = $this->_condition("", $field, $sign, $value, $native);
        return $this;
    }

    public function orWhere(string $field, string $sign, string $value = null, bool $native = false): self
    {
        $this->components["where"][] = $this->_condition("OR", $field, $sign, $value, $native);
        return $this;
    }

    public function andWhere(string $field, string $sign, string $value = null, bool $native = false): self
    {
        $this->components["where"][] = $this->_condition("AND", $field, $sign, $value, $native);
        return $this;
    }

    public function whereGroup(callable $where): self
    {
        return $this->_whereGroup($where);
    }

    public function andWhereGroup(callable $where): self
    {
        return $this->_whereGroup($where, "AND");
    }

    public function orWhereGroup(callable $where): self
    {
        return $this->_whereGroup($where, "OR");

    }

    public function having(string $field, string $sign, string $value = null, bool $native = false): self
    {
        $this->components["having"][] = $this->_condition("", $field, $sign, $value, $native);
        return $this;
    }

    public function orHaving(string $field, string $sign, string $value = null, bool $native = false): self
    {
        $this->components["having"][] = $this->_condition("OR", $field, $sign, $value, $native);
        return $this;
    }

    public function andHaving(string $field, string $sign, string $value = null, bool $native = false): self
    {
        $this->components["having"][] = $this->_condition("AND", $field, $sign, $value, $native);
        return $this;
    }

    public function asc(string $field = "id"): self
    {
        $this->components["order"][] = ["ASC", $this->_toString($field)];
        return $this;
    }

    public function desc(string $field = "id"): self
    {
        $this->components["order"][] = ["DESC", $this->_toString($field)];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->components["limit"] = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->components["offset"] = $offset;
        return $this;
    }

    public function fields(array $table_fields): self
    {
        $this->components["fields"] = array_map(function ($field) {
            return $this->_toString($field);
        }, $table_fields);
        return $this;
    }
//    ["ff.name","user_role.name"]

    public function all(array $params = [])
    {
        return $this->_execute($this->_select(), $params)->fetchAll();
    }

    public function first(array $params = [])
    {
        return $this->_execute($this->_select(), $params)->fetch();
    }

}