jQuery(function($){

    $('#edbm-export').on('click', function(){

        let offset = 0;

        function run(){
            $.post(edbm_ajax.ajax_url, {
                action: 'edbm_export',
                offset: offset,
                _ajax_nonce: edbm_ajax.nonce
            }, function(resp){

                $('#edbm-export-progress').text('Export offset: ' + offset);

                if(resp.more){
                    offset = resp.next_offset;
                    run();
                } else {
                    $.post(edbm_ajax.ajax_url, {
                        action: 'edbm_export_zip',
                        _ajax_nonce: edbm_ajax.nonce
                    }, function(zip){
                        window.location = zip.url;
                    });
                }
            });
        }

        run();
    });


   

    // --- RESTORE ---
    $('#edbm-restore').on('click', function(){

        let fileInput = $('#edbm-file')[0];
        if(fileInput.files.length === 0){
            alert('Selecteer eerst een SQL-bestand!');
            return;
        }

        let formData = new FormData();
        formData.append('action', 'edbm_restore');
        formData.append('_ajax_nonce', edbm_ajax.nonce);
        formData.append('file', fileInput.files[0]);
        formData.append('search', $('#edbm-search').val() || '');
        formData.append('replace', $('#edbm-replace').val() || '');

        $('#edbm-restore-progress').text('Restore gestart...');

        $.ajax({
            url: edbm_ajax.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp){
                if(resp.done){
                    $('#edbm-restore-progress').text('Import voltooid!');
                    alert('Database import succesvol afgerond.');
                } else {
                    $('#edbm-restore-progress').text('Er is iets misgegaan tijdens de import.');
                }
            },
            error: function(){
                $('#edbm-restore-progress').text('Fout tijdens import!');
            }
        });
    });



});
