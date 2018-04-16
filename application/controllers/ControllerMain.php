<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 21.03.2018
 * Time: 13:24
 */

class ControllerMain extends Controller
{
    public function action_index()
    {
        $view = new View("main");
        $view->useTemplate();
        $view->css = "main";
        $view->main = true;
        $this->response($view);
    }

    public function action_delete()
    {
        if (!ModuleAuth::instance()->isAuth()) return $this->redirect(URLROOT);
        $id = $this->getUriParam("id");
        ModuleDatabaseConnection::instance()->notes->deleteById($id);
        $this->redirect("/todo");
    }

    public function action_change()
    {
        if (!ModuleAuth::instance()->isAuth()) $this->redirect(URLROOT);
        $id = (int)$this->getUriParam("id");
        $note = ModuleDatabaseConnection::instance()->notes->getElementById($id);
        if (!$note) $this->redirect404();
        $image = ModuleDatabaseConnection::instance()
            ->images
            ->fields(["id", "name", "url", "images_id"])
            ->join("note_image", "images_id")
            ->where("notes_id", "=", $id)
            ->first();
        $view = new View("details");
        $view->useTemplate("default2");
        $view->note = $note;
        if ($image) $view->image = $image;
        $view->css = "addnote";
        $view->description = "Change and save inforation into fields below";
        $this->response($view);
    }

    public function action_addNote()
    {
        if (!ModuleAuth::instance()->isAuth()) $this->redirect(URLROOT);
        $name = @$_POST["name"];
        $description = @$_POST["description"];
        $url = @$_POST["url"];
        $image_name = @$_POST["image_name"];
        $add_image = @$_POST["add_image"];
        if (empty($name) || empty($description)) throw new Exception("Enter all notes fields");
        $model_notes = new ModelNotes();
        if (isset($add_image)) {
            if (empty($url) || empty($image_name)) throw new Exception("Enter all images fields");
            $model_image = new ModelImages();
            ModuleDatabaseConnection::instance()->note_image->insert([
                "notes_id" => $model_notes->addNote(new \Entity\Note($name, $description, (int)ModuleAuth::instance()->getUser()["id"])),
                "images_id" => $model_image->addImage(new \Entity\Image($image_name, $url))
            ]);
        } else {
            $model_notes->addNote(new \Entity\Note($name, $description, (int)ModuleAuth::instance()->getUser()["id"]));
        }
        $this->redirect("/todo");
    }

    public function action_notes()
    {
        if (!ModuleAuth::instance()->isAuth()) $this->redirect(URLROOT);
        $user = ModuleAuth::instance()->getUser();
        $history = ModuleDatabaseConnection::instance()
            ->notes_history
            ->getAllWhere("users_id=?",[$user["id"]]);
        $notes = new ControllerNotes();
        $notes->notes($user["id"]);

        $view = new View("notes");
        $view->useTemplate("default2");
        if (!empty($history)) $view->doneNotes = $history;
        $view->notes = $notes->getResponse();
        $view->main = true;
        $view->user = $user["login"];
        $view->css = "notes";
        $this->response($view);
    }

    public function action_register()
    {
        $view = new View("register");
        $view->useTemplate();
        $view->css = "reg";
        $this->response($view);
    }

    public function action_new()
    {
        $view = new View("addnote");
        $view->useTemplate("default2");
        $view->css = "addnote";
        $view->description = "Enter information into fields below";
        $this->response($view);
    }

    public function action_updateNote()
    {
        if (!ModuleAuth::instance()->isAuth()) $this->redirect(URLROOT);
        $name = @$_POST["name"];
        $description = @$_POST["description"];
        $url = @$_POST["url"];
        $image_name = @$_POST["image_name"];
        $add_image = @$_POST["add_image"];
        $image_id = @$_POST["image_id"];
        if (empty($name) || empty($description)) throw new Exception("Enter all notes fields");
        $note_id = (int)$this->getUriParam("id");
        ModuleDatabaseConnection::instance()
            ->notes
            ->updateById($note_id, [
                "name" => $name,
                "description" => $description]);
        if (isset($add_image)) {
            if (empty($url) || empty($image_name)) throw new Exception("Enter all images fields");
            if (!empty($image_id)) {
                ModuleDatabaseConnection::instance()
                    ->images
                    ->updateById((int)$image_id, ["name" => $image_name, "url" => $url]);
            } else {
                $model_images = new ModelImages();
                ModuleDatabaseConnection::instance()->note_image->insert([
                    "notes_id" => $note_id,
                    "images_id" => $model_images->addImage(new \Entity\Image($image_name, $url))
                ]);
            }
        }
        $this->redirect("/todo");
    }

    public function action_done()
    {
        if (!ModuleAuth::instance()->isAuth()) $this->redirect(URLROOT);
        $note_id = (int)$this->getUriParam("id");
        $note = new ModelNotes();
        $note = $note->getById($note_id);
        ModuleDatabaseConnection::instance()->notes->deleteById($note_id);
        ModuleDatabaseConnection::instance()->notes_history->insert([
            'name'=>$note->name,
            'description'=>$note->description,
            'users_id'=>$note->users_id
        ]);
        $this->redirect("/todo");
    }

    public function action_showNote()
    {
        if (!ModuleAuth::instance()->isAuth()) $this->redirect(URLROOT);
        $id = (int)$this->getUriParam("id");
        $note_model = new ModelNotes();
        $note = $note_model->getDoneById($id);
        $view = new View("description");
        $view->useTemplate("default2");

        $user = ModuleAuth::instance()->getUser();
        $history = ModuleDatabaseConnection::instance()
            ->notes_history
            ->getAllWhere("users_id=?",[$user["id"]]);
        if (!empty($history)) $view->doneNotesFromDesc = $history;
        $view->css = "notes";
        $view->description = "This is your done note";
        $view->note = $note;
        $this->response($view);
    }
}