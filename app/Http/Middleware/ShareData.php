<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\MoNotif;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ShareData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // cek apakah sudah login
        if (Auth::check()) {
            // Data yang ingin Anda kirim ke semua halaman
            $user = Auth::user();
            $notif_motion = MoNotif::where('nik_tujuan', $user->nik)
                ->leftJoin('tb_mo_post', 'tb_mo_post.id', '=', 'tb_mo_notif.post_id')
                ->select('tb_mo_post.*', 'tb_mo_notif.pengirim')
                ->get();
            View::share('share', [
                'notif_motion' => $notif_motion
            ]);
        }

        return $next($request);
    }
}
