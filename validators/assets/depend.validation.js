(function ($) {
    $.fn.extend(yii.validation, {
        dependedrequired: function (form, value, messages, options) {
            var depended = options.depended;
            var isValid = depended.every(function (element) {
                value = $('[name="' + element + '"]').val();
                var isString = typeof value == 'string' || value instanceof String;
                return !yii.validation.isEmpty(isString ? $.trim(value) : value);
            });
            if (!isValid) {
                yii.validation.addMessage(messages, options.message, value);
            }
        }
    });
})(jQuery);
