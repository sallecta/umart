(function ($) {
    $(document).ready(function () {
        if ($('#umart_component').length) {
            return true;
        }

        _umart.ajax(_umart.getData('uri').pathBase + '/index.php?option=com_umart&task=system.loadNavigation', {}, function (response) {
            $('#j-sidebar-container').remove();
            var componentArea = $('<div id="umart_component" class="umart_category umart_scope umartui_scope">' + response.data + '<div id="umart_body" class="umartui_width-3-4@m umartui_width-4-5@xl umartui_width-2-3@s"></div></div>');
            var mainContainer = $('#j-main-container');
            var form = mainContainer.parents('form:eq(0)');
            mainContainer.attr({
                'id': 'umart_main_container',
                'class': 'umart_main_container',
            });

            form.before(componentArea);
            componentArea.find('#umart_body').append(form);
        }, true);
    });
})(jQuery);
