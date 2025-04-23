<?php
namespace App\Http\Services\Dashboard\PublicServices\ChangeStatus\Controller;

use App\Models\Service;
use App\Models\SubService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\Dashboard\PublicServices\ChangeStatus\Logic\ChangeStatusInput;
use App\Http\Services\Dashboard\PublicServices\ChangeStatus\Logic\ChangeStatusLogic;

class ChangeStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        // if (demoUserPermission()) {
        //     $message = __('messages.demo_permission_denied');
        //     $response = [
        //         'status'    => false,
        //         'message'   => $message
        //     ];

       // return $request->all();
       dd($request->all());
        print_r($request->all());
        //     return comman_custom_response($response);
        // }
        $type = $request->type;
        $message_form = __('messages.item');
        $message = trans('messages.update_form', ['form' => trans('messages.status')]);
        switch ($type) {
            case 'role':
                $role = \App\Models\Role::find($request->id);
                $role->status = $request->status;
                $role->save();
                break;
                
            case 'service_status':
                $category = Service::find($request->id);
                $category->status = $request->status;
                $category->save();
                break;
            // case 'category_featured':
            //     $message_form = __('messages.category');
            //     $category = \App\Models\Category::find($request->id);
            //     $category->is_featured = $request->status;
            //     $category->save();
            //     break;
            // case 'service_status':
            //     $service = \App\Models\Service::find($request->id);
            //     $service->status = $request->status;
            //     $service->save();
            //     break;
            // case 'coupon_status':
            //     $coupon = \App\Models\Coupon::find($request->id);
            //     $coupon->status = $request->status;
            //     $coupon->save();
            //     break;
            // case 'document_status':
            //     $document = \App\Models\Documents::find($request->id);
            //     $document->status = $request->status;
            //     $document->save();
            //     break;
            // case 'document_required':
            //     $message_form = __('messages.document');
            //     $document = \App\Models\Documents::find($request->id);
            //     $document->is_required = $request->status;
            //     $document->save();
            //     break;
            // case 'provider_is_verified':
            //     $message_form = __('messages.providerdocument');
            //     $document = \App\Models\ProviderDocument::find($request->id);
            //     $document->is_verified = $request->status;
            //     $document->save();
            //     break;
            // case 'tax_status':
            //     $tax = \App\Models\Tax::find($request->id);
            //     $tax->status = $request->status;
            //     $tax->save();
            //     break;
            // case 'provideraddress_status':
            //     $provideraddress = \App\Models\ProviderAddressMapping::find($request->id);
            //     $provideraddress->status = $request->status;
            //     $provideraddress->save();
            //     break;
            // case 'slider_status':
            //     $slider = \App\Models\Slider::find($request->id);
            //     $slider->status = $request->status;
            //     $slider->save();
            //     break;
            // case 'servicefaq_status':
            //     $servicefaq = \App\Models\ServiceFaq::find($request->id);
            //     $servicefaq->status = $request->status;
            //     $servicefaq->save();
            //     break;
            // case 'wallet_status':
            //     $wallet = \App\Models\Wallet::find($request->id);
            //     $wallet->status = $request->status;
            //     $wallet->save();
            //     break;
            case 'subservice_status':
                $subcategory = SubService::find($request->id);
                $subcategory->status = $request->status;
                $subcategory->save();
                break;
            // case 'subcategory_featured':
            //     $message_form = __('messages.subcategory');
            //     $subcategory = \App\Models\SubCategory::find($request->id);
            //     $subcategory->is_featured = $request->status;
            //     $subcategory->save();
            //     break;
            // case 'plan_status':
            //     $plans = \App\Models\Plans::find($request->id);
            //     $plans->status = $request->status;
            //     $plans->save();
            //     break;
            // case 'bank_status':
            //     $banks = \App\Models\Bank::find($request->id);
            //     $banks->status = $request->status;
            //     $banks->save();
            //     break;
            // case 'blog_status':
            //     $blog = \App\Models\Blog::find($request->id);
            //     $blog->status = $request->status;
            //     $blog->save();
            //     break;
            // case 'servicepackage_status':
            //     $servicepackage = \App\Models\ServicePackage::find($request->id);
            //     $servicepackage->status = $request->status;
            //     $servicepackage->save();
            //     break;
            // case 'notificationtemplate_status':
            //     $notificationtemplate = \App\Models\NotificationTemplate::find($request->id);
            //     $notificationtemplate->status = $request->status;
            //     $notificationtemplate->save();
            // case 'serviceaddon_status':
            //     $serviceaddon = \App\Models\ServiceAddon::find($request->id);
            //     $serviceaddon->status = $request->status;
            //     $serviceaddon->save();
            //     break;
            // case 'user_verify_email':
            //     $user = \App\Models\User::find($request->id);
            //     $user->is_email_verified = $request->status;
            //     $user->save();
            //     break;
            // case 'user_service_status':
            //     $userService = \App\Models\Service::find($request->id);
            //     $userService->status = $request->status;
            //     $userService->save();
            //     break;
            // case 'handyman_type_status':
            //     $handyman_type_status = \App\Models\HandymanType::find($request->id);
            //     $handyman_type_status->status = $request->status;
            //     $handyman_type_status->save();
            //     break;
            // case 'providertype_status':
            //     $providertype_status = \App\Models\ProviderType::find($request->id);
            //     $providertype_status->status = $request->status;
            //     $providertype_status->save();
            //     break;

            default:
                $message = 'error';
                break;
        }
        // if ($request->has('is_email_verified') && $request->is_email_verified == 'is_email_verified') {
        //     $message =  __('messages.user_verified', ['form' => $message_form]);
        //     if ($request->status == 0) {
        //         $message = __('messages.remove_form', ['form' => $message_form]);
        //     }
        // }
        // if ($request->has('is_featured') && $request->is_featured == 'is_featured') {
        //     $message =  __('messages.added_form', ['form' => $message_form]);
        //     if ($request->status == 0) {
        //         $message = __('messages.remove_form', ['form' => $message_form]);
        //     }
        // }
        // if ($request->has('is_required') && $request->is_required == 'is_required') {
        //     $message =  __('messages.added_form', ['form' => $message_form]);
        //     if ($request->status == 0) {
        //         $message = __('messages.remove_form', ['form' => $message_form]);
        //     }
        // }
        // if ($request->has('provider_is_verified') && $request->provider_is_verified == 'provider_is_verified') {
        //     $message =  __('messages.is_verify', ['form' => $message_form]);
        //     if ($request->status == 0) {
        //         $message = __('messages.remove_form_verify', ['form' => $message_form]);
        //     }
        // }
        return comman_custom_response(['message' => $message, 'status' => true]);
    }
}