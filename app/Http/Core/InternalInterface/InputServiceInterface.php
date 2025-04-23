<?php
namespace App\Http\Core\InternalInterface;

Interface InputServiceInterface {

    public function __construct(array $input);
    public function toArray();

}


