Event.observe(window, 'load', processForm);

function disconnectConnection(storeViewId) {
    new Ajax.Request(disconnectUrl, {
        parameters: {
            storeviewid: storeViewId
        },
        onSuccess: function (response) {
            var responseText = response.responseText.evalJSON();
            if (responseText.success == true) {
                $('oauth_connect_form').submit();
                $$('#oauth_connect_form input[type=submit]').first().disabled = true;
            }
            else {
                alert(reponseText.errors);
            }
        }
    });
}

function processForm(bool) {
    /* mark any already used storeview option */
    for (var i = 0; i < shopgateConnectionsData.length; i++) {
        var element = $$('#oauth_connect_form select option[value=' + shopgateConnectionsData[i] + ']');
        if (element.length) {
            element = element.first();
        }

        element.update(element.innerHTML + ' (' + alreadyInUseString + ')');
        element.addClassName('already_in_use');
    }

    var dialog = new Dialog.Box('unregister_shopgate_connection_dialog')
    dialog.hide();
    var select = $$('#oauth_connect_form select').first();

    /* set the first not already used option as the selected index */
    var nonDisabledOptions = $$('#oauth_connect_form select option').findAll(function (s) {
        return s.hasClassName('already_in_use') == false;
    });
    if (nonDisabledOptions.length) {
        var firstNotDisabledOption = nonDisabledOptions.first();
        firstNotDisabledOption.selected = true;
        select.selectedOptions = firstNotDisabledOption;
        select.value = firstNotDisabledOption.value;
    }

    /* temporary save the current selected index */
    var currentIndex = $$('#oauth_connect_form select').first().options.selectedIndex;

    /* onchange handler for the select and the special behaviour of already used storeview's */
    select.observe('change', function (event) {
        var option = $(event.srcElement.selectedOptions[0]);
        if (option.hasClassName('already_in_use')) {
            dialog.show();
        }
        else {
            currentIndex = $$('#oauth_connect_form select').first().options.selectedIndex;
        }
    });

    /* observer for the decline option on disconnect dialog */
    $('disconnect_shopgate_storeview_decline').observe('click', function (event) {
        var select = $$('#oauth_connect_form select').first();
        select.selectedOptions = select.options[currentIndex];
        select.value = select.options[currentIndex].value;
        dialog.hide();
    });

    /* observer for the confirm option on disconnect dialog */
    $('disconnect_shopgate_storeview_confirm').observe('click', function (event) {
        dialog.hide();
        var select = $$('#oauth_connect_form select').first();
        disconnectConnection(select.value);
        currentIndex = $$('#oauth_connect_form select').first().options.selectedIndex;
    });
}

var Dialog = {};
Dialog.Box = Class.create();
Object.extend(Dialog.Box.prototype, {
    initialize: function (id) {
        this.createOverlay();

        this.dialog_box = $(id);
        this.dialog_box.show = this.show.bind(this);
        this.dialog_box.persistent_show = this.persistent_show.bind(this);
        this.dialog_box.hide = this.hide.bind(this);

        this.parent_element = this.dialog_box.parentNode;

        this.dialog_box.style.position = "absolute";

        var e_dims = Element.getDimensions(this.dialog_box);
        var b_dims = Element.getDimensions(this.overlay);

        this.dialog_box.style.left = ((b_dims.width / 2) - (e_dims.width / 2)) + 'px';
        this.dialog_box.style.top = this.getScrollTop() + ((this.winHeight() - (e_dims.width / 2)) / 2) + 'px';
        this.dialog_box.style.zIndex = this.overlay.style.zIndex + 1;
    },

    createOverlay: function () {
        if ($('dialog_overlay')) {
            this.overlay = $('dialog_overlay');
        } else {
            this.overlay = document.createElement('div');
            this.overlay.id = 'dialog_overlay';
            Object.extend(this.overlay.style, {
                position: 'absolute',
                top: 0,
                left: 0,
                zIndex: 90,
                width: '100%',
                backgroundColor: '#000',
                display: 'none'
            });
            document.body.insertBefore(this.overlay, document.body.childNodes[0]);
        }
    },

    moveDialogBox: function (where) {
        Element.remove(this.dialog_box);
        if (where == 'back')
            this.dialog_box = this.parent_element.appendChild(this.dialog_box);
        else
            this.dialog_box = this.overlay.parentNode.insertBefore(this.dialog_box, this.overlay);
    },

    show: function (optHeight/* optionally override the derived height, which often seems to be short. */) {
        this.overlay.style.height = this.winHeight() + 'px';
        this.moveDialogBox('out');

        this.selectBoxes('hide');
        new Effect.Appear(this.overlay, {duration: 0.1, from: 0.0, to: 0.3});
        this.dialog_box.style.display = '';

        this.dialog_box.style.left = '0px';

        var e_dims = Element.getDimensions(this.dialog_box);

        this.dialog_box.style.left = (this.winWidth() - e_dims.width) / 2 + 'px';

        var h = optHeight || (e_dims.height + 200);
        this.dialog_box.style.top = this.getScrollTop() + (this.winHeight() - h / 2) / 2 + 'px';
    },

    getScrollTop: function () {
        return (window.pageYOffset) ? window.pageYOffset : (document.documentElement && document.documentElement.scrollTop) ? document.documentElement.scrollTop : document.body.scrollTop;
    },

    persistent_show: function () {
        this.overlay.style.height = this.winHeight() + 'px';
        this.moveDialogBox('out');

        this.selectBoxes('hide');
        new Effect.Appear(this.overlay, {duration: 0.1, from: 0.0, to: 0.3});

        this.dialog_box.style.display = '';
        this.dialog_box.style.left = '0px';
        var e_dims = Element.getDimensions(this.dialog_box);
        this.dialog_box.style.left = (this.winWidth() / 2 - e_dims.width / 2) + 'px';
    },

    hide: function () {
        this.selectBoxes('show');
        new Effect.Fade(this.overlay, {duration: 0.1});
        this.dialog_box.style.display = 'none';
        this.moveDialogBox('back');
        $A(this.dialog_box.getElementsByTagName('input')).each(function (e) {
            if (e.type != 'submit' && e.type != 'button') e.value = '';
        });
    },

    selectBoxes: function (what) {
        $A(document.getElementsByTagName('select')).each(function (select) {
            Element[what](select);
        });

        if (what == 'hide')
            $A(this.dialog_box.getElementsByTagName('select')).each(function (select) {
                Element.show(select)
            })
    },

    winWidth: function () {
        if (typeof window.innerWidth != 'undefined')
            return window.innerWidth;
        if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientWidth != 'undefined' && document.documentElement.clientWidth != 0)
            return document.documentElement.clientWidth;
        return document.getElementsByTagName('body')[0].clientWidth
    },
    winHeight: function () {
        if (typeof window.innerHeight != 'undefined')
            return window.innerHeight
        if (typeof document.documentElement != 'undefined' && typeof document.documentElement.clientHeight != 'undefined' && document.documentElement.clientHeight != 0)
            return document.documentElement.clientHeight;
        return document.getElementsByTagName('body')[0].clientHeight;
    }
});