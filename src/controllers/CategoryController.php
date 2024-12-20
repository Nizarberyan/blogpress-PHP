<?php
require_once __DIR__ . '/../models/Category.php';

class CategoryController
{
    private $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    public function getAllCategories()
    {
        return $this->category->getAll();
    }

    public function getCategory($id)
    {
        return $this->category->getById($id);
    }

    public function createCategory($name, $description)
    {
        $this->category->name = $name;
        $this->category->description = $description;
        return $this->category->create();
    }
}
