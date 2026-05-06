<?php

namespace App\Services;


use App\Library\Logger;
use yedincisenol\Parasut\Client;
use yedincisenol\Parasut\Exceptions\ParasutException;
use yedincisenol\Parasut\Models\Contact;
use yedincisenol\Parasut\Models\EArchive;
use yedincisenol\Parasut\Models\EInvoice;
use yedincisenol\Parasut\Models\EInvoiceInbox;
use yedincisenol\Parasut\Models\Product;
use yedincisenol\Parasut\Models\SaleInvoice;
use yedincisenol\Parasut\Models\Trackable;
use yedincisenol\Parasut\RequestModel;

class ParasutService
{
    protected $client;

    protected $logged_in = false;

    public function __construct()
    {
        $clientID = config('parasut.connection.client_id');
        $clientSecret = config('parasut.connection.client_secret');
        $callbackUrl = config('parasut.connection.redirect_uri', 'urn:ietf:wg:oauth:2.0:oob');
        $companyID = config('parasut.connection.company_id');
        $email = config('parasut.connection.username');
        $password = config('parasut.connection.password');
        $isStage = (bool) config('parasut.connection.is_stage', false);

        if (!$clientID || !$clientSecret || !$companyID || !$email || !$password) {
            Logger::error('PARASUT_CONFIG_MISSING');
            return;
        }

        $this->client = new Client($clientID, $clientSecret, $callbackUrl, $email, $password, $companyID, $isStage);
        try {
            $this->client->login();
            $this->logged_in = true;
        } catch (\Exception $exception) {
            Logger::error('PARASUT_LOGIN_ERROR', ['message' => $exception->getMessage()]);
        }
    }

    public function createCustomer($data = [])
    {
        if (!$this->logged_in) return false;

        $contacts = new Contact($this->client);

        $contact_data = [
            "name"         => $data['name'] ?? '', //*zorunlu //ad soyad
            "email"        => $data['email'] ?? '', //e-posta
            "contact_type" => isset($data['type']) && $data['type'] == 'CORPORATE' ? 'company' : 'person', //company, person (tüzel kişi, gerçek kişi)
            "tax_office"   => $data['taxOffice'] ?? '', //vergi dairesi
            "tax_number"   => isset($data['taxNumber']) && $data['taxNumber'] ? $data['taxNumber'] : '11111111111', //vergi numarası
            "district"     => $data['district'] ?? '', //ilçe
            "city"         => $data['city'] ?? '', //il
            "address"      => $data['address'] ?? '', //adres
            "phone"        => $data['phone'] ?? '', //tel no
            "account_type" => "customer" //customer, supplier
        ];

        $request = new RequestModel('', 'contacts', $contact_data);

        $submit = $contacts->create($request);
        if ($submit) {
            $data = $submit->getData();
            return $data['id'];
        }
        return false;

        if (!$submit) return false;

        if ($submit->code == '201' || $submit->code == '200') {
            $submit = $this->resultToArray($submit);
            $submit = $submit['result'] ?? [];

            $this->tempData['customers'][$customer['name']] = $submit['data']['id'];
            return $submit["data"]["id"] ?? false;
        }
        if ($submit->code == '429') {
            sleep(10);
            return $this->createCustomer($params);
        }

        return false;


    }

    public function editCustomer($data = [], $contact_id = null)
    {
        if (!$this->logged_in) return false;
        $contacts = new Contact($this->client);

        $contact_data = [
            "name"         => $data['name'] ?? '', //*zorunlu //ad soyad
            "email"        => $data['email'] ?? '', //e-posta
            "contact_type" => isset($data['type']) && $data['type'] == 'CORPORATE' ? 'company' : 'person', //company, person (tüzel kişi, gerçek kişi)
            "tax_office"   => $data['taxOffice'] ?? '', //vergi dairesi
            "tax_number"   => isset($data['taxNumber']) && $data['taxNumber'] ? $data['taxNumber'] : '11111111111', //vergi numarası
            "district"     => $data['district'] ?? '', //ilçe
            "city"         => $data['city'] ?? '', //il
            "address"      => $data['address'] ?? '', //adres
            "phone"        => $data['phone'] ?? '', //tel no
            "account_type" => "customer" //customer, supplier
        ];

        $request = new RequestModel($contact_id, 'contacts', $contact_data);

        $submit = $contacts->update($request);
        if ($submit) {
            $data = $submit->getData();
            return $data['id'];
        }
        return false;

    }

    public function createProduct($data = [])
    {
        if (!$this->logged_in) return false;

        $products = new Product($this->client);

        $product_data = [
            "name"                => $data['name'],
            "vat_rate"            => $data['vat_percent'],
            "unit"                => $data['unit'] ?? 'Adet',
            "currency"            => 'TRL',
            "inventory_tracking"  => false, //stok durumu
            "initial_stock_count" => 0 //stok adedi
        ];

        $request = new RequestModel('', 'products', $product_data);

        $submit = $products->create($request);
        if ($submit) {
            $data = $submit->getData();
            return $data['id'];
        }
        return false;
    }

    public function editProduct($data = [], $parasut_id = null)
    {
        if (!$this->logged_in) return false;

        $products = new Product($this->client);

        $product_data = [
            "name"                => $data['name'],
            "vat_rate"            => $data['vat_percent'],
            "unit"                => $data['unit'] ?? 'Adet',
            "currency"            => 'TRL',
            "inventory_tracking"  => false, //stok durumu
            "initial_stock_count" => 0 //stok adedi
        ];

        $request = new RequestModel($parasut_id, 'products', $product_data);

        $submit = $products->update($request);
        if ($submit) {
            $data = $submit->getData();
            return $data['id'];
        }
        return false;
    }

    public function createInvoice($data = [])
    {
        if (!$this->logged_in) return false;

        $invoice = new SaleInvoice($this->client);

        $data = [
            "type"          => "sales_invoices",
            "attributes"    => $data['attributes'],
            "relationships" => $data['relationships'] ?? []
        ];



        $request = new RequestModel('', $data['type'], $data['attributes'], $data['relationships']);

        $submit = $invoice->create($request);

        if ($submit) {
            $data = $submit->getData();
            return $data['id'];
        }
        return false;
    }

    public function editInvoice($data = [], $parasut_id = null)
    {
        if (!$this->logged_in) return false;

        $invoice = new SaleInvoice($this->client);

        $data = [
            "type"          => "sales_invoices",
            "attributes"    => $data['attributes'],
            "relationships" => $data['relationships']
        ];


        $request = new RequestModel($parasut_id, $data['type'], $data['attributes'], $data['relationships']);
        $submit = $invoice->update($request);

        if ($submit) {
            $data = $submit->getData();
            return $data['id'];
        }
        return false;
    }

    public function deleteInvoice($parasut_id = null)
    {
        if (!$this->logged_in) return false;

        $invoice = new SaleInvoice($this->client);

        $delete = $invoice->delete($parasut_id);

        if ($delete) {
            return true;
        }
        return false;
    }

    public function showInvoice($invoice_parasut_id)
    {
        $invoice = new SaleInvoice($this->client);
        $data = $invoice->show($invoice_parasut_id,[
            'include' => 'active_e_document'
        ]);

        return $data->getData();
    }

    public function formalizeInvoice($invoice_parasut_id = null)
    {
        if (!$this->logged_in) return ['success' => false,'message' => 'Paraşüt oturum zaman aşımı.'];


        try {
            $invoice = new SaleInvoice($this->client);

            $data = $invoice->show($invoice_parasut_id);

            $tax_number = $data->getData()['attributes']['tax_number'];

            $invoice_type = $this->checkFormalInvoiceType($tax_number);

            if ($invoice_type['type'] == 'e_archive') {

                $earchive = new EArchive($this->client);

                $e_archive_data = [
                    'attributes'    => [
                        'note'                      => '',
                        'vat_exemption_reason_code' => ''
                    ],
                    "relationships" => [
                        "sales_invoice" => [
                            "data" => [
                                "id"   => $invoice_parasut_id, //invoice_id
                                "type" => "sales_invoices"
                            ]
                        ]
                    ]

                ];

                $request = new RequestModel('', 'e_archives', $e_archive_data['attributes'], $e_archive_data['relationships']);
                $create = $earchive->create($request);

                if ($create && $create->getData()) {
                    sleep(3);
                    $data = $create->getData();
                    $trackable_job_id = $data['id'];
                    $a = new Trackable($this->client);

                    $track = $a->show($trackable_job_id);

                    if ($track->getData()) {
                        $data = $track->getData();
                        if ($data['attributes']['status'] == 'done') {
                            return [
                                'success' => true
                            ];
                        } else if ($data['attributes']['status'] == 'pending') {
                            return [
                                'success' => false,
                                'status'  => 'pending',
                                'id'      => $trackable_job_id
                            ];
                        } else if ($data['attributes']['status'] == 'error') {
                            return [
                                'success' => false,
                                'status'  => 'error',
                                'error'   => json_encode($data['attributes']['errors']),
                                'id'      => $trackable_job_id
                            ];
                        } else {
                            return [
                                'success' => false,
                                'status'  => 'error',
                                'error'   => '#100003',
                                'id'      => null
                            ];
                        }
                    }
                }
                $data = $create->getData();

                $e_document_info = [
                    'type' => 'e_archives',
                    'id'   => $data->id
                ];

                return [
                    'success' => true,
                    'data'    => $e_document_info
                ];

            } else {

                $einvoice = new EInvoice($this->client);

                $e_invoice_data = [
                    'attributes'    => [
                        'note'                      => '',
                        'vat_exemption_reason_code' => config('parasut.vat_exemption_code', '335'),
                        'scenario'                  => 'basic',
                        'to'                        => $invoice_type['address']
                    ],
                    "relationships" => [
                        "invoice" => [
                            "data" => [
                                "id"   => $invoice_parasut_id, //invoice_id
                                "type" => "sales_invoices"
                            ]
                        ]
                    ]

                ];

                $request = new RequestModel('', 'e_invoices', $e_invoice_data['attributes'], $e_invoice_data['relationships']);

//            dd($request->toArray());

                $create = $einvoice->create($request);
                if ($create) {
                    sleep(8);
                    $data = $create->getData();
                    $trackable_job_id = $data['id'];
                    $a = new Trackable($this->client);

                    $track = $a->show($trackable_job_id);

                    if ($track->getData()) {
                        $data = $track->getData();
                        if ($data['attributes']['status'] == 'done') {
                            return [
                                'success' => true
                            ];
                        } else if ($data['attributes']['status'] == 'pending') {
                            return [
                                'success' => false,
                                'status'  => 'pending',
                                'id'      => $trackable_job_id
                            ];
                        } else if ($data['attributes']['status'] == 'error') {
                            return [
                                'success' => false,
                                'status'  => 'error',
                                'error'   => json_encode($data['attributes']['errors']),
                                'id'      => $trackable_job_id
                            ];
                        } else {
                            return [
                                'success' => false,
                                'status'  => 'error',
                                'error'   => '#100003',
                                'id'      => null
                            ];
                        }
                    }
                }
                $data = $create->getData();

                $e_document_info = [
                    'type' => 'e_archives',
                    'id'   => $data->id
                ];

                return [
                    'success' => true,
                    'data'    => $e_document_info
                ];


            }
        }catch (ParasutException $parasutException){
            return [
                'success' => false,
                'message' => 'ApiError: '.$parasutException->getMessage()
            ];
        }
    }

    private function checkFormalInvoiceType($tax_number)
    {
        $einvoice_inboxes = new EInvoiceInbox($this->client);
        $result = $einvoice_inboxes->all(['filter' => ['vkn' => $tax_number]]);

        $data = $result->getData();

        if ($data && $data[0]['type'] == 'e_invoice_inboxes') {
            $data = $data[0];
            $type = 'e_invoices';

            $e_invoice_address = $data['attributes']['e_invoice_address'];
            return [
                'type'    => 'e_invoice',
                'address' => $e_invoice_address
            ];
        }

        return [
            'type' => 'e_archive'
        ];

    }

    public function getEDocumentPdf()
    {
        if (!$this->logged_in) return false;
    }

    public function getPdfUrl($invoice_number)
    {
        if (!$this->logged_in) return false;

        try {
            $invoice = new SaleInvoice($this->client);

            $invoice = $invoice->show($invoice_number, [
                'include' => 'active_e_document'
            ])->getData();

            if (!$invoice) return false;
            $e_document = $invoice['relationships']['active_e_document'];
            if (!$e_document || !isset($e_document['data'])) return false;

            $id = $e_document['data']['id'];
            $type = $e_document['data']['type'];

            if ($type == 'e_archives') {
                return $this->getEArchivePdfUrl($id);
            }
            return $this->getEInvoicePdfUrl($id);
        } catch (\Exception $e) {
            return false;
        }

    }

    private function getEArchivePdfUrl($active_document_id)
    {
        $earchive = new EArchive($this->client);
        $response = $earchive->pdf($active_document_id)->getData();

        if (!$response || !isset($response['attributes']['url'])) return false;

        return $response['attributes']['url'];

    }

    private function getEInvoicePdfUrl($active_document_id)
    {
        $einvoice = new EInvoice($this->client);
        $response = $einvoice->pdf($active_document_id)->getData();

        if (!$response || !isset($response['attributes']['url'])) return false;

        $url = $response['attributes']['url'];

        return $url;
    }

    public function payInvoice($invoice_parasut_id, $amount)
    {
        if (!$this->logged_in) return false;

        $invoice = new SaleInvoice($this->client);


        $payInvoiceData = [
            "type"       => "payments",
            "attributes" => [
                "description" => "Netpus tarafından otomatik oluşturuldu.",
                "account_id"  => config('parasut.account_id', 1000230432),
                "date"        => date('Y-m-d'),
                "amount"      => $amount
            ]
        ];

        $request = new RequestModel($invoice_parasut_id, 'payments', $payInvoiceData['attributes']);
        $result = $invoice->payment($request);

        if ($result->getData()){
            return true;
        }
        return false;
    }
}
