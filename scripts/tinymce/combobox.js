// Override the Combobox in TinyMCE
tinymce.ui.ComboBox.prototype.renderHtml = function() {
	var self = this, id = self._id, settings = self.settings, prefix = self.classPrefix;
	var value = settings.value || settings.placeholder || '';
	var icon, text, openBtnHtml = '', extraAttrs = '';

	if ("spellcheck" in settings) {
		extraAttrs += ' spellcheck="' + settings.spellcheck + '"';
	}

	if (settings.maxLength) {
		extraAttrs += ' maxlength="' + settings.maxLength + '"';
	}

	if (settings.size) {
		extraAttrs += ' size="' + settings.size + '"';
	}

	if (settings.subtype) {
		extraAttrs += ' type="' + settings.subtype + '"';
	}

	if (self.disabled()) {
		extraAttrs += ' disabled="disabled"';
	}

	icon = settings.icon;
	if (icon && icon != 'caret') {
		icon = prefix + 'ico ' + prefix + 'i-' + settings.icon;
	}

	text = self._text;

	if (icon || text) {
		openBtnHtml = (
			'<div id="' + id + '-open" class="' + prefix + 'btn ' + prefix + 'open" tabIndex="-1" role="button">' +
				'<button id="' + id + '-action" type="button" hidefocus="1" tabindex="-1">' +
					(icon != 'caret' ? '<i class="' + icon + '"></i>' : '<i class="' + prefix + 'caret"></i>') +
					(text ? (icon ? ' ' : '') + text : '') +
				'</button>' +
			'</div>'
		);

		self.addClass('has-open');
	}

	return (
		'<div id="' + id + '" class="' + self.classes() + '">' +
			'<textarea style="white-space:nowrap; overflow:hidden; width:246px !important;height:16px;border-radius: 4px 0 0 4px;padding-right:2px !important;" id="' + id + '-inp" class="' + prefix + 'textbox ' + prefix + 'placeholder" hidefocus="1"' + extraAttrs + '>' + value + '</textarea>' +
			openBtnHtml +
		'</div>'
	);
};
