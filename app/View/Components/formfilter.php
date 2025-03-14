<?php

namespace App\View\Components;

use Illuminate\View\Component;

class formfilter extends Component
{
    public $tanggal;
    public $form;
    public $start;
    public $end;
    public $fap;
    public $funit;
    public $tampil;
    public $excel;
    public $listunit;

    private function attrs($props = [], $default = [])
    {
        return collect($default)
            ->merge($props)
            ->filter(fn($_, $x) => !in_array($x, ['value', 'binding']))
            ->map(fn($i, $x) => preg_match('/^\d+$/', $x) ? $i : "$x=\"$i\"")
            ->join(' ');
    }

    private function iff($any)
    {
        return is_array($any) || !!$any;
    }

    public function __construct(
        $tanggal = true,
        $form = [],
        $start = [],
        $end = [],
        $fap = [],
        $funit = [],
        $tampil = [],
        $excel = [],
        $listunit
    ) {
        $this->tanggal = $tanggal;

        $this->form = (object) [
            'attr' => $this->attrs($form, [
                'action' => '',
                'method' => 'get',
                'class' => 'form-inline',
            ]),
            'value' => $form['value'] ?? null,
        ];

        if ($this->iff($tanggal)) {
            $this->start = (object) [
                'attr' => $this->attrs($start, [
                    'name' => 'fawal',
                    'type' => 'date',
                    'class' => 'form-control mb-2',
                ]),
                'value' => $start['value'] ?? null,
            ];

            $this->end = (object) [
                'attr' => $this->attrs($end, [
                    'name' => 'fakhir',
                    'type' => 'date',
                    'class' => 'form-control mb-2 mr-lg-2',
                ]),
                'value' => $end['value'] ?? null,
            ];
        }

        if ($this->iff($fap)) {
            $this->fap = (object) [
                'attr' => $this->attrs($fap, [
                    'name' => 'fap',
                    'class' => 'form-control mb-2 mr-lg-2',
                ]),
                'value' => $fap['value'] ?? null,
            ];
        }

        if ($this->iff($funit)) {
            $this->funit = (object) [
                'attr' => $this->attrs($funit, [
                    'name' => 'funit',
                    'class' => 'form-control mb-2 mr-lg-2',
                ]),
                'value' => $funit['value'] ?? null,
            ];
        }

        if ($this->iff($tampil)) {
            $this->tampil = (object) [
                'attr' => $this->attrs($tampil, [
                    'type' => 'submit',
                    'class' => 'btn btn-primary mb-2 mr-lg-2',
                    'name' => 'show',
                ]),
            ];
        }

        if ($this->iff($excel)) {
            $this->excel = (object) [
                'attr' => $this->attrs($excel, [
                    'type' => 'button',
                    'class' => 'btn btn-success mb-2 mr-lg-2'
                ]),
            ];
        }

        $this->listunit = $listunit;
    }

    public function render()
    {
        return view('components.formfilter');
    }
}
