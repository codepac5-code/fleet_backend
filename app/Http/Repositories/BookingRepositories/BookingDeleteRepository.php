<?php
namespace App\Http\Repositories\BookingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\DeleteRepository;
use App\Models\Booking;

class BookingDeleteRepository extends DeleteRepository
{
    public function __construct()
    {
        $this->model = new Booking();
    }
}