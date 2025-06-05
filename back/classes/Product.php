<?php
class Product {
    public $id;
    public $code;
    public $name;
    public $description;
    public $image;
    public $category;
    public $price;
    public $quantity;
    public $internalReference;
    public $shellId;
    public $inventoryStatus;
    public $rating;
    public $createdAt;
    public $updatedAt;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
