(function ($) {
	'use strict';

	$('[data-ucp-media-field]').each(function () {
		const field = $(this);
		const input = field.find('[data-ucp-media-id]');
		const preview = field.find('[data-ucp-media-preview]');
		const remove = field.find('[data-ucp-media-remove]');

		field.find('[data-ucp-media-select]').on('click', function () {
			const frame = wp.media({
				title: 'Choose team member portrait',
				button: { text: 'Use this portrait' },
				library: { type: 'image' },
				multiple: false
			});

			frame.on('select', function () {
				const attachment = frame.state().get('selection').first().toJSON();
				const source = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
				input.val(attachment.id);
				preview.html($('<img>', { src: source, alt: '' }).css({
					display: 'block', maxWidth: '240px', maxHeight: '160px', margin: '8px 0', objectFit: 'contain', background: '#fff', border: '1px solid #dcdcde', padding: '8px'
				}));
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

	$('[data-ucp-publications]').each(function () {
		const field = $(this);
		const list = field.find('[data-ucp-publication-list]');
		const template = field.find('[data-ucp-publication-template]').html();
		let nextIndex = list.find('[data-ucp-publication-row]').length;

		field.on('click', '[data-ucp-publication-add]', function () {
			list.append(template.replaceAll('__INDEX__', String(nextIndex)));
			nextIndex += 1;
		});

		field.on('click', '[data-ucp-publication-remove]', function () {
			$(this).closest('[data-ucp-publication-row]').remove();
		});

		field.on('click', '[data-ucp-publication-up]', function () {
			const row = $(this).closest('[data-ucp-publication-row]');
			const previous = row.prev('[data-ucp-publication-row]');
			if (previous.length) {
				row.insertBefore(previous);
			}
		});

		field.on('click', '[data-ucp-publication-down]', function () {
			const row = $(this).closest('[data-ucp-publication-row]');
			const next = row.next('[data-ucp-publication-row]');
			if (next.length) {
				row.insertAfter(next);
			}
		});
	});
})(jQuery);