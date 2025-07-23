<?php
namespace App\Http\Services\Dashboard\RoleAndPermission\ToView;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AddRoleViewController extends Controller
{
    public function __invoke(Request $request)
    {
     $type = 'role';
     $title = trans('messages.add_form_title',['form' => trans('messages.role')  ]);
      
    return view('permission.add_permission',compact(['title','type']));
    }
}
