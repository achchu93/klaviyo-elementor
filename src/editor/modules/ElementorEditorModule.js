export default elementorModules.editor.utils.Module.extend({
	elementType: null,

	__construct(elementType) {
		this.elementType = elementType;

		this.addEditorListener();
	},

	addEditorListener() {
		var self = this;

		if (self.onElementChange) {
			var eventName = 'change';

			if ('global' !== self.elementType) {
				eventName += ':' + self.elementType;
			}

			elementor.channels.editor.on(eventName, function (controlView, elementView) {
				self.onElementChange(controlView.model.get('name'), controlView, elementView);
			});
		}
	},

	addControlSpinner(name) {
		var $el = this.getEditorControlView(name).$el,
			$input = $el.find(':input');

		if ($input.attr('disabled')) {
			return;
		}

		$input.attr('disabled', true);

		$el.find('.elementor-control-title').after('<span class="elementor-control-spinner"><i class="eicon-spinner eicon-animation-spin"></i>&nbsp;</span>');
	},

	removeControlSpinner(name) {
		var $controlEl = this.getEditorControlView(name).$el;

		$controlEl.find(':input').attr('disabled', false);
		$controlEl.find('elementor-control-spinner').remove();
	},

	addSectionListener(section, callback) {
		var self = this;

		elementor.channels.editor.on('section:activated', function (sectionName, editor) {
			var model = editor.getOption('editedElementView').getEditModel(),
				currentElementType = model.get('elType'),
				_arguments = arguments;

			if ('widget' === currentElementType) {
				currentElementType = model.get('widgetType');
			}

			if (self.elementType === currentElementType && section === sectionName) {
				setTimeout(function () {
					callback.apply(self, _arguments);
				}, 10);
			}
		});
	},
})