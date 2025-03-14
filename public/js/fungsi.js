function appendNotifikasi(judul, isi, href, peringatan = false) {
    $('#show-notif').append(`
        <a href="${href}" class="dropdown-item">
            <div class="d-flex flex-column">
                <div class="d-flex justify-content-between">
                    <span class="font-weight-bold">${judul}</span>
                    ${peringatan ? '<span class="text-danger"><i class="cil-warning"></i></span>' : ''}
                </div>
                <span>${isi}</span>
            </div>
        </a>
    `);
}

$.get('/notifikasi')
.done(function(res){
    if (res?.data?.count > 0) {
        if (res?.data?.count > 9) {
            $('#dropdown-notifikasi').find('#count-notifikasi').text('9+');
        } else {
            $('#dropdown-notifikasi').find('#count-notifikasi').text(res?.data?.count);
        }
    }
    if (res?.data?.count > 0) {
        res.data.need_approval.map(function(item) {
            appendNotifikasi(item.title, item.body, item.href, true);
        });
        res.data.notif.map(function(item) {
            appendNotifikasi(item.title, item.body, item.href);
        });
    } else {
        $('#show-notif').append(`<span class="dropdown-item">Tidak ada notifikasi</span>`);
    }
})
.fail(function(err){
    $('#show-notif').append(`<span class="dropdown-item">Gagal memuat notifikasi</span>`);
});

$.fn.maksTotal = function(options) {
    var settings = $.extend({}, options);
    return this.each(function() {
        var $input = $(this);
        var $elements = $(settings.class);
        var maxval = settings.maxval || 100;
        var minval = settings.minval || 0;
        var allowedval = maxval;
        $input.on('input', function() {
            $input.val() > maxval ? $input.val(maxval) : null;
            $input.val() < minval ? $input.val(minval) : null;
            if (/^0[0-9]+$/.test($input.val())) {
                if ($input.val() == "0") {
                    $input.val(0);
                } else {
                    $input.val(parseInt($input.val()));
                }
            }
            $input.val($input.val().replace(/^-/, ''));
            var total = 0;
            $elements.each(function() {
                var value = parseInt($(this).val()) || 0;
                total += value;
            });
            allowedval = maxval - (total - (parseInt($input.val()) || 0));
            $input.val() > allowedval ? $input.val(allowedval) : null;
        });
    });
};
