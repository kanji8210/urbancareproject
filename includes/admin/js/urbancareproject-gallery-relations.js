(function ($) {
	'use strict';

	$('[data-ucp-gallery-relations]').each(function () {
		const field = $(this);
		const picker = field.find('[data-ucp-gallery-relation-picker]');
		const list = field.find('[data-ucp-gallery-relation-list]');
		const input = field.find('[data-ucp-gallery-relation-ids]');

		function syncRelations() {
			input.val(list.find('[data-ucp-gallery-relation]').map(function () { return $(this).data('id'); }).get().join(','));
		}

		field.on('click', '[data-ucp-gallery-relation-add]', function () {
			const option = picker.find('option:selected');
			const id = Number(option.val());
			if (!id || list.find('[data-id="' + id + '"]').length) return;
			const item = $('<div>', { class: 'ucp-gallery-relation', 'data-ucp-gallery-relation': '', 'data-id': id });
			item.append($('<strong>').text(option.data('title') || option.text()));
			item.append('<div><button type="button" class="button-link" data-ucp-gallery-relation-up>Earlier</button><button type="button" class="button-link" data-ucp-gallery-relation-down>Later</button><button type="button" class="button-link-delete" data-ucp-gallery-relation-remove>Remove</button></div>');
			list.append(item);
			picker.val('');
			syncRelations();
		});
		field.on('click', '[data-ucp-gallery-relation-remove]', function () { $(this).closest('[data-ucp-gallery-relation]').remove(); syncRelations(); });
		field.on('click', '[data-ucp-gallery-relation-up]', function () { const item = $(this).closest('[data-ucp-gallery-relation]'); const previous = item.prev(); if (previous.length) item.insertBefore(previous); syncRelations(); });
		field.on('click', '[data-ucp-gallery-relation-down]', function () { const item = $(this).closest('[data-ucp-gallery-relation]'); const next = item.next(); if (next.length) item.insertAfter(next); syncRelations(); });
	});
})(jQuery);
