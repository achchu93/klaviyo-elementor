import ElementorEditorModule from "./ElementorEditorModule";

export default ElementorEditorModule.extend({
	__construct() {
		this.cache = {};
		ElementorEditorModule.prototype.__construct.apply(this, arguments);
	},

	getName() {
		return '';
	},

	getCacheKey(args) {
		return JSON.stringify({
			service: this.getName(),
			data: args
		});
	},

	fetchCache(type, cacheKey, requestArgs) {
		var _this = this;

		return elementorPro.ajax.addRequest('forms_panel_action_data', {
			unique_id: 'integrations_' + this.getName(),
			data: requestArgs,
			success: function success(data) {
				_this.cache[type] = _.extend({}, _this.cache[type]);
				_this.cache[type][cacheKey] = data[type];
			}
		});
	},

	updateOptions(name, options) {
		var controlView = this.getEditorControlView(name);

		if (controlView) {
			this.getEditorControlModel(name).set('options', options);

			controlView.render();
		}
	},

	onInit() {
		this.addSectionListener('section_' + this.getName(), this.onSectionActive);
	},

	onSectionActive() {
		this.onApiUpdate();
	},

	onApiUpdate() {
	}
});