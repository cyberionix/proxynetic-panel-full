<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BalanceActivity extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function drawAdminAction()
    {
        switch ($this->model) {
            case "admin":
                if ($this->admin) {
                    return "<span class='badge badge-primary' data-bs-toggle='tooltip' title='" . $this->admin->id . " | " . $this->admin->full_name . "'>Admin</span>";
                } else {
                    return __("invoice");
                }
            case "invoice":
                if ($this->invoice) {
                    return "<a target='_blank' href='" . route("admin.invoices.show", ["invoice" => $this->invoice->id]) . "' class='badge badge-primary' data-bs-toggle='tooltip' title='#" . $this->invoice->invoice_number . "'>" . __("invoice") . "</a>";
                } else {
                    return __("invoice");
                }
            default:
                return __("unknown");
        }
    }

    public function drawPortalAction()
    {
        switch ($this->model) {
            case "admin":
                if ($this->admin) {
                    return "<span class='badge badge-primary' data-bs-toggle='tooltip' title='" . $this->admin->id . " | " . $this->admin->full_name . "'>Admin</span>";
                } else {
                    return __("invoice");
                }
            case "invoice":
                if ($this->invoice) {
                    return "<a target='_blank' href='" . route("portal.invoices.show", ["invoice" => $this->invoice->id]) . "' class='badge badge-primary' data-bs-toggle='tooltip' title='#" . $this->invoice->invoice_number . "'>" . __("invoice") . "</a>";
                } else {
                    return __("invoice");
                }
            default:
                return __("unknown");
        }
    }


    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, "id", "model_id");
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, "id", "model_id");
    }
}
