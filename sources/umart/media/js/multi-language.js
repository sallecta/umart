(function ($) {
    var options = Joomla.getOptions('com_easyshop.multiLanguage');

    if (typeof options === 'object') {
        $.ajax({
            url: options.ajaxUrl,
            type: 'post',
            dataType: 'json',
            data: {
                refTable: options.refTable,
                refKey: options.refKey,
            },
            success: function (responseJson) {
                if (responseJson.success) {
                    $('#jform_title').after(responseJson.data.title).remove();
                    $('#jform_alias').after(responseJson.data.alias).remove();
                    $('.es-language-tabs').addClass('uk-scope');
                } else {
                    console.log(responseJson.message);
                }
            }
        });
    }

})(jQuery);