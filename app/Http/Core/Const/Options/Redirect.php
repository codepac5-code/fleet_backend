<?php
namespace App\Http\Core\Const\Options;


enum Redirect: string {
    case Back = 'back';
    case ToView = 'view';
    case ToRoute = 'route';
}

