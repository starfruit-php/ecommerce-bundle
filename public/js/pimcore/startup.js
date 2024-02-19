pimcore.registerNS("pimcore.plugin.StarfruitEcommerceBundle");

pimcore.plugin.StarfruitEcommerceBundle = Class.create({

    initialize: function () {
        document.addEventListener(pimcore.events.pimcoreReady, this.pimcoreReady.bind(this));
    },

    pimcoreReady: function (e) {
        // alert("StarfruitEcommerceBundle ready!");
    }
});

var StarfruitEcommerceBundlePlugin = new pimcore.plugin.StarfruitEcommerceBundle();
