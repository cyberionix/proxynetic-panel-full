<?php

namespace App\Traits;
trait SupportAttributes
{
    public function getDrawIdAttribute()
    {
        return "#" . $this->id;
    }

    public function getDrawDepartmentAttribute()
    {
        return match ($this->department) {
            "GENERAL" => __("general"),
            "ORDER" => __("order"),
            "ACCOUNTING" => __("accounting"),
            "TECHNICAL_SUPPORT" => __("technical_support"),
            default => "",
        };
    }

    public function getDrawPriorityAttribute()
    {
        return match ($this->priority) {
            "LOW" => __("low"),
            "MEDIUM" => __("medium"),
            "HIGH" => __("high"),
            default => "",
        };
    }

    public function getDrawStatusAttribute()
    {
        return match ($this->status) {
            "WAITING_FOR_AN_ANSWER" => __("waiting_for_an_answer"),
            "ANSWERED" => __("answered"),
            "RESOLVED" => __("resolved"),
            default => "",
        };
    }
}
