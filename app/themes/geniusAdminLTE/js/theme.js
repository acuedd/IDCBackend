$(function () {
    'use strict'

    $('[data-toggle="control-sidebar"]').controlSidebar()
    $('[data-toggle="push-menu"]').pushMenu()

    $('[data-toggle="control-sidebar"]').controlSidebar()
    $('[data-toggle="push-menu"]').pushMenu()
    /*var $pushMenu = $('[data-toggle="push-menu"]').data('lte.pushmenu')
    var $controlSidebar = $('[data-toggle="control-sidebar"]').data('lte.controlsidebar')
    var $layout = $('body').data('lte.layout')
    $(window).on('load', function() {
        // Reinitialize variables on load
        $pushMenu = $('[data-toggle="push-menu"]').data('lte.pushmenu')
        $controlSidebar = $('[data-toggle="control-sidebar"]').data('lte.controlsidebar')
        $layout = $('body').data('lte.layout')
    })*/

});