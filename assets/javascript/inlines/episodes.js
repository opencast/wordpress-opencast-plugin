window.$j = window.jquery = window.$ = jQuery.noConflict();
if (typeof window.OcConnected === 'undefined') {
    window.OcConnected = false;
}
$(document).ready(function() {
    $('.opencast-episodes-container').each(function(){
        var contents = $(this).find('a.episode');
        //Pagination
        var totalNumber = $(this).data('total');
        var pageSize = $(this).data('limit');
        if (contents.length && pageSize && pageSize < totalNumber) {
            contents.remove();
            $(this).pagination({
                dataSource: contents.toArray(),
                locator: 'data',
                totalNumber: totalNumber,
                pageSize: pageSize,
                autoHidePrevious: true,
                autoHideNext: true,
                callback: function(data, pagination) {
                    var main_div = pagination.el.parent();
                    main_div.find('a.episode').remove();
                    $(data).each((index, episode)=> {
                        $(episode).on('click', function(event){
                            var playersrc = $(this).data('playersrc');
                            if (playersrc) {
                                firePlayer(event, playersrc);
                            }
                        })
                    });
                    main_div.prepend(data);
                }
            });
        }
    });
});
function submitLtiForm(lti_form) {
    if (lti_form) {
        lti_form.submit(function(e) {
            e.preventDefault();
            var ocurl = decodeURIComponent($(this).attr("action"));
            $.ajax({
                url: ocurl,
                crossDomain: true,
                type: 'post',
                xhrFields: {withCredentials: true},
                data: $(lti_form).serialize(),
                complete: function () {
                    OcConnected = true;
                }
            });
        });
        lti_form.submit();
    }
}
function firePlayer(e, playersrc) {
    e.preventDefault();
    var clicked = e.target;
    var target = $(e.target);
    if (clicked.nodeName != 'A') {
        target = $(target).parent();
    }
    $(target).blur();
    if (!OcConnected) {
        var lti_form = $(target).parent().find('form#OCLtiLaunchForm');
        submitLtiForm(lti_form);
    }
    Swal.fire({
        html:
            "<iframe src='" + playersrc + "' style='width:90%; height: 455px;' class='oc-player' allowfullscreen='true'></iframe>",
        showCloseButton: true,
        showConfirmButton: false,
        width: '75%',
    })
}