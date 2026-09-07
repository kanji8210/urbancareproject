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

	$('[data-ucp-location-search]').on('input', function () {
		const query = $(this).val().toLowerCase().trim();
		$(this).next('[data-ucp-location-select]').find('option').each(function () {
			$(this).prop('hidden', query && !$(this).text().toLowerCase().includes(query));
		});
	});

	$('[data-ucp-location-select]').on('change', function () {
		const siteId = $(this).find('option:selected').data('site-id');
		if (siteId) $('#ucp_related_site_ids').find('option[value="' + siteId + '"]').prop('selected', true);
	});

	$('[data-ucp-study-site-toggle]').on('click', function () {
		const button = $(this);
		const form = button.next('[data-ucp-study-site-form]');
		const isOpening = form.prop('hidden');
		form.prop('hidden', !isOpening);
		button.attr('aria-expanded', String(isOpening));
		if (isOpening) form.find('[data-ucp-study-site-title]').trigger('focus');
	});

	$('[data-ucp-study-site-cancel]').on('click', function () {
		const form = $(this).closest('[data-ucp-study-site-form]');
		form.prop('hidden', true).prev('[data-ucp-study-site-toggle]').attr('aria-expanded', 'false');
		form.find('input').val('');
		form.find('[data-ucp-study-site-status]').text('');
	});

	$('[data-ucp-study-site-save]').on('click', function () {
		const button = $(this);
		const form = button.closest('[data-ucp-study-site-form]');
		const title = form.find('[data-ucp-study-site-title]').val().trim();
		const location = form.find('[data-ucp-study-site-location]').val().trim();
		const status = form.find('[data-ucp-study-site-status]');
		if (!title || !location) {
			status.text('Enter both a Study Site name and location.');
			return;
		}

		button.prop('disabled', true);
		status.text('Creating Study Site...');
		$.post(ucpActivityFields.ajaxUrl, {
			action: 'ucp_create_study_site',
			nonce: ucpActivityFields.nonce,
			title: title,
			location: location
		}).done(function (response) {
			const site = response.data;
			const locationSelect = $('[data-ucp-location-select]');
			locationSelect.append($('<option>', { value: site.location, text: site.label, selected: true }).attr('data-site-id', site.id));

			const siteSelect = $('#ucp_related_site_ids');
			if (!siteSelect.find('option[value="' + site.id + '"]').length) {
				siteSelect.append($('<option>', { value: site.id, text: site.title, selected: true }));
			} else {
				siteSelect.find('option[value="' + site.id + '"]').prop('selected', true);
			}

			form.find('input').val('');
			status.text('Study Site created and selected.');
			form.prop('hidden', true).prev('[data-ucp-study-site-toggle]').attr('aria-expanded', 'false');
		}).fail(function (request) {
			const message = request.responseJSON && request.responseJSON.data && request.responseJSON.data.message;
			status.text(message || 'The Study Site could not be created.');
		}).always(function () {
			button.prop('disabled', false);
		});
	});
})(jQuery);