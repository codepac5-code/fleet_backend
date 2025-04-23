<?php
namespace App\Http\Repositories\BookingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\CreateRepository;
use App\Models\Booking;

class BookingCreateRepository extends CreateRepository
{
    public function __construct()
    {
        $this->model = new Booking();
    }
}