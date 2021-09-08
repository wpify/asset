<?php

namespace Wpify\Asset;

class Asset {
	const TYPE_SCRIPT = 'script';
	const TYPE_STYLE = 'style';

	/** @var boolean */
	private $is_admin;

	/** @var boolean */
	private $is_login;

	/** @var callable */
	private $do_enqueue;

	/** @var string */
	private $handle;

	/** @var string */
	private $src;

	/** @var array */
	private $dependencies;

	/** @var string? */
	private $version;

	/** @var boolean */
	private $in_footer;

	/** @var string */
	private $type;

	/** @var string? */
	private $media;

	/** @var boolean */
	private $is_done = false;

	/** @var array */
	private $variables;

	/** @var string */
	private $script_before;

	/** @var string */
	private $script_after;

	public function __construct( array $args = array() ) {
		$args = wp_parse_args( $args, array(
			'is_admin'      => false,
			'is_login'      => false,
			'do_enqueue'    => '__return_true',
			'src'           => null,
			'handle'        => null,
			'dependencies'  => array(),
			'version'       => false,
			'in_footer'     => false,
			'type'          => null,
			'media'         => null,
			'variables'     => array(),
			'script_before' => null,
			'script_after'  => null,
		) );

		$this->is_admin      = $args['is_admin'];
		$this->is_login      = $args['is_login'];
		$this->do_enqueue    = $args['do_enqueue'];
		$this->src           = $args['src'];
		$this->handle        = $args['handle'] ?? $this->generate_handle( $args['src'] );
		$this->dependencies  = $args['dependencies'];
		$this->version       = $args['version'];
		$this->in_footer     = $args['in_footer'];
		$this->media         = $args['media'];
		$this->type          = $args['type'] ?? $this->get_file_type( $args['src'] );
		$this->variables     = $args['variables'] ?? array();
		$this->script_before = $args['script_before'];
		$this->script_after  = $args['script_after'];

		$this->init();
		$this->setup();
	}

	private function generate_handle( $src ) {
		return sanitize_title( pathinfo( $src, PATHINFO_FILENAME ) . '-' . md5( $src ) );
	}

	private function get_file_type( $src ) {
		$extension = $this->get_file_extension( $src );

		if ( $extension === 'js' ) {
			return self::TYPE_SCRIPT;
		} else if ( $extension === 'css' ) {
			return self::TYPE_STYLE;
		}

		return null;
	}

	private function get_file_extension( $src ) {
		return current( explode( '?', pathinfo( $src, PATHINFO_EXTENSION ) ) );
	}

	public function init() {
		if ( $this->is_login ) {
			if ( did_action( 'login_enqueue_scripts' ) ) {
				$this->register();
			} else {
				add_action( 'login_enqueue_scripts', array( $this, 'register' ) );
				add_action( 'login_enqueue_scripts', array( $this, 'enqueue' ), 20 );
			}
		} elseif ( $this->is_admin ) {
			if ( did_action( 'admin_enqueue_scripts' ) ) {
				global $hook_suffix;
				$this->register( $hook_suffix );
			} else {
				add_action( 'admin_enqueue_scripts', array( $this, 'register' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ), 20 );
			}
		} else {
			if ( did_action( 'wp_enqueue_scripts' ) ) {
				$this->register();
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 20 );
			}
		}
	}

	public function register() {
		if ( $this->type === self::TYPE_SCRIPT ) {
			wp_register_script( $this->handle, $this->src, $this->dependencies, $this->version, $this->in_footer );

			if ( ! empty( $this->variables ) && is_array( $this->variables ) ) {
				$script = array();

				foreach ( $this->variables as $name => $value ) {
					$script[] = 'var ' . $name . '=' . wp_json_encode( $value ) . ';';
				}

				wp_add_inline_script( $this->handle, join( '', $script ), 'before' );
			}

			if ( ! empty( $this->script_before ) ) {
				wp_add_inline_script( $this->handle, $this->script_before, 'before' );
			}

			if ( ! empty( $this->script_after ) ) {
				wp_add_inline_script( $this->handle, $this->script_after );
			}
		} elseif ( $this->type === self::TYPE_STYLE ) {
			wp_register_style( $this->handle, $this->src, $this->dependencies, $this->version, $this->media );
		}
	}

	public function setup() {
	}

	public function enqueue() {
		if ( call_user_func( $this->do_enqueue, $this->get_args() ) && ! $this->is_done ) {
			if ( $this->type === self::TYPE_SCRIPT ) {
				wp_enqueue_script( $this->handle );
			} elseif ( $this->type === self::TYPE_STYLE ) {
				wp_enqueue_style( $this->handle );
			}
		}
	}

	private function get_args() {
		return array(
			'is_admin'   => $this->is_admin,
			'is_login'   => $this->is_login,
			'do_enqueue' => $this->do_enqueue,
			'handle'     => $this->handle,
			'src'        => $this->src,
			'deps'       => $this->dependencies,
			'ver'        => $this->version,
			'in_footer'  => $this->in_footer,
			'type'       => $this->type,
		);
	}

	public function get_handle() {
		return $this->handle;
	}

	public function print() {
		if ( ! $this->is_done ) {
			if ( $this->type === self::TYPE_SCRIPT ) {
				if ( wp_scripts()->do_item( $this->handle ) ) {
					$this->is_done = true;
				}
			} elseif ( $this->type === self::TYPE_STYLE ) {
				if ( wp_styles()->do_item( $this->handle ) ) {
					$this->is_done = true;
				}
			}
		}
	}
}
