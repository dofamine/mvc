<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 15.04.2018
 * Time: 14:25
 */
use Entity\Note;

class ModelNotes extends Model
{
    public function getAllByUserId(int $id):array
    {
        return Note::fromAssocies($this->db->notes->getAllWhere("users_id=?",[$id]));
    }

    public function getById(int $id): Note
    {
        $note = new Note();
        $note->fromAssoc($this->db->notes->getElementById($id));
        return $note;
    }

    public function addNote(Note $note): int
    {
        return $this->db->notes->insert([
            "name"=>$note->name,
            "description"=>$note->description,
            "users_id"=>$note->users_id
        ]);
    }
    public function getDoneById(int $id): Note
    {
        $note = new Note();
        $note->fromAssoc($this->db->notes_history->getElementById($id));
        return $note;
    }
}