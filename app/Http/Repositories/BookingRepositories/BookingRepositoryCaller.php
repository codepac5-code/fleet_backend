<?php
namespace App\Http\Repositories\BookingRepositories;
use App\Models\{Booking};

class BookingRepositoryCaller{

    static public function createRepository(){return (new BookingCreateRepository());}
    static public function readRepository(){return (new BookingReadRepository());}
    static public function updateRepository(){return (new BookingUpdateRepository());}
    static public function deleteRepository(){return (new BookingDeleteRepository());}
    static public function get_model() : Booking {return (new Booking());}


}