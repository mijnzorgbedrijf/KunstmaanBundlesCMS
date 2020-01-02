var kunstmaanbundles = kunstmaanbundles || {};

kunstmaanbundles.mediaChooser = (function (window, undefined) {

    var init, initDelBtn, initCropBtn;

    var $body = $('body');

    init = function () {
        // Save and update preview can be found in url-chooser.js
        initDelBtn();
        initCropBtn();
    };


    // Del btn
    initDelBtn = function () {
        $body.on('click', '.js-media-chooser-del-preview-btn', function (e) {
            var $this = $(this),
                linkedID = $this.data('linked-id'),
                $widget = $('#' + linkedID + '-widget'),
                $input = $('#' + linkedID);

            $this.parent('.media-chooser__preview').find('.media-chooser__preview__img').attr({
                'src': '',
                'srcset': '',
                'alt': ''
            });

            $(".media-thumbnail__icon").remove();

            $widget.removeClass('media-chooser--choosen');
            $input.val('');
        });
    };

    // Del btn
    initCropBtn = function () {
        $body.on('click', '.js-media-chooser-crop-preview-btn', function (e) {
            var $this = $(this),
                linkedID = $this.data('linked-id'),
                $widget = $('#' + linkedID + '-widget'),
                $mediaCropperModal = $('#' + linkedID + '-mediaCropperModal'),
                $input = $('#' + linkedID);

            console.log(linkedID)

            $mediaCropperModal.modal('show');
        });
    };

    return {
        init: init
    };

})(window);
