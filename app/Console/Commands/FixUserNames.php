<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Console\Command;

class FixUserNames extends Command
{
    protected $signature = 'app:fix-user-names';
    protected $description = 'Fix existing user/admin names to use proper title case (Turkish-aware)';

    public function handle()
    {
        $count = 0;

        User::chunk(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                $fixed = false;
                $newFirst = mb_convert_case(mb_strtolower($user->getRawOriginal('first_name'), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                $newLast  = mb_convert_case(mb_strtolower($user->getRawOriginal('last_name'), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

                if ($user->getRawOriginal('first_name') !== $newFirst || $user->getRawOriginal('last_name') !== $newLast) {
                    $user->forceFill(['first_name' => $newFirst, 'last_name' => $newLast])->saveQuietly();
                    $fixed = true;
                    $count++;
                }
            }
        });

        Admin::all()->each(function ($admin) use (&$count) {
            $newFirst = mb_convert_case(mb_strtolower($admin->getRawOriginal('first_name'), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $newLast  = mb_convert_case(mb_strtolower($admin->getRawOriginal('last_name'), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

            if ($admin->getRawOriginal('first_name') !== $newFirst || $admin->getRawOriginal('last_name') !== $newLast) {
                $admin->forceFill(['first_name' => $newFirst, 'last_name' => $newLast])->saveQuietly();
                $count++;
            }
        });

        $this->info("{$count} kullanıcı/admin ismi düzeltildi.");
    }
}
