<?php
if (!defined('ABSPATH')) exit;
class MKWA_Points_Manager {
    private static $instance = null;
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}