(function ($) {
    $(document).ready(function () {
        if ($('#es-component').length) {
            return true;
        }

        _es.ajax(_es.getData('uri').pathBase + '/index.php?option=com_easyshop&task=system.loadNavigation', {}, function (response) {
            $('#j-sidebar-container').remove();
            var componentArea = $('<div id="es-component" class="es-category es-scope uk-scope">' + response.data + '<div id="es-body" class="uk-width-3-4@m uk-width-4-5@xl uk-width-2-3@s"></div></div>');
            var mainContainer = $('#j-main-container');
            var form = mainContainer.parents('form:eq(0)');
            mainContainer.attr({
                'id': 'es-main-container',
                'class': 'es-main-container',
            });

            form.before(componentArea);
            componentArea.find('#es-body').append(form);
        }, true);
    });
})(jQuery);
