/* ===================================
 * Author: Nazarkin Roman
 * -----------------------------------
 * Contacts:
 * email - roman@nazarkin.su
 * icq - 642971062
 * skype - roman444ik
 * -----------------------------------
 * GitHub:
 * https://github.com/NazarkinRoman
 * ===================================
 */

$(document).ready(function () {

    $('#short_url').select(); // auto select short url

    // custom select`s
    if($.isFunction( $.fn.coreUISelect )) {
        $('select').coreUISelect({
            appendToBody : true
        });
    }

    // simulate :hover on mobile devices
    $('*').on('touchstart', function() {
        $(this)
            .mouseenter()
            .on('touchend', function() { $(this).mouseleave() });
    });

    if($('.flash_message').length > 0)
        setTimeout(function() {
            $('.flash_message').removeClass('fadeInRight').addClass('fadeOutRight');
            $('.flash_message').bind("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function () {
                $(this).remove();
            });
        }, 10000);

    /* params bar state */
    if(typeof $.cookie === 'function' && $.cookie('paramsBar_state') !== undefined)
    {
        if($.cookie('paramsBar_state') === 'visible') {
            $('#config_link').addClass('hover');
            $('#config_params').show();
        } else {
            $('#config_link').removeClass('hover');
            $('#config_params').hide();
        }
    }

    $('#config_link').on('click', function ()
    {
        toggleParamsBar();
        return false;
    });

    /* select all text on textbox */
    $('#short_url').on('click', function ()
    {
        $(this).select();
    });

    /* `delete link` button animation */
    $('#delete_link').on('click', function()
    {
        if(!confirm('Are you sure?')) return false;

        $(this).find('i').removeClass().addClass('icon-spinner icon-spin');
        return true;
    });

    /* button redirect support */
    $('[data-href]').on('click', function()
    {
        window.location.href = $(this).attr('data-href');
        return false;
    });

    /* link submission */
    $('#link_post').on('submit', function ()
    {
        // validate link
        if (!validateURL($('#url', this).val())) {
            $('#url', this).showMessage('Type a valid URL!', true);
            return false;
        } else $('#url', this).hideMessage();

        // validate alias value
        if (!validateAlias($('#alias', this).val())) {
            $('#alias', this).showMessage('Invalid alias', true);
            if (!$('#alias', this).is(':visible')) toggleParamsBar();
            return false;
        } else $('#alias', this).hideMessage();

        $('#submit_button i', this).removeClass().addClass('icon-spinner icon-spin');

        return true;
    });

});

/**
 * URL validation
 *
 * @param url
 * @returns {boolean}
 */
function validateURL(url)
{
    return /^(http|https):\/\/[a-zA-Zа-яёА-ЯЁ\d-]{2,}(\.[a-zA-Zа-яёА-ЯЁ\d-]{2,})+(\/.*)?$/i
        .test(url.toLowerCase());
}

function deleteLink(alias)
{
    if(!confirm('Are you sure?')) return;

    var url = SITEURL + 'delete/' + alias;
    var elem = $('#link_' + alias);
    $('td.actions', elem).css('text-align', 'center').html('<i class="icon-spinner icon-spin"></i>');

    $.get(url, function() {
        elem.fadeOut('fast', function() {
            if($('table.linksFlow tbody tr:visible').length == 0)
                window.location.reload();
        });
    })
    .fail(function() { window.location.href = url; });
}

function deletePage(alias)
{
    if(!confirm('Are you sure?')) return;

    var url = SITEURL + 'admin/page_delete/' + alias;
    var elem = $('#page_' + alias);
    $('td.actions', elem).css('text-align', 'center').html('<i class="icon-spinner icon-spin"></i>');

    $.get(url, function() {
        elem.fadeOut('fast', function() {
            if($('table.linksFlow tbody tr:visible').length == 0)
                window.location.reload();
        });
    })
        .fail(function() { window.location.href = url; });
}

/**
 * ALNUM check
 *
 * @param text
 * @returns {boolean}
 */
function validateAlias(text)
{
    if ($.trim(text) == '') return true;
    if (text.length < 3)    return false
    if (text.length > 15)   return false

    return /^[a-zA-Z0-9]+[a-zA-Z0-9_]+[a-zA-Z0-9]+$/.test(text);
}

/**
 * Open/close params panel
 */
function toggleParamsBar()
{
    // work with cookies
    if(typeof $.cookie === 'function') {
        $.cookie('paramsBar_state', $('#config_params').is(':visible') ? 'hide' : 'visible',
            { expires: 365 });
    }

    $('#config_link').toggleClass('hover');
    $('#config_params').slideToggle('fast');
}

/**
 * Show error for specified element
 *
 * @param text
 * @returns jQuery
 */
$.fn.showMessage = function (text, error, shake)
{
    var msgLabel = $(this).parent().find('.l_hint');
    var classList = 'l_hint animated fadeInRight';

    if(error === true)
        classList += ' error';

    if (msgLabel.length == 0)
        return $(this).after('<div class="' + classList + '">' + text + '</div>');

    if(shake !== false)
        return msgLabel.removeClass('fadeInRight shake').addClass('shake');
    else
        return msgLabel;
}

/**
 * Hide error on specified element
 *
 * @returns jQuery
 */
$.fn.hideMessage = function ()
{
    if ($(this).children().size() > 0)
        var selector = $('.l_hint', $(this));
    else
        var selector = $('.l_hint', $(this).parent());

    selector.removeClass('fadeInRight shake').addClass('fadeOutRight');
    return selector.bind("animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd", function () {
        $(this).remove();
    });
}