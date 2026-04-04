<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Support;
use App\Models\SupportAutoReply;
use App\Models\SupportMessage;
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

            $hasAdminReply = $support->messages->whereNotNull('admin_id')->isNotEmpty();
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
                    $ruleId = $rule->id;
                    $supportId = $support->id;
                    dispatch(function () use ($ruleId, $supportId) {
                        $r = SupportAutoReply::find($ruleId);
                        $s = Support::withoutGlobalScope('for_user')->find($supportId);
                        if ($r && $s) {
                            SupportAutoReplyService::sendAutoReply($r, $s);
                        }
                    })->afterCommit()->delay(now()->addMinutes($rule->delay_minutes));
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

    protected static function sendAutoReply(SupportAutoReply $rule, Support $support): void
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
