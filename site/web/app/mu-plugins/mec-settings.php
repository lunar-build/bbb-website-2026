<?php

/**
 * Plugin Name: Modern Events Calendar settings
 * Description: Settings this site relies on, fixed in code so every environment matches. An mu-plugin because MEC reads and caches its settings while plugins load — before the theme's filters exist.
 */

/*
 * Text ("Alphabetic") cost type: the default number input loads our text
 * prices ("£5 per child | £7 per adult") as empty, so saving wiped them.
 */
add_filter('option_mec_options', function ($options) {
    if (is_array($options)) {
        $options['settings']['single_cost_type'] = 'alphabetic';

        // MEC's settings screens read these unchecked; until its settings are
        // first saved, each is a warning that Acorn turns into a 500.
        $options['settings'] += [
            'breadcrumbs_events_page' => '',
            'fes_event_link_target' => '',
        ];
    }

    return $options;
});
