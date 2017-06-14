(function() {
    Array.isArray || (Array.isArray = function (a) {
        return '[object Array]' === Object.prototype.toString.call(a)
    });

    function fa(j, forceJs, k) {

        var fc = function (url) {
            for (var i = 0; i < forceJS.length; i++) {
                var rule = forceJS[i];
                var match = new RegExp("^" + fd(rule).split('\\*').join('.*') + "$").test(url);
                if (match === true) {
                    return true;
                }
            }

            return false;
        };

        var fd = function (str) {
            return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
        };

        var fe = function (url) {
            return document.querySelectorAll("script[src='" + url + "']").length ? true : false;
        };

        var l = function (q) {
            return function () {
                console.log(q + ' was loaded.'), 1 > --o && k()
            }
        };

        var m = function (q) {
            if (!fc(q) && (fe(q) && fc(q))) {
                var r = document.createElement('script');
                r.setAttribute('src', q), r.onload = l(q), document.head.appendChild(r)
            } else {
                l(q);
            }
        };

        for (var n, o = j.length; 0 < j.length && (n = j.shift());)
            if (Array.isArray(n)) {
                var p = j.splice(0, j.length);
                o -= p.length, fa(n, forceJs, function () {
                    0 < p.length ? fa(p, forceJs, k) : k()
                })
            } else m(n)
    };

    fa([['https://maps.googleapis.com/maps/api/js?key=AIzaSyAp4uSuXVYep6w2YQNUtyC9RDhaLsE842o&libraries=places&libraries=places,drawing&language=EN','/assets/jquery.min.js','/js/libraries/gmaps.min.js','/assets/es5-shim.min.js'],['/assets/jquery-ui.min.js'],['/js/assets/yii.min.js','/js/libraries/jquery.mask.min.js','/js/jquery.widgets/watch-password.jquery.min.js','/js/libraries/slick.min.js'],['/assets/js/library.extends.min.js','/js/modules/common.min.js'],['/js/modules/app/index.min.js','/js/modules/app/search-cities.min.js','/js/assets/yii.activeForm.min.js','/assets/js/bootstrap.min.js']], ['/js/modules/app/index.min.js','/js/modules/app/search-cities.min.js','/js/assets/yii.activeForm.min.js','/assets/js/bootstrap.min.js'], function() {jQuery(document).ready(function () {jQuery('#w0').yiiActiveForm([], []);(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');ga('create', 'UA-80042929-1', 'auto');ga('send', 'pageview');(function($){ $('.loader').fadeOut(1000); })(jQuery);});});
})();