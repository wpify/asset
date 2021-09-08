<?php

namespace Wpify\Asset;

class AssetFactory {
	public function wp_script( string $asset_path, array $args = array() ): Asset {
		$asset_base = pathinfo( $asset_path, PATHINFO_FILENAME );
		$build_path = pathinfo( $asset_path, PATHINFO_DIRNAME );
		$asset_info = wp_normalize_path( $build_path . '/' . $asset_base . '.asset.php' );
		$extension  = current( explode( '?', pathinfo( $asset_path, PATHINFO_EXTENSION ) ) );

		if ( file_exists( $asset_path ) ) {
			$args['src'] = str_replace( WP_CONTENT_DIR, content_url(), $asset_path );
		}

		if ( file_exists( $asset_info ) && $extension === 'js' ) {
			$info                 = require $asset_info;
			$dependencies         = $args['dependencies'] ?? array();
			$info['dependencies'] = array_unique( array_merge( $dependencies, $info['dependencies'] ) );
			$args                 = array_merge( $info, $args );
		}

		return new Asset( $args );
	}

	public function url( string $src, array $args = array() ): Asset {
		$args = array_merge( $args, array( 'src' => $src ) );

		return new Asset( $args );
	}

	public function theme( string $path, array $args = array() ): Asset {
		$args['src'] = get_theme_file_uri( $path );

		return new Asset( $args );
	}

	public function parent_theme( string $path, array $args = array() ): Asset {
		$args['src'] = wp_normalize_path( get_template_directory_uri() . '/' . $path );

		return new Asset( $args );
	}
}
