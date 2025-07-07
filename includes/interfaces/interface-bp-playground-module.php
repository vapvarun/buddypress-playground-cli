<?php
/**
 * BuddyPress Playground Module Interface
 *
 * @package BuddyPress_Playground
 * @subpackage Interfaces
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Interface for all BuddyPress Playground modules
 *
 * @since 1.0.0
 */
interface BP_Playground_Module_Interface {

    /**
     * Generate data for this module
     *
     * @since 1.0.0
     * @param array $args Generation arguments
     * @return array|WP_Error Generation results or error on failure
     */
    public function generate($args = []);

    /**
     * Get module statistics
     *
     * @since 1.0.0
     * @return array Module statistics
     */
    public function get_stats();

    /**
     * Clean up module data
     *
     * @since 1.0.0
     * @param array $options Cleanup options
     * @return array Cleanup results
     */
    public function cleanup($options = []);

    /**
     * Validate module dependencies
     *
     * @since 1.0.0
     * @return bool|WP_Error True if dependencies are met, WP_Error otherwise
     */
    public function check_dependencies();

    /**
     * Get module name
     *
     * @since 1.0.0
     * @return string Module name
     */
    public function get_name();

    /**
     * Get module description
     *
     * @since 1.0.0
     * @return string Module description
     */
    public function get_description();

    /**
     * Get module version
     *
     * @since 1.0.0
     * @return string Module version
     */
    public function get_version();
}