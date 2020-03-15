export const ApiValidation = function (key, fieldId) {

	const self = this;

	self.cacheElements = () => {
		this.cache = {
			$button: jQuery('#elementor_pro_' + key + '_button'),
			$apiKeyField: jQuery('#elementor_pro_' + key),
			$apiUrlField: jQuery('#elementor_pro_' + fieldId)
		};
	}

	self.bindEvents = () => {
		this.cache.$button.on('click', (event) => {
			event.preventDefault();
			this.validateApi();
		});

		this.cache.$apiKeyField.on('change', () => {
			this.setState('clear');
		});
	}

	self.validateApi = () => {
		this.setState('loading');

		const apiKey = this.cache.$apiKeyField.val();

		if ('' === apiKey) {
			this.setState('clear');
			return;
		}

		if (this.cache.$apiUrlField.length && '' === this.cache.$apiUrlField.val()) {
			this.setState('clear');
			return;
		}

		jQuery.post(ajaxurl, {
			action: this.cache.$button.data('action'),
			api_key: apiKey,
			api_url: this.cache.$apiUrlField.val(),
			_nonce: this.cache.$button.data('nonce')
		}).done((data) => {
			if (data.success) {
				this.setState('success');
			} else {
				this.setState('error');
			}
		}).fail(() => {
			this.setState();
		});
	}

	self.setState = (type) => {
		const classes = ['loading', 'success', 'error'];

		let currentClass, classIndex;

		for (classIndex in classes) {
			currentClass = classes[classIndex];
			if (type === currentClass) {
				this.cache.$button.addClass(currentClass);
			} else {
				this.cache.$button.removeClass(currentClass);
			}
		}
	}

	self.init = () => {
		this.cacheElements();
		this.bindEvents();
	}

	self.init();
}