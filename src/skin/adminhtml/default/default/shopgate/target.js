document.observe("dom:loaded", function () {
    setExternalLinkTarget('/shopgate/shopgate/');
    setExternalLinkTarget('/shopgate/support/');
});

function setExternalLinkTarget(route) {
    $$('.nav-bar a[href*="' + route + '"]').each(function (link) {
        link.writeAttribute('target', 'external-link-shopgate');
    });
}