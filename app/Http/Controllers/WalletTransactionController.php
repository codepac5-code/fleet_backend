<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Http\Request;

class WalletTransactionController extends Controller
{
    public function index()
    {
        $pageTitle = 'معاملات المحفظة';
        return view('walletTransactions.index', compact('pageTitle'));
    }

    public function data(Request $request)
    {

            $query = WalletTransaction::query()
            ->with(['fromUser', 'toUser'])
            ->select('wallet_transactions.*');

        if (!empty($request->filter['date_from']) && !empty($request->filter['date_to'])) {
            $from = $request->filter['date_from'] . ' 00:00:00';
            $to = $request->filter['date_to'] . ' 23:59:59';
            $query->whereBetween('created_at', [$from, $to]);
        } elseif (!empty($request->filter['date_from'])) {
            $query->whereDate('created_at', '>=', $request->filter['date_from']);
        } elseif (!empty($request->filter['date_to'])) {
            $query->whereDate('created_at', '<=', $request->filter['date_to']);
        }

        if (!empty($request->filter['column_status'])) {
            $query->where('status', $request->filter['column_status']);
        }

        
            if ($request->filter['from_type'] ?? false) {
                $query->where('from_type', $request->filter['from_type']);
            }
        
            if ($request->filter['to_type'] ?? false) {
                $query->where('to_type', $request->filter['to_type']);
            }
        
            if ($request->filter['column_status'] ?? false) {
                $query->where('status', $request->filter['column_status']);
            }
        
            // if ($request->search['value'] ?? false) {
            //     $search = $request->search['value'];
            //     $query->where(function ($q) use ($search) {
            //         $q->where('id', 'like', "%$search%")
            //           ->orWhereHas('from', fn($q) => $q->where('firstName', 'like', "%$search%"))
            //           ->orWhereHas('from', fn($q) => $q->where('officeName', 'like', "%$search%"))
            //           ->orWhereHas('to', fn($q) => $q->where('firstName', 'like', "%$search%"))
            //           ->orWhereHas('from', fn($q) => $q->where('officeName', 'like', "%$search%"))
            //           ->orWhere('amount', 'like', "%$search%")
            //           ->orWhere('paymentName', 'like', "%$search%");
            //     });
            // }
        
            return DataTables::of($query)
                ->addColumn('check', fn($row) => '<input type="checkbox" class="row-checkbox" value="'.$row->id.'">')
                ->addColumn('bookingId', fn($row) => $row->id) 
                ->addColumn('from_name', function( $row ){
                    switch ($row->from_type) 
                    {
                        case 'App\Models\Driver' : 
                                $driver = $row->fromUser;
                                return view('driver.driver',compact('driver'));
                            break;
                        case 'App\Models\Office' : 
                            $query = $row->fromUser;
                            return view('office.office',compact('query'));
                        break;
                        case 'App\Models\User' :  $user = $row->fromUser;
                            return view('customer.user',compact('user'));
                        break;
                        case 'App\Models\FleetOffice' : return app()->getLocale() == 'en' ? 'Fleet Company' : 'شركة فليت';
                         break;
                        default :return 'unknow';
                    }
                })
                ->addColumn('to_name', function( $row ){
                    switch ($row->to_type) 
                    {
                        case 'App\Models\Driver' : 
                                $driver = $row->toUser;
                                return view('driver.driver',compact('driver'));
                            break;
                        case 'App\Models\Office' : 
                            $query = $row->toUser;
                            return view('office.office',compact('query'));
                        break;
                        case 'App\Models\User' :  $user = $row->toUser;
                            return view('customer.user',compact('user'));
                        break;
                        case 'App\Models\FleetOffice' : return app()->getLocale() == 'en' ? 'Fleet Company' : 'شركة فليت';
                         break;
                        default :return 'unknow';
                    }
                })
                ->addColumn('amount', fn($row) => number_format($row->amount, 2))
                // ->addColumn('payment_status', fn($row) => $row->status)
                ->addColumn('discount', fn($row) => $row->discount ?? 0)
                ->addColumn('commission', fn($row) => $row->commission ?? 0)
                ->addColumn('Payment_datetime', fn($row) => $row->created_at->format('Y-m-d H:i'))
                ->addColumn('action', function($row) {
                    $showUrl = route('wallet-transactions.show', $row->id);
                    return '<a href="'.$showUrl.'" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> '.__('messages.view').'
                            </a>';
                })
                ->addColumn('description', function($row) {
                    return app()->getLocale() == 'en' ? $row->description_en : $row->description;
                })
                ->rawColumns(['check', 'action','description'])
                ->make(true);
        
    }

    public function show($id)
    {
        $transaction = WalletTransaction::findOrFail($id);
        return view('walletTransactions.show', compact('transaction'));
    }
}
