<?php
namespace App\Http\Services\Dashboard\Settings\ToView;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Core\Response\SendResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use App\Http\Services\Dashboard\ServiceManagement\AddService\Request\AddServiceRequest;
use App\Models\AppSetting;
use App\Models\City;
use App\Models\Country;
use App\Models\Setting;

class ToViewSettingsController extends Controller
{
    public function __invoke(Request $request)
    {
        $auth_user = authSession();

        $pageTitle = __('messages.Settings');
        $page = $request->page;

        if ($page == '') {
            // if ($auth_user->hasAnyRole(['admin', 'demo_admin'])) {
                $page = 'general-setting';
            // } else {
                // $page = 'profile_form';
            // }
        }

        return view('setting.index', compact('page', 'pageTitle', 'auth_user'));
    }


    public function generalSetting(Request $request)
    {
        if (demoUserPermission()) {
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $auth_user = authSession();

        $data = $request->all();
        $page = $request->page;
        $generalsetting = [
            'site_name' => (isset($data['site_name'])) ? $data['site_name'] : null,
            'site_description' => (isset($data['site_description'])) ? $data['site_description'] : null,
            'inquriy_email' => (isset($data['inquriy_email'])) ? $data['inquriy_email'] : null,
            'helpline_number' => (isset($data['helpline_number'])) ? $data['helpline_number'] : null,
            'website' => (isset($data['website'])) ? $data['website'] : null,
            'country' => isset($data['country']) ? $data['country'] : null,
            'city' => isset($data['city']) ? $data['city'] : null,
            'region' => isset($data['region']) ? $data['region'] : null,
            'address' => (isset($data['address'])) ? $data['address'] : null,

        ];

        $res = Setting::updateOrCreate(
            ['id' => $request->id],
            ['type' => 'general-setting', 'key' => 'general-setting', 'value' => json_encode($generalsetting)]
        );
        $resdata = $res ? json_decode($res->value, true) : [];
        $type = 'APP_NAME';
        $env = $request->ENV;
        $env['APP_NAME'] = $resdata['site_name'];
        foreach ($env as $key => $value) {
            envChanges($key, $value);
        }

        if ($res) {
            $message = trans('messages.update_form', ['form' => trans('messages.general_settings')]);
        }

        return redirect()->route('setting.index', ['page' => $page])->withSuccess($message);
    }




    public function themeSetup(Request $request)
    {
        if (demoUserPermission()) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }

        $auth_user = authSession();
        $data = $request->all();
        $page = $request->page;
        $setting = Setting::updateOrCreate(
            ['id' => $request->id],
            ['type' => 'theme-setup', 'key' => 'theme-setup', 'value' => Null]
        );

        storeMediaFile($setting, $request->logo, 'logo');
        storeMediaFile($setting, $request->favicon, 'favicon');
        storeMediaFile($setting, $request->footer_logo, 'footer_logo');
        storeMediaFile($setting, $request->loader, 'loader');
        imageSession('set');
        $message = trans('messages.update_form', ['form' => trans('messages.theme_setup')]);

        return redirect()->route('setting.index', ['page' => $page])->withSuccess($message);
    }


















    public function layoutPage(Request $request)
    {
        $page = $request->page;
        $auth_user = authSession();
        $user_id = $auth_user->id;
        $settings = AppSetting::first();
        $user_data = User::find($user_id);
        $envSettting = $envSettting_value = [];
        if ($auth_user['user_type'] == 'provider') {
            date_default_timezone_set($admin->time_zone ?? 'UTC');

            $current_time = \Carbon\Carbon::now();
            $time = $current_time->toTimeString();

            $current_day = strtolower(date('D'));

            $provider_id = $request->id ?? auth()->user()->id;

            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

            $slotsArray = ['days' => $days];
            $activeDay = 'mon';
            $activeSlots = [];

            // foreach ($days as $value) {
            //     $slot = ProviderSlotMapping::where('provider_id', $provider_id)
            //         ->where('days', $value)
            //         ->orderBy('start_at', 'DESC')
            //         ->selectRaw("SUBSTRING(start_at, 1, 5) as start_at")
            //         ->pluck('start_at')
            //         ->toArray();

            //     $obj = [
            //         "day" => $value,
            //         "slot" => $slot,
            //     ];
            //     $slotsArray[] = $obj;
            //     $activeSlots[$value] = $slot;
            // }
            $pageTitle = __('messages.slot', ['form' => __('messages.slot')]);
        }
        if (count($envSettting) > 0) {
            $envSettting_value = Setting::whereIn('key', array_keys($envSettting))->get();
        }
        if ($settings == null) {
            $settings = new AppSetting;
        } elseif ($user_data == null) {
            $user_data = new User;
        }




        //---------
        switch ($page) {
            case 'general-setting':
                $generalsetting   = Setting::where('type', '=', 'general-setting')->first();

                $countries = Country::pluck('name', 'id');
                $cities = City::pluck('name', 'id');
            
                if (!empty($generalsetting['value'])) {
                    $decodedata = json_decode($generalsetting['value']);
                    $keys = ['site_name', 'site_description', 'inquriy_email', 'helpline_number', 'website', 'country', 'region', 'city', 'address'];
                    foreach ($keys as $key) {
                        $generalsetting[$key] = $decodedata->$key;
                    }
                }
                $data = view('setting.' . $page, compact('page', 'generalsetting','cities','countries'))->render();
                break;


            case 'theme-setup':
                $themesetup   = Setting::where('type', '=', 'theme-setup')->first();
                $data = view('setting.' . $page, compact('page', 'themesetup'))->render();
                break;

            case 'site-setup':
                $site   = Setting::where('type', '=', 'site-setup')->first();
                if (!empty($site['value'])) {
                    $decodedata = json_decode($site['value']);
                    $keys = ['date_format', 'time_format', 'time_zone', 'language_option', 'default_currency', 'currency_position', 'google_map_keys', 'latitude', 'longitude', 'distance_type', 'radious', 'digitafter_decimal_point', 'android_app_links', 'playstore_url', 'provider_playstore_url', 'ios_app_links', 'appstore_url', 'provider_appstore_url', 'site_copyright'];
                    foreach ($keys as $key) {
                        $site[$key] = $decodedata->$key;
                    }
                }
                $data = view('setting.' . $page, compact('page', 'site'))->render();
                break;
            // case 'service-configurations':
            //     $serviceconfig   = Setting::where('type', '=', 'service-configurations')->first();

            //     if (!empty($serviceconfig['value'])) {
            //         $decodedata = json_decode($serviceconfig['value']);
            //         $keys = ['advance_payment', 'slot_service', 'digital_services', 'service_packages', 'service_addons', 'post_services'];
            //         foreach ($keys as $key) {
            //             $serviceconfig[$key] = $decodedata->$key;
            //         }
            //     }
            //     $data = view('setting.' . $page, compact('page', 'serviceconfig'))->render();
            //     break;
            // case 'social-media':
            //     $socialmedia   = Setting::where('type', '=', 'social-media')->first();
            //     if (!empty($socialmedia['value'])) {
            //         $decodedata = json_decode($socialmedia['value']);
            //         $keys = ['facebook_url', 'linkedin_url', 'instagram_url', 'youtube_url', 'twitter_url'];
            //         foreach ($keys as $key) {
            //             $socialmedia[$key] = $decodedata->$key;
            //         }
            //     }
            //     $data = view('setting.' . $page, compact('page', 'socialmedia'))->render();
            //     break;
            // case 'cookie-setup':
            //     $cookiesetup   = Setting::where('type', '=', 'cookie-setup')->first();

            //     if (!empty($cookiesetup['value'])) {
            //         $decodedata = json_decode($cookiesetup['value']);
            //         $keys = ['title', 'description'];
            //         foreach ($keys as $key) {
            //             $cookiesetup[$key] = $decodedata->$key;
            //         }
            //     }
            //     $data = view('setting.' . $page, compact('page', 'cookiesetup'))->render();
            //     break;

            // case 'role-permission-setup':
            //     $tabpage = 'role';
            //     $data = view('setting.' . $page, compact('page', 'tabpage'))->render();
            //     break;
            // case 'time_slot':
            //     $data  = view('setting.' . $page, compact('user_data', 'page', 'slotsArray', 'pageTitle', 'activeDay', 'provider_id', 'activeSlots'))->render();
            //     break;
            // case 'password_form':
            //     $data  = view('setting.' . $page, compact('user_data', 'page'))->render();
            //     break;
            // case 'profile_form':
            //     $why_choose_me = json_decode($user_data->why_choose_me, true);

            //     if ($why_choose_me !== null && is_array($why_choose_me)) {
            //         $user_data['title'] = $why_choose_me['title'] ?? null;
            //         $user_data['about_description'] = $why_choose_me['about_description'] ?? null;
            //         $user_data['reason'] = $why_choose_me['reason'] ?? null;
            //     } else {
            //         $user_data['title'] =  null;
            //         $user_data['about_description'] = null;
            //         $user_data['reason'] =  null;
            //     }

            //     $data  = view('setting.' . $page, compact('user_data', 'page'))->render();
            //     break;
            // case 'mail-setting':
            //     $data  = view('setting.' . $page, compact('page'))->render();
            //     break;

            // case 'payment-setting':
            //     $tabpage = 'cash';
            //     $data  = view('setting.' . $page, compact('tabpage', 'page'))->render();
            //     break;


            // case 'notification-setting':
            //     $query_data = NotificationTemplate::with('defaultNotificationTemplateMap', 'constant')->get();
            //     $data = [];

            //     $notificationKeyChannels = array_keys(config('notification-setting.channels'));

            //     $arr = [];
            //     foreach ($notificationKeyChannels as $key => $value) {
            //         $arr[$value] = 0;
            //     }

            //     foreach ($query_data as $key => $value) {
            //         $data[$key] = [
            //             'id' => $value->id,
            //             'type' => $value->type,
            //             'template' => $value->defaultNotificationTemplateMap->subject,
            //             'is_default' => false,
            //         ];

            //         if (isset($value->channels)) {
            //             $data[$key]['channels'] = $value->channels;
            //         } else {
            //             $data[$key]['channels'] = $arr;
            //         }
            //     }

            //     $notificationChannels = config('notification-setting.channels');

            //     $data = view('setting.' . $page, compact('page', 'data', 'notificationChannels'))->render();
            //     break;
            // case 'other-setting':
            //     $othersetting   = Setting::where('type', '=', 'OTHER_SETTING')->first();

            //     if (!empty($othersetting['value'])) {
            //         $decodedata = json_decode($othersetting['value']);

            //         if (!empty($decodedata->auto_assign_provider)) {
            //             $keys = [
            //                 'social_login', 'google_login', 'apple_login', 'otp_login', 'online_payment', 'blog', 'maintenance_mode',
            //                 'wallet', 'enable_chat_gpt', 'test_without_key', 'chat_gpt_key', 'force_update_user_app', 'user_app_minimum_version', 'user_app_latest_version',
            //                 'force_update_provider_app', 'provider_app_minimum_version', 'provider_app_latest_version', 'force_update_admin_app', 'admin_app_minimum_version', 'admin_app_latest_version',
            //                 'firebase_notification', 'project_id', 'auto_assign_provider','dashboard_type'
            //             ];
            //         } else {
            //             $keys = [
            //                 'social_login', 'google_login', 'apple_login', 'otp_login', 'online_payment', 'blog', 'maintenance_mode',
            //                 'wallet', 'enable_chat_gpt', 'test_without_key', 'chat_gpt_key', 'force_update_user_app', 'user_app_minimum_version', 'user_app_latest_version',
            //                 'force_update_provider_app', 'provider_app_minimum_version', 'provider_app_latest_version', 'force_update_admin_app', 'admin_app_minimum_version', 'admin_app_latest_version',
            //                 'firebase_notification', 'project_id','dashboard_type'
            //             ];
            //         }

            //         foreach ($keys as $key) {
            //             $othersetting[$key] = $decodedata->$key;
            //         }
            //     }
            //     $data = view('setting.' . $page, compact('page', 'othersetting'))->render();
            //     break;
            // case 'notification-templates':

            //     $module_action = 'List';

            //     $filter = [
            //         'status' => request()->status,
            //     ];

            //     $pageTitle = trans('messages.notification_templates');


            //     $data = view('setting.' . $page, compact('page', 'module_action', 'filter', 'pageTitle'))->render();
            //     break;

            // case 'mail-templates':

            //         $module_action = 'List';

            //         $filter = [
            //             'status' => request()->status,
            //         ];

            //         $pageTitle = trans('messages.mail_templates');


            //         $data = view('setting.' . $page, compact('page', 'module_action', 'filter', 'pageTitle'))->render();
            //         break;
            // case 'earning-setting':
            //     $earningsetting   = Setting::where('type', '=', 'earning-setting')->first();

            //     $data  = view('setting.' . $page, compact('earningsetting', 'page'))->render();
            //     break;
            default:
                $data  = view('setting.' . $page, compact('settings', 'page', 'envSettting'))->render();
                break;
        }

        return response()->json($data);
    }
    



}
