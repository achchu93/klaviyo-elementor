import KlaviyoAddToList from "./modules/KlaviyoAddToListForm";

const KlaviyoELementor = Marionette.Application.extend({
	onStart: function onStart() {
		jQuery(window).on('elementor:init', this.onElementorInit);
	},

	onElementorInit: function onElementorInit() {
		elementor.debug.addURLToWatch('klaviyo-elementor/dist');
		elementorPro.modules.forms = new KlaviyoAddToList("form");
	}
});

window.klaviyoELementor = new KlaviyoELementor();

klaviyoELementor.start();