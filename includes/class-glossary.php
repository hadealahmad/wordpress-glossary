<?php

/**
 * Helper class for accessing the glossary DB.
 */
class Glossary {
	private $cache_group   = 'wporg-glossary';
	private $cache_version = 3;

	/**
	 * Construct the Glossary object.
	 */
	public function __construct() {
		// Clear the cache upon Glossary item being updated. Items on sub-sites will be cleared in an hour or so.
		add_action( 'save_post_glossary', array( $this, 'clear_cache' ) );
	}

	/**
	 * Load an item from the Glossary by name.
	 *
	 * @param string $name
	 *
	 * @return false|object The glossary item or false if none exist.
	 */
	public function get_active_item( $name ) {
		$case_sensitive = $this->name_is_case_sensitive( $name );

		$item = array_filter(
			$this->get_active_items(),
			function( $item ) use ( $name, $case_sensitive ) {
				if ( $case_sensitive && $item->name === $name ) {
					return true;
				} elseif ( ! $case_sensitive && 0 === strcasecmp( $item->name, $name ) ) {
					return true;
				} elseif ( $item->alternatives ) {
					if ( $case_sensitive && in_array( $name, $item->alternatives, true ) ) {
						return true;
					} elseif ( ! $case_sensitive && in_array( strtolower( $name ), array_map( 'strtolower', $item->alternatives ), true ) ) {
						return true;
					}
				}

				return false;
			}
		);

		return array_shift( $item ) ?: false;
	}

	/**
	 * Get all item names from the glossary.
	 *
	 * @return array
	 */
	public function get_active_item_names() {
		$items = $this->get_active_items();
		foreach($items as $item){
			$names_transient[] = $item->name;
			foreach ( $item->alternatives as $single_alternative){
				$names_transient[] = $single_alternative;
			}
			$names_array[] = $names_transient;
		}
// 		$names = array_values( wp_list_pluck( $items, 'name' ) );

// 		// Retrieve and flatten the list of alternative names for the glossary items.
// 		$alternatives = wp_list_pluck( $items, 'alternatives' );
// 		if ( $alternatives ) {
// 			$alternatives = call_user_func_array( 'array_merge', $alternatives );
// 		}

// 		return array_merge( $names, $alternatives );
	return $names_array;
	}

	/**
	 * Get all glossary items.
	 *
	 * @return array
	 */
	public function get_active_items() {
		$cache_key = "items-v{$this->cache_version}";
		if ( ! $items = wp_cache_get( $cache_key, $this->cache_group ) ) {
			$items = array();
			if ( is_multisite() && ! is_main_site() ) {
				// Fetch any from the main parent site first.
				switch_to_blog( get_main_site_id() );

				$items = $this->get_active_items();

				restore_current_blog();
			}

			$posts = get_posts(
				array(
					'post_type'   => 'glossary',
					'post_status' => 'publish',
					'numberposts' => -1,
				)
			);
			foreach ( $posts as $post ) {
				$item                               = $this->post_to_glossary_item( $post );
				$items[ strtolower( $item->name ) ] = $item;
			}

			if ( $items ) {
				wp_cache_set( $cache_key, $items, $this->cache_group, HOUR_IN_SECONDS );
			}
		}

		return $items;
	}

	/**
	 * Map a Post object into a Glossary item.
	 *
	 * @param WP_Post $post The Post object
	 *
	 * @return object A Glossary item object.
	 */
	protected function post_to_glossary_item( $post ) {
		return (object) array(
			'id'           => $post->ID,
			'site'         => get_current_blog_id(),
			'name'         => trim( $post->post_title ),
			'description'  => trim( $post->post_content ),
			'alternatives' => get_post_meta( $post->ID, 'alternatives', true ) ?: array(),
		);
	}

	/**
	 * Get all glossary items as a regex.
	 *
	 * @return false|string The Regex.
	 */
	public function get_item_names_regex() {
		$item_names = $this->get_active_item_names();
		if ( ! $item_names ) {
			return false;
		}

		// Sort long -> short so that the longer items match first.
// 		usort( $item_names, function( $a, $b ) {
// 			return ( strlen($a) < strlen($b) ) ? 1 : -1;
// 		} );

// 		$regex = implode(
// 			'|',
// 			array_map(
// 				function( $name ) {
// 					return preg_quote( $name, '/' );
// 				},
// 				$item_names
// 			)
// 		);
		foreach ($item_names as $name_and_alternatives) {
// 			Sort long -> short so that the longer items match first.
				usort( $name_and_alternatives, function( $a, $b ) {
					return ( strlen($a) < strlen($b) ) ? 1 : -1;
				} );
			$single_regex = 
				implode(
						'|',
						array_map(
						function( $name ) {
							return preg_quote( $name, '/' );
						},
						$name_and_alternatives
					)
				);
			$regex[] = "/\b($single_regex)(?![^<]*>|[.]\w)\b/iu";
			
		}
		return $regex;
	}

	/**
	 * Clear the Glossary item cache.
	 */
	public function clear_cache() {
		wp_cache_delete( "items-v{$this->cache_version}", $this->cache_group );
	}

	/**
	 * Determine if a name should be case sensitive
	 */
	public function name_is_case_sensitive( $name ) {
		return strlen( $name ) <= 3;
	}
}
