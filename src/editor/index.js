import Klaviyo from "./modules/KlaviyoForm";

const KlaviyoELementor = Marionette.Application.extend({
	onStart: function onStart() {
		jQuery(window).on('elementor:init', this.onElementorInit);
	},

	onElementorInit: function onElementorInit() {
		elementor.debug.addURLToWatch('klaviyo-elementor/dist');
		elementorPro.modules.forms = new Klaviyo("form");
	}
});

window.klaviyoELementor = new KlaviyoELementor();

klaviyoELementor.start();