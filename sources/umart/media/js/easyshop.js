if (!window._es) {
    window._es = {
        $: $ instanceof jQuery ? $ : jQuery.noConflict(),
        globalData: {},
        uri: {
            getParams: function () {
                var search = location.search.replace(/^\?/g, '');
                var params = [], parts;

                if (search.length) {
                    search = search.split('&');
                    for (var i = 0, n = search.length; i < n; i++) {
                        if (search[i].indexOf('=') !== -1) {
                            parts = search[i].split('=');
                            params[parts[0]] = parts[1];
                        }
                    }
                }

                return params;
            },
            getParam: function (name) {
                var params = uri.getParams();

                return params[name] ? params[name] : false;
            },
            setParam: function (name, value) {
                var params = uri.getParams();
                var url = location.origin + location.pathname + '?';
                params[name] = value;

                for (var p in params) {
                    if (params.hasOwnProperty(p)) {
                        url += p + '=' + params[p] + '&';
                    }
                }

                return url.slice(0, -1);
            },
            pushState: function (url, data, title) {
                if (history.pushState) {
                    history.pushState({contentData: data ? data : ''}, title ? title : document.title, url);
                } else {
                    location.href = url;
                }
            }
        },
        setData: function (key, val) {
            if (typeof key === 'object') {
                for (var k in key) {
                    this.globalData[k] = key[k];
                }
            } else {
                this.globalData[key] = val;
            }
        },
        getData: function (key, def) {
            return typeof this.globalData[key] === 'undefined' ? def : this.globalData[key];
        },
        currencyFormat: function (number, currency) {
            currency = currency || this.getData('currency');

            if (typeof currency === 'object') {
                number = parseFloat(number).toFixed(parseInt(currency.decimals));
                number = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, currency.separator);
                number = currency.format.toString().replace('{value}', number);
                number = number.replace('{symbol}', currency.symbol);
                number = number.replace('{code}', currency.code);
            }

            return number;
        },
        storage: {
            setData: function (name, value) {
                if (window.localStorage) {
                    window.localStorage.setItem(name, value);
                } else {
                    _es.setCookie(name, value);
                }
            },
            getData: function (name) {
                if (window.localStorage) {
                    return window.localStorage.getItem(name);
                }

                return _es.getCookie(name);
            }
        },
        getCookie: function (cname) {
            var name = cname + '=';
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1);
                if (c.indexOf(name) === 0) return c.substring(name.length, c.length);
            }
            return '';

        },
        setCookie: function (cname, cvalue) {
            var exdays = 1;
            var d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            var expires = 'expires=' + d.toUTCString();
            document.cookie = cname + '=' + cvalue + '; ' + expires;
        },
        ajax: function (url, data, callback, noLoader) {
            var
                loader = _es.$('#es-component').length ? _es.$('#es-component') : _es.$('.es-scope:eq(0)'),
                token = _es.getData('token'),
                notification = function (message) {
                    message.split('::').forEach(function (msg) {
                        _es.uikit.notification('<span uk-icon="icon: warning"></span> ' + msg, {
                            status: 'danger',
                            timeout: 3500,
                            pos: 'top-center',
                        });
                    });
                };

            if (data.easyshopArea instanceof _es.$) {
                loader = data.easyshopArea;
                delete data.easyshopArea;
            }

            if (typeof data === 'object' && token && !data[token]) {
                data[token] = 1;
            }

            noLoader !== true && loader.addClass('es-ajax-loading');
            _es.$.ajax({
                url: url,
                type: 'post',
                dataType: 'json',
                data: data,
                success: function (response) {
                    if (response.success) {
                        if (typeof callback === 'function') {
                            callback(response);
                        }

                        // @since 1.2.0 Update Javascript and Stylesheet
                        var
                            $ = _es.$
                            , head = $('head')
                            , data = response.data
                            , src, href;

                        if (typeof data === 'object') {
                            if (data.hasOwnProperty('_scripts')
                                && typeof data._scripts === 'object'
                            ) {
                                for (src in data._scripts) {
                                    if (!$('script[src^="' + src + '"]').length) {
                                        head.append('<script type="text/javascript" src="' + src + '"></script>');
                                    }
                                }
                            }

                            if (data.hasOwnProperty('_script')
                                && typeof data._script === 'object'
                                && data._script.hasOwnProperty('text/javascript')) {
                                eval(data._script['text/javascript']);
                            }

                            if (data.hasOwnProperty('_styleSheets')
                                && typeof data._styleSheets === 'object'
                            ) {
                                for (href in data._styleSheets) {
                                    if (!$('link[href^="' + href + '"]').length) {
                                        head.append('<link type="text/css" href="' + href + '" rel="stylesheet"/>');
                                    }
                                }
                            }

                            if (data.hasOwnProperty('_style')
                                && typeof data._style === 'object'
                                && data._style.hasOwnProperty('text/css')
                            ) {
                                head.append('<style>' + data._style['text/css'] + '</style>');
                            }
                        }
                        _es.$(document).trigger('esAjaxSuccess', [url, data, response]);
                    } else {
                        notification(response.message);
                    }
                },
                complete: function (xhr, textStatus) {
                    loader.removeClass('es-ajax-loading');
                },
                error: function (xhr, textStatus, errorThrown) {
                    if (data.errorThrown !== undefined && data.errorThrown) {
                        notification(errorThrown);
                    }
                }
            });
        },
        lang: {
            data: [],
            load: function (data) {
                for (var d in data) {
                    _es.lang.data[d] = data[d];
                }
            },
            _: function (string) {
                var key = _es.$.trim(string.toUpperCase());
                return _es.lang.data[key] !== undefined ? _es.lang.data[key] : string;
            }
        },
        events: {
            getProductPriceTemplate: function () {
                var tr = _es.$('<tr/>');
                tr.html(
                    '<td><input type="number" name="jform[prices][min_quantity][]" class="uk-input" min="0" value=""/></td>' +
                    '<td><input type="number" name="jform[prices][price][]" class="price uk-input" min="0" value=""/></td>' +
                    '<td>' + _es.getData('currency_box') + '</td>' +
                    '<td><div class="flatpickr uk-position-relative"><a class="uk-form-icon uk-form-icon-flip" uk-icon="icon: close" data-clear></a><input type="text" name="jform[prices][valid_from_date][]" class="datetime-picker uk-input" autocomplete="off" placeholder="' + _es.lang._('COM_EASYSHOP_FROM_DATE') + '" value="" data-input/></div></td>' +
                    '<td><div class="flatpickr  uk-position-relative"><a class="uk-form-icon uk-form-icon-flip" uk-icon="icon: close" data-clear></a><input type="text" name="jform[prices][valid_to_date][]" class="datetime-picker uk-input" autocomplete="off" placeholder="' + _es.lang._('COM_EASYSHOP_TO_DATE') + '" value="" data-input/></div></td>' +
                    '<td><button type="button" class="uk-button uk-button-danger uk-button-small"' +
                    ' onclick="_es.events.removeParentBox(this, \'tr\');"><i class="fa fa-times"></i></button></td>'
                );

                return tr;
            },
            addProductPrice: function (boxElement) {
                var priceBox = _es.$(boxElement);
                var rowBox = _es.events.getProductPriceTemplate();
                priceBox
                    .find('table:eq(0)>tbody')
                    .append(rowBox);
                _es.initChosen(boxElement);
                priceBox.find('.flatpickr').each(function () {
                    flatpickr(this, {
                        enableTime: true,
                        dateFormat: 'Y-m-d H:i:s',
                        altFormat: _es.getData('dateTimeFormat'),
                        enableSeconds: true,
                        altInput: true,
                        time_24hr: true,
                        wrap: true,
                        showMonths: 2,
                    });
                });
            },
            removeParentBox: function (el, boxEl) {
                _es.$(el).parents(boxEl + ':eq(0)').remove();
            },
            getOptionTemplate: function () {
                var tr = _es.$('<tr/>');
                tr.html(
                    '<td><i class="fa fa-sort"></i></td>' +
                    '<td><input type="text" name="jform[params][options][value][]"' +
                    ' class="uk-input" placeholder="' + _es.lang._('COM_EASYSHOP_OPTION_VALUE') + '"/></td>' +
                    '<td><input type="text" name="jform[params][options][text][]"' +
                    ' class="uk-input" placeholder="' + _es.lang._('COM_EASYSHOP_OPTION_TEXT') + '"/></td>' +
                    '<td><button type="button" class="uk-button uk-button-danger uk-button-small"' +
                    ' onclick="_es.events.removeParentBox(this, \'tr\');"><i class="fa fa-times"></i></button></td>'
                );

                return tr;
            },
            addOption: function (boxElement) {
                var optionBox = _es.$(boxElement);
                var rowBox = _es.events.getOptionTemplate();
                optionBox
                    .find('table:eq(0)>tbody')
                    .append(rowBox);
            },
            productInit: function (product) {
                var $ = _es.$, wrap, productId;
                if (product instanceof $) {
                    wrap = product.length > 1 ? product.eq(0) : product;
                    productId = product.data('productId');
                } else {
                    productId = product;
                    wrap = $('.es-scope [data-product-id="' + productId + '"]:eq(0)');
                }

                wrap.data('productInit', true);
                wrap.find('[data-product-full-price]').addClass('uk-hidden');
                wrap.find('.option-prefix').text('');

                var
                    url = _es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=cart.calculate',
                    data = {
                        optionArray: wrap.find('[data-product-options] [name^="product_option"]').serializeArray(),
                        quantity: wrap.find('[data-product-quantity]').val(),
                        productId: parseInt(productId),
                        easyshopArea: wrap
                    },
                    mainImage = wrap.find('.es-main-image [data-image-size]'),
                    aImage = mainImage.parent('a[type="image"]'),
                    sliderItems = wrap.find('.es-product-images [uk-slider] .uk-slider-items'),
                    priceBox = wrap.find('[data-product-price]'),
                    taxesBox = wrap.find('[data-product-taxes]'),
                    btnAddToCart = wrap.find('[data-disable-on-zero="1"]');

                if (sliderItems.length && !sliderItems.data('originInnerHtml')) {
                    sliderItems.data('originInnerHtml', sliderItems.html());
                }

                if (btnAddToCart.length) {
                    if (priceBox.data('productPrice') < 0.01) {
                        btnAddToCart.addClass('uk-disabled').prop('disabled', true);
                    } else {
                        btnAddToCart.removeClass('uk-disabled').prop('disabled', false);
                    }
                }

                _es.ajax(url, data, function (response) {
                    priceBox.html(response.data.price)
                        .attr('data-product-price', response.data.priceRaw)
                        .data('productPrice', response.data.priceRaw);

                    if (taxesBox.length) {
                        taxesBox.html(response.data.taxes)
                            .attr('data-product-taxes', response.data.taxesRaw)
                            .data('productPrice', response.data.taxesRaw);
                    }

                    if (btnAddToCart.length) {
                        if (response.data.priceRaw < 0.01) {
                            btnAddToCart.addClass('uk-disabled').prop('disabled', true);
                        } else {
                            btnAddToCart.removeClass('uk-disabled').prop('disabled', false);
                        }
                    }

                    for (var id in response.data.options) {
                        var prefix = wrap.find('[data-prefix-id="' + id + '"]');
                        if (response.data.options[id].price !== '') {
                            prefix.html('(' + response.data.options[id].price + ')');
                        }
                    }

                    if (response.data.images && response.data.images.length) {
                        var
                            images = response.data.images
                            , src;

                        if (mainImage.length && mainImage.data('imageSize')) {
                            src = images[0][mainImage.data('imageSize')];
                            if (src) {
                                mainImage.attr({
                                    'data-src': src,
                                    'src': src
                                });
                                if (mainImage.parent('a[type="image"]').length) {
                                    mainImage.parent('a[type="image"]').attr('href', images[0].large);
                                }
                            }
                        }

                        if (sliderItems.length) {
                            var tmpItems = $('<ul>' + sliderItems.data('originInnerHtml') + '</ul>');
                            sliderItems.empty();

                            for (var i in images) {
                                sliderItems.append(tmpItems.find('[data-image="' + images[i].originBasePath + '"]'));
                            }

                            tmpItems.remove();

                            if (sliderItems.find('[data-image]').length < 2 && sliderItems.hasClass('es-thumbnails-slider')) {
                                sliderItems.empty();
                            }
                        }

                    } else {
                        if (mainImage.length && mainImage.attr('data-image-size-origin')) {
                            mainImage.attr('src', mainImage.attr('data-image-size-origin'))
                        }

                        if (sliderItems.length) {
                            sliderItems.html(sliderItems.data('originInnerHtml'));
                        }

                        if (aImage.length) {
                            aImage.attr('href', aImage.attr('href'));
                        }
                    }
                });
            },
            printOrder: function (dataOrder, pageTitle) {
                _es.ajax(_es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=ajax.loadPrintPage', {
                    dataOrder: dataOrder || {},
                    pageTitle: pageTitle
                }, function (response) {
                    var iframeName = ('PrintOrderWindow' + (new Date()).getTime());
                    var iframe = _es.$('<iframe name=' + iframeName + '>').css({
                        width: '1px',
                        height: '1px',
                        position: 'absolute',
                        left: '-9999px'
                    }).appendTo(_es.$('body'));
                    var objFrame = window.frames[iframeName];
                    var objDoc = objFrame.document;
                    objDoc.open();
                    objDoc.write(response.data);
                    objDoc.close();

                    setTimeout(function () {
                        objFrame.focus();
                        objFrame.print();
                    }, 100);

                    setTimeout(function () {
                        iframe.remove();
                    }, 60000);
                });
            }
        },
        cart: {
            reloadModules: function () {
                var $ = _es.$
                $('.mod-easyshop-cart[data-module-id]').each(function () {
                    var
                        module = $(this),
                        moduleId = module.data('moduleId');
                    _es.ajax(
                        _es.getData('uri').pathRoot + '/index.php?option=com_ajax',
                        {
                            moduleId: moduleId,
                            module: 'easyshop_cart',
                            params: module.parents('[data-cart-params]').length ? module.parents('[data-cart-params]').data('cartParams') : {},
                            method: 'loadModule',
                            format: 'json',
                            easyshopArea: module,
                        },
                        function (response) {
                            if (response.data !== '') {
                                var content = $(response.data);
                                content.find('script').remove();
                                module.html(content.html());
                            }
                        },
                        true
                    );
                });
            },
            addItem: function (pk, quantity, options) {
                var
                    $ = _es.$,
                    url = _es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=cart.addItem',
                    request = {
                        requestType: 'addToCart',
                        productId: pk,
                        quantity: quantity,
                        options: typeof options === 'object' ? options.serializeArray() : [],
                        easyshopArea: $('.es-scope [data-product-id="' + pk + '"]')
                    };
                _es.ajax(url, request, function (response) {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        $(document).trigger('esCartResponse', [request, response]);

                        if ($.trim(response.data.html) !== '') {
                            var container = $(_es.uikit.container);
                            container.find('[data-cart-modal]').remove();
                            container.append(response.data.html);
                            _es.uikit.modal('[data-cart-modal]').show();
                        }

                        _es.cart.reloadModules();
                    }
                });
            },
            update: function (type, pk, quantity, key) {
                var
                    $ = _es.$,
                    isRemove = type === 'remove',
                    url = _es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=cart.update',
                    container = $('[data-cart-modal].uk-open .es-cart-modal-detail').length ? $('[data-cart-modal].uk-open .es-cart-modal-detail') : $(_es.uikit.container),
                    request = {
                        requestType: 'updateCart',
                        productId: pk,
                        quantity: quantity,
                        updateType: isRemove ? 'remove' : 'update',
                        key: key,
                        easyshopArea: container
                    };
                _es.ajax(url, request, function (response) {
                    var data = response.data;
                    var summaryWrap = $('#es-summary-wrap');
                    var message = $('<p class="text-response uk-text-' + (response.success ? 'success' : 'danger') + '"/>')
                        .css({
                            fontSize: 13,
                            fontWeight: 400,
                            marginTop: 5
                        });

                    if ($('[data-cart-output]').length) {
                        $('[data-cart-output]').html($('<span>' + data.html + '</span>').find('[data-cart-output]').html());
                    }

                    if (summaryWrap.length) {
                        summaryWrap.find('.es-cart-summary').html($(data.summaryHTML).find('.es-cart-summary').html());

                        if (!summaryWrap.find('[data-items-count]').length
                            || isNaN(parseInt(summaryWrap.find('[data-items-count]').attr('data-items-count')))
                            || parseInt(summaryWrap.find('[data-items-count]').attr('data-items-count')) === 0
                        ) {
                            location.reload();
                        }
                    }

                    _es.uikit.notification('<span uk-icon="icon: check"></span> ' + response.message, {
                        status: 'success',
                        timeout: 2500,
                        pos: 'top-center'
                    });

                    window.setTimeout(function () {
                        message.remove();
                    }, 2500);

                    $(document).trigger('esCartResponse', [request, response]);
                });
            },
            coupon: function (coupon) {
                var
                    $ = _es.$,
                    url = _es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=cart.coupon',
                    code = $.trim(coupon),
                    area = $('#es-summary-wrap'),
                    isCheckout = true,
                    data;

                if (code.length < 1) {
                    return false;
                }

                if (!area.length) {
                    area = $('.es-scope [data-cart-body]');
                    isCheckout = false;
                }

                data = {
                    coupon: code,
                    easyshopArea: area,
                };

                _es.ajax(url, data, function (response) {
                    var status, icon;
                    switch (response.data.type) {
                        case 'succeed':
                            status = 'success';
                            icon = '<span uk-icon="icon: check"></span>';
                            break;

                        case 'failed':
                            status = 'danger';
                            icon = '<span uk-icon="icon: close"></span>';
                            break;

                        case 'warning':
                            status = 'warning';
                            icon = '<span uk-icon="icon: warning"></span>';
                            break;
                    }
                    _es.uikit.notification(icon + ' ' + response.data.message, {
                        status: status,
                        timeout: 2500,
                        pos: 'top-center'
                    });

                    if (isCheckout) {
                        area.find('.es-cart-summary').html($(response.data.summaryHTML).find('.es-cart-summary').html());
                        $('#es-checkout-form [data-coupon]').val('');
                    } else {
                        area.html($(response.data.html).find('[data-cart-body]').html());
                    }
                });
            },
            removeCoupon: function (couponId) {
                var area = _es.$('#es-summary-wrap');
                _es.ajax(_es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=cart.removeCoupon', {
                    easyshopArea: area,
                    couponId: couponId
                }, function (response) {
                    area.find('.es-cart-summary').html(_es.$(response.data).find('.es-cart-summary').html());
                });
            }
        },
        checkout: {
            saveAddress: function () {
                var
                    $ = _es.$,
                    checkout = $('#es-checkout-form'),
                    url = _es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=checkout.saveAddress&' + _es.getData('token') + '=1';
                if (checkout.length) {
                    var addressDiff = checkout.find('[name="jform[address_different]"]')
                        , fields;

                    if (addressDiff.length && addressDiff.is(':checked')) {
                        fields = checkout.find('.es-billing-address [name^="jform"], .es-shipping-address [name^="jform"]');
                    } else {
                        fields = checkout.find('.es-billing-address [name^="jform"]');
                    }

                    if (checkout.find('[name="jform[registration][register]"]').length) {
                        var passwords = checkout.find('[name="jform[registration][password1]"], [name="jform[registration][password2]"]');
                        passwords.data('ruleRequired', checkout.find('[name="jform[registration][register]"]').is(':checked'));
                    }

                    fields = fields.add('#es-checkout-form [name^="jform[registration]"],' +
                        '#es-checkout-form [name^="jform[checkoutFields]"],' +
                        '#es-checkout-form [name="jform[address_different]"],' +
                        '#es-checkout-form [name="jform[note]"]');

                    if (fields.es_validate(checkout)) {
                        _es.ajax(url, fields.serialize(), function (response) {
                            var data = $('<div/>').html(response.data.html);
                            checkout.html(data.find('#es-checkout-form').html());
                            $('#product-checkout-navigation').html(data.find('#product-checkout-navigation').html());
                            data.remove();
                            if (history.pushState) {
                                history.pushState({esCheckoutPage: 'confirm'}, response.data.pushState.title, response.data.pushState.url);
                            }
                        });
                    }
                }
            },
            editAddress: function () {
                var
                    $ = _es.$,
                    checkout = $('#es-checkout-form'),
                    url = _es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=checkout.editAddress&' + _es.getData('token') + '=1';
                if (checkout.length) {
                    _es.ajax(url, {}, function (response) {
                        var data = $('<div/>').html(response.data.html);
                        checkout.html(data.find('#es-checkout-form').html())
                            .find('select:not(.not-chosen)').chosen({
                            disable_search_threshold: 10
                        });

                        checkout.find('.flatpickr').each(function () {
                            flatpickr(this, $(this).data('flatpickr'));
                        });

                        $('#product-checkout-navigation').html(data.find('#product-checkout-navigation').html());
                        data.remove();

                        if (_es.getData('jVersion') === 4 && Joomla.Event) {
                            Joomla.Event.dispatch(checkout[0], 'joomla:updated');
                        } else {
                            $(document).trigger('subform-row-add', [checkout]);
                        }

                        if (history.pushState) {
                            history.pushState({esCheckoutPage: 'checkout'}, response.data.pushState.title, response.data.pushState.url);
                        }
                    });
                }
            },
            confirm: function () {
                var
                    $ = _es.$,
                    checkout = $('#es-checkout-form'),
                    data = checkout.find('[name^="jform"]'),
                    url = _es.getData('uri') + '/index.php?option=com_easyshop&task=checkout.confirm&' + _es.getData('token') + '=1';
                if (checkout.length && data.es_validate()) {
                    _es.ajax(url, data.serializeArray(), function (response) {
                        var layoutData = $('<div/>').html(response.html).find('[data-confirm]');
                        if (layoutData.length) {
                            checkout.html(layoutData.html()).fadeIn();
                            $('#es-checkout-navigation a').removeClass('active');
                            $('#es-checkout-navigation a[href="' + response.url + '"]').addClass('active');
                            document.title = response.title;
                            window.history.pushState({layout: 'confirm'}, response.title, response.url);
                        }
                    });
                }
            }
        },
        toggleView: function (key, button) {
            var $ = _es.$;
            if (key === 'list' || key === 'grid') {
                _es.storage.setData('list.view', key);

                if (key === 'list') {
                    $('[data-view-mode]')
                        .addClass('list-view')
                        .removeClass('grid-view');
                } else {
                    $('[data-view-mode]')
                        .addClass('grid-view')
                        .removeClass('list-view');
                }
            }

            if (button) {
                button = $(button);
                if (button.hasClass('uk-button-default')) {
                    button.removeClass('uk-button-default').addClass('uk-button-primary');
                }

                button.siblings('.uk-button').removeClass('uk-button-primary').addClass('uk-button-default');
                var grid = $(button).parents('.product-list').find('[data-product-list]');

                if (grid.length) {
                    setTimeout(function () {
                        _es.uikit.grid(grid[0]).$emit();
                    }, 300);
                }
            }
        },
        initChosen: function (container) {
            // This method will be overridden by ES\Html class
        }
    };
    _es.$(document).on('initUIContainer', function () {
        var
            $ = _es.$,
            body = $('body'),
            esScope = $('.es-scope');

        if (_es.getData('jVersion')) {
            body.addClass('es-detect-jversion-' + _es.getData('jVersion'));
        }

        _es.uikit = UIkit;

        // @since 1.1.6
        // Only handle on load for high performance reason
        var handleImage = function () {
            var
                winWidth = $(window).width()
                , mediaSets = _es.getData('mediaSets')
                , mediaSet
                , img
                , dataSrc
                , i
                , n;
            if (typeof mediaSets === 'object') {
                for (i = 0, n = mediaSets.length; i < n; i++) {
                    mediaSet = mediaSets[i];
                    mediaSet[0] = parseInt(mediaSet[0]);
                    mediaSet[1] = parseInt(mediaSet[1]);

                    if (mediaSet.length === 3
                        && !isNaN(mediaSet[0])
                        && !isNaN(mediaSet[1])
                    ) {
                        if (winWidth >= mediaSet[0]
                            && (winWidth <= mediaSet[1] || mediaSet[1] === 0)
                        ) {
                            $('img[data-image-size]').each(function () {
                                img = $(this);

                                if (!img.hasClass('imageHandled')) {
                                    img.addClass('imageHandled')
                                        .data('imageSize', mediaSet[2])
                                        .attr('data-image-size', mediaSet[2]);
                                }
                            });
                            break;
                        }
                    }
                }
            }

            $('img[data-image-size]:not(.imageLoaded), img[data-image-size]:not([src])').each(function () {
                img = $(this);
                img.addClass('imageLoaded');
                dataSrc = img.attr('data-image-' + img.data('imageSize') + '-src');
                dataSrc && _es.uikit.img(this, {dataSrc: dataSrc});
            });
        };

        handleImage();
        esScope.on('DOMSubtreeModified', function () {
            setTimeout(handleImage, 500);
        });

        // Toggle grid view
        $('[data-view-mode] .es-btn-' + _es.storage.getData('list.view') || 'grid').trigger('click');

        esScope.find('img[data-image-size]').attr('data-image-size-origin', function () {
            return $(this).attr('src');
        });

        esScope.find('a[type="image"]').attr('data-image-size-origin', function () {
            return $(this).attr('href');
        });

        esScope.find('.owl-carousel').each(function () {
            var carousel = $(this);
            if (carousel.find('a[type="image"]').length) {
                carousel.parents('[data-product-id]:eq(0)').data('originCarousel', carousel.find('a[type="image"]'));
            }
        });

        // The same checkout address
        esScope.on('change', '#es-checkout-form [name^="jform[billing_address"]', function () {
            var
                el = $(this),
                target = $('#es-checkout-form [name="' + el.attr('name').toString().replace('billing', 'shipping') + '"]'),
                type = target.attr('type');
            if (!target.length) {
                return;
            }
            var nodeName = target.get(0).nodeName;
            if (!$('#es-checkout-form [name="jform[address_different]"]').prop('checked')) {
                if (nodeName === 'INPUT') {
                    if (type === 'radio' || type === 'checkbox') {
                        target.prop('checked', el.prop('checked'));
                    } else {
                        target.val(el.val());
                    }
                } else {
                    if (nodeName === 'SELECT') {
                        target.find('option').prop('selected', false);
                        el.find('option:selected').each(function () {
                            target.find('option[value="' + $(this).attr('value') + '"]').prop('selected', true);
                        });
                        target.trigger('liszt:updated');
                    } else {
                        target.val(el.val());
                    }
                }

                target.trigger('change');
            }
        });

        esScope.on('change', '#es-checkout-form [name="jform[address_different]"]', function () {
            var
                el = $(this),
                shippingBox = $('#es-checkout-form .es-shipping-address');
            if (el.is(':checked')) {
                shippingBox.fadeIn();
            } else {
                shippingBox.fadeOut();
            }
        });

        // Fix cart button not redirect
        esScope.on('click', 'a.es-btn-checkout', function () {
            window.location.href = $(this).attr('href');
        });

        $('form.data-validate').on('submit', function () {
            return $(this).find('input, select, textarea').es_validate();
        });

        //Add to cart
        esScope.on('click', '[data-add-to-cart]', function (e) {
            e.preventDefault();
            var
                el = $(this),
                area = el.parents('[data-product-id]:eq(0)'),
                options = area.find('[name^="product_option"]'),
                quantity = area.find('[data-product-quantity]');

            if (options.length && !options.es_validate(area)) {
                return false;
            }
            _es.cart.addItem(area.data('productId'), quantity.length ? quantity.val() : 1, options);
        });

        esScope.on('change', '[data-product-id] [name^="product_option"], [data-product-id] [data-product-quantity]', function () {
            var
                el = $(this),
                productWrap = el.parents('[data-product-id]:eq(0)');
            if (this.hasAttribute('data-product-quantity')) {
                var
                    qty = parseInt(this.value),
                    listRange = productWrap.find('[data-range-qty]'),
                    range;

                if (!isNaN(qty) && listRange.length) {
                    listRange.removeClass('active');
                    listRange.each(function () {
                        range = $(this).data('rangeQty');

                        if (range.length === 2 && qty >= range[0] && qty <= range[1]) {
                            $(this).addClass('active');
                        }
                    });
                }

            } else {
                el.es_validate(productWrap);
            }

            _es.events.productInit(productWrap);
        });

        $('[data-product-id] [name^="product_option"]').each(function () {
            var
                el = $(this),
                productWrap = el.parents('[data-product-id]:eq(0)');
            if (productWrap.data('productInit') !== true) {
                productWrap.data('productInit', true);
                if (el.val() !== '') {
                    _es.events.productInit(productWrap);
                }
            }
        });

        esScope.on('change', '#es-checkout-form [name="jform[payment_id]"]', function () {
            var el = $(this);
            $('[data-card-target]').each(function () {
                $($(this).data('cardTarget'))
                    .addClass('uk-hidden');
            });
            if (el.data('cardTarget')) {
                $(el.data('cardTarget'))
                    .removeClass('uk-hidden');
            }
            el.parents('li:eq(0)').addClass('active').siblings('li').removeClass('active');
            _es.ajax(_es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=checkout.addPayment', {
                paymentId: el.val(),
                easyshopArea: $('#es-component')
            }, function (response) {
                var data = $('<div/>').html(response.data);
                $('#es-checkout-form').html(data.find('#es-checkout-form').html());
                data.remove();
            });
        });

        esScope.on('click', '#es-submit-button', async function (e) {
            e.preventDefault();
            e.stopPropagation();
            var
                btn = $(this),
                form = btn.parents('form:eq(0)'),
                shipping = form.find('[name="jform[shipping_id]"]'),
                payment = form.find('[name="jform[payment_id]"]'),
                cardTarget, isValid = true;
            btn.addClass('uk-disabled').prop('disabled', true);
            var fail = function() {
                btn.removeClass('uk-disabled').prop('disabled', false);

                return false;
            };

            if ((shipping.length && !shipping.es_validate())
                || (payment.length && !payment.es_validate())
            ) {

                return fail();
            }

            if (payment.length) {
                payment = form.find('[name="jform[payment_id]"]:checked');

                try {
                    payment.data('asyncCallBack', null);
                    $(document).trigger('doPaymentHandleCheckout', [form, payment]);

                    if (typeof payment.data('asyncCallBack') === 'function') {
                        var result = await payment.data('asyncCallBack')();

                        if (false === result) {
                            return fail();
                        }
                    }

                    if (true === form.data('stopSubmit')) {
                        return true;
                    }

                } catch (err) {
                    _es.uikit.notification('<span uk-icon="icon: warning"></span> ' + err, {
                        status: 'warning',
                        pos: 'top-center'
                    });

                    return fail();
                }

                cardTarget = payment.data('cardTarget');

                if (cardTarget && form.find(cardTarget).length) {
                    var
                        card = form.find(cardTarget),
                        cardInput = card.find('[data-card-number]'),
                        expiryMonth = card.find('[data-card-expiry-month]'),
                        expiryYear = card.find('[data-card-expiry-year]'),
                        cardCvv = card.find('[data-card-cvv]'),
                        date = card.find('[data-date]').data('date'),
                        nowDate, cardDate, year, code;
                    if (cardInput.length) {
                        var cardOptions = {};
                        if (cardInput.data('cards').length) {
                            cardOptions.accept = cardInput.data('cards');
                        }
                        cardInput.es_validate_card(function (result) {
                            isValid = result.valid;

                            if (isValid) {
                                cardInput.removeClass('uk-form-danger');
                            } else {
                                cardInput.addClass('uk-form-danger');
                            }
                        }, cardOptions);
                        date = date.toString().split('-');
                        year = new Date().getFullYear();
                        nowDate = new Date(date[0], date[1], date[2]);
                        cardDate = new Date(year.toString().substr(0, 2) + expiryYear.val(), expiryMonth.val(), date[2]);
                        if (cardDate.getTime() < nowDate.getTime()) {
                            isValid = false;
                            expiryMonth.addClass('uk-form-danger');
                            expiryYear.addClass('uk-form-danger');
                        } else {
                            expiryMonth.removeClass('uk-form-danger');
                            expiryYear.removeClass('uk-form-danger');
                        }
                        code = $.trim(cardCvv.val().toString());
                        if (code.match(/[^0-9]/) || code.length < 3 || code.length > 4) {
                            isValid = false;
                            cardCvv.addClass('uk-form-danger');
                        } else {
                            cardCvv.removeClass('uk-form-danger');
                        }

                        if (isValid) {
                            try {
                                $(document).trigger('doPaymentHandleCard', [form, card]);
                            } catch (err) {
                                _es.uikit.notification('<span uk-icon="icon: warning"></span> ' + err, {
                                    status: 'warning',
                                    pos: 'top-center'
                                });

                                return fail();
                            }
                        }
                    }
                }
            }

            if (isValid) {
                form.submit();
            } else {
                return fail();
            }
        });

        esScope.on('change', '#es-checkout-form [name="jform[shipping_id]"]', function () {
            var el = $(this);
            el.parents('li:eq(0)').addClass('active').siblings('li').removeClass('active');
            _es.ajax(_es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=checkout.addShipping', {
                shippingId: el.val(),
                easyshopArea: $('#es-component')
            }, function (response) {
                var data = $('<div/>').html(response.data);
                $('#es-checkout-form').html(data.find('#es-checkout-form').html());
                data.remove();
            });
        });

        $(document).on('click', '.es-quantity-button', function (e) {
            e.preventDefault();
            var
                el = $(this),
                input = el.parents('.es-quantity:eq(0)').find('input[type="number"]'),
                value = parseInt(input.val()),
                min = input.attr('min'),
                max = input.attr('max');
            if (el.hasClass('es-quantity-up')) {
                value++;
                if (isNaN(max) || parseInt(max) >= value) {
                    input.val(value).trigger('change');
                }
            } else {
                value--;
                if (isNaN(min) || parseInt(min) <= value) {
                    input.val(value).trigger('change');
                }
            }
        });

        $(document).on('click', '.es-scope #es-button-filter', function () {
            var
                btnFilter = $('.es-scope .js-stools-container-filters'),
                btnIcon = $('#es-button-filter .fa');
            if (btnIcon.length) {
                if (btnIcon.hasClass('fa-caret-down')) {
                    btnIcon.attr('class', 'fa fa-caret-up');
                } else {
                    btnIcon.attr('class', 'fa fa-caret-down')
                }
            }
            btnFilter.slideToggle('fast');
        });

        $(document).on('click', '.es-scope a[data-cart-vendor-id]', function (e) {
            e.preventDefault();
            var href = $(this).attr('href').toString();
            var vendorId = parseInt($(this).data('cartVendorId'));

            if (!isNaN(vendorId) && _es.getData('vendor.isMultiple') === true) {
                if (href.indexOf('?') === -1) {
                    href += '?vendorActiveId=' + vendorId;
                } else {
                    href += '&vendorActiveId=' + vendorId;
                }
            }

            location.href = href;
        });

        /*@since 1.1.5*/
        $(document).on('click', '.es-field-inline .es-inline-button, .es-field-colors .es-color-button', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var btn = $(this);

            if (btn.parents('.es-option').length) {
                btn.addClass('active');
            } else {
                btn.toggleClass('active');
            }

            if (!btn.hasClass('multiple')) {
                btn.siblings().removeClass('active');
            }

            btn.siblings().andSelf().each(function () {
                $(this).find('input').prop('checked', $(this).hasClass('active'));
            });

            btn.find('input').trigger('change');
        });

        var wrapperCheckSize = function () {
            if ($('.es-wrapper').length) {
                var el, width, size, cls;

                $('.es-wrapper').each(function () {
                    el = $(this);
                    width = el.width();
                    size = 'xsmall';

                    if (width >= 320 && width < 410) {
                        size = 'small';
                    } else if (width >= 410 && width < 750) {
                        size = 'medium';
                    } else if (width >= 750 && width < 991) {
                        size = 'large';
                    } else if (width >= 992 && width < 1199) {
                        size = 'xlarge';
                    } else if (width >= 1200) {
                        size = 'xxlarge';
                    }

                    cls = $.trim(el.attr('class').replace(/es-size\-[a-z]+/, ''));
                    el.attr('class', cls + ' es-size-' + size);
                });
            }
        };

        $(window).on('load resize', wrapperCheckSize);

        // @since 1.3.6
        esScope.on('change', '#es-checkout-form [name^="jform[checkoutFields]"]', function (e) {
            e.preventDefault();
            var
                $ = _es.$,
                fields = $('#es-checkout-form [name^="jform[checkoutFields]"]'),
                url = _es.getData('uri').pathRoot + '/index.php?option=com_easyshop&task=checkout.calculateCheckoutFieldsPrice&' + _es.getData('token') + '=1';
            _es.ajax(url, fields.serialize(), function (response) {
                if (response.success) {
                    $('.checkout-field-price').remove();
                    _es.cart.reloadModules();
                    var fieldId, fieldLbl;
                    for (fieldId in response.data.fieldsPrice) {
                        fieldLbl = $('label[for="jform_checkoutFields_' + fieldId + '"]');
                        if (fieldLbl.length) {
                            $('<div class="checkout-field-price uk-display-inline-block"/>')
                                .html(_es.currencyFormat(response.data.fieldsPrice[fieldId].price))
                                .appendTo(fieldLbl);

                        }
                    }

                    $('#es-summary-amount-details').html($(response.data.html).find('#es-summary-amount-details').html());
                }
            }, true);
        });

        // @since 1.3.9
        $('input[data-product-quantity]').each(function () {
            var
                input = $(this),
                qty = parseInt(input.val());

            if (!isNaN(qty) && qty > 1) {
                input.trigger('change');
            }
        });
    });
}
