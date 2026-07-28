<?php
namespace App\Http\Services\WebSite\ViewFleetLandingPage\Logic;

use App\Http\Core\Const\Options\Settings\PublicSettingsKies;
use App\Http\Core\Const\Options\Settings\SettingsTypes;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;

class ViewFleetLandingPageLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private ViewFleetLandingPageInput $input,  /*| Pass Request To Service*/
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {


        // $services = $this->repository->ServiceRepository()
        // ->readRepository()->getByConditions(['status'=>true]);

        // $payment_methods = $this->repository->PaymentMethodRepository()
        // ->readRepository()->getByConditions(['status'=>true]);

        // ,compact('services' ,'payment_methods')

        try {
            $plans = \App\Models\SubscriptionPlan::query()
                ->where('is_active', true)
                ->orderBy('sort')
                ->get();
        } catch (\Throwable $e) {
            $plans = collect();
        }

        return view('panel.fleet-landing', ['plans' => $plans]);
        // return view('web-site.site'); // original landing (kept as backup)

   }
}
