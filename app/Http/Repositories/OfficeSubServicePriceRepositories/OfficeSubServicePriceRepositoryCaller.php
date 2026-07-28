<?php
namespace App\Http\Repositories\OfficeSubServicePriceRepositories;
use App\Models\{OfficeSubServicePrice};

class OfficeSubServicePriceRepositoryCaller{

    static public function createRepository(){return (new OfficeSubServicePriceCreateRepository());}
    static public function readRepository(){return (new OfficeSubServicePriceReadRepository());}
    static public function updateRepository(){return (new OfficeSubServicePriceUpdateRepository());}
    static public function deleteRepository(){return (new OfficeSubServicePriceDeleteRepository());}
    static public function get_model() : OfficeSubServicePrice {return (new OfficeSubServicePrice());}


}