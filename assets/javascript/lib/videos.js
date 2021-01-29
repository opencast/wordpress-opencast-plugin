$(function() {
    if ($('.oc-admin-video-list').is(':visible')) {
        let video_list_main_container = $('.oc-admin-video-list');
        let ajax_url = video_list_main_container.data('ajaxurl');

        //limit
        var limit_box = video_list_main_container.find('p.limit-box');
        var limit_btn = limit_box.find('input#limit-submit');
        limit_btn.on('click', function(e) {
            e.preventDefault();
            var limit_value = $(this).prev().val();
            if (limit_value) {
                $.ajax({
                    url: ajax_url,
                    type : 'POST',
                    data : {
                        "action"        : 'opencast_video_save_limit_ajax',
                        "oc_table_limit": limit_value
                    },
                    success: function( resp ) {
                        if (resp.success) {
                            var urlParams = new URLSearchParams(window.location.search);
                            if (urlParams.has('paged')) {
                                urlParams.delete('paged');
                            }
                            var new_url = window.location.href.split('?')[0] + '?' + urlParams.toString().replace('?', '');
                            reloadPage(new_url);
                        }
                    }
                });
            }
        });

        //Search
        var search_box = video_list_main_container.find('p.search-box');
        
        var search_btn = search_box.find('input#search-submit');
        search_btn.on('click', function(e) {
            e.preventDefault();
            var search_value = encodeURIComponent($(this).prev().val().trim());
            if (search_value) {
                var urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('oc_search')) {
                    urlParams.set('oc_search', search_value);
                } else {
                    urlParams.append('oc_search', search_value);
                }
                if (urlParams.has('paged')) {
                    urlParams.set('paged', 1);
                } else {
                    urlParams.append('paged', 1);
                }

                var search_url = window.location.href.split('?')[0] + '?' + urlParams.toString().replace('?', '');
                reloadPage(search_url);
            }
        });
        var search_clear_btn = search_box.find('a#search-clear');
        search_clear_btn.on('click', function(e) {
            e.preventDefault();
            var input_element = $(this).parent().find('input[type="text"]');
            if (input_element) {
                input_element.val('');
            }
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('oc_search')) {
                urlParams.delete('oc_search');
            }
            if (urlParams.has('paged')) {
                urlParams.set('paged', 1);
            }
            var search_url = window.location.href.split('?')[0] + '?' + urlParams.toString().replace('?', '');
            reloadPage(search_url);
        });
        
        //Actions
        /* DELETE SINGLE */
        $('a.oc-admin-video-delete').on('click', function(e) {
            e.preventDefault();
            var video_id = $(this).data('id');
            if (video_id !== undefined) {
                deleteVideo(ajax_url, new Array(video_id));
            }
        });
        /* BULK action */
        $('input#doaction2, input#doaction').on('click', function(e) {
            var action = $(this).prev().val();
            if (action == 'delete') {
                var to_be_deleted = new Array();
                $('input[type="checkbox"].oc-cb-select:checked').each((index, elm) => {
                    var id = $(elm).data('id');
                    if (id) {
                        to_be_deleted.push(id);
                    }
                });
                deleteVideo(ajax_url, to_be_deleted);
            }
        });

        $('input[type="checkbox"].oc-cb-select:checked').each((index, elm) => {
            $(elm).prop('checked', false);
        });
    }
});
function deleteVideo(ajax_url, video_ids) {
    if (Array.isArray(video_ids) && video_ids.length) {
        Swal.fire({
            title: delete_confirm_data.title,
            text: delete_confirm_data.text,
            icon: 'warning',
            showCancelButton: true,
            showCloseButton: true,
            confirmButtonColor: 'green',
            cancelButtonColor: 'red',
            confirmButtonText: delete_confirm_data.confirm_btn,
            cancelButtonText: delete_confirm_data.cancel_btn,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: ajax_url,
                    method: "POST",
                    async: true,
                    data : {
                        "action": 'opencast_video_delete_ajax',
                        "videos": video_ids
                    },
                }).fail(function(err) {
                    Swal.showValidationMessage(
                        `Request failed: ${err.statusText}`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading(),
        }).then((result) => {
            if (result.value) {
                console.log(result.value);
                var title = delete_confirm_data.result_success_title;
                var msg = delete_confirm_data.result_success_msg;
                var icon = 'success';
                var reload =  true;
                if (result.value.success) {
                    
                    if (result.value.success.notdeleted) {
                        msg = delete_confirm_data.result_success_partial_msg;
                        icon = 'warning';
                        var notdeleted_list = new Array();
                        result.value.success.notdeleted.forEach((id, index) => {
                            var link = $(`a[data-id='${id}']`);
                            if (link) {
                                var title_elm = link.parent().parent().find('td.title');
                                var title = title_elm.text();
                                notdeleted_list.push(`Video Title: ${title}`);
                            }
                        });
                        if (notdeleted_list.length) {
                            msg += '<br>' + notdeleted_list.join('<br>');
                        }
                    }
                    
                } 
                if (result.value.error) {
                    title = delete_confirm_data.result_error_title;
                    reload = false;
                    icon = 'error';
                    msg = result.value.error;
                }
                Swal.fire(
                    title,
                    msg,
                    icon
                ).then(() => {
                    if (reload) {
                        reloadPage();
                    }
                });
            }
        })
    }
}
function reloadPage(new_url = '') {
    if (new_url) {
        location.href = new_url;
    } else {
        window.location.reload();
    }
}