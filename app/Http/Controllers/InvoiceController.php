<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function create()
    {
        return view('ohaus.invoice.create');
    }


    public function generate(Request $request)
    {


        $pdf = PDF::loadView('ohaus.invoice.ikap.ika')
        ->setPaper('a4', 'portrait')
            // ->setOption('defaultFont', 'Questrial')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('chroot', public_path());
        return $pdf->download('invoice-'.'invoiceNumber6' . '.pdf');


        $validated = $request->validate([
            // 'company_name' => 'required|string|max:255',
            'invoice_number' => 'required|string|max:255',
        ]);

        $items = [];
        if ($request->has('item_description')) {
            foreach ($request->item_description as $index => $description) {
                if (!empty($description)) {
                    $items[] = [
                        'description' => $description,
                        'productId' => $request->item_product_id[$index] ?? '',
                        'quantity' => $request->item_quantity[$index] ?? 0,
                        'unit' => $request->item_unit[$index] ?? '',
                        'pricePerUnit' => $request->item_price[$index] ?? 0,
                        'discount' => $request->item_discount[$index] ?? '0%',
                        'total' => ($request->item_quantity[$index] ?? 0) * ($request->item_price[$index] ?? 0),
                        'notes' => array_filter(explode("\n", $request->item_notes[$index] ?? ''))
                    ];
                }
            }
        }

    $data = [
            'companyName' => 'OHAUS CORPORATION',
            'companyAddress' => 'P.O. Box 5667, Parsippany, NJ 07054',
            'companyPhone' => '(973) 377-9000 / (800) 672-7722',
            'companyFax' => '(973) 944-7177',
            'companyWebsite' => "www.ohaus.com",
            'invoiceNumber' => $request->invoice_number,
            'invoiceDate' => $request->invoice_date,
            'orderNumber' => $request->order_number,
            'collectAccount' => $request->collect_account,
            'paymentTerms' => $request->payment_terms,
            'customerNumber' => $request->customer_number,
            'customerPage' => 'Customer Page ' . $request->customer_number,
            'currentPage' => 1,
            'totalPages' => 1,
            'billTo' => [
                'name' => $request->bill_to_name,
                'address' => $request->bill_to_address,
                'cityStateZip' => $request->bill_to_city
            ],
            'soldTo' =>[
                'name' => $request->sold_to_name,
                'address' => $request->sold_to_address,
                'cityStateZip' => $request->sold_to_city
            ],
            'shipTo' => [
                'name' => $request->ship_to_name,
                'company' => $request->ship_to_company,
                'address' => $request->ship_to_address,
                'cityStateZip' => $request->ship_to_city
            ],
            'remitTo' => [
                'name' => $request->remit_to_name,
                'address' => $request->remit_to_address,
                'cityStateZip' => $request->remit_to_city,
                'note' => $request->remit_to_note
            ],
            'customerContact' => [
                'name' => $request->contact_name,
                'telephone' => $request->contact_phone,
                'email' => $request->contact_email
            ],
            'purchaseInfo' => [
                'purchaseOrder' => $request->purchase_order,
                'deliveryNote'  => $request->delivery_note,
                'orderDate'     => $request->order_date,
                'shipDate'      => $request->ship_date,
                'shipmentNumber' => $request->shipment_number
            ],
            'items' => $items,
            'subTotal' => array_sum(array_column($items, 'total')),
            'invoiceTotal' => array_sum(array_column($items, 'total')),
            'bankInfo' => [
                'bankName' => $request->bank_name,
                'routingNumber' => $request->routing_number,
                'accountNumber' => $request->account_number,
                'swiftCode' => $request->swift_code
            ]
        ];

        // return $data;

        return view('ohaus.invoice.preview', $data);
        $pdf = PDF::loadView('ohaus.invoice.preview', $data)  ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'Questrial')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('chroot', public_path());
        return $pdf->download('invoice-' . $data['invoiceNumber'] . '.pdf');


        //  $pdf = PDF::loadView('invoice.preview', $data)
        //     ->setPaper('a4', 'portrait')
        //     ->setOption('defaultFont', 'Questrial')
        //     ->setOption('isHtml5ParserEnabled', true)
        //     ->setOption('isRemoteEnabled', true)
        //     ->setOption('chroot', public_path());
        // return view('ohaus.invoice.preview', $data);

    }

    public function exportPDF(Request $request)
    {
        $data = $request->all();
        $pdf = PDF::loadView('ohaus.invoice.preview', $data);
        return $pdf->download('invoice-' . $data['invoiceNumber'] . '.pdf');
    }
}
