<?php
/**
 * Code creates new column for Memberslist and Memberlist CSV export
 *
 */

function pmpro_memberslist_donation_header() {     ?>
<th><?php _e( 'Donation', 'pmprodon' ); ?></th>
	<?php
}
add_action( 'pmpro_memberslist_extra_cols_header', 'pmpro_memberslist_donation_header' );

function pmpro_memberslist_donation_body( $theuser ) {
	?>
	<td>
	<?php
		echo calculate_the_donation_amount( $theuser->ID );
	?>
	</td>
	<?php
}

add_action( 'pmpro_memberslist_extra_cols_body', 'pmpro_memberslist_donation_body' );

function pmpro_memberslist_csv_login_column( $columns ) {
	$new_columns = array(
		'last_donation' => 'pmpro_csv_donation_extra_column',
	);

	$columns = array_merge( $columns, $new_columns );

	return $columns;
}
add_filter( 'pmpro_members_list_csv_extra_columns', 'pmpro_memberslist_csv_login_column' );

function pmpro_csv_donation_extra_column( $user ) {
	$last_login = pmpro_get_last_member_login( $user );

	if ( $last_login ) {
		return $last_login;
	} else {
		return '';
	}
}


function calculate_the_donation_amount( $user_id ) {
	$user_object = get_pmpro_member_object( $user_id );
	$some_order = get_pmpro_member_order_object( $user_id, $user_object->ID );
	if ( empty( $some_order ) && empty( $user_object ) ) {
		return '-nada-';
	} elseif ( ! is_object( $some_order ) ) {
		return '-na-';
	} else {
		$donation = $some_order->subtotal - $user_object->initial_payment;
	}
	if ( null === $donation ) {
		return '--';
	}
	return $donation;
}

/**
 * [get_pmpro_member_level description]
 *
 * @param  [type] $user_id [description]
 * @return [type]           [description]
 */
function get_pmpro_member_object( $user_id ) {
	$user_object = new \WP_User( $user_id );
	$member_data = get_userdata( $user_object->ID );
	$member_object = pmpro_getMembershipLevelForUser( $member_data->ID );
	return $member_object;
}


function get_pmpro_member_order_object( $user_id, $level_id ) {
	global $wpdb;

	$sqlQuery = "
	SELECT id, code, user_id, subtotal, checkout_id, payment_type, payment_transaction_id, timestamp
	FROM $wpdb->pmpro_membership_orders
	WHERE membership_id = $level_id 
	AND  user_id = $user_id
	";
	$variable = $wpdb->get_results( $sqlQuery, OBJECT );
	if ( empty( $variable ) ) {
		return '-na-';
	} else {
		return $variable[0];
	}
}
