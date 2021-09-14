<?php

namespace Wpify\Asset;

class AssetFactory {
	public function admin_wp_script( string $asset_path, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_is_admin( true );

		return $this->wp_script( $asset_path, $config );
	}

	public function wp_script( string $asset_path, $args = null ): Asset {
		$config      = new AssetConfig( $args );
		$asset_base  = pathinfo( $asset_path, PATHINFO_FILENAME );
		$build_path  = pathinfo( $asset_path, PATHINFO_DIRNAME );
		$asset_info  = wp_normalize_path( $build_path . '/' . $asset_base . '.asset.php' );
		$assets_info = wp_normalize_path( $build_path . '/assets.php' );
		$extension   = current( explode( '?', pathinfo( $asset_path, PATHINFO_EXTENSION ) ) );
		$filename    = $asset_base . '.' . $extension;

		if ( file_exists( $asset_path ) ) {
			$config->set_src( str_replace( WP_CONTENT_DIR, content_url(), $asset_path ) );
		}

		if ( file_exists( $asset_info ) && $config->get_type() === AssetConfigInterface::TYPE_SCRIPT ) {
			$info = require $asset_info;

			$config->set_version( $info['version'] ?? $config->get_version() );
			$config->set_dependencies(
				array_unique(
					array_merge(
						$config->get_dependencies(),
						$info['dependencies'] ?? array()
					)
				)
			);
		}

		if ( file_exists( $assets_info ) && $config->get_type() === AssetConfigInterface::TYPE_SCRIPT ) {
			$infos = require $assets_info;

			if ( ! empty( $infos[ $filename ] ) ) {
				$info = $infos[ $filename ];

				$config->set_version( $info['version'] ?? $config->get_version() );
				$config->set_dependencies(
					array_unique(
						array_merge(
							$config->get_dependencies(),
							$info['dependencies'] ?? array()
						)
					)
				);
			}
		}

		return new Asset( $config );
	}

	public function login_wp_script( string $asset_path, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_is_login( true );

		return $this->wp_script( $asset_path, $config );
	}

	public function admin_url( string $src, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_is_admin( true );

		return $this->url( $src, $config );
	}

	public function url( string $src, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_src( $src );

		return new Asset( $config );
	}

	public function login_url( string $src, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_is_login( true );

		return $this->url( $src, $config );
	}

	public function admin_theme( string $asset_path, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_is_admin( true );

		return $this->theme( $asset_path, $config );
	}

	public function theme( string $path, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_src( get_theme_file_uri( $path ) );

		return new Asset( $config );
	}

	public function login_theme( string $asset_path, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_is_login( true );

		return $this->theme( $asset_path, $config );
	}

	public function admin_parent_theme( string $asset_path, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_is_admin( true );

		return $this->parent_theme( $asset_path, $config );
	}

	public function parent_theme( string $path, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_src( wp_normalize_path( get_template_directory_uri() . '/' . $path ) );

		return new Asset( $config );
	}

	public function login_parent_theme( string $asset_path, $args = null ): Asset {
		$config = new AssetConfig( $args );

		$config->set_is_login( true );

		return $this->parent_theme( $asset_path, $config );
	}
}
