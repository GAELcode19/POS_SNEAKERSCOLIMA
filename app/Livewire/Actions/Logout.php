<?php

namespace App\Livewire\Actions;

use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    public function __invoke()
    {
        $user = Auth::user();
        if ($user) {
            $shift = Shift::where('employee_id', $user->id)
                ->whereNull('ended_at')
                ->first();

            if ($shift) {
                $hours = $shift->started_at->diffInMinutes(now()) / 60;
                $shift->update([
                    'ended_at' => now(),
                    'hours_logged' => round($hours, 2),
                ]);
            }
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
