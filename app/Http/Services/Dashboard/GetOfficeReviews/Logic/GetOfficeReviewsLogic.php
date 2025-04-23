<?php
namespace App\Http\Services\Dashboard\GetOfficeReviews\Logic;
use App\Http\Repositories\RepositoryCaller;
use App\Http\Core\InternalInterface\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Core\Response\Adapter\PresentersModels\ResponseModel;
use Yajra\DataTables\DataTables;

class GetOfficeReviewsLogic implements Service {

    private RepositoryCaller $repository ; // access to all model's repositories

    public function __construct(
    //---------------------------------------------------------------------------------------
    private GetOfficeReviewsInput $input,  /*| Pass Request To Service */
    //---------------------------------------------------------------------------------------
    ){
        $this->repository = new RepositoryCaller(); // init repository object
    }


    public function execute (): ResponseModel | JsonResponse | View | RedirectResponse {

        $auth_user = authSession();
        $office = $this->repository->OfficeRepository()->readRepository()
        ->getFirstByConditions([ 'id'=> $this->input->getOfficeId() ]);


        // if ($request->ajax()) {

        //     $ratings = $office->ratings;

        //     return DataTables::of($ratings)
        //         ->addIndexColumn()
        //         ->editColumn('date', function ($row) {
        //             if (is_array($row)) {
        //                 $row = (object)$row;
        //             }
        //         $startAt = isset($row->create_at) ? $row->create_at : null;
        //             if ($startAt !== null) {
        //                 $sitesetup = $this->repository->SettingsRepository()->readRepository()
        //                 ->getFirstByConditions(['type'=> 'site-setup' ,'key'=> 'site-setup']); 
        //                 $datetime = $sitesetup ? json_decode($sitesetup->value) : null;
                        
        //                 $date = optional($datetime)->date_format && optional($datetime)->time_format
        //                 ? date(optional($datetime)->date_format, strtotime($startAt)) . ' / ' . date(optional($datetime)->time_format, strtotime($startAt))
        //                 : $startAt;
        //                 return $date;
        //             }
        //             return null;
        //         })
        //         ->rawColumns(['action'])
        //         ->make(true);
        // }

        if (empty($office)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.office')]);
            return redirect(route('office.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title' , ['form' => __('messages.office')]);
        $totalVotes = ($office->ratingExcellent + $office->ratingGood + $office->ratingAverage + $office->ratingBelowAverage + $office->ratingPoor );
        return view('office.review', compact('pageTitle', 'auth_user', 'office','totalVotes'));
   }
}

