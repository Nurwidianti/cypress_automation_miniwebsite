<?php

class HomeController {
    private function user_survey_logistik()
    {
        $user = $this->generate_mock_user();
        // $user = Auth::user();
        $user->jabatan_survey = DB::table('tb_survey_logistik_users')->where('nik', $user->nik)->first()->jabatan ?? 'NO JABATAN';
        return $user;
    }

    private function update_survey_logistik_last_login()
    {
        $user = $this->user_survey_logistik();
        DB::table('tb_survey_logistik_last_login')->upsert(['nik' => $user->nik, 'updated_time' => time()], ['nik']);
    }

    public function soal_survey_logistik()
    {
        $date_start = '2024-04-6'; // sesuaikan
        $user = $this->user_survey_logistik();
        $last_time = DB::table('tb_survey_logistik_last_login')->where('nik', $user->nik)->first()->updated_time ?? 0;
        $last = ((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', $last_time))) / 86400);
        if ($last === 0 || $last < 0) {
            return collect([]);
        }
        $get_day_of_soal = ((strtotime(date('Y-m-d')) - strtotime($date_start)) / 86400) + 1;
        if ($get_day_of_soal < 0) {
            $get_day_of_soal = 0;
        }
        $vendors = DB::table('master_kuisoner_logistik')
        ->where('unit', $user->unit)
        ->pluck('vendor');
        if ($vendors->count() === 0) {
            return collect([]);
        }
        $soal = DB::table('tb_survey_logistik_soal')
        ->whereIn('vendor', $vendors)
        ->whereJsonContains('jabatan', $user->jabatan_survey)
        ->orderBy('id', 'ASC')
        ->limit($get_day_of_soal)
        ->get();
        if ($soal->count() === 0) {
        $this->update_survey_logistik_last_login();
            return collect([]);
        }
        $soal_done = DB::table('tb_survey_logistik_jawaban')
        ->where('nik', $user->nik)
        ->whereIn('soal_id', $soal->pluck('id'))
        ->pluck('soal_id')
        ->toArray();
        $soal_yet = $soal->filter(function($item) use ($soal_done) {
            return !in_array($item->id, $soal_done);
        })
        ->map(function($item) {
        $item->pilihan = json_decode($item->pilihan);
            return $item;
        });
        if ($soal_yet->count() === 0) {
            $this->update_survey_logistik_last_login();
        }
        return $soal_yet;
    }

    public function survey_logistik()
    {
        $user = $this->user_survey_logistik();
        $soal = $this->soal_survey_logistik();
        // if ($soal->count() === 0) {
        //   abort(404);
        // }
        return view('dashboard.home.survey_logistik', compact('user', 'soal'));
    }

    public function survey_logistik_save(Request $request)
    {
        try {
            $user = $this->user_survey_logistik();
            $data = collect($request->all())
            ->filter(function($_, $index) {
                return $index !== '_token';
            })
            ->map(function($item, $index) use ($user) {
                return [
                    'nik' => $user->nik,
                    'soal_id' => (int) $index,
                    'jawaban' => $item,
                ];
            })
            ->values()
            ->toArray();
            DB::table('tb_survey_logistik_jawaban')->insert($data);
            $this->update_survey_logistik_last_login();
            return redirect()->route('home');
        } catch (\Exception $e) {
            Alert::error('Gagal: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
