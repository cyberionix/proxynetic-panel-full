<?php

namespace App\Library;

use App\Models\Book;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use App\Services\ParasutService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class EInvoiceManager
{
    protected $service;

    public function __construct(ParasutService $parasutService)
    {
        $this->service = $parasutService;
    }

    public function createCustomer(User $user)
    {
        $address = $user->invoiceAddress;

        $data = [
            'name'         => $user->full_name,
            'email'        => $user->email,
            'contact_type' => $address?->invoice_type ?? 'INDIVIDUAL',
            'tax_office'   => $address?->tax_office,
            'tax_number'   => $address?->tax_number,
            'city'         => $address?->city?->title,
            'district'     => $address?->city?->district?->title,
            'country'      => 'Türkiye',
            'address'      => $address?->address,
            'phone'        => ltrim($user?->phone, '9')
        ];

        $id = $this->service->createCustomer($data);

        if ($id) {
            $user->parasut_id = $id;
            $user->save();
            return $id;
        }
        return false;
    }

    public function editCustomer(User $user)
    {

        $address = $user->invoiceAddress;

        $data = [
            'name'         => $user->full_name,
            'email'        => $user->email,
            'contact_type' => @$address?->invoice_type ?? 'INDIVIDUAL',
            'tax_office'   => @$address?->tax_office,
            'tax_number'   => @$address?->tax_number,
            'city'         => @$address?->city?->title,
            'district'     => @$address?->city?->district?->title,
            'country'      => 'Türkiye',
            'address'      => $address?->address,
            'phone'        => ltrim($user?->phone, '9')
        ];
        if (!$user->parasut_id) {
            return $this->service->createCustomer($data);
        }
        $id = $this->service->editCustomer($data, $user->parasut_id);

        if ($id) {
            $user->parasut_id = $id;
            $user->save();
            return $id;
        }
        return false;
    }

    public function createProduct(Product|Book $product)
    {
        $data = [
            "name"                => $product['name'],
            "vat_percent"         => $product['vat_percent'],
            "unit"                => $product['unit'] ?? 'Adet',
            "currency"            => 'TRL',
            "inventory_tracking"  => false, //stok durumu
            "initial_stock_count" => 0 //stok adedi
        ];

        if (!$product->parasut_id) {
            return $this->service->createProduct($data);
        }
        $id = $this->service->createProduct($data);

        if ($id) {
            $product->parasut_id = $id;
            $product->save();
            return $id;
        }
        return false;
    }

    public function editProduct(Product|Book $product)
    {
        $data = [
            "name"                => $product['name'],
            "vat_percent"         => $product['vat_percent'],
            "unit"                => $product['unit'] ?? 'Adet',
            "currency"            => 'TRL',
            "inventory_tracking"  => false, //stok durumu
            "initial_stock_count" => 0 //stok adedi
        ];

        $id = $this->service->editProduct($data, $product->parasut_id);

        if ($id) {
            $product->parasut_id = $id;
            $product->save();
            return $id;
        }
        return false;
    }

    public function createInvoice(Invoice $invoice)
    {

        $user = $invoice->user;
        if (!$user->parasut_id) {
            $user->parasut_id = $this->createCustomer($user);
            $user->save();
        }
        $address = $invoice->invoice_address;
        if (is_array($address)) {
            $address = json_decode(json_encode($address));
        }
        if (!$address) return false;
        $items = $invoice->items();

        $issue_date = $invoice->invoice_date;


        $invoice_items = $items->get()->map(function ($item) {
            if ($item->book_id && $item->book) {
                $parasut_id = $item?->book?->parasut_id;
                if (!$parasut_id) {
                    $item->book->parasut_id = $this->createProduct($item->book);
                    $item->book->save();
                    $parasut_id = $item?->book?->parasut_id;

                }
            } elseif ($item->product_id && $item->product) {
                $parasut_id = $item?->product?->parasut_id;
                if (!$parasut_id) {
                    $item->product->parasut_id = $this->createProduct($item->product);
                    $item->product->save();
                    $parasut_id = $item?->product?->parasut_id;
                }
            } else {
                return [];
            }
            return [
                "type"          => "sales_invoice_details",
                "attributes"    => [
                    "quantity"    => 1,
                    "unit_price"  => $item['total_price'],
                    "vat_rate"    => $item['vat_percent'],
                    "description" => ""
                ],
                "relationships" => [
                    "product" => [
                        'data' => [
                            'type' => 'products',
                            'id'   => $parasut_id
                        ]
                    ]
                ]
            ];
        })->toArray();


        $data = [
            "attributes"    => [
                "item_type"         => "invoice",
                "description"       => 'AIBC - #' . rand(100, 999),
                "issue_date"        => $issue_date,
                "due_date"          => $issue_date,
                "invoice_series"    => 'AIBC',
                "invoice_id"        => $invoice->invoice_number ?: date('Y') . rand(100000, 999999),
                "currency"          => 'TRL',
                "shipment_included" => false,
                'billing_phone'     => $user->phone,
                'billing_address'   => $address->address,
                'tax_office'        => $address->tax_office,
                'tax_number'        => $address->tax_number,
                'country'           => 'Türkiye',
                'city'              => $address?->city ? $address?->city?->title : '',
                'district'          => $address?->district ? $address?->district?->title : '',
            ],
            "relationships" => [
                "details" => [
                    "data" => $invoice_items
                ],
                "contact" => [
                    "data" => [
                        "type" => "contacts",
                        "id"   => $user?->parasut_id,
                    ]
                ]
            ]
        ];
//dd($data);

        if ($invoice->discount_amount > 0){

            $total_price_without_vat = $invoice->total_price_with_vat / (1 + (20/100));
            $discount_without_vat = $invoice->discount_amount / (1 + (20/100));



            $arr = [
                'invoice_discount' => $discount_without_vat,
                'invoice_discount_type' => 'amount',
                'net_total' => $total_price_without_vat
            ];

            $data['attributes']=array_merge($data['attributes'],$arr);
        }
        $id = $this->service->createInvoice($data);

        if ($id) {
            $invoice->parasut_id = $id;
            $invoice->save();
            return $id;
        }
        return false;

    }

    public function editInvoice(Invoice $invoice)
    {

        $user = $invoice->user;
        if (!$user->parasut_id) {
            $user->parasut_id = $this->createCustomer($user);
            $user->save();
        }
        $address = $invoice->invoice_address;

        if (is_array($address)) {
            $address = json_decode(json_encode($address));
        }
        if (!$address) return false;

        if (!$invoice->parasut_id) return $this->createInvoice($invoice);


        $items = $invoice->items();
        $showPdf = $this->service->showInvoice($invoice->parasut_id);
        if (isset($showPdf['relationships']['active_e_document']) && isset($showPdf['relationships']['active_e_document']['data']) && $showPdf['relationships']['active_e_document']['data']) {
            return $invoice->parasut_id;
        }

        $issue_date = $invoice->invoice_date;
        if ($issue_date instanceof Carbon) {
            $issue_date = $issue_date->format('Y-m-d');
        }

        $invoice_items = $items->get()->map(function ($item) {
            if ($item->book_id && $item->book) {
                $parasut_id = $item?->book?->parasut_id;
                if (!$parasut_id) {
                    $item->book->parasut_id = $this->createProduct($item->book);
                    $item->book->save();
                }
            } elseif ($item->product_id && $item->product) {
                $parasut_id = $item?->product?->parasut_id;
                if (!$parasut_id) {
                    $item->product->parasut_id = $this->createProduct($item->product);
                    $item->product->save();
                }
            } else {
                return [];
            }

            return [
                "type"          => "sales_invoice_details",
                "attributes"    => [
                    "quantity"    => $item['quantity'],
                    "unit_price"  => $item['unit_price'],
                    "vat_rate"    => $item['vat_percent'],
                    "description" => ""
                ],
                "relationships" => [
                    "product" => [
                        'data' => [
                            'type' => 'products',
                            'id'   => $parasut_id
                        ]
                    ]
                ]
            ];
        })->toArray();


        $data = [
            "attributes"    => [
                "item_type"         => "invoice",
                "description"       => 'AIBC - #' . rand(100, 999),
                "issue_date"        => $issue_date,
                "due_date"          => $issue_date,
                "invoice_series"    => 'AIBC',
                "invoice_id"        => $invoice->invoice_number ?: date('Y') . rand(100000, 999999),
                "currency"          => 'TRL',
                "shipment_included" => false,
                'billing_phone'     => $user->phone,
                'billing_address'   => $address?->address,
                'tax_office'        => $address?->tax_office,
                'tax_number'        => $address?->tax_number,
                'country'           => 'Türkiye',
                'city'              => $address?->city?->title,
                'district'          => $address?->district?->title,
            ],
            "relationships" => [
                "details" => [
                    "data" => $invoice_items
                ],
                "contact" => [
                    "data" => [
                        "type" => "contacts",
                        "id"   => $user?->parasut_id
                    ]
                ]
            ]
        ];


        $id = $this->service->editInvoice($data, $invoice->parasut_id);

        if ($id) {
            $invoice->parasut_id = $id;
            $invoice->save();
            return $id;
        }
    }

    public function deleteInvoice(Invoice $invoice)
    {
        if ($invoice->parasut_id) return $this->service->deleteInvoice($invoice->parasut_id);

        return true;
    }

    public function getInvoices()
    {

    }

    public function formalizeInvoice(Invoice $invoice)
    {
        $address = $invoice->invoice_address;
        $address = json_decode(json_encode($address));


        if (!$address || !$address->city || !$address->district) return throw new \Exception('Geçerli bir fatura adresi bulunamadı.');

        if (!$invoice->parasut_id) {
            $this->createInvoice($invoice);
            return [
                'success' => true,
                'message' => 'Fatura paraşüte aktarılmamıştı. İşlemi tekrar edebilirsiniz.'
            ];
        }
        $pInvoice = $this->service->showInvoice($invoice->parasut_id);


        if (isset($pInvoice['relationships']['active_e_document']) && isset($pInvoice['relationships']['active_e_document']['data']) && $pInvoice['relationships']['active_e_document']['data']) {

            $this->savePdf($invoice);
            return [
                'success' => true,
                'message' => 'Fatura resmileştirme işlemi başarılı sonuçlandı! #100'
            ];
        }
//        dd($pInvoice);
        if ($pInvoice['attributes']['remaining_in_trl'] != 0) {
            $this->service->payInvoice($invoice->parasut_id, $invoice->total_price_with_vat);
        }

        $formalize = $this->service->formalizeInvoice($invoice->parasut_id);
        if ($formalize['success'] === true) {
            $invoice->formalized_at = now();
            $invoice->status = 'FORMALIZED';
            $invoice->save();
//            sleep(2);
            $this->savePdf($invoice);

            return [
                'success' => true,
                'message' => 'Fatura resmileştirme işlemi başarılı sonuçlandı..'
            ];

        } else {
            if ($formalize['status'] == 'pending') {
                $invoice->formalized_at = now();
                $invoice->status = 'FORMALIZED';
                $invoice->save();
                return [
                    'success' => true,
                    'message' => 'Fatura resmileştirme işlemi devam ediyor.'
                ];
            }

            $invoice->status = 'FORMALIZE_ERROR';
            $invoice->formalize_error = $formalize['message'] ?? '-';
            $invoice->save();
            Logger::error('PARASUT_FORMALIZING_ERROR', ['invoice' => $invoice, 'formalize' => $formalize]);
            return [
                'success' => false,
                'message' => 'Fatura resmileştirme aşamasında bir hata meydana geldi. Detaylar loglandı.'
            ];
        }
    }

    public function savePdf(Invoice $invoice)
    {
        if (!$invoice->parasut_id) {
            // TODO: Save the invoice to paraşüt
            return 'TODO#1';
        }
        $url = $this->service->getPdfUrl($invoice->parasut_id);
        if ($url) {
            $file_data = file_get_contents($url);

            $path = 'invoices/' . $invoice->user_id . '/' . $invoice->invoice_number . '.pdf';

            $save = Storage::put($path, $file_data);

            if ($save) {
                $invoice->update(['formalized_at' => now(), 'status' => 'FORMALIZED', 'invoice_pdf' => $path]);
            }
            return true;
        }

        return false;
    }
}
