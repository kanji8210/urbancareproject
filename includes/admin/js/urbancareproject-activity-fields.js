(function ($) {
	'use strict';

	function openImageFrame(title, multiple, onSelect) {
		const frame = wp.media({
			title: title,
			button: { text: multiple ? 'Add images' : 'Use this image' },
			library: { type: 'image' },
			multiple: multiple
		});
		frame.on('select', function () {
			onSelect(frame.state().get('selection').toJSON());
		});
		frame.open();
	}

	function imageSource(attachment) {
		return attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
	}

	function setEndDateState(checkbox, endDate) {
		const ongoing = checkbox.prop('checked');
		endDate.prop('disabled', ongoing);
		if (ongoing) {
			endDate.val('');
		}
	}

	const overallOngoing = $('#ucp_ongoing');
	const overallEndDate = $('#ucp_end_date');
	if (overallOngoing.length && overallEndDate.length) {
		setEndDateState(overallOngoing, overallEndDate);
		overallOngoing.on('change', function () {
			setEndDateState(overallOngoing, overallEndDate);
		});
	}

	$('[data-ucp-phases]').each(function () {
		const field = $(this);
		const list = field.find('[data-ucp-phase-list]');
		const template = field.find('[data-ucp-phase-template]').html();

		function reindexPhases() {
			list.find('[data-ucp-phase-row]').each(function (index) {
				$(this).find('[name]').each(function () {
					this.name = this.name.replace(/_ucp_activity_phases\]\[\d+\]/, '_ucp_activity_phases][' + index + ']');
				});
			});
		}

		field.on('click', '[data-ucp-phase-add]', function () {
			list.append(template.replaceAll('__INDEX__', String(list.find('[data-ucp-phase-row]').length)));
			reindexPhases();
		});

		field.on('change', '[data-ucp-phase-ongoing]', function () {
			const row = $(this).closest('[data-ucp-phase-row]');
			setEndDateState($(this), row.find('[data-ucp-phase-end]'));
		});

		field.find('[data-ucp-phase-ongoing]').each(function () {
			setEndDateState($(this), $(this).closest('[data-ucp-phase-row]').find('[data-ucp-phase-end]'));
		});

		field.on('click', '[data-ucp-phase-image-select]', function () {
			const media = $(this).closest('[data-ucp-phase-media]');
			openImageFrame('Choose programme phase image', false, function (attachments) {
				const attachment = attachments[0];
				media.find('[data-ucp-phase-image-id]').val(attachment.id);
				media.find('[data-ucp-phase-preview]').html($('<img>', { src: imageSource(attachment), alt: '' }));
				media.find('[data-ucp-phase-image-remove]').prop('hidden', false);
			});
		});

		field.on('click', '[data-ucp-phase-image-remove]', function () {
			const media = $(this).closest('[data-ucp-phase-media]');
			media.find('[data-ucp-phase-image-id]').val('0');
			media.find('[data-ucp-phase-preview]').empty();
			$(this).prop('hidden', true);
		});

		field.on('click', '[data-ucp-phase-remove]', function () {
			$(this).closest('[data-ucp-phase-row]').remove();
			reindexPhases();
		});
		field.on('click', '[data-ucp-phase-up]', function () {
			const row = $(this).closest('[data-ucp-phase-row]');
			const previous = row.prev('[data-ucp-phase-row]');
			if (previous.length) row.insertBefore(previous);
			reindexPhases();
		});
		field.on('click', '[data-ucp-phase-down]', function () {
			const row = $(this).closest('[data-ucp-phase-row]');
			const next = row.next('[data-ucp-phase-row]');
			if (next.length) row.insertAfter(next);
			reindexPhases();
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
			openImageFrame('Choose activity gallery images', true, function (attachments) {
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

	$('[data-ucp-relation-search]').on('input', function () {
		const query = $(this).val().toLowerCase().trim();
		$(this).next('.ucp-relation-select').find('option').each(function () {
			$(this).prop('hidden', query && !$(this).text().toLowerCase().includes(query));
		});
	});
})(jQuery);