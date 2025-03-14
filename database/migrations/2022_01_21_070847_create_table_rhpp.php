<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRhpp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_rhpp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('kodeflok');
            $table->string('namaflok');
            $table->date('tgldocin');
            $table->date('tgldocfinal');
            $table->integer('ciawal')->unsigned();
            $table->double('coekor', 12, 2);
            $table->double('cokg', 12, 2);
            $table->double('fiek', 12, 2);
            $table->double('pfmcbw', 12, 2);
            $table->double('pfmcfcr', 12, 2);
            $table->double('pfmcdpls', 12, 2);
            $table->double('pfmcumur', 12, 2);
            $table->double('pfmcadg', 12, 2);
            $table->double('pfmcip', 12, 2);
            $table->double('nilaifcrstd', 12, 2);
            $table->double('nilaidifffcr', 12, 2);
            $table->double('nilaidplsstd', 12, 2);
            $table->double('nilaidiffdpls', 12, 2);
            $table->double('rhpplabarugi', 12, 2);
            $table->double('rhpprugiproduksi', 12, 2);
            $table->double('rhppcndoc', 12, 2);
            $table->double('rhppbonus', 12, 2);
            $table->double('rhppkompensasi', 12, 2);
            $table->double('rhpptotal', 12, 2);
            $table->double('rhppkg', 12, 2);
            $table->double('biayadoc', 12, 2);
            $table->double('biayafeed', 12, 2);
            $table->double('biayaovk', 12, 2);
            $table->double('biayatotal', 12, 2);
            $table->integer('jualdoc')->unsigned();
            $table->integer('jualcndoc')->unsigned();
            $table->double('jualfeed', 12, 2);
            $table->integer('jualovk')->unsigned();
            $table->integer('belidoc')->unsigned();
            $table->double('belifeed', 12, 2);
            $table->double('belitransport', 12, 2);
            $table->double('belifranco', 12, 2);
            $table->double('beliovk', 12, 2);
            $table->integer('labarugidoc')->unsigned();
            $table->double('labarugifeed', 12, 2);
            $table->double('labarugiovk', 12, 2);
            $table->double('pencapaianhpp', 12, 2);
            $table->double('pencapaianharga', 12, 2);
            $table->double('labarugiperkg', 12, 2);
            $table->double('labarugiperek', 12, 2);
            $table->double('labaruginominal', 12, 2);
            $table->string('namappl');
            $table->double('feedkgqty', 12, 2);
            $table->double('valbbdoc', 12, 2);
            $table->double('valbbloco', 12, 2);
            $table->double('valbbtransport', 12, 2);
            $table->double('valbbbelifeed', 12, 2);
            $table->double('valbbovk', 12, 2);
            $table->double('valtotbeli', 12, 2);
            $table->double('valbbjualdoc', 12, 2);
            $table->double('valbbcndoc', 12, 2);
            $table->double('valbbjualfeed', 12, 2);
            $table->double('valbbjualovk', 12, 2);
            $table->double('valbbtotjual', 12, 2);
            $table->double('jualayamactual', 12, 2);
            $table->double('jualayamcnbakul', 12, 2);
            $table->double('jualayamdnbakul', 12, 2);
            $table->double('jualayamkontrak', 12, 2);
            $table->double('nomrhpppfmc', 12, 2);
            $table->double('nomrhpprugi', 12, 2);
            $table->double('nomrhppupah', 12, 2);
            $table->double('nomrhppbonus', 12, 2);
            $table->double('nomrhppkompensasi', 12, 2);
            $table->double('nomrhpptotal', 12, 2);
            $table->double('hitunguangselisih', 12, 2);
            $table->double('hitunguangpenerimaan', 12, 2);
            $table->double('rmsbantulabarugi', 12, 2);
            $table->double('rmsbantuumur', 12, 2);
            $table->double('rmsbantudpls', 12, 2);
            $table->integer('lamapanen')->unsigned();
            $table->integer('haripanen')->unsigned();
            $table->string('kondisiayam');
            $table->string('jenisdoc');
            $table->string('areaplasma');
            $table->string('jeniskandang');
            $table->double('hitunglamapanen', 12, 2);
            $table->double('hitungharipanen', 12, 2);
            $table->double('biayaservispfmc', 12, 2);
            $table->double('biayaservisbb', 12, 2);
            $table->double('biayaservistotal', 12, 2);
            $table->double('biayaservisperekor', 12, 2);
            $table->string('namakaprod');
            $table->string('kodekaprod');
            $table->string('kodeppl');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_rhpp');
    }
}
