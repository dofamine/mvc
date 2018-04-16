<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 11.04.2018
 * Time: 15:47
 */

class ControllerNotes extends Controller
{
    public function notes(int $id)
    {
        $model = new ModelNotes();
        $view = new View("note");
        $view->notes = $model->getAllByUserId($id);
        if (sizeof($view->notes)>0) {
            $range = "(" . implode(",", array_map(function ($note) {
                    return $note->id;
                }, $view->notes)) . ")";
            $view->images = ModuleDatabaseConnection::instance()
                ->images->fields(["name", "url", "notes_id",])
                ->join("note_image", "images_id")
                ->where("notes_id", "IN", $range, true)
                ->all();
        }
        $this->response($view);
    }
//    public function images()
//    {
//        $model = new ModelImages();
//        $view = new View("images");
//        $view->cats = $model->getAll();
//        $this->response($view);
//    }
}