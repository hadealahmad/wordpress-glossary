<?php
/**
 * Plugin Name:     WordPress.org Glossary
 * Description:     Interactive WordPress.org glossary.
 * Author:          Automattic
 * Author URI:      https://automattic.com/
 * Text Domain:     wporg-glossary
 * Version:         1.1
 *
 * @package         Wporg_Glossary
 */

require_once __DIR__ . '/includes/class-glossary-handler.php';
require_once __DIR__ . '/includes/class-glossary-hovercards.php';
require_once __DIR__ . '/includes/class-glossary-admin.php';
require_once __DIR__ . '/includes/class-glossary.php';

// Bootstrap the Glossary_Handler.
new Glossary_Handler();
