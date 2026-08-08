(function ($) {
    'use strict';

    if (typeof wp === 'undefined' || typeof wp.customize === 'undefined') {
        return;
    }

    function initBannersControl(control) {
        var container = control.container;
        var $thumbs = container.find('.farmapaz-banners-thumbs');
        var $addBtn = container.find('.farmapaz-banners-add');
        var setting = wp.customize(control.id);
        var frame = null;

        function currentIds() {
            var v = setting.get();
            if (!v) return [];
            return String(v).split(',').filter(Boolean).map(Number);
        }

        function getAttachmentUrl(id) {
            return wp.media.attachment(id).get('sizes')
                ? (wp.media.attachment(id).get('sizes').thumbnail || wp.media.attachment(id).get('sizes').full).url
                : wp.media.attachment(id).get('url');
        }

        function renderThumbs(ids) {
            $thumbs.empty();
            ids.forEach(function (id) {
                var att = wp.media.attachment(id);
                var url = att.get('url');
                var $thumb = $('<div class="farmapaz-banner-thumb" data-id="' + id + '">' +
                    '<img src="' + url + '" loading="lazy">' +
                    '<span class="remove" title="Quitar este banner">&times;</span>' +
                    '</div>');
                if (!att.get('sizes')) {
                    att.fetch();
                    att.on('change:sizes', function () {
                        var u = getAttachmentUrl(id);
                        if (u) $thumb.find('img').attr('src', u);
                    });
                }
                $thumbs.append($thumb);
            });
        }

        $addBtn.on('click', function (e) {
            e.preventDefault();
            if (frame) frame.detach();
            frame = wp.media({
                title: 'Selecciona las imágenes del carrusel',
                multiple: 'add',
                library: { type: 'image' },
                button: { text: 'Usar estas imágenes' }
            });

            frame.on('open', function () {
                var selection = frame.state().get('selection');
                var ids = currentIds();
                ids.forEach(function (id) {
                    var att = wp.media.attachment(id);
                    att.fetch();
                    selection.add(att);
                });
            });

            frame.on('select', function () {
                var selection = frame.state().get('selection');
                var ids = [];
                selection.each(function (att) {
                    ids.push(att.id);
                });
                setting.set(ids.join(','));
                renderThumbs(ids);
            });

            frame.open();
        });

        $thumbs.on('click', '.remove', function () {
            var id = Number($(this).closest('.farmapaz-banner-thumb').data('id'));
            var ids = currentIds().filter(function (x) { return x !== id; });
            setting.set(ids.join(','));
            renderThumbs(ids);
        });

        setting.bind(function (value) {
            renderThumbs(String(value).split(',').filter(Boolean).map(Number));
        });

        renderThumbs(currentIds());
    }

    function initAllBannersControls() {
        wp.customize.control.each(function (control) {
            if (control.params && control.params.type === 'farmapaz_multi_image') {
                initBannersControl(control);
            }
        });
    }

    wp.customize.bind('ready', initAllBannersControls);
    wp.customize.control.bind('add', function (control) {
        if (control.params && control.params.type === 'farmapaz_multi_image') {
            initBannersControl(control);
        }
    });
})(jQuery);
