<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThreeProxyLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ThreeProxyLogController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
        $logs = $query->paginate(50)->appends($request->query());

        $actions = self::actionList();

        return view('admin.pages.threeProxyLogs.index', compact('logs', 'actions'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->buildQuery($request);
        $logs = $query->get();

        $filename = '3proxy_logs';
        if ($request->filled('date_from')) $filename .= '_from_' . $request->date_from;
        if ($request->filled('date_to')) $filename .= '_to_' . $request->date_to;
        $filename .= '_' . now()->format('Ymd_His') . '.csv';

        return new StreamedResponse(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Tarih',
                'Sipariş ID',
                'Müşteri',
                'İşlem',
                'IP Listesi',
                'Proxy Sayısı',
                'Kullanıcı',
                'Şifre',
                'Başlangıç',
                'Bitiş',
                'Süre',
                'Detay',
            ], ';');

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->created_at->format('d.m.Y H:i:s'),
                    $log->order_id,
                    $log->order?->user?->full_name ?? '-',
                    $log->action,
                    $log->ip_list ? implode(', ', $log->ip_list) : '-',
                    $log->proxy_count,
                    $log->username ?? '-',
                    $log->password ?? '-',
                    $log->started_at?->format('d.m.Y H:i:s') ?? '-',
                    $log->ended_at?->format('d.m.Y H:i:s') ?? '-',
                    $log->duration_human ?? '-',
                    $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_UNICODE) : '-',
                ], ';');
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildQuery(Request $request)
    {
        $query = ThreeProxyLog::with(['order.user', 'order.product'])
            ->latest();

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('ip')) {
            $query->whereJsonContains('ip_list', $request->ip);
        }

        if ($request->filled('username')) {
            $query->where('username', 'like', '%' . $request->username . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query;
    }

    private static function actionList(): array
    {
        return [
            ThreeProxyLog::ACTION_CREATED,
            ThreeProxyLog::ACTION_REINSTALLED,
            ThreeProxyLog::ACTION_STOPPED,
            ThreeProxyLog::ACTION_STARTED,
            ThreeProxyLog::ACTION_EXPIRED,
            ThreeProxyLog::ACTION_RENEWED,
            ThreeProxyLog::ACTION_CREDENTIALS_CHANGED,
            ThreeProxyLog::ACTION_PORT_CHANGED,
            ThreeProxyLog::ACTION_DELETED,
            ThreeProxyLog::ACTION_EXPIRE_EXTENDED,
        ];
    }
}
