<script>
    $(document).ready(function() {
        $("#kriteria_id").select2({
			placeholder: "-- PILIH KRITERIA --",
            theme: 'bootstrap4'
        });

        $.ajax({
			type: "GET",
			url: "<?= base_url('kriteria/options/') ?>",
			success: function(data){
				var opts = $.parseJSON(data);
				$.each(opts, function(i, d) {
					$("#kriteria_id").append('<option value="'+d.id+'">'+d.text+'</option>');
				});
			}
        });

        $("#subkriteria_id").select2({
			placeholder: "-- PILIH SUBKRITERIA --",
            theme: 'bootstrap4'
        });

        $.ajax({
			type: "GET",
			url: "<?= base_url('subkriteria/options/') ?>",
			success: function(data){
				var opts = $.parseJSON(data);
				$.each(opts, function(i, d) {
					$("#subkriteria_id").append('<option value="'+d.id+'">'+d.text+'</option>');
				});
			}
        });

        $("#rekomendasi_id").select2({
			placeholder: "-- PILIH REKOMENDASI --",
            theme: 'bootstrap4'
        });

        $.ajax({
			type: "GET",
			url: "<?= base_url('rekomendasi/options/') ?>",
			success: function(data){
				var opts = $.parseJSON(data);
				$.each(opts, function(i, d) {
					$("#rekomendasi_id").append('<option value="'+d.id+'">'+d.text+'</option>');
				});
			}
        });

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            onBeforeOpen: () => {
                $('#ModalForm').modal('hide');
                $('#dtTable').DataTable().ajax.reload(null,false);
            }
        });
        $.fn.dataTableExt.oApi.fnPagingInfo = function(oSettings)
        {
            return {
                "iStart": oSettings._iDisplayStart,
                "iEnd": oSettings.fnDisplayEnd(),
                "iLength": oSettings._iDisplayLength,
                "iTotal": oSettings.fnRecordsTotal(),
                "iFilteredTotal": oSettings.fnRecordsDisplay(),
                "iPage": Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
                "iTotalPages": Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
            };
        };
 
        var table = $("#dtTable").dataTable({
            initComplete: function() {
                var api = this.api();
                $('#dtTable_filter input').off('.DT').on('input.DT', function() { api.search(this.value).draw(); });
            },
            oLanguage: {
                sProcessing: "loading..."
            },
            processing: true,
            serverSide: true,
            ajax: {
                "url": "<?= base_url('rekomendasi_nilai/list_data_akademik') ?>", 
                "type": "POST"
            },
            responsive: true,
            pageLength: 5,
            columns: [
                // {"data": "no", render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }},
                {"data": "kriteria_nama"},
                {"data": "subkriteria_nama"},
                {"data": "rekomendasi_kode"},
                {"data": "rekomendasi_nilai_bobot"},
                {"data": "view"}
            ],
            order: [[0, 'asc']],
            ordering: false,
            rowCallback: function(row, data, iDisplayIndex) {
                var info = this.fnPagingInfo();
                var page = info.iPage;
                var length = info.iLength;
                $('td:eq(0)', row).html();
            }
        });

        var table = $("#dtTable1").dataTable({
            initComplete: function() {
                var api = this.api();
                $('#dtTable_filter input').off('.DT').on('input.DT', function() { api.search(this.value).draw(); });
            },
            oLanguage: {
                sProcessing: "loading..."
            },
            processing: true,
            serverSide: true,
            ajax: {
                "url": "<?= base_url('rekomendasi_nilai/list_data_non_akademik') ?>", 
                "type": "POST"
            },
            responsive: true,
            pageLength: 5,
            columns: [
                // {"data": "no", render: function (data, type, row, meta) { return meta.row + meta.settings._iDisplayStart + 1; }},
                {"data": "kriteria_nama"},
                {"data": "subkriteria_nama"},
                {"data": "rekomendasi_kode"},
                {"data": "rekomendasi_nilai_bobot"},
                {"data": "view"}
            ],
            order: [[0, 'asc']],
            ordering: false,
            rowCallback: function(row, data, iDisplayIndex) {
                var info = this.fnPagingInfo();
                var page = info.iPage;
                var length = info.iLength;
                $('td:eq(0)', row).html();
            }
        });

        $("#Frm").submit(function(e) {
            event.preventDefault();
            Pace.track(function() {
                jQuery.ajax({
                    type: "POST",
                    url: "<?= base_url('rekomendasi_nilai/simpan/') ?>",
                    data: $("#Frm").serialize(),
                    success: function(response) {
                        var data = JSON.parse(response);
                        Toast.fire({
                            type: data.kode,
                            title: data.pesan
                        });
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        Toast.fire({
                            type: 'error',
                            title: 'Terjadi kesalahan! kode : ' + xhr.status + ', ' + thrownError + '. Silahkan periksa kembali form isian!'
                        });
                    }
                });
            });
        });

        $(document).on('click', '#edit', function() {
            $('#ModalForm').modal({backdrop: 'static', keyboard: false})  
            jQuery.ajax({
                type: "POST",
                url: "<?= base_url('rekomendasi_nilai/get_data/') ?>",
                dataType: 'json',
                data: {
                    rekomendasi_nilai_id: $(this).attr("data")
                },
                success: function(data) {
                    $.each(data, function(key, value) {
                        var ctrl = $('[name=' + key + ']', $('#Frm'));
                        switch (ctrl.prop("type")) {
                            case "select-one":
                                ctrl.val(value).change();
                                break;
                            default:
                                ctrl.val(value);
                        }
                    });
                }
            });
        });

        $(document).on('click', '#hapus', function() {
            Swal.fire({
                title: 'Apakah Anda Yakin Ingin Menghapus Data Ini?',
                text: 'Harap di perhatikan, data yang anda hapus tidak dapat di kembalikan!',
                type: 'question',
                showCloseButton: true,
                confirmButtonText: 'Ya, Saya Yakin!',
                allowOutsideClick: false,
                allowEscapeKey: false
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: "POST",
                        url: "<?= base_url('rekomendasi_nilai/hapus/') ?>",
                        data: {
                            rekomendasi_nilai_id: $(this).attr("data")
                        },
                        success: function(response) {
                            var data = JSON.parse(response);
                            Toast.fire({
                                type: data.kode,
                                title: data.pesan
                            });
                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            Toast.fire({
                                type: 'error',
                                title: 'Terjadi kesalahan! kode : ' + xhr.status + ', ' + thrownError + '. Silahkan tanyakan kepada support!'
                            });
                        }
                    })
                } else {
                    Toast.fire({
                        type: 'warning',
                        title: 'Hapus data di batalkan'
                    });
                }
            })
        });
    });
</script>