jQuery(document).ready(function ($) {

    if (!window.sessionStorage) {
        return;
    }

    var loadTab = function () {
        var
            storageKey = window.location.href.toString().split(window.location.host)[1].replace(/&return=[a-zA-Z0-9%]+/, '').split('#')[0],
            index = 0,
            tabs = $('[uk-tab], .uk-tab');

        // Initial tab key
        tabs.each(function () {
            $(this).data('tabKey', storageKey + '#tab' + (index++));
        });

        // Set active tab
        tabs.find('li>a').on('click', function () {
            var a = $(this);
            sessionStorage.setItem(a.parents('.uk-tab:eq(0)').data('tabKey'), a.parent().index());
        });

        // Load active tab
        tabs.each(function () {
            UIkit.tab(this).show(sessionStorage.getItem($(this).data('tabKey')) || 0);
        });
    };

    setTimeout(loadTab, 300);
});


