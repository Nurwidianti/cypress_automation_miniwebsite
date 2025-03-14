@php
    ${'-'} = (function (
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
        $t = (object) [];
        $t->tanggal = null;
        $t->form = null;
        $t->start = null;
        $t->end = null;
        $t->fap = null;
        $t->funit = null;
        $t->tampil = null;
        $t->excel = null;
        $t->listunit = null;

        $attrs = function ($props = [], $default = []) {
            return collect($default)
                ->merge($props)
                ->filter(fn($_, $x) => !in_array($x, ['value', 'binding']))
                ->map(fn($i, $x) => preg_match('/^\d+$/', $x) ? $i : "$x=\"$i\"")
                ->join(' ');
        };

        $iff = function ($any) {
            return is_array($any) || !!$any;
        };

        $t->tanggal = $tanggal;

        $t->form = (object) [
            'attr' => $attrs($form, [
                'action' => '',
                'method' => 'get',
                'class' => 'form-inline',
            ]),
            'value' => $form['value'] ?? null,
        ];

        if ($iff($tanggal)) {
            $t->start = (object) [
                'attr' => $attrs($start, [
                    'name' => 'fawal',
                    'type' => 'date',
                    'class' => 'form-control mb-2',
                ]),
                'value' => $start['value'] ?? null,
            ];

            $t->end = (object) [
                'attr' => $attrs($end, [
                    'name' => 'fakhir',
                    'type' => 'date',
                    'class' => 'form-control mb-2 mr-lg-2',
                ]),
                'value' => $end['value'] ?? null,
            ];
        }

        if ($iff($fap)) {
            $t->fap = (object) [
                'attr' => $attrs($fap, [
                    'name' => 'fap',
                    'class' => 'form-control mb-2 mr-lg-2',
                ]),
                'value' => $fap['value'] ?? null,
            ];
        }

        if ($iff($funit)) {
            $t->funit = (object) [
                'attr' => $attrs($funit, [
                    'name' => 'funit',
                    'class' => 'form-control mb-2 mr-lg-2',
                ]),
                'value' => $funit['value'] ?? null,
            ];
        }

        if ($iff($tampil)) {
            $t->tampil = (object) [
                'attr' => $attrs($tampil, [
                    'type' => 'submit',
                    'class' => 'btn btn-primary mb-2 mr-lg-2',
                    'name' => 'show',
                ]),
            ];
        }

        if ($iff($excel)) {
            $t->excel = (object) [
                'attr' => $attrs($excel, [
                    'type' => 'button',
                    'class' => 'btn btn-success mb-2 mr-lg-2',
                ]),
            ];
        }

        $t->listunit = $listunit;

        return $t;
    })(
        $tanggal ?? true,
        $form ?? [],
        $start ?? [],
        $end ?? [],
        $fap ?? [],
        $funit ?? [],
        $tampil ?? [],
        $excel ?? [],
        $listunit ?? [],
    );
@endphp

<form {!! ${'-'}->form->attr !!}>
    @if (${'-'}->tanggal !== null)
        <label class="mb-2 mr-lg-1">TANGGAL</label>
        <input value="{{ ${'-'}->start->value ?? '' }}" {!! ${'-'}->start->attr !!}>
        <label class="mb-2 mx-lg-1">s/d</label>
        <input value="{{ ${'-'}->end->value ?? '' }}" {!! ${'-'}->end->attr !!}>
    @endif

    @if (${'-'}->fap !== null)
        <label class="mb-2 mr-lg-1">AP</label>
        <select {!! ${'-'}->fap->attr !!} binding="ap"></select>
    @endif

    @if (${'-'}->funit !== null)
        <label class="mb-2 mr-lg-1">UNIT</label>
        <select {!! ${'-'}->funit->attr !!} binding="unit"></select>
    @endif

    @if (${'-'}->tampil !== null)
        <button value="true" {!! ${'-'}->tampil->attr !!}>TAMPIL</button>
    @endif

    @if (${'-'}->excel !== null)
        <button {!! ${'-'}->excel->attr !!}>EXCEL</button>
    @endif
</form>

<script type="module">
    (() => {
        const $ = window.jQuery;
        const _fap = @json(${'-'}->fap->value ?? '');
        const _funit = @json(${'-'}->funit->value ?? '');

        $(document).ready(() => {
            const list_unit = @json(${'-'}->listunit);
            const akses_all = Object.keys(list_unit).length > 1;

            const funit = $('select[binding="unit"]');

            $('select[binding="ap"]')
                .html([
                    ...[...(akses_all ? [$('<option>', {
                        value: ''
                    }).text('SEMUA')] : [])],
                    ...Object.keys(list_unit).map(ap => $('<option>', {
                        value: ap
                    }).text(ap)),
                ])
                .on('change', e => {
                    const _ap = $(e.currentTarget).val().trim();
                    funit
                        .html([
                            ...[$('<option>', {
                                value: ''
                            }).text('SEMUA')],
                            ...(
                                _ap === '' ?
                                Object.values(list_unit).flat() :
                                list_unit[$(e.currentTarget).val().trim()]
                            )
                            .map(unit => $('<option>', {
                                value: unit
                            }).text(unit)),
                        ])
                        .val(_funit)
                        .trigger('change');
                })
                .val(_fap)
                .trigger('change');
        });
    })();
</script>
