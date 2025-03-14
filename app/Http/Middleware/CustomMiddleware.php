<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\HomeController;

class CustomMiddleware
{
    protected $user;
    protected $home;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->home = new HomeController();
    }

    public function handle(Request $request, Closure $next)
    {
        if ($this->user !== null) {
            $unblocked_routes = [
                $request->getRequestUri() === '/logout',
                $request->getRequestUri() === '/login',
                $request->routeIs('home.survey_mis'),
                $request->routeIs('home.survey_mis_submit'),
                $request->routeIs('home.survey_ho'),
                $request->routeIs('home.survey_ho_save'),
                $request->routeIs('home.survey_logistik'),
                $request->routeIs('home.survey_logistik_save'),

                // $request->routeIs('home.survey_media_sosial'),
                // $request->routeIs('home.survey_media_sosial_save'),
            ];

            if (in_array(true, $unblocked_routes)) {
                return $next($request);
            }

            //   if (!$this->check_survey_mis()) {
            //     return redirect()->route('home.survey_mis');
            //   }
            //   if (!$this->check_survey_kayrawan_ho()) {
            //     return redirect()->route('home.survey_ho');
            //   }

            //   if (!$this->check_survey_media_sosial()) { // tambahan
            //     return redirect()->route('home.survey_media_sosial'); // tambahan
            //   }
            //   if (!$this->check_user_login()) {
            //     abort(404);
            //   }

            return $next($request);
        }
        return $next($request);
    }

    private function check_survey_mis()
    {
        return DB::table('tb_survey_web_mis')->where('nik', $this->user->nik)->get()->count() !== 0;
    }

    private function check_survey_kayrawan_ho()
    {
        return check_survey_kepuasan_karyawan_ho($this->user);
    }

    private function check_user_login()
    {
        $allow_jabatan = ['DIREKTUR PT', 'DIREKTUR UTAMA', 'ADMINISTRATOR', 'SUPERVISOR', 'STAFF PENJUALAN', 'STAFF FINANCE', 'STAFF IA', 'STAFF REGION', 'STAFF HRD', 'AKUNTING', 'KEPALA UNIT', 'KANIT', 'KEPALA PRODUKSI', 'KAPROD', 'KEPALA REGION'];
        $allow_nik = ['admin', '2334.MTK.0424'];
        return in_array($this->user->jabatan, $allow_jabatan) || in_array($this->user->nik, $allow_nik);
    }

    private function check_survey_media_sosial() // tambahan
    { // tambahan
        return $this->home->survey_media_sosial_soal()->count() === 0; // tambahan
    } //

    private function check_indeks_kepuasan_tim_ho()
    {
        return $this->home->indeks_kepuasan_tim_ho_soal()->bagians->count() === 0;
    }
}
