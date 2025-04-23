<?php
namespace App\Http\Repositories\BookingRepositories;
use App\Http\Core\Repositories\Abstract_CRUD_Repositoris\UpdateRepository;
use App\Models\Booking;

class BookingUpdateRepository extends UpdateRepository
{
    public function __construct()
    {
        $this->model = new Booking();
    }

}