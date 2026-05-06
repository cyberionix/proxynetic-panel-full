<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTemplate;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;

class SupportTemplateController extends Controller
{
    use AjaxResponses;

    public function index()
    {
        $templates = SupportTemplate::orderBy('sort_order')->orderByDesc('id')->get();
        return view('admin.pages.supports.templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ], [
            'title.required' => 'Şablon başlığı zorunludur.',
            'content.required' => 'Şablon içeriği zorunludur.',
        ]);

        SupportTemplate::create([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return $this->successResponse('Şablon başarıyla oluşturuldu.');
    }

    public function update(Request $request, SupportTemplate $template)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ], [
            'title.required' => 'Şablon başlığı zorunludur.',
            'content.required' => 'Şablon içeriği zorunludur.',
        ]);

        $template->update([
            'title' => $request->title,
            'content' => $request->content,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return $this->successResponse('Şablon başarıyla güncellendi.');
    }

    public function delete(SupportTemplate $template)
    {
        $template->delete();
        return $this->successResponse('Şablon başarıyla silindi.');
    }

    public function getActive()
    {
        $templates = SupportTemplate::where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get(['id', 'title', 'content']);

        return $this->successResponse('', ['templates' => $templates]);
    }
}
