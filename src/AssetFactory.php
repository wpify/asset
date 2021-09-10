<?php

namespace Wpify\Asset;

class AssetFactory {
	public function admin_wp_script( string $asset_path, array $args = array() ): Asset {
		return $this->wp_script( $asset_path, array_merge( $args, array( 'is_admin' => true ) ) );
	}

	public function wp_script( string $asset_path, array $args = array() ): Asset {
		$asset_base  = pathinfo( $asset_path, PATHINFO_FILENAME );
		$build_path  = pathinfo( $asset_path, PATHINFO_DIRNAME );
		$asset_info  = wp_normalize_path( $build_path . '/' . $asset_base . '.asset.php' );
		$assets_info = wp_normalize_path( $build_path . '/assets.php' );
		$extension   = current( explode( '?', pathinfo( $asset_path, PATHINFO_EXTENSION ) ) );
		$filename    = $asset_base . '.' . $extension;

		if ( file_exists( $asset_path ) ) {
			$args['src'] = str_replace( WP_CONTENT_DIR, content_url(), $asset_path );
		}

		if ( file_exists( $asset_info ) && $extension === 'js' ) {
			$info                 = require $asset_info;
			$dependencies         = $args['dependencies'] ?? array();
			$info['dependencies'] = array_unique( array_merge( $dependencies, $info['dependencies'] ) );
			$args                 = array_merge( $info, $args );
		}

		if ( file_exists( $assets_info ) && $extension === 'js' ) {
			$infos = require $assets_info;

			if ( ! empty( $infos[ $filename ] ) ) {
				$info                 = $infos[ $filename ];
				$dependencies         = $args['dependencies'] ?? array();
				$info['dependencies'] = array_unique( array_merge( $dependencies, $info['dependencies'] ) );
				$args                 = array_merge( $info, $args );
			}
		}

		return new Asset( $args );
	}

	public function login_wp_script( string $asset_path, array $args = array() ): Asset {
		return $this->wp_script( $asset_path, array_merge( $args, array( 'is_login' => true ) ) );
	}

	public function admin_url( string $asset_path, array $args = array() ): Asset {
		return $this->url( $asset_path, array_merge( $args, array( 'is_admin' => true ) ) );
	}

	public function url( string $src, array $args = array() ): Asset {
		$args = array_merge( $args, array( 'src' => $src ) );

		return new Asset( $args );
	}

	public function login_url( string $asset_path, array $args = array() ): Asset {
		return $this->url( $asset_path, array_merge( $args, array( 'is_login' => true ) ) );
	}

	public function admin_theme( string $asset_path, array $args = array() ): Asset {
		return $this->theme( $asset_path, array_merge( $args, array( 'is_admin' => true ) ) );
	}

	public function theme( string $path, array $args = array() ): Asset {
		$args['src'] = get_theme_file_uri( $path );

		return new Asset( $args );
	}

	public function login_theme( string $asset_path, array $args = array() ): Asset {
		return $this->theme( $asset_path, array_merge( $args, array( 'is_login' => true ) ) );
	}

	public function admin_parent_theme( string $asset_path, array $args = array() ): Asset {
		return $this->parent_theme( $asset_path, array_merge( $args, array( 'is_admin' => true ) ) );
	}

	public function parent_theme( string $path, array $args = array() ): Asset {
		$args['src'] = wp_normalize_path( get_template_directory_uri() . '/' . $path );

		return new Asset( $args );
	}

	public function login_parent_theme( string $asset_path, array $args = array() ): Asset {
		return $this->parent_theme( $asset_path, array_merge( $args, array( 'is_login' => true ) ) );
	}
}
