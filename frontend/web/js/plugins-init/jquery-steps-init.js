(function($) {
    "use strict"

    var form = $("#step-form-horizontal");
    form.children('div').steps({
        headerTag: "h4",
        bodyTag: "section",
        transitionEffect: "slideLeft",
        autoFocus: true,
        onStepChanging: function (event, currentIndex, newIndex)
        {
            form.validate().settings.ignore = ":disabled,:hidden";
            return form.valid();
        },
        onFinished: function (event, currentIndex) {
            form.submit();
        }
    });

})(jQuery);