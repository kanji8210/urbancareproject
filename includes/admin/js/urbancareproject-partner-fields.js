(function ($) {
	'use strict';

	const typeField = $('#ucp_partner_type');
	const mediaLabel = $('[data-ucp-media-label] strong');

	function updateAdaptiveCopy() {
		if (!typeField.length) {
			return;
		}
		const individual = typeField.val() === 'individual';
		mediaLabel.text(individual ? 'Profile photo' : 'Logo');
		$('#ucp_about').closest('.ucp-field').find('label strong').text(individual ? 'About the individual' : 'About the institution');
	}

	typeField.on('change', updateAdaptiveCopy);
	updateAdaptiveCopy();

	$('[data-ucp-media-field]').each(function () {
		const field = $(this);
		const input = field.find('[data-ucp-media-id]');
		const preview = field.find('[data-ucp-media-preview]');
		const remove = field.find('[data-ucp-media-remove]');

		field.find('[data-ucp-media-select]').on('click', function () {
			const title = field.data('ucp-media-title') || 'Choose image';
			const frame = wp.media({
				title: typeField.length ? (typeField.val() === 'individual' ? 'Choose profile photo' : 'Choose organization logo') : title,
				button: { text: 'Use this image' },
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
})(jQuery);