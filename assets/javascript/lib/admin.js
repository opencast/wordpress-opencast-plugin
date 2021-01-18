window.urlParams = new URLSearchParams(window.location.search);
$(document).ready(function() {
    //Select 2
    $('.oc-select2').each(function(){
        $(this).select2({
            placeholder: $(this).attr('placeholder'),
            allowClear: true,
            tags: true,
            maximumSelectionSize: $(this).data('maxsize'),
            width: 'resolve'
          });
    });

    //Tabs
    var active_li = $('.oc-admin-wrapper ul.nav-tabs li.active');
    var active_tabpane = $('.oc-admin-wrapper .tab-pane.active');
    if (!active_li.length && !active_tabpane.length) {
        $('.oc-admin-wrapper ul.nav-tabs li').first().addClass('active');
        $('.oc-admin-wrapper .tab-pane').first().addClass('active');
    }
    $('.oc-admin-wrapper ul.nav-tabs > li').each(function() {
        if (urlParams.has('activetab')) {
            var activetab = urlParams.get('activetab');
            $('.oc-admin-wrapper ul.nav-tabs li.active').removeClass('active');
            $('.oc-admin-wrapper .tab-pane.active').removeClass('active');
            $(`#${activetab}`).addClass('active');
            if ($(this).find(`a[href="#${activetab}"]`)) {
                $(this).addClass('active');
            }
        }
        $(this).click(function(e){
            e.preventDefault();
            $('.oc-admin-wrapper ul.nav-tabs li.active').removeClass('active');
            $('.oc-admin-wrapper .tab-pane.active').removeClass('active');

            $(this).addClass('active');

            var activePaneID = $(this).find('a').attr('href');
            $(activePaneID).addClass('active');
            
            $('input[type="hidden"]#activetabpane').val(activePaneID.replace('#',''));
        });
    });

    //Trigger Disbaled
    $('input.trigger-disabled-parent').each(function() {
        hanldetriggerdisabled(this);
        $(this).click(function(e) {
            hanldetriggerdisabled(this);
        });
        function hanldetriggerdisabled(elm) {
            var to_be_activated = $(elm).prop('checked');
            var child = $(elm).data('child');
            $(`#${child}`).toggleClass('disabled', !to_be_activated);
            $(`#${child}`).attr('disabled', !to_be_activated);
        }
    });

    //Single Episodes
    let ajax_url = $('div.oc-admin-se-list').data('ajaxurl');
    // clear
    $('div.oc-admin-se-list').find('input#_wpnonce').remove();
    $('div.oc-admin-se-list').find('input[name="_wp_http_referer"]').remove();
    //Edit
    $('a.oc-admin-se-edit').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var se_table = $(this).parents('table.singleepisodes');
        var roles = $('input[type="hidden"]#_wprls').val();
        roles = JSON.parse(window.atob(roles));
        if (id && se_table && roles) {
            //Gathering Infos
            var edit_data = new Array();
            se_table.find('thead tr th').each((i, td) => {
                var id = $(td).attr('id');
                if (id && id != 'cd' && id != 'name' && id != 'actions') {
                    edit_data.push({"id": id, "displayname": $(td).text()});
                }
            });
            $.each(edit_data, (key, obj) => {
                se_table.find(`input[type="hidden"][data-id="${id}"][data-key="${obj.id}"].hidden-values`).each((i, input) => {
                    var value = $(input).val();
                    if (obj.id == 'usepermissions') {
                        edit_data[key].value = ((value == 1) ? true : false);
                    } else if (obj.id == 'permissions') {
                        if (Object.keys(obj).includes('value')) {
                            var values = obj.value;
                            values.push(value);
                        } else {
                            var values = new Array();
                            values.push(value);
                        }
                        obj.value = values;
                    } else {
                        edit_data[key].value = value;
                    }
                });
            });
            Swal.fire({
                html: '<table id="swal-table" style="padding: 30px" class="form-table opencast-option-table" role="presentation"><tbody></tbody></table>',
                showCloseButton: true,
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: update_se_dialog_data.save_btn,
                cancelButtonText: update_se_dialog_data.cancel_btn,
                showLoaderOnConfirm: true,
                onBeforeOpen: () => {
                    var swal_table = $('#swal2-content').find('#swal-table');
                    if (swal_table) {
                        $.each(edit_data, (key, obj) => {
                            var input_div = $("<div class='input'></div>");
                            var tr = $('<tr></tr>');
                            var th = $(`<th scope="row"><label>${obj.displayname}</label></th>`);
                            tr.append(th);
                            var td = $('<td></td>');
                            if (obj.id == 'oc_id' || obj.id == 'class' ) {
                                var input = $(`<input type='text' class='regular-text' name='${obj.id}' value='${obj.value}'>`);
                                td.append(input_div.append(input));
                                tr.append(td);
                            } else if (obj.id == 'usepermissions') {
                                var input = $(`<input type='checkbox' data-child='permissions' class='regular-checkbox swal-checkbox' name='${obj.id}'>`);
                                input.prop('checked', obj.value);
                                td.append(input_div.append(input));
                                tr.append(td);
                            } else if (obj.id == 'permissions') {
                                var maxsize = roles.length;
                                var select = $(`<select id='${obj.id}-swal' placeholder='${obj.displayname}' data-maxsize=${maxsize} multiple='multiple' class="oc-select2 swal-select2 trigger-disabled-child disabled"></select>`);
                                $.each(roles, (rkey, rvalue) => {
                                    var option = $(`<option value='${rkey}'>${rvalue}</option>`);
                                    if (obj.value && obj.value.includes(rkey)) {
                                        option.prop('selected', true);
                                    }  
                                    select.append(option);
                                });
                                
                                td.append(input_div.append(select));
                                tr.append(td);
                            }
                            swal_table.append(tr);
                            swal_table.find('.swal-checkbox').each(function(i, input){
                                hanldetriggerdisabled(input);
                                $(input).click(function(e) {
                                    hanldetriggerdisabled($(this));
                                });
                                function hanldetriggerdisabled(elm) {
                                    var to_be_activated = $(elm).prop('checked');
                                    var child = $(elm).data('child');
                                    $(`#${child}-swal`).toggleClass('disabled', !to_be_activated);
                                    $(`#${child}-swal`).attr('disabled', !to_be_activated);
                                }
                            });
                            swal_table.find('.oc-select2').each(function(){
                                $(this).select2({
                                    placeholder: $(this).attr('placeholder'),
                                    allowClear: true,
                                    tags: true,
                                    maximumSelectionSize: $(this).data('maxsize'),
                                    width: 'resolve'
                                  });
                            });
                        });
                    }
                },
                preConfirm: () => {
                    var swal_table = $('#swal2-content').find('#swal-table');
                    if (swal_table) {
                        var oc_id = swal_table.find('input[type="text"][name="oc_id"]').val();
                        var cls = swal_table.find('input[type="text"][name="class"]').val();
                        var usepermissions = swal_table.find('input[type="checkbox"][name="usepermissions"]').prop('checked');
                        var permissions = swal_table.find('select.swal-select2').val();
                        return $.ajax({
                            url: ajax_url,
                            method: "POST",
                            async: true,
                            data : {
                                "action": 'update_se_ajax',
                                "se_id": id,
                                "oc_id": oc_id,
                                "class": cls,
                                "usepermissions": usepermissions,
                                "permissions": permissions,
                            },
                        }).fail(function(err) {
                            Swal.showValidationMessage(
                                `Request failed: ${err.statusText}`
                            );
                        });
                    }
                },
                allowOutsideClick: () => !Swal.isLoading(),
            }).then((result) => {
                if (result.value) {
                    var msg = update_se_dialog_data.result_success_msg;
                    var icon = 'success';
                    var reload =  true;
                    if (result.value.error) {
                        msg = update_se_dialog_data.result_error_msg;
                        icon = 'warning'
                    }
                    Swal.fire({
                        text: msg,
                        icon: icon
                    }).then(() => {
                        if (reload) {
                            reloadGeneralPage();
                        }
                    });
                }
            });

        }
    });
    //Delete
    $('a.oc-admin-se-delete').click(function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        if (id) {
            deleteSingleEpisode(ajax_url, new Array(id));
        }
    });
    //Bulkactions
    $('div.oc-admin-se-list').find('.bulkactions').each(function (index, elm) {
        $(elm).find('select').each(function(i, e){
            $(e).removeAttr('name');
            $(e).addClass('oc-admin-se-bulkaction');
        });
        $(elm).find('input[type="submit"]').each(function(i, e){
            $(e).click(function(e) {
                e.preventDefault();
                e.stopPropagation()
                var action = $(this).prev().val();
                if (action == 'delete') {
                    var to_be_deleted = new Array();
                    $('input[type="checkbox"].oc-cb-se-select:checked').each((index, elm) => {
                        var id = $(elm).data('id');
                        if (id) {
                            to_be_deleted.push(id);
                        }
                    });
                    deleteSingleEpisode(ajax_url, to_be_deleted);
                }
            });
        });
    });
});
function deleteSingleEpisode(ajax_url, episode_ids) {
    if (Array.isArray(episode_ids) && episode_ids.length) {
        Swal.fire({
            title: delete_se_confirm_data.title,
            text: delete_se_confirm_data.text,
            icon: 'warning',
            showCancelButton: true,
            showCloseButton: true,
            confirmButtonColor: 'green',
            cancelButtonColor: 'red',
            confirmButtonText: delete_se_confirm_data.confirm_btn,
            cancelButtonText: delete_se_confirm_data.cancel_btn,
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: ajax_url,
                    method: "POST",
                    async: true,
                    data : {
                        "action": 'delete_se_ajax',
                        "se_ids": episode_ids
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
                var title = delete_se_confirm_data.result_success_title;
                var msg = delete_se_confirm_data.result_success_msg;
                var icon = 'success';
                var reload =  true;
                if (result.value.success) {
                    if (result.value.success.notdeleted) {
                        msg = delete_se_confirm_data.result_success_partial_msg;
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
                    title = delete_se_confirm_data.result_error_title;
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
                        reloadGeneralPage();
                    }
                });
            }
        })
    }
}
function reloadGeneralPage(new_url = '') {
    if (new_url) {
        location.href = new_url;
    } else {
        var url_reload = window.location.href.split('?')[0] + '?' + urlParams.toString().replace('?', '');
        location.href = url_reload;
    }
}