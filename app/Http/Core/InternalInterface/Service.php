<?php
namespace App\Http\Core\InternalInterface;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\Prese;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

interface Service  {
    public function execute () : ResponseModel  | JsonResponse | View |RedirectResponse;
}
