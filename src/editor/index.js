import KlaviyoWPAction from "./modules/KlaviyoWPForm";

const KlaviyoWP = Marionette.Application.extend({
	onStart: function onStart() {
		jQuery(window).on('elementor:init', this.onElementorInit);
	},

	onElementorInit: function onElementorInit() {
		elementor.debug.addURLToWatch('klaviyo-elementor/dist');
		elementorPro.modules.forms = new KlaviyoWPAction("form");
	}
});

window.klaviyowp = new KlaviyoWP();

klaviyowp.start();