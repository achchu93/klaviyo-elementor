import IntegrationBase from "./IntegrationBase.js";

export default IntegrationBase.extend({

	getName() {
		return 'klaviyo-elementor';
	},

	onElementChange(setting) {
		if(setting === 'klaviyo_api_key'){
			this.onApiUpdate();
		}
	},

	onApiUpdate() {
		var self = this,
			controlView = self.getEditorControlView('klaviyo_api_key');

		if ('' === controlView.getControlValue()) {
			self.updateOptions('klaviyo_list', []);
			self.getEditorControlView('klaviyo_list').setValue('');
			return;
		}

		self.addControlSpinner('klaviyo_list');
		var cacheKey = this.getCacheKey({
			type: 'lists',
			controls: [controlView.getControlValue()]
		});

		self.getKlaviyoCache('lists', 'lists', cacheKey).done(function (data) {
			self.updateOptions('klaviyo_list', data);
		});
	},

	getKlaviyoCache(type, action, cacheKey, requestArgs) {
		if (_.has(this.cache[type], cacheKey)) {
			var data = {};
			data[type] = this.cache[type][cacheKey];
			return jQuery.Deferred().resolve(data);
		}

		requestArgs = _.extend({}, requestArgs, {
			service: 'klaviyo-elementor',
			klaviyo_action: action,
			api_key: this.getEditorControlView('klaviyo_api_key').getControlValue(),
		});

		return this.fetchCache(type, cacheKey, requestArgs);
	},

	onSectionActive() {
		IntegrationBase.prototype.onSectionActive.apply(this, arguments);
		this.onApiUpdate();
	}
});