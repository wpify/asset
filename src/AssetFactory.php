<?php

namespace Wpify\Asset;

class AssetFactory {
	public function admin_wp_script( string $asset_path, $args = null ): Asset {
		return $this->wp_script(
			$asset_path,
			( new AssetConfig( $args ) )
				->set_is_admin( true )
		);
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

		if ( file_exists( $asset_info ) ) {
			$info = require $asset_info;

			$config->set_version( $info['version'] ?? $config->get_version() );

			if ( $config->get_type() === AssetConfigInterface::TYPE_SCRIPT ) {
				$config->merge_dependencies( $info['dependencies'] ?? array() );
			}
		}

		if ( file_exists( $assets_info ) ) {
			$infos = require $assets_info;

			if ( ! empty( $infos[ $filename ] ) ) {
				$info = $infos[ $filename ];

				$config->set_version( $info['version'] ?? $config->get_version() );

				if ( $config->get_type() === AssetConfigInterface::TYPE_SCRIPT ) {
					$config->merge_dependencies( $info['dependencies'] ?? array() );
				}
			}
		}

		return $this->factory( $config );
	}

	public function factory( AssetConfigInterface $config ) {
		return new Asset( $config );
	}

	public function login_wp_script( string $asset_path, $args = null ): Asset {
		return $this->wp_script(
			$asset_path,
			( new AssetConfig( $args ) )
				->set_is_login( true )
		);
	}

	public function admin_url( string $src, $args = null ): Asset {
		return $this->url(
			$src,
			( new AssetConfig( $args ) )
				->set_is_admin( true )
		);
	}

	public function url( string $src, $args = null ): Asset {
		return $this->factory( ( new AssetConfig( $args ) )->set_src( $src ) );
	}

	public function login_url( string $src, $args = null ): Asset {
		return $this->url(
			$src,
			( new AssetConfig( $args ) )
				->set_is_login( true )
		);
	}

	public function admin_theme( string $asset_path, $args = null ): Asset {
		return $this->theme(
			$asset_path,
			( new AssetConfig( $args ) )
				->set_is_admin( true )
		);
	}

	public function theme( string $path, $args = null ): Asset {
		return $this->factory(
			( new AssetConfig( $args ) )
				->set_src( get_theme_file_uri( $path ) )
		);
	}

	public function login_theme( string $asset_path, $args = null ): Asset {
		return $this->theme(
			$asset_path,
			( new AssetConfig( $args ) )
				->set_is_login( true )
		);
	}

	public function admin_parent_theme( string $asset_path, $args = null ): Asset {
		return $this->parent_theme(
			$asset_path,
			( new AssetConfig( $args ) )
				->set_is_admin( true )
		);
	}

	public function parent_theme( string $path, $args = null ): Asset {
		return $this->factory(
			( new AssetConfig( $args ) )
				->set_src( wp_normalize_path( get_template_directory_uri() . '/' . $path ) )
		);
	}

	public function login_parent_theme( string $asset_path, $args = null ): Asset {
		return $this->parent_theme(
			$asset_path,
			( new AssetConfig( $args ) )
				->set_is_login( true )
		);
	}
}
