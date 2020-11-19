<?php

class Glossary_Admin {

	/**
	 * Construct the Glossary object.
	 */
	public function __construct() {
		// Must be after Handbooks Glossary loaded on init priority 10
		add_action( 'init', array( $this, 'register_post_type' ), 100 );

		// Add an "Alternative Names" metabox to the Glossary edit screen
		add_action( 'add_meta_boxes', array( $this, 'register_alternatives_metabox' ) );
		add_action( 'edit_form_after_title', array( $this, 'form_after_title' ) );
		add_action( 'save_post_glossary', array( $this, 'save_alternatives_metabox' ) );
	}

	/**
	 * When the Handbook Glossary post_type isn't available, register our own.
	 */
	public function register_post_type() {
		if ( post_type_exists( 'glossary' ) ) {
			return;
		}

		register_post_type(
			'glossary',
			array(
				'labels'       => array(
					'name'               => _x( 'القاموس', 'wporg-glossary' ),
					'singular_name'      => _x( 'المصطلح', 'wporg-glossary' ),
					'add_new'            => _x( 'مصطلح جديد', 'wporg-glossary' ),
					'add_new_item'       => _x( 'إضافة مصطلح جديد', 'wporg-glossary' ),
					'edit_item'          => _x( 'تحرير المصطلح', 'wporg-glossary' ),
					'new_item'           => _x( 'مصطلح جديد', 'wporg-glossary' ),
					'view_item'          => _x( 'عرض المصطلح', 'wporg-glossary' ),
					'search_items'       => _x( 'البحث في القاموس', 'wporg-glossary' ),
					'not_found'          => _x( 'لم يتم العثور على مصطلحات', 'wporg-glossary' ),
					'not_found_in_trash' => _x( 'لم يتم العثور على مصطلحات محذوفة', 'wporg-glossary' ),
					'parent_item_colon'  => _x( 'المصطلح الرئيسي:', 'wporg-glossary' ),
					'menu_name'          => _x( 'القاموس', 'wporg-glossary' ),
					'name_admin_bar'     => _x( 'مصطلح', 'wporg-glossary' ),
				),
				'public'       => true,
				'show_ui'      => true,
				'hierarchical' => false,
				'rewrite'      => false,
				'supports'     => array( 'title', 'editor', 'revisions', 'comments','page-attributes', 'thumbnail', 'custom-fields',  'post-formats' ),
				'has_archive'      => true,

			)
		);
	}

	/**
	 * Register the Alternative Names metabox
	 */
	public function register_alternatives_metabox() {
		add_meta_box(
			'alternate-names',
			__( 'Alternate Names', 'wporg-glossary' ),
			array( $this, 'alternative_names_metabox' ),
			'glossary',
			'advanced',
			'high'
		);
	}

	/**
	 * Display the 'Advanced' metaboxes after the title on the Glossary edit screen.
	 */
	public function form_after_title() {
		global $post, $wp_meta_boxes;

		if ( 'glossary' === $post->post_type ) {
			do_meta_boxes( get_current_screen(), 'advanced', $post );
			unset( $wp_meta_boxes['glossary']['advanced'] );
		}
	}

	/**
	 * Output a Alternative Names metabox on the edit screen.
	 */
	public function alternative_names_metabox( $post ) {
		$alternatives = get_post_meta( $post->ID, 'alternatives', true ) ?: array();

		echo '<p><label for="alternative_names">' . esc_html__( 'Comma-separated list of alternative names or abbreviations matching this glossary entry. For example, "WordCamp, WC, WordCamps"', 'wporg-glossary' ) . '</label></p>';
		echo '<input type="text" id="alternative_names" name="alternative_names" class="large-text" value="' . esc_attr( implode( ', ', $alternatives ) ) . '" />';
	}

	/**
	 * Save the Alternative Names metabox details.
	 */
	public function save_alternatives_metabox( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['alternative_names'] ) ) {
			return;
		}

		$names = wp_unslash( $_POST['alternative_names'] );
		$names = sanitize_text_field( $names );
		$names = preg_split( '!,\s*!', $names );
		$names = array_map( 'trim', $names );
		$names = array_unique( $names );

		$names = array_filter(
			$names,
			function( $name ) {
				return strlen( $name ) >= 2;
			}
		);

		update_post_meta( $post_id, 'alternatives', $names );
	}

}
new Glossary_Admin();
