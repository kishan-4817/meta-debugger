<?php
/**
 * Data retrieval and metadata inspection engine.
 *
 * @package MetaDebugger
 */

namespace MetaDebugger\Inspector;

use MetaDebugger\Core\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaInspector
 *
 * Inspects metadata across posts, pages, products, and custom post types safely.
 */
class MetaInspector {

    /**
     * Security handler instance.
     *
     * @var Security
     */
    private Security $security;

    /**
     * Constructor.
     *
     * @param Security $security Security handler.
     */
    public function __construct( Security $security ) {
        $this->security = $security;
    }

    /**
     * Detect the currently viewed post/page/product ID from the context.
     *
     * @return int Detected post ID or 0.
     */
    public function get_current_object_id(): int {
        // Admin post edit screen
        if ( is_admin() ) {
            global $pagenow;
            if ( 'post.php' === $pagenow && isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return absint( $_GET['post'] );
            }
            return 0;
        }

        // Frontend WooCommerce product or standard singular post/page/CPT
        if ( is_singular() ) {
            return (int) get_queried_object_id();
        }

        return 0;
    }

    /**
     * Search items (posts, pages, products, CPTs) by title, ID, or SKU.
     *
     * @param string $search Search query.
     * @param int    $limit Max items to return.
     * @return array Array of matching items.
     */
    public function search_items( string $search, int $limit = 20 ): array {
        $search = trim( $search );

        $args = [
            'post_type'      => 'any',
            'post_status'    => 'any',
            'posts_per_page' => $limit,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ];

        if ( is_numeric( $search ) ) {
            $args['p'] = absint( $search );
        } elseif ( '' !== $search ) {
            $args['s'] = $search;
        }

        $ids = get_posts( $args );

        // Search by SKU if WooCommerce is active and search is non-empty string
        if ( class_exists( 'WooCommerce' ) && '' !== $search && ! is_numeric( $search ) ) {
            $sku_query = new \WP_Query( [
                'post_type'      => 'product',
                'post_status'    => 'any',
                'posts_per_page' => 10,
                'fields'         => 'ids',
                'no_found_rows'  => true,
                'meta_query'     => [
                    [
                        'key'     => '_sku',
                        'value'   => $search,
                        'compare' => 'LIKE',
                    ],
                ],
            ] );
            $ids = array_unique( array_merge( $ids, $sku_query->posts ) );
        }

        $results = [];
        $ids = array_slice( $ids, 0, $limit );

        foreach ( $ids as $id ) {
            $post = get_post( $id );
            if ( ! $post ) {
                continue;
            }

            $sku = '';
            if ( class_exists( 'WooCommerce' ) && 'product' === $post->post_type ) {
                $product = wc_get_product( $id );
                if ( $product ) {
                    $sku = $product->get_sku();
                }
            } else {
                $sku = (string) get_post_meta( $id, '_sku', true );
            }

            $results[] = [
                'id'       => $id,
                'title'    => get_the_title( $id ) ?: sprintf( __( '(No title #%d)', 'meta-debugger' ), $id ),
                'sku'      => $sku,
                'type'     => $post->post_type,
                'status'   => $post->post_status,
                'edit_url' => get_edit_post_link( $id, 'display' ),
            ];
        }

        return $results;
    }

    /**
     * Safely deserialize and sanitize a value.
     * Prevents PHP Object Injection by parsing arrays/primitives and converting objects safely.
     *
     * @param mixed $value Raw meta value.
     * @param int   $depth Current recursion depth.
     * @return mixed Safe normalized representation (array, scalar, or string).
     */
    public function safe_unserialize_value( $value, int $depth = 0 ) {
        if ( $depth > 8 ) {
            return '[max depth reached]';
        }

        if ( is_string( $value ) && is_serialized( $value ) ) {
            // Unserialize with allowed_classes set to false to prevent PHP Object Injection
            $unserialized = @unserialize( $value, [ 'allowed_classes' => false ] );
            if ( false !== $unserialized || 'b:0;' === $value ) {
                $value = $unserialized;
            }
        }

        if ( is_array( $value ) ) {
            $cleaned = [];
            foreach ( $value as $k => $v ) {
                $safe_k = is_string( $k ) ? sanitize_text_field( $k ) : $k;
                $cleaned[ $safe_k ] = $this->safe_unserialize_value( $v, $depth + 1 );
            }
            return $cleaned;
        }

        if ( is_object( $value ) ) {
            // Convert object to associative array safely
            $cleaned = [];
            $vars = get_object_vars( $value );
            foreach ( $vars as $k => $v ) {
                $safe_k = is_string( $k ) ? sanitize_text_field( $k ) : $k;
                $cleaned[ $safe_k ] = $this->safe_unserialize_value( $v, $depth + 1 );
            }
            return $cleaned;
        }

        return $value;
    }

    /**
     * Fetch all metadata for a given post/product/page ID.
     *
     * @param int $object_id Post or product ID.
     * @return array Array containing item details, metadata, and stats.
     */
    public function fetch_meta( int $object_id ): array {
        $post = get_post( $object_id );
        if ( ! $post ) {
            return [
                'success' => false,
                'message' => __( 'Item not found.', 'meta-debugger' ),
            ];
        }

        // Collect item card info
        $item_info = [
            'id'         => $object_id,
            'name'       => get_the_title( $object_id ) ?: sprintf( __( 'Item #%d', 'meta-debugger' ), $object_id ),
            'type'       => $post->post_type,
            'status'     => $post->post_status,
            'sku'        => '',
            'price_html' => '',
            'stock'      => '',
            'thumb'      => '',
            'edit_url'   => get_edit_post_link( $object_id, 'raw' ),
        ];

        $thumb_id = get_post_thumbnail_id( $object_id );
        if ( $thumb_id ) {
            $thumb_src = wp_get_attachment_image_url( $thumb_id, 'thumbnail' );
            if ( $thumb_src ) {
                $item_info['thumb'] = $thumb_src;
            }
        }

        if ( class_exists( 'WooCommerce' ) && 'product' === $post->post_type ) {
            $product = wc_get_product( $object_id );
            if ( $product ) {
                $item_info['name']       = $product->get_name();
                $item_info['sku']        = $product->get_sku();
                $item_info['price_html'] = $product->get_price_html();
                $item_info['stock']      = $product->get_stock_status();
            }
        }

        $all_raw_meta = get_post_meta( $object_id );
        $meta = [];

        foreach ( $all_raw_meta as $key => $values ) {
            if ( $this->security->is_blocked_key( $key ) ) {
                continue;
            }
            $meta[ $key ] = $this->safe_unserialize_value( $values[0] ?? '' );
        }

        // If ACF is active, get ACF formatted values to overwrite raw meta where available
        if ( function_exists( 'get_fields' ) ) {
            $acf_fields = get_fields( $object_id );
            if ( is_array( $acf_fields ) ) {
                foreach ( $acf_fields as $key => $val ) {
                    if ( ! $this->security->is_blocked_key( $key ) ) {
                        $meta[ $key ] = $this->safe_unserialize_value( $val );
                    }
                }
            }
        }

        ksort( $meta );

        return [
            'success' => true,
            'item'    => $item_info,
            'meta'    => $meta,
            'total'   => count( $meta ),
        ];
    }
}
