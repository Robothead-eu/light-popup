( function () {
	'use strict';

	var triggerHints = {
		time_delay:   { label: 'Delay (seconds)', hint: 'e.g. 8' },
		scroll_depth: { label: 'Scroll depth (%)', hint: 'e.g. 50' },
		exit_intent:  { label: '', hint: '' },
		click:        { label: 'CSS selector', hint: 'e.g. #my-button or .open-popup' },
	};

	function updateTriggerField( selectEl, valueWrapId, valueLabelId, valueHintId ) {
		var val      = selectEl.value;
		var wrap     = document.getElementById( valueWrapId );
		var label    = document.getElementById( valueLabelId );
		var hint     = document.getElementById( valueHintId );
		var info     = triggerHints[ val ] || {};

		if ( ! wrap ) return;

		if ( 'exit_intent' === val || '' === val ) {
			wrap.style.display = 'none';
		} else {
			wrap.style.display = '';
			if ( label && info.label ) label.textContent = info.label;
			if ( hint ) hint.textContent = info.hint || '';
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {

		// Primary trigger.
		var primarySelect = document.getElementById( 'lp_trigger_type' );
		if ( primarySelect ) {
			updateTriggerField( primarySelect, 'lp_trigger_value_wrap', 'lp_trigger_value_label', 'lp_trigger_value_hint' );
			primarySelect.addEventListener( 'change', function () {
				updateTriggerField( this, 'lp_trigger_value_wrap', 'lp_trigger_value_label', 'lp_trigger_value_hint' );
			} );
		}

		// Secondary trigger.
		var secondarySelect = document.getElementById( 'lp_trigger_secondary_type' );
		if ( secondarySelect ) {
			updateTriggerField( secondarySelect, 'lp_trigger_2_value_wrap', null, 'lp_trigger_2_value_hint' );
			secondarySelect.addEventListener( 'change', function () {
				updateTriggerField( this, 'lp_trigger_2_value_wrap', null, 'lp_trigger_2_value_hint' );
			} );
		}

		// Targeting type radios.
		var targetingRadios = document.querySelectorAll( 'input[name="lp_targeting_type"]' );
		var idsWrap         = document.getElementById( 'lp_targeting_ids_wrap' );
		var typesWrap       = document.getElementById( 'lp_targeting_post_types_wrap' );

		function updateTargeting() {
			var checked = document.querySelector( 'input[name="lp_targeting_type"]:checked' );
			if ( ! checked ) return;
			if ( idsWrap )   idsWrap.style.display   = ( 'page_ids'    === checked.value ) ? '' : 'none';
			if ( typesWrap ) typesWrap.style.display  = ( 'post_types'  === checked.value ) ? '' : 'none';
		}

		targetingRadios.forEach( function ( radio ) {
			radio.addEventListener( 'change', updateTargeting );
		} );
		updateTargeting();

		// GDPR label field.
		var gdprCheckbox = document.getElementById( 'lp_gdpr_checkbox' );
		var gdprLabelWrap = document.getElementById( 'lp_gdpr_label_wrap' );

		function updateGdpr() {
			if ( ! gdprCheckbox || ! gdprLabelWrap ) return;
			gdprLabelWrap.style.display = gdprCheckbox.checked ? '' : 'none';
		}

		if ( gdprCheckbox ) {
			gdprCheckbox.addEventListener( 'change', updateGdpr );
			updateGdpr();
		}
	} );
} )();
