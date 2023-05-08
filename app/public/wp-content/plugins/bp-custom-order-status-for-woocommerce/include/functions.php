<?php

/**
 * @return mixed
 */
function bpcosOrderStatusList() {
	$allStatus                  = array();
	$statuses                   = wc_get_order_statuses();
	$allStatus['bpos_disabled'] = 'No changes';
	foreach ( $statuses as $status => $status_name ) {
		$allStatus[substr( $status, 3 )] = $status_name;
	}
	return $allStatus;

}
//Preorder Transition Status
add_filter( 'change_order_status_on_preorder_date', function ( $status ) {
	$bvos_options = get_option( 'wcbv_status_default' );
	return $bvos_options['preorder_status'];
}, 30, 1 );
