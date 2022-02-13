(function () {
    var $, Range, Trie,
        indexOf = [].indexOf || function (item) {
            for (var i = 0, l = this.length; i < l; i++) {
                if (i in this && this[i] === item) return i;
            }
            return -1;
        };

    Trie = (function () {
        function Trie() {
            this.trie = {};
        }

        Trie.prototype.push = function (value) {
            var char, i, j, len, obj, ref, results;
            value = value.toString();
            obj = this.trie;
            ref = value.split('');
            results = [];
            for (i = j = 0, len = ref.length; j < len; i = ++j) {
                char = ref[i];
                if (obj[char] == null) {
                    if (i === (value.length - 1)) {
                        obj[char] = null;
                    } else {
                        obj[char] = {};
                    }
                }
                results.push(obj = obj[char]);
            }
            return results;
        };

        Trie.prototype.find = function (value) {
            var char, i, j, len, obj, ref;
            value = value.toString();
            obj = this.trie;
            ref = value.split('');
            for (i = j = 0, len = ref.length; j < len; i = ++j) {
                char = ref[i];
                if (obj.hasOwnProperty(char)) {
                    if (obj[char] === null) {
                        return true;
                    }
                } else {
                    return false;
                }
                obj = obj[char];
            }
        };

        return Trie;

    })();

    Range = (function () {
        function Range(trie1) {
            this.trie = trie1;
            if (this.trie.constructor !== Trie) {
                throw Error('Range constructor requires a Trie parameter');
            }
        }

        Range.rangeWithString = function (ranges) {
            var j, k, len, n, r, range, ref, ref1, trie;
            if (typeof ranges !== 'string') {
                throw Error('rangeWithString requires a string parameter');
            }
            ranges = ranges.replace(/ /g, '');
            ranges = ranges.split(',');
            trie = new Trie;
            for (j = 0, len = ranges.length; j < len; j++) {
                range = ranges[j];
                if (r = range.match(/^(\d+)-(\d+)$/)) {
                    for (n = k = ref = r[1], ref1 = r[2]; ref <= ref1 ? k <= ref1 : k >= ref1; n = ref <= ref1 ? ++k : --k) {
                        trie.push(n);
                    }
                } else if (range.match(/^\d+$/)) {
                    trie.push(range);
                } else {
                    throw Error("Invalid range '" + r + "'");
                }
            }
            return new Range(trie);
        };

        Range.prototype.match = function (number) {
            return this.trie.find(number);
        };

        return Range;

    })();

    $ = jQuery;

    $.fn.es_validate_card = function (callback, options) {
        var bind, card, card_type, card_types, get_card_type, is_valid_length, is_valid_luhn, j, len, normalize, ref,
            validate, validate_number;
        card_types = [
            {
                name: 'amex',
                range: '34,37',
                valid_length: [15]
            }, {
                name: 'diners_club_carte_blanche',
                range: '300-305',
                valid_length: [14]
            }, {
                name: 'diners_club_international',
                range: '36',
                valid_length: [14]
            }, {
                name: 'jcb',
                range: '3528-3589',
                valid_length: [16]
            }, {
                name: 'laser',
                range: '6304, 6706, 6709, 6771',
                valid_length: [16, 17, 18, 19]
            }, {
                name: 'visa_electron',
                range: '4026, 417500, 4508, 4844, 4913, 4917',
                valid_length: [16]
            }, {
                name: 'visa',
                range: '4',
                valid_length: [13, 14, 15, 16, 17, 18, 19]
            }, {
                name: 'mastercard',
                range: '51-55,2221-2720',
                valid_length: [16]
            }, {
                name: 'discover',
                range: '6011, 622126-622925, 644-649, 65',
                valid_length: [16]
            }, {
                name: 'dankort',
                range: '5019',
                valid_length: [16]
            }, {
                name: 'maestro',
                range: '50, 56-69',
                valid_length: [12, 13, 14, 15, 16, 17, 18, 19]
            }, {
                name: 'uatp',
                range: '1',
                valid_length: [15]
            }
        ];
        bind = false;
        if (callback) {
            if (typeof callback === 'object') {
                options = callback;
                bind = false;
                callback = null;
            } else if (typeof callback === 'function') {
                bind = true;
            }
        }
        if (options == null) {
            options = {};
        }
        if (options.accept == null) {
            options.accept = (function () {
                var j, len, results;
                results = [];
                for (j = 0, len = card_types.length; j < len; j++) {
                    card = card_types[j];
                    results.push(card.name);
                }
                return results;
            })();
        }
        ref = options.accept;
        for (j = 0, len = ref.length; j < len; j++) {
            card_type = ref[j];
            if (indexOf.call((function () {
                var k, len1, results;
                results = [];
                for (k = 0, len1 = card_types.length; k < len1; k++) {
                    card = card_types[k];
                    results.push(card.name);
                }
                return results;
            })(), card_type) < 0) {
                throw Error("Credit card type '" + card_type + "' is not supported");
            }
        }
        get_card_type = function (number) {
            var k, len1, r, ref1;
            ref1 = (function () {
                var l, len1, ref1, results;
                results = [];
                for (l = 0, len1 = card_types.length; l < len1; l++) {
                    card = card_types[l];
                    if (ref1 = card.name, indexOf.call(options.accept, ref1) >= 0) {
                        results.push(card);
                    }
                }
                return results;
            })();
            for (k = 0, len1 = ref1.length; k < len1; k++) {
                card_type = ref1[k];
                r = Range.rangeWithString(card_type.range);
                if (r.match(number)) {
                    return card_type;
                }
            }
            return null;
        };
        is_valid_luhn = function (number) {
            var digit, k, len1, n, ref1, sum;
            sum = 0;
            ref1 = number.split('').reverse();
            for (n = k = 0, len1 = ref1.length; k < len1; n = ++k) {
                digit = ref1[n];
                digit = +digit;
                if (n % 2) {
                    digit *= 2;
                    if (digit < 10) {
                        sum += digit;
                    } else {
                        sum += digit - 9;
                    }
                } else {
                    sum += digit;
                }
            }
            return sum % 10 === 0;
        };
        is_valid_length = function (number, card_type) {
            var ref1;
            return ref1 = number.length, indexOf.call(card_type.valid_length, ref1) >= 0;
        };
        validate_number = function (number) {
            var length_valid, luhn_valid;
            card_type = get_card_type(number);
            luhn_valid = false;
            length_valid = false;
            if (card_type != null) {
                luhn_valid = is_valid_luhn(number);
                length_valid = is_valid_length(number, card_type);
            }
            return {
                card_type: card_type,
                valid: luhn_valid && length_valid,
                luhn_valid: luhn_valid,
                length_valid: length_valid
            };
        };
        validate = (function (_this) {
            return function () {
                var number;
                number = normalize($(_this).val());
                return validate_number(number);
            };
        })(this);
        normalize = function (number) {
            return number.replace(/[ -]/g, '');
        };
        if (!bind) {
            return validate();
        }
        this.on('input.jccv', (function (_this) {
            return function () {
                $(_this).off('keyup.jccv');
                return callback.call(_this, validate());
            };
        })(this));
        this.on('keyup.jccv', (function (_this) {
            return function () {
                return callback.call(_this, validate());
            };
        })(this));
        callback.call(this, validate());
        return this;
    };

}).call(this);

(function ($) {
    $.fn.es_validate = function (area) {
        var pass = true;

        $(this).each(function () {

            var
                el = $(this),
                data = [],
                value = '',
                name = el.attr('name'),
                nodeName = this.nodeName.toLowerCase(),
                type = this.type.toLowerCase();

            if (type === 'hidden') {
                return;
            }

            area = area || el.parents('form');
            if (nodeName === 'select') {
                value = $.trim(el.find('>option:selected').val());
            } else if (type === 'radio' || type === 'checkbox') {
                var checked = area.find('input[type="' + type + '"][name="' + name + '"]:checked');
                value = checked.length ? checked.val() : '';
            } else {
                value = el.val();
            }

            if (this.hasAttribute('required')) {
                el.attr('data-rule-required', '');
            }

            if (el.attr('class')) {
                var classes = el.attr('class').split(/\s+/g);
                for (var i = 0, n = classes.length; i < n; i++) {
                    var className = $.trim(classes[i]).toLowerCase();

                    if (className === 'required'
                        || className === 'validate-email'
                        || className === 'validate-number') {
                        el.attr('data-rule-' + className.replace('validate-', ''), '');
                    }
                }
            }

            data = el.data();
            // @since 1.3.0
            var parentField = el.parents('[data-field-validate-regex]:eq(0)');

            if (parentField.length) {
                data.ruleRegex = parentField.attr('data-field-validate-regex');

                if (parentField.attr('data-field-regex-message')) {
                    data.regexMsg = parentField.attr('data-field-regex-message');
                }
            }

            var invalid;
            var validate = function (rule) {
                switch (rule.toUpperCase()) {
                    case 'REQUIRED':
                        if (value.length <= 0) {
                            invalid = {
                                rule: 'required',
                                message: data['requiredMsg'] || _umart.lang._('COM_UMART_INPUT_INVALID_REQUIRED')
                            };
                            el.data('ruleInvalid').push(invalid);
                        }
                        break;

                    case 'MIN':
                        var min = parseInt(data[d]);
                        if (value.length < min) {
                            invalid = {
                                rule: 'minLength',
                                message: data['minLengthMsg'] || _umart.lang._('COM_UMART_INPUT_INVALID_MIN') + min
                            };
                            el.data('ruleInvalid').push(invalid);
                        }
                        break;

                    case 'MAX':
                        var max = parseInt(data[d]);
                        if (value.length > max) {
                            invalid = {
                                rule: 'maxLength',
                                message: data['maxLengthMsg'] || _umart.lang._('COM_UMART_INPUT_INVALID_MAX') + max
                            };
                            el.data('ruleInvalid').push(invalid);
                        }
                        break;

                    case 'EMAIL':
                        var regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        if (value.length && !regex.test(value)) {
                            invalid = {
                                rule: 'email',
                                message: data['emailMsg'] || _umart.lang._('COM_UMART_INPUT_INVALID_EMAIL')
                            };
                            el.data('ruleInvalid').push(invalid);
                        }
                        break;

                    case 'NUMBER':
                        if (!value.length && !/[\.0-9]+/g.test(value)) {
                            invalid = {
                                rule: 'number',
                                message: data['numberMsg'] || _umart.lang._('COM_UMART_INPUT_INVALID_NUMBER')
                            };
                            el.data('ruleInvalid').push(invalid);
                        }
                        break;

                    case 'REGEX':
                        if (!(new RegExp(data[d], 'g')).test(value)) {
                            invalid = {
                                rule: 'regex',
                                message: data['regexMsg'] || _umart.lang._('COM_UMART_INPUT_INVALID_REGEX')
                            };
                            el.data('ruleInvalid').push(invalid);
                        }
                        break;
                }
            };

            if (data) {
                el.data('ruleInvalid', []);

                if (type === 'checkbox' && el.parents('fieldset.required').length && value === '') {
                    el.data('ruleInvalid').push({
                        rule: 'required',
                        message: data['requiredMsg'] || _umart.lang._('COM_UMART_INPUT_INVALID_REQUIRED')
                    });
                }

                for (var d in data) {
                    if (d.length > 4 && d.indexOf('rule') === 0 && data[d] !== false) {
                        validate(d.substr(4));
                    }
                }
            }

            var
                invalids = $.unique(el.data('ruleInvalid'))
                , tip = ''
                , formControl;

            if (el.parent('.flatpickr').length) {
                formControl = el.parent('.flatpickr');
            } else {
                if (type === 'radio' || type === 'checkbox') {
                    formControl = area.find('input[type="' + type + '"][name="' + name + '"]').last().parent();
                } else {
                    formControl = el;
                }
            }

            formControl.next('.es-form-help-block').remove();

            if (invalids.length) {
                for (var i = 0, n = invalids.length; i < n; i++) {
                    tip += '<span class="uk-text-italic uk-text-danger">' + invalids[i].message + '</span>';
                }

                if (pass) {
                    pass = false;
                }

                el.addClass('invalid uk-form-danger');
                formControl.after('<div class="es-form-help-block">' + tip + '</div>');

            } else {
                el.removeClass('invalid uk-form-danger');
            }
        });

        return pass;
    };

    $(document).ready(function () {
        var form = $('form[data-validate]');

        if (form.length) {


            form.on('submit', function () {
                return $(this).find('input, textarea, select').es_validate();
            });

            form.on('change', 'input, textarea, select', function () {
                return $(this).es_validate();
            });
        }
    });
})(jQuery);
