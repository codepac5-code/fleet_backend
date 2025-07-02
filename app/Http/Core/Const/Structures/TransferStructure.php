<?php
namespace App\Http\Core\Const\Structures;

use Illuminate\Database\Eloquent\Model;

class TransferStructure {

    public function __construct(    private Model $entity,
    private $amount,
    private $description_ar,
    private $description_en
    ){}




    /**
     * Get the value of amount
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Get the value of description_ar
     */
    public function getDescriptionAr() {
        return $this->description_ar;
    }

    /**
     * Get the value of entity
     */
    public function getEntity() {
        return $this->entity;
    }

    /**
     * Get the value of description_en
     */
    public function getDescriptionEn() {
        return $this->description_en;
    }
}