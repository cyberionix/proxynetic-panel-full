<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Support;
use App\Models\SupportAutoReply;
use App\Models\SupportMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupportAutoReplyService
{
    public static function handleEvent(string $event, Support $support): void
    {
        try {
            $rules = SupportAutoReply::where('is_active', true)
                ->where('trigger_event', $event)
                ->orderBy('sort_order')
                ->get();

            if ($rules->isEmpty()) {
                return;
            }

            $support->loadMissing(['order', 'messages']);

            $hasAdminReply = $support->messages
                ->where('is_auto_reply', false)
                ->whereNotNull('admin_id')
                ->isNotEmpty();

            $orderCategoryId = $support->order?->product_data['category']['id'] ?? null;

            $matchedRules = [];

            foreach ($rules as $rule) {
                if ($rule->trigger_department && $rule->trigger_department !== $support->department) {
                    continue;
                }

                if ($rule->skip_if_admin_replied && $hasAdminReply) {
                    continue;
                }

                $categoryIds = $rule->trigger_product_category_ids ?? [];
                if (!empty($categoryIds)) {
                    if (!$orderCategoryId || !in_array((int) $orderCategoryId, array_map('intval', $categoryIds))) {
                        continue;
                    }
                }

                $matchedRules[] = $rule;
            }

            $priorityRule = collect($matchedRules)->firstWhere('is_priority', true);
            $rulesToSend = $priorityRule ? [$priorityRule] : $matchedRules;

            foreach ($rulesToSend as $rule) {
                if ($rule->delay_minutes > 0) {
                    DB::table('support_pending_auto_replies')->insert([
                        'rule_id' => $rule->id,
                        'support_id' => $support->id,
                        'send_at' => now()->addMinutes($rule->delay_minutes),
                        'sent' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    static::sendAutoReply($rule, $support);
                }
            }
        } catch (\Throwable $e) {
            Log::error('AutoReply hata', [
                'event' => $event,
                'support_id' => $support->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    public static function processPendingAutoReplies(): int
    {
        $sent = 0;
        try {
            $pending = DB::table('support_pending_auto_replies')
                ->where('sent', false)
                ->where('send_at', '<=', now())
                ->orderBy('send_at')
                ->limit(20)
                ->get();

            foreach ($pending as $item) {
                try {
                    $rule = SupportAutoReply::find($item->rule_id);
                    $support = Support::withoutGlobalScope('for_user')->find($item->support_id);

                    if ($rule && $support) {
                        if ($rule->skip_if_admin_replied) {
                            $hasAdminReply = SupportMessage::where('support_id', $support->id)
                                ->where('is_auto_reply', false)
                                ->whereNotNull('admin_id')
                                ->exists();
                            if ($hasAdminReply) {
                                Log::info('AutoReply atlandı: admin zaten yanıt vermiş', [
                                    'rule_id' => $rule->id,
                                    'support_id' => $support->id,
                                ]);
                            } else {
                                static::sendAutoReply($rule, $support);
                                $sent++;
                            }
                        } else {
                            static::sendAutoReply($rule, $support);
                            $sent++;
                        }
                    }

                    DB::table('support_pending_auto_replies')
                        ->where('id', $item->id)
                        ->update(['sent' => true, 'updated_at' => now()]);
                } catch (\Throwable $e) {
                    Log::error('AutoReply pending send hatası', [
                        'pending_id' => $item->id,
                        'exception' => $e->getMessage(),
                    ]);
                    DB::table('support_pending_auto_replies')
                        ->where('id', $item->id)
                        ->update(['sent' => true, 'updated_at' => now()]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('AutoReply processPending hatası', ['exception' => $e->getMessage()]);
        }
        return $sent;
    }

    public static function sendAutoReply(SupportAutoReply $rule, Support $support): void
    {
        try {
            $support->loadMissing('user');

            $content = $rule->replaceVariables($support);
            $admin = Admin::first();

            SupportMessage::create([
                'message' => $content,
                'support_id' => $support->id,
                'admin_id' => $admin?->id,
                'is_auto_reply' => true,
            ]);

            if ($rule->trigger_event === 'TICKET_CREATED') {
                $support->updateQuietly(['status' => 'ANSWERED']);
            }
        } catch (\Throwable $e) {
            Log::error('AutoReply mesaj hatası', [
                'rule_id' => $rule->id,
                'support_id' => $support->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
