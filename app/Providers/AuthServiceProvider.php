<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('isAdmin', function() {
            return Auth::user()->roles == 'admin';
        });

        Gate::define('isUser', function() {
            return Auth::user()->roles == 'user';
        });

        Gate::define('isRegion', function() {
            return Auth::user()->roles == 'region';
        });

        Gate::define('isSr', function() {
            return Auth::user()->roles == 'sr';
        });

        Gate::define('isPusat', function() {
            return Auth::user()->roles == 'pusat';
        });

        Gate::define('isKanit', function() {
            return Auth::user()->jabatan == 'KEPALA UNIT';
        });

        Gate::define('isTs', function() {
            return Auth::user()->jabatan == 'TECHNICAL SUPPORT';
        });

        Gate::define('isKp', function() {
            return Auth::user()->jabatan == 'KEPALA PRODUKSI';
        });

        Gate::define('isKareg', function() {
            return Auth::user()->jabatan == 'KEPALA REGION';
        });

        Gate::define('isDirektur', function() {
            return Auth::user()->jabatan == 'DIREKTUR PT';
        });

        Gate::define('isDirekturUtama', function() {
            return Auth::user()->jabatan == 'DIREKTUR UTAMA';
        });

        Gate::define('isFinance', function() {
            return Auth::user()->jabatan == 'STAFF FINANCE';
        });

        Gate::define('isTri', function() {
            return Auth::user()->nik == '0639.MTK.1215';
        });

        Gate::define('isAmi', function() {
            return Auth::user()->nik == '0267.MTK.1013';
        });

        Gate::define('isSofi', function() {
            return Auth::user()->nik == '0447.MTK.0515';
        });

        Gate::define('isWildan', function() {
            return Auth::user()->nik == '1810.MTK.0921';
        });

        Gate::define('isWahyu', function() {
            return Auth::user()->nik == '0036.MTK.0210';
        });

        Gate::define('isFadlu', function() {
            return Auth::user()->nik == '1761.MTK.0121';
        });

        Gate::define('isRachmad', function() {
            return Auth::user()->nik == '0053.MTK.0811';
        });

        Gate::define('isAsri', function() {
            return Auth::user()->nik == '1498.MTK.0918';
        });

        Gate::define('isElis', function() {
            return Auth::user()->nik == '0126.MTK.0912';
        });

        Gate::define('isIan', function() {
            return Auth::user()->nik == '1903.MTK.1022';
        });

        Gate::define('isLia', function() {
            return Auth::user()->nik == '1902.MTK.0822';
        });

        Gate::define('isAkunting', function() {
            return Auth::user()->jabatan == 'AKUNTING';
        });

        Gate::define('isSusi', function() {
            return Auth::user()->nik == '0261.MTK.0913';
        });

        Gate::define('isDimas', function() {
            return Auth::user()->nik == '0856.MTK.0416';
        });

        Gate::define('isTiwi', function() {
            return Auth::user()->nik == '0110.MTK.0412';
        });

        Gate::define('isLinda', function() {
            return Auth::user()->nik == '1505.MTK.1018';
        });

        Gate::define('isHusni', function() {
            return Auth::user()->nik == '1908.MTK.0822';
        });

        Gate::define('isSatriyo', function() {
            return Auth::user()->nik == '0315.MTK.1213';
        });

        Gate::define('isSuperAdmin', function() {
            return Auth::user()->nik == 'admin';
        });

        Gate::define('isTeri', function() {
            return Auth::user()->nik == '0034.MTK.0110';
        });

        Gate::define('isOperation', function() {
            return Auth::user()->jabatan == 'STAFF OPERASIONAL';
        });

        Gate::define('isHeris', function() {
            return Auth::user()->nik == '1958.MTK.1222';
        });

        Gate::define('isPutra', function() {
            return Auth::user()->nik == '0945.MTK.1115';
        });

        Gate::define('isLD', function() {
            return Auth::user()->jabatan == 'STAFF DEVELOPMENT';
        });

        Gate::define('isHanif', function() {
            return Auth::user()->nik == '2123.MTK.0723';
        });

        Gate::define('isRifqi', function() {
            return Auth::user()->nik == '2183.MTK.1023';
        });

        Gate::define('isMIS', function () {
            return in_array(Auth::user()->nik, [
                '1962.MTK.1122', // KHOIRUL MILAD BASYA
                '1872.MTK.0622', // TANTI YULIANITA
                '1761.MTK.0121', // FADLU MUHAMMAD AMRULLOH
                '1978.MTK.1222', // SISWO SUKMO PAMUNGKAS
                '1287.MTK.0717', // CHOIRUL ROZAQ
                '0008.MTK.0309', // Y SOFE MEIYANTOKO
                '2118.MTK.0723', // MUHAMMAD ALAMUL YAQIN
                'adib.ho', // ADIB
                'admin',
            ]);
        });

        Gate::define('isIA', function () {
            return Auth::user()->jabatan == 'STAFF IA';
        });
        //
    }
}
