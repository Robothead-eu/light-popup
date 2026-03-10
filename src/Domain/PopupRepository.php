<?php

namespace Robothead\LightPopup\Domain;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PopupRepository {

	private const CACHE_KEY = 'light_popup_active_pages';
	private const CACHE_TTL = 43200; // 12 hours.

	/**
	 * Returns active popup IDs applicable to the given page/post type.
	 *
	 * @return int[]
	 */
	public static function get_active_popup_ids_for_page( int $post_id, string $post_type ): array {
		$map = self::get_map();

		$ids = [];

		// Popups targeting all pages.
		if ( isset( $map['all'] ) ) {
			$ids = array_merge( $ids, $map['all'] );
		}

		// Popups targeting this specific post ID.
		if ( isset( $map['page_ids'][ $post_id ] ) ) {
			$ids = array_merge( $ids, $map['page_ids'][ $post_id ] );
		}

		// Popups targeting this post type.
		if ( isset( $map['post_types'][ $post_type ] ) ) {
			$ids = array_merge( $ids, $map['post_types'][ $post_type ] );
		}

		return array_unique( array_map( 'intval', $ids ) );
	}

	/**
	 * Returns all enabled popup post IDs regardless of targeting.
	 *
	 * @return int[]
	 */
	public static function get_all_enabled_popup_ids(): array {
		$map = self::get_map();
		$ids = [];
		$ids = array_merge( $ids, $map['all'] ?? [] );
		foreach ( $map['page_ids'] ?? [] as $page_ids ) {
			$ids = array_merge( $ids, $page_ids );
		}
		foreach ( $map['post_types'] ?? [] as $type_ids ) {
			$ids = array_merge( $ids, $type_ids );
		}
		return array_unique( array_map( 'intval', $ids ) );
	}

	public static function flush_cache(): void {
		delete_transient( self::CACHE_KEY );
	}

	/**
	 * Builds or retrieves the cached targeting map.
	 *
	 * Map structure:
	 * [
	 *   'all'        => [ popup_id, ... ],
	 *   'page_ids'   => [ post_id => [ popup_id, ... ], ... ],
	 *   'post_types' => [ post_type => [ popup_id, ... ], ... ],
	 * ]
	 *
	 * @return array<string, mixed>
	 */
	private static function get_map(): array {
		$cached = get_transient( self::CACHE_KEY );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$map = [
			'all'        => [],
			'page_ids'   => [],
			'post_types' => [],
		];

		$popups = get_posts(
			[
				'post_type'      => 'light_popup',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					[
						'key'   => '_lp_enabled',
						'value' => '1',
					],
				],
			]
		);

		foreach ( $popups as $popup_id ) {
			$targeting_type = get_post_meta( $popup_id, '_lp_targeting_type', true );

			if ( 'page_ids' === $targeting_type ) {
				$raw_ids = get_post_meta( $popup_id, '_lp_targeting_ids', true );
				foreach ( array_filter( array_map( 'intval', explode( ',', (string) $raw_ids ) ) ) as $pid ) {
					$map['page_ids'][ $pid ][] = $popup_id;
				}
			} elseif ( 'post_types' === $targeting_type ) {
				$raw_types = get_post_meta( $popup_id, '_lp_targeting_post_types', true );
				foreach ( array_filter( array_map( 'trim', explode( ',', (string) $raw_types ) ) ) as $ptype ) {
					$map['post_types'][ $ptype ][] = $popup_id;
				}
			} else {
				$map['all'][] = $popup_id;
			}
		}

		set_transient( self::CACHE_KEY, $map, self::CACHE_TTL );

		return $map;
	}
}
