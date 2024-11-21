<?php
class MKWA_Core {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Initialize any core functionality here
        $this->init_hooks();
    }

    private function init_hooks() {
        // Add any necessary WordPress action/filter hooks
    }
}