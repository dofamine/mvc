<?php

namespace Entity;


class Note extends Entity
{
    public $id, $name, $description, $users_id;

    public function __construct(string $name = "", string $description = "", int $users_id = 0)
    {
        $this->name = $name;
        $this->description = $description;
        $this->users_id = $users_id;
    }

    public static function fromAssocies(array $array): array
    {
        return self::_fromAssocies($array, self::class);
    }
}