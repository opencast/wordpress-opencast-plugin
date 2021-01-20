window.$j = window.jquery = window.$ = jQuery.noConflict();
if (typeof window.OcConnected === 'undefined') {
    window.OcConnected = false;
}
$(function() {
    $('div.episode.oc-select-redirect').on('click', function(e) {
        e.preventDefault();
        var selectlink = $(this).data('selectlink');
        if (selectlink) {
            window.location.href = selectlink;
        }
    });
    $(".oc-list-direction a").on('click', function(e) {
        e.preventDefault();
        var offsets = $('.oc-searchable-list div.offset');
        var active_index = offsets.filter('.active').data('index');
        offsets.removeClass('active');
        var tobeshown = 1;
        if ($(this).hasClass('previous')) {
            tobeshown = ((active_index - 1) < 1 ? 1 : (active_index - 1));
        } else {
            tobeshown = ((active_index + 1) > offsets.length ? offsets.length : (active_index + 1));
        }
        $('.oc-searchable-list div.offset').filter(`[data-index='${tobeshown}']`).addClass('active');
    });
    $("button.oc-list-search-btn").on('click', function(e) {
        e.preventDefault();
        var text = $("input.oc-list-search-text").val();
        if (text != '' && text.length > 2) {
            oc_list_search(text)
        }
    });
    window.default_episodes = $(".offset").find(".episode");
    $("input.oc-list-search-text").on('keyup', function(e) {
        e.preventDefault();
        var text = $(this).val().toLocaleLowerCase().trim();

        var offsets = $(".offset");
        var episodes = offsets.find('.episode');

        if (text) {
            var searched_episodes = Array();
            episodes.each(function(i, elm) {
                var title = $(elm).find('.title').text().toLocaleLowerCase();
                var creator = $(elm).find('.creator').text().toLocaleLowerCase();
                if (title.includes(text) || creator.includes(text)) {
                    searched_episodes.push(i);
                }
            });
            if (searched_episodes.length > 0) {
                searched_episodes.forEach((s_episode, ei) => { 
                    var curr = episodes[ei];
                    var se = episodes[s_episode];
                    episodes[ei] = se;
                    episodes[s_episode] = curr;
                });
            } else {
                episodes = window.default_episodes;
            }
        } else {
            episodes = window.default_episodes;
        }
        var searched_rows_length = (episodes.length % 2 == 1) ? ((episodes.length + 1) / 2) : (episodes.length / 2);
        var searched_rows = Array();
        for (var sr = 0; sr < searched_rows_length; sr++) {
            var created_row = $('<div></div>').addClass('row');
            searched_rows.push(created_row);
        };
        var ri = 0;
        episodes.each(function(ei, s_episode) {
            if (searched_rows[ri].find('.episode').length == 2) {
                ri++;
            }
            searched_rows[ri].append(s_episode);
        });
        var searched_offsets_length = (searched_rows.length % 2 == 1) ? ((searched_rows.length + 1) / 2) : (searched_rows.length / 2);
        var searched_offsets = Array();
        for (var so = 0; so < searched_offsets_length; so++) {
            var created_offset = $('<div></div>').addClass('offset' + ((so == 0) ? ' active' : '')).attr('data-index', (so + 1));
            searched_offsets.push(created_offset);
        };
        var oi = 0;
        searched_rows.forEach((s_row, ri) => {
            if (searched_offsets[oi].find('.row').length == 2) {
                oi++;
            }
            searched_offsets[oi].append(s_row);
        });
        $('.oc-searchable-list > .offset').remove();
        $('.oc-searchable-list').append(searched_offsets);
    });

    $("div.oc-player-container").each(function() {
        var lti_form = $(this).find('form[name="OCLtiLaunchForm"]');
        var iframe = $(this).find('iframe.oc-player');
        if (lti_form) {
            if (OcConnected) {
                iframe.attr('src', iframe.data('playersrc'));
            } else {

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
                            iframe.attr('src', iframe.data('playersrc'));
                            OcConnected = true;
                        }
                    });
                });
                lti_form.trigger('submit');
            }
        }
    });
});