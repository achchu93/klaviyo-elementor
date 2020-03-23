import KlaviyotorAction from "./modules/KlaviyotorForm";

const Klaviyotor = Marionette.Application.extend({
	onStart: function onStart() {
		jQuery(window).on('elementor:init', this.onElementorInit);
	},

	onElementorInit: function onElementorInit() {
		elementor.debug.addURLToWatch('klaviyo-elementor/dist');
		elementorPro.modules.forms = new KlaviyotorAction("form");
	}
});

window.klaviyotor = new Klaviyotor();

klaviyotor.start();