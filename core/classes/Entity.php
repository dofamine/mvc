<?php
namespace Entity;

abstract class Entity
{
    protected static function _fromAssocies(array $elems,string $className):array
    {
        $arr = [];
        foreach ($elems as $elem){
            $entity = new $className();
            $entity->fromAssoc($elem);
            $arr[] = $entity;
        }
        return $arr;
    }
    public function fromAssoc(array $data)
    {
        foreach ($data as $name=>$value) $this->$name = $value;
    }
    abstract public static function fromAssocies (array $array): array;
}