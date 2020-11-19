<?php

class Glossary_Hovercards {
	public function __construct() {
		add_action( 'init', array( $this, 'load_styles' ) );
		add_action( 'wp_footer', array( $this, 'load_scripts' ) );
	}

	public function load_styles() {
		wp_register_style( 'glossary-hovercards', plugins_url( '../css/glossary-hovercards.css', __FILE__ ), array(), '20190524a' );
		wp_enqueue_style( 'glossary-hovercards' );
	}

	public function load_scripts() {
		wp_register_script( 'popper', plugins_url( '../js/popper.min.js', __FILE__ ), array(), '1.3.2', true );
		wp_register_script( 'tippy', plugins_url( '../js/tippy.min.js', __FILE__ ), array( 'popper' ), '1.3.2', true );
		wp_register_script( 'glossary-hovercards', plugins_url( '../js/glossary-hovercards.js', __FILE__ ), array( 'tippy', 'jquery', 'hoverintent-js' ), '20200519', true );
		wp_enqueue_script( 'glossary-hovercards' );
	}
}

new Glossary_Hovercards();
