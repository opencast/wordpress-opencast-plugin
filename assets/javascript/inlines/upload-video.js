window.$j = window.jquery = window.$ = jQuery.noConflict();
Dropzone.autoDiscover = false;
window.OcUploadConnected = false;
$(function() {
    $('div.oc-upload-box > form#ingestForm').each(function () {
        var action = $(this).attr('action');
        
        var dropzone = $(this).find('div.dropzone');
        var config = $(dropzone).data('config');
        var dropzoneID = $(dropzone).attr('id');

        var ocDropzone = new Dropzone(`#${dropzoneID}`, {
            url: action,
            maxFiles: 1,
            maxFilesize: config.maxFilesize,
            dictDefaultMessage: config.dictDefaultMessage,
            acceptedFiles: config.acceptedFiles,
            addRemoveLinks: true,
            timeout: 1200000,
            autoProcessQueue: false,
        });

        ocDropzone.on("addedfile", function(file) { 
            dropzone.removeClass('has-error');
        });

        var submitBtn = $(this).find('input[type="submit"]');
        submitBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!OcUploadConnected) {
                var lti_form = $(this).parent().siblings('form#OCLtiLaunchForm');
                submitUploadLtiForm(lti_form, $(this));
                return false;
            }

            var success_text = $(this).data('success');
            var fail_text = $(this).data('fail');

            var url = $(this).parent().attr('action');
            var title_input = $(this).parent().find('input[name="title"]');
            title_input.removeClass('has-error disabled');
            var title = title_input.val();

            var creator_input = $(this).parent().find('input[name="creator"]');
            creator_input.removeClass('has-error disabled');
            var creator = creator_input.val();

            var flavor = $(this).parent().find('input[name="flavor"]').val();
            var isPartOf = $(this).parent().find('input[name="isPartOf"]').val();
            var workflowId = $(this).parent().find('input[name="workflowId"]').val();
            var oc_acl = $(this).parent().find('input[name="acl"]').val();

            var terms = {
                "title": title,
                "creator": creator,
                "isPartOf": isPartOf,
                "oc_acl": decodeURIComponent(oc_acl).replace(/\+/g," ")
            };

            var dropzone_div = $(this).parent().find('div.dropzone');
            dropzone_div.removeClass('has-error');

            if (ocDropzone.getQueuedFiles().length > 0 && title.trim() && creator.trim() && OcUploadConnected) {
                var files = ocDropzone.getQueuedFiles();
                title_input.addClass('disabled');
                title_input.prop('disabled', true);
                creator_input.addClass('disabled');
                creator_input.prop('disabled', true);
                $(this).prop('disabled', true);
                $(this).addClass('disabled');
                dropzone_div.addClass('disabled');
                $(".dz-hidden-input").prop("disabled",true);
                var that = $(this);
                $(this).parent().find('div.oc-message').hide();
                $(this).parent().find('div.oc-progress').show();
                files.forEach(file => {
                    upload(url, file, terms, flavor, workflowId)
                    .done(function (_data, _status, xhr) {
                        ocDropzone.removeFile(file);
                        clearUploadProccess(title_input, creator_input,  that, dropzone_div, success_text, 'green')
                    })
                    .fail(function (xhr, status, error) {
                        ocDropzone.removeFile(file);
                        clearUploadProccess(title_input, creator_input, that, dropzone_div, fail_text, 'red')
                    });
                });
            } else {                       
                if (!title.trim()) {
                    $(title_input).addClass('has-error');
                }
                if (!creator.trim()) {
                    $(creator_input).addClass('has-error');
                }
                if (ocDropzone.getQueuedFiles().length == 0) {
                    $(dropzone_div).addClass('has-error');
                }
                ocDropzone.uploadFiles([]);
            }
        });
        $(this).find('input[type="text"]').keyup(function() {
            if ($(this).val().trim()) {
                $(this).removeClass('has-error');
            }
        });
    });
});
function clearUploadProccess(title_input, creator_input, that, dropzone_div, text, color) {
    title_input.val('');
    title_input.removeClass('disabled has-error');
    title_input.trigger('blur');
    title_input.prop('disabled', false);
    creator_input.val('');
    creator_input.removeClass('disabled has-error');
    creator_input.trigger('blur');
    creator_input.prop('disabled', false);
    that.prop('disabled', false);
    that.removeClass('disabled');
    dropzone_div.removeClass('disabled');
    $(".dz-hidden-input").prop("disabled",false);
    that.parent().find('div.oc-message').css('color', color).text(text).show();
    that.parent().find('div.oc-progress').hide();
    that.parent().find('div.oc-progress > span.loader-progress').text('0');
}
function submitUploadLtiForm(lti_form, elm) {
    if (lti_form) {
        lti_form.on('submit', function(e) {
            e.preventDefault();
            var ocurl = decodeURIComponent($(this).attr("action"));
            $.ajax({
                url: ocurl,
                crossDomain: true,
                type: 'post',
                xhrFields: {withCredentials: true},
                data: $(lti_form).serialize(),
                complete: function () {
                    OcUploadConnected = true;
                    elm.trigger('click');
                }
            });
        });
        lti_form.trigger('submit');
    }
}

function upload(serviceUrl, file, terms, flavor, workflowId) {
    return createMediaPackage(serviceUrl)
        .then(function (_mediaPackage, _status, resp) {
            return addDCCatalog(serviceUrl, resp.responseText, terms)
        })
            .then(function (_mediaPackage, _status, resp) {
                var acl = terms.oc_acl;
                return addACL(serviceUrl, resp.responseText, acl)
            })
                .then(function (_mediaPackage, _status, resp) {
                        return addTrack(serviceUrl, resp.responseText, file, flavor)
                })
                    .then(function (mediaPackage, _status, resp) {
                        return finishIngest(serviceUrl, resp.responseText, workflowId)
                    })
}


function createMediaPackage(serviceUrl) {
    return $.ajax({
        url: serviceUrl + "/createMediaPackage",
        xhrFields: { withCredentials: true },
    });
}
function createDCCatalog(terms) {
    var escapeString = function (string) {
        return new XMLSerializer().serializeToString(new Text(string));
    };

    return '<?xml version="1.0" encoding="UTF-8"?>' +
        '<dublincore xmlns="http://www.opencastproject.org/xsd/1.0/dublincore/"' +
        '            xmlns:dcterms="http://purl.org/dc/terms/"' +
        '            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' +
        '<dcterms:creator>' + escapeString(terms.creator) + '</dcterms:creator>' +
        '<dcterms:title>'+ escapeString(terms.title) + '</dcterms:title>' +
        '<dcterms:isPartOf>'+ escapeString(terms.isPartOf) + '</dcterms:isPartOf>' +
        '</dublincore>';
}
function addDCCatalog(serviceUrl, mediaPackage, terms) {
    // Prepare meta data
    var episodeDC = createDCCatalog(terms);

    return $.ajax({
        url: serviceUrl + "/addDCCatalog",
        method: "POST",
        data: {
            mediaPackage: mediaPackage,
            dublinCore: episodeDC,
            flavor: 'dublincore/episode'
        },
        xhrFields: { withCredentials: true },
    })
}
function addACL(serviceUrl, mediaPackage, acl) {
    var acldata = new FormData();
    acldata.append('mediaPackage', mediaPackage);
    acldata.append('flavor', 'security/xacml+episode');
    acldata.append('BODY', new Blob([acl]), 'acl.xml');

    return $.ajax({
        url: serviceUrl + "/addAttachment",
        method: "POST",
        data: acldata,
        processData: false,
        contentType: false,
        xhrFields: { withCredentials: true },
    })
}

function addTrack(serviceUrl, mediaPackage, file, flavor) {
    var data = new FormData();
    data.append('mediaPackage', mediaPackage);
    data.append('flavor', flavor);
    data.append('tags', '');
    data.append('BODY', file, file.name);

    return $.ajax({
        xhr: function()
        {
            var xhr = new window.XMLHttpRequest();
            //Upload progress
            xhr.upload.addEventListener("progress", function(evt){
                if (evt.lengthComputable) {
                    var percentComplete = parseInt((evt.loaded / evt.total) * 100);
                    $('div.oc-progress > span.loader-progress').text(percentComplete);
                }
            }, false);
            return xhr;
        },
        url: serviceUrl + "/addTrack",
        method: "POST",
        data: data,
        processData: false,
        contentType: false,
        xhrFields: { withCredentials: true },
        timeout: 0
    });
}
function finishIngest(serviceUrl, mediaPackage, workflowId) {
    return $.ajax({
        url: serviceUrl + "/ingest/" + workflowId,
        method: "POST",
        data: {
            "mediaPackage": mediaPackage,
        },
        xhrFields: { withCredentials: true },
    });
}

