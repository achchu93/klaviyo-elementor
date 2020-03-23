import IntegrationBase from "./IntegrationBase.js";

export default IntegrationBase.extend({

	getName() {
		return 'klaviyotor';
	},

	onElementChange(setting) {
		if( ["klaviyo_api_key_source", "klaviyo_api_key"].includes(setting) ){
			this.onApiUpdate();
		}
	},

	onApiUpdate() {
		var self = this,
			controlView = self.getEditorControlView('klaviyo_api_key'),
			globalControlView = self.getEditorControlView('klaviyo_api_key_source');

		if ('default' !== globalControlView.getControlValue() && '' === controlView.getControlValue()) {
			self.updateOptions('klaviyo_list', []);
			self.getEditorControlView('klaviyo_list').setValue('');
			return;
		}

		self.addControlSpinner('klaviyo_list');
		var cacheKey = this.getCacheKey({
			type: 'lists',
			controls: [controlView.getControlValue(), globalControlView.getControlValue()]
		});

		self.getKlaviyoCache('lists', 'lists', cacheKey).done(function (data) {
			self.updateOptions('klaviyo_list', data.lists);
		});
	},

	getKlaviyoCache(type, action, cacheKey, requestArgs) {
		if (_.has(this.cache[type], cacheKey)) {
			var data = {};
			data[type] = this.cache[type][cacheKey];
			return jQuery.Deferred().resolve(data);
		}

		requestArgs = _.extend({}, requestArgs, {
			service: 'klaviyotor',
			klaviyo_action: action,
			api_key: this.getEditorControlView('klaviyo_api_key').getControlValue(),
			use_global_api_key: this.getEditorControlView('klaviyo_api_key_source').getControlValue()
		});

		return this.fetchCache(type, cacheKey, requestArgs);
	},

	onSectionActive() {
		IntegrationBase.prototype.onSectionActive.apply(this, arguments);
		this.onApiUpdate();
	}
});