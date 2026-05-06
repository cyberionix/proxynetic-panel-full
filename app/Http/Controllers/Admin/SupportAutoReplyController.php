<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Models\SupportAutoReply;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;

class SupportAutoReplyController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $autoReplies = SupportAutoReply::orderBy('sort_order')->get();
        $productCategories = ProductCategory::orderBy('name')->get();
        return view('admin.pages.supports.auto-replies.index', compact('autoReplies', 'productCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string|in:' . implode(',', array_keys(SupportAutoReply::TRIGGER_EVENTS)),
            'trigger_department' => 'nullable|string',
            'trigger_product_category_ids' => 'nullable|array',
            'trigger_product_category_ids.*' => 'integer|exists:product_categories,id',
            'skip_if_admin_replied' => 'nullable|in:0,1',
            'is_priority' => 'nullable|in:0,1',
            'message' => 'required|string',
            'is_active' => 'required|in:0,1',
            'sort_order' => 'nullable|integer',
            'delay_minutes' => 'nullable|integer|min:0',
        ]);

        $categoryIds = $request->trigger_product_category_ids;
        if ($categoryIds) {
            $categoryIds = array_map('intval', array_filter($categoryIds));
        }

        SupportAutoReply::create([
            'name' => $request->name,
            'trigger_event' => $request->trigger_event,
            'trigger_department' => $request->trigger_department ?: null,
            'trigger_product_category_ids' => !empty($categoryIds) ? $categoryIds : null,
            'skip_if_admin_replied' => $request->skip_if_admin_replied ?? 0,
            'is_priority' => $request->is_priority ?? 0,
            'message' => $request->message,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
            'delay_minutes' => $request->delay_minutes ?? 0,
        ]);

        return $this->successResponse('Otomatik yanıt kuralı başarıyla oluşturuldu.');
    }

    public function update(Request $request, SupportAutoReply $autoReply)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string|in:' . implode(',', array_keys(SupportAutoReply::TRIGGER_EVENTS)),
            'trigger_department' => 'nullable|string',
            'trigger_product_category_ids' => 'nullable|array',
            'trigger_product_category_ids.*' => 'integer|exists:product_categories,id',
            'skip_if_admin_replied' => 'nullable|in:0,1',
            'is_priority' => 'nullable|in:0,1',
            'message' => 'required|string',
            'is_active' => 'required|in:0,1',
            'sort_order' => 'nullable|integer',
            'delay_minutes' => 'nullable|integer|min:0',
        ]);

        $categoryIds = $request->trigger_product_category_ids;
        if ($categoryIds) {
            $categoryIds = array_map('intval', array_filter($categoryIds));
        }

        $autoReply->update([
            'name' => $request->name,
            'trigger_event' => $request->trigger_event,
            'trigger_department' => $request->trigger_department ?: null,
            'trigger_product_category_ids' => !empty($categoryIds) ? $categoryIds : null,
            'skip_if_admin_replied' => $request->skip_if_admin_replied ?? 0,
            'is_priority' => $request->is_priority ?? 0,
            'message' => $request->message,
            'is_active' => $request->is_active,
            'sort_order' => $request->sort_order ?? 0,
            'delay_minutes' => $request->delay_minutes ?? 0,
        ]);

        return $this->successResponse('Otomatik yanıt kuralı başarıyla güncellendi.');
    }

    public function delete(SupportAutoReply $autoReply)
    {
        $autoReply->delete();
        return $this->successResponse('Otomatik yanıt kuralı başarıyla silindi.');
    }

    public function toggleStatus(SupportAutoReply $autoReply)
    {
        $autoReply->update(['is_active' => !$autoReply->is_active]);
        $statusText = $autoReply->is_active ? 'aktif' : 'pasif';
        return $this->successResponse("Kural {$statusText} olarak güncellendi.");
    }
}
