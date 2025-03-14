<?php
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
Auth::routes();
//Route::auth();
Route::get('/', function () {return view('auth.login');});
Route::post('/logout', [LoginController::class, 'logout']);
Route::get('/logout', [LoginController::class, 'logout']);
Route::get('/home', 'HomeController@index');
Route::get('/reg', 'RegController@reg')->name('reg');
Route::post('/reg', 'RegController@post_reg')->name('reg.post');
Route::get('/reg/peserta', 'RegController@peserta')->name('reg.peserta');
Route::get('/reg/resume_ap', 'RegController@resume_ap')->name('reg.resume_ap');
Route::get('/password_reset', 'RegController@password_reset')->name('password_reset');
Route::get('/password_reset_send_code', 'RegController@password_reset_send_code')->name('password_reset_send_code');
Route::post('/password_reset_send_nik', 'RegController@password_reset_send_nik')->name('password_reset_send_nik');
Route::post('/password_reset_send_code_by_wa', 'RegController@password_reset_send_code_by_wa')->name('password_reset_send_code_by_wa');
Route::post('/password_reset_finalize', 'RegController@password_reset_finalize')->name('password_reset_finalize');

Route::group(['middleware' => ['auth']], function () {
    Route::group(['name' => 'admin'], function () {
        Route::get('/#', function () {return view('dashboard.404');});
        Route::get('/admin', function () {return view('dashboard.homepage');});


/** jangan hapus */        Route::post('/home/dilihat', 'HomeController@dilihat')->name('home.dilihat');
        Route::post('/home/dilihat/dashboard', 'HomeController@dilihat_dashboard')->name('home.dilihat_dashboard');
        Route::post('/password', 'ApController@password')->name('password.ubah');
        Route::post('/whatsapp', 'ApController@whatsapp')->name('whatsapp.ubah');
        Route::post('/whatsapp/modal', 'ApController@modal_whatsapp')->name('whatsapp.modal');
        Route::resource('ap', 'ApController');
        Route::post('/source-stok-ayam/insert', 'ScStokAyamController@insertStok')->name('source-stok-ayam.insert');
        Route::post('/karyawan/sinkron', 'KaryawanController@sinkron')->name('karyawan.sinkron');

        // CREATE SISWO
        Route::post('/logdoc/poDocPerVendor/deleteRange', 'LogDocController@deleteRangePoDocPerVendor')->name('logdoc.poDocPerVendor.deleteRange');
        Route::get('/logdoc/poDocPerVendor', 'LogDocController@poDocPerVendor')->name('logdoc.poDocPerVendor');
        Route::get('/logdoc/poDocPerVendor/create', 'LogDocController@createPoDocPerVendor')->name('logdoc.poDocPerVendor.create');
        Route::post('/logdoc/poDocPerVendor/upload', 'LogDocController@uploadPoDocPerVendor')->name('logdoc.poDocPerVendor.upload');
        Route::get('/logdoc/poDocPerVendor/edit/{id}', 'LogDocController@editPoDocPerVendor')->name('logdoc.poDocPerVendor.edit');
        Route::post('/logdoc/poDocPerVendor/update/{id}', 'LogDocController@updatePoDocPerVendor')->name('logdoc.poDocPerVendor.update');
        Route::post('/logdoc/poDocPerVendor/delete/{id}', 'LogDocController@deletePoDocPerVendor')->name('logdoc.poDocPerVendor.delete');
        Route::get('/logdoc/poDocPerVendor/create/dataPeternak/get', 'LogDocController@dataPeternak')->name('logdoc.poDocPerVendor.dataPeternak');
        Route::get('/logdoc/poDocPerVendor/create/periksaKontrak/get', 'LogDocController@periksaKontrak')->name('logdoc.poDocPerVendor.periksaKontrak');
        Route::post('/logdoc/poDocPerVendor/databasePlasma', 'LogDocController@databasePlasma')->name('logdoc.poDocPerVendor.databasePlasma');
        Route::post('/logdoc/poDocPerVendor/gradePlasma', 'LogDocController@gradePlasma')->name('logdoc.poDocPerVendor.gradePlasma');
        Route::post('/logdoc/poDocPerVendor/plasmaAktif', 'LogDocController@plasmaAktif')->name('logdoc.poDocPerVendor.plasmaAktif');
        Route::post('/logdoc/poDocPerVendor/prasyaratFlokAktif', 'LogDocController@prasyaratFlokAktif')->name('logdoc.poDocPerVendor.prasyaratFlokAktif');
        Route::post('/logdoc/poDocPerVendor/uploadBuktiRekomendasi', 'LogDocController@uploadBuktiRekomendasi')->name('logdoc.poDocPerVendor.uploadBuktiRekomendasi');
        Route::post('/logdoc/poDocPerVendor/ubahStatusPengiriman', 'LogDocController@ubahStatusPengiriman')->name('logdoc.poDocPerVendor.ubahStatusPengiriman');
        Route::get('/logdoc/poDocPerVendor/arsip', 'LogDocController@arsipPoDocPerVendor')->name('logdoc.poDocPerVendor.arsip');
        Route::get('/logdoc/poDocPerVendor/masterRing', 'LogDocController@masterRingPoDocPerVendor')->name('logdoc.poDocPerVendor.masterRing');
        Route::post('/logdoc/poDocPerVendor/masterRing/import', 'LogDocController@masterRingPoDocPerVendorImport')->name('logdoc.poDocPerVendor.masterRing.import');
        Route::get('/logdoc/poDocPerVendor/masterRing/edit/{id}', 'LogDocController@masterRingPoDocPerVendorEdit')->name('logdoc.poDocPerVendor.masterRing.edit');
        Route::post('/logdoc/poDocPerVendor/masterRing/update/{id}', 'LogDocController@masterRingPoDocPerVendorUpdate')->name('logdoc.poDocPerVendor.masterRing.update');
        Route::post('/logdoc/poDocPerVendor/masterRing/delete/{id}', 'LogDocController@masterRingPoDocPerVendorDelete')->name('logdoc.poDocPerVendor.masterRing.delete');
        Route::resource('logdoc', 'LogDocController');

        Route::get('/user', 'UserController@index')->name('user.index');
        Route::post('/user/simpan', 'UserController@simpan')->name('user.simpan');
        Route::get('/user/reset/{id}', 'UserController@reset')->name('user.reset');
        Route::get('/user/cari', 'UserController@cari')->name('user.cari');
        Route::get('/user/list', 'UserController@list')->name('user.list');
        Route::get('/user/create', 'UserController@index')->name('user.create');
        Route::get('/user/edit/{id}', 'UserController@edit')->name('user.edit');
        Route::post('/user/hapus/{id}', 'UserController@hapus')->name('user.hapus');
        Route::post('/user/update/{id}', 'UserController@update')->name('user.update');
        Route::get('/user/{region}','UserController@getUnit')->name('user.getUnit');
        Route::get('/kodeunit/{region}','UserController@getKodeUnit')->name('user.getKodeUnit');
        Route::resource('user', 'UserController');

        Route::get('/lokasi', 'HomeController@lokasi')->name('home.lokasi');
        Route::post('/lokasi_save', 'HomeController@lokasi_save')->name('home.lokasi_save');

        Route::get('/c', 'NewMotionController@c')->name('c');
        Route::get('/x', function()
            {
                return view('dashboard.x');
            })
            ->name('x');
        Route::post('/post_x', 'HomeController@post_x')->name('post_x');
    });

    Route::group(['name' => 'user'], function () {
        Route::get('/profil/{id}', 'KaryawanController@profil')->name('karyawan.profil');
        Route::resource('karyawan', 'KaryawanController');
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/privacy', 'HomeController@privacy');
