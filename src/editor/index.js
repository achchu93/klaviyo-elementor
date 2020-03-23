import KlaviyotorAddToList from "./modules/KlaviyotorAddToListForm";

const Klaviyotor = Marionette.Application.extend({
	onStart: function onStart() {
		jQuery(window).on('elementor:init', this.onElementorInit);
	},

	onElementorInit: function onElementorInit() {
		elementor.debug.addURLToWatch('klaviyo-elementor/dist');
		elementorPro.modules.forms = new KlaviyotorAddToList("form");
	}
});

window.klaviyotor = new Klaviyotor();

klaviyotor.start();