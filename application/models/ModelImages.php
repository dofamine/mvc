<?php
/**
 * Created by PhpStorm.
 * User: asus
 * Date: 21.03.2018
 * Time: 15:58
 */

use Entity\Image;

class ModelImages extends Model
{
    public function getAll()
    {
        return Image::fromAssocies($this->db->images->getAllWhere());
    }

    public function getById(int $id): Image
    {
        $img = new Image();
        $img->fromAssoc($this->db->images->getElementById($id));
        return $img;
    }

    public function addImage(Image $image): int
    {
        return $this->db->images->insert([
            "name"=>$image->name,
            "url"=>$image->url
        ]);
    }
}