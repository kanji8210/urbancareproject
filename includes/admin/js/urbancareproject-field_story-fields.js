(function ($) {
	'use strict';

	function openGalleryFrame(onSelect) {
		const frame = wp.media({
			title: 'Choose gallery images',
			button: { text: 'Add images' },
			library: { type: 'image' },
			multiple: true
		});
		frame.on('select', function () {
			onSelect(frame.state().get('selection').toJSON());
		});
		frame.open();
	}

	function imageSource(attachment) {
		return attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
	}

	$('[data-ucp-media-field]').each(function () {
		const field = $(this);
		const input = field.find('[data-ucp-media-id]');
		const preview = field.find('[data-ucp-media-preview]');
		const remove = field.find('[data-ucp-media-remove]');

		field.find('[data-ucp-media-select]').on('click', function () {
			const frame = wp.media({
				title: field.data('ucp-media-title') || 'Choose image',
				button: { text: 'Use this image' },
				library: { type: 'image' },
				multiple: false
			});
			frame.on('select', function () {
				const attachment = frame.state().get('selection').first().toJSON();
				input.val(attachment.id);
				preview.html($('<img>', { src: imageSource(attachment), alt: '' }));
				remove.prop('hidden', false);
			});
			frame.open();
		});

		remove.on('click', function () {
			input.val('');
			preview.empty();
			remove.prop('hidden', true);
		});
	});

	$('[data-ucp-story-lenses]').each(function () {
		const field = $(this);
		const list = field.find('[data-ucp-story-lens-list]');
		const template = field.find('[data-ucp-story-lens-template]').html();

		function reindex() {
			list.find('[data-ucp-story-lens-row]').each(function (index) {
				$(this).find('[name]').each(function () {
					this.name = this.name.replace(/_ucp_story_lenses\]\[\d+\]/, '_ucp_story_lenses][' + index + ']');
				});
			});
		}

		field.on('click', '[data-ucp-story-lens-add]', function () {
			list.append(template.replaceAll('__INDEX__', String(list.find('[data-ucp-story-lens-row]').length)));
			reindex();
		});
		field.on('click', '[data-ucp-story-lens-remove]', function () {
			$(this).closest('[data-ucp-story-lens-row]').remove();
			reindex();
		});
		field.on('click', '[data-ucp-story-lens-up]', function () {
			const row = $(this).closest('[data-ucp-story-lens-row]');
			const previous = row.prev('[data-ucp-story-lens-row]');
			if (previous.length) row.insertBefore(previous);
			reindex();
		});
		field.on('click', '[data-ucp-story-lens-down]', function () {
			const row = $(this).closest('[data-ucp-story-lens-row]');
			const next = row.next('[data-ucp-story-lens-row]');
			if (next.length) row.insertAfter(next);
			reindex();
		});
	});

	$('[data-ucp-gallery]').each(function () {
		const gallery = $(this);
		const list = gallery.find('[data-ucp-gallery-list]');
		const input = gallery.find('[data-ucp-gallery-ids]');

		function syncGallery() {
			input.val(list.find('[data-ucp-gallery-item]').map(function () { return $(this).data('id'); }).get().join(','));
		}

		gallery.on('click', '[data-ucp-gallery-add]', function () {
			openGalleryFrame(function (attachments) {
				attachments.forEach(function (attachment) {
					if (list.find('[data-id="' + attachment.id + '"]').length) return;
					const item = $('<div>', { class: 'ucp-gallery__item', 'data-ucp-gallery-item': '', 'data-id': attachment.id });
					item.append($('<img>', { src: imageSource(attachment), alt: '' }));
					item.append('<div><button type="button" class="button-link" data-ucp-gallery-up>Earlier</button><button type="button" class="button-link" data-ucp-gallery-down>Later</button><button type="button" class="button-link-delete" data-ucp-gallery-remove>Remove</button></div>');
					list.append(item);
				});
				syncGallery();
			});
		});
		gallery.on('click', '[data-ucp-gallery-remove]', function () { $(this).closest('[data-ucp-gallery-item]').remove(); syncGallery(); });
		gallery.on('click', '[data-ucp-gallery-up]', function () { const item = $(this).closest('[data-ucp-gallery-item]'); const previous = item.prev(); if (previous.length) item.insertBefore(previous); syncGallery(); });
		gallery.on('click', '[data-ucp-gallery-down]', function () { const item = $(this).closest('[data-ucp-gallery-item]'); const next = item.next(); if (next.length) item.insertAfter(next); syncGallery(); });
	});

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

	$('[data-ucp-relation-search]').on('input', function () {
		const query = $(this).val().toLowerCase().trim();
		$(this).next('.ucp-relation-select').find('option').each(function () {
			$(this).prop('hidden', query && !$(this).text().toLowerCase().includes(query));
		});
	});
})(jQuery);