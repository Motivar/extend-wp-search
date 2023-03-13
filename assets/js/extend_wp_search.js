jQuery(document).ready(function() {

    liveSearch();
    extend_wp_search_auto_trigger();

    jQuery(document).on('click', '#more-results-button', function() {
        jQuery('#search-full-screen form#ewps-search-form #submit').trigger('click');
    });
    if (extend_wp_search_vars.trigger !== '') {
        jQuery(document).on('click', extend_wp_search_vars.trigger, function() {
            jQuery('body').toggleClass('full-screen-open');
            jQuery('body').toggleClass('full-screen-open-left');
        });
    }

});

function extend_wp_search_close_search() {
    jQuery('body').toggleClass('full-screen-open');
    jQuery('body').toggleClass('full-screen-open-left');
}

function liveSearch() {
    jQuery(document).on('keypress', 'input[name="searchtext"]', function(e) {

        if (e.which == 13) {
            e.preventDefault();
            extend_wp_search();
        }

    });

    /**
     * code for live search
     */
    var typingTimer; //timer identifier
    var doneTypingInterval = 249; //time in ms, 5 second for example
    var $input = jQuery('input[name="searchtext"]');

    //on keyup, start the countdown
    $input.on('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(extend_wp_search, doneTypingInterval);
    });

    //on keydown, clear the countdown 
    $input.on('keydown', function() {
        clearTimeout(typingTimer);
    });
}

function changeSearchContainer(wrap) {
    var id = jQuery(wrap).attr('id');
    var fullScreen = jQuery('body').hasClass('full-screen-open');
    var container = '#search-full-screen';
    if (!fullScreen) {
        container = 'body.page';
    }
    jQuery(container + ' #' + id).toggleClass('active');
    switch (id) {
        case 'filter-trigger':
            jQuery(container + ' #search_form_filter').toggleClass('active');
            jQuery(container + ' #search_form_resutls').toggleClass('active');

            break;
    }
}

function extend_wp_search_auto_trigger() {
    if (jQuery('body.page #search_form').attr('data-trigger') == 1) {
        extend_wp_search();
    }
}

function extend_wp_search() {
    var fullScreen = jQuery('body').hasClass('full-screen-open');
    var container = '#search-full-screen';

    if (!fullScreen) {
        container = 'body.page';


    }

    if (jQuery(container + ' #search_form_filter').hasClass('active')) {
        changeSearchContainer(jQuery(container + ' #filter-trigger'));
    }
    extend_wp_search_query(container);

}

function extend_wp_search_query(container) {

    var loading = container + ' #search-results';
    extend_wp_search_loading(loading, true),
        jQuery.ajax({
            type: "GET",
            async: true,
            cache: false,
            data: jQuery(container + ' #ewps-search-form').serializeArray(),
            url: awmGlobals.url + "/wp-json/extend-wp-search/search/",
            success: function(response) {
                extend_wp_search_loading(loading, false);
                jQuery(container + ' #search-results').html(response);
                jQuery(document).trigger('extend_wp_search_results');
            }
        });
}

function extend_wp_search_loading(div, action) {

    if (action) {
        jQuery(div).addClass('ewps-on-load');
        var html = jQuery("#ewps-loading").html();
        jQuery(div).html(html);
    } else {
        jQuery(div).removeClass('ewps-on-load');
        jQuery(div + " .loading-wrapper").fadeOut('slow');
    }
}

function disableCheckboxes() {
    var fullScreen = jQuery('body').hasClass('full-screen-open');
    var container = '#search-full-screen';
    if (!fullScreen) {
        container = 'body.page';
    }
    jQuery(container + ' #ewps-search-form input[type="checkbox"]').prop("checked", false);
    changeSearchContainer(jQuery(container + ' #filter-trigger'));
    extend_wp_search();
}

function newSearch() {
    var fullScreen = jQuery('body').hasClass('full-screen-open');
    var container = '#search-full-screen';
    if (!fullScreen) {
        container = 'body.page';
    }
    changeSearchContainer(jQuery(container + ' #filter-trigger'));
    extend_wp_search();
}