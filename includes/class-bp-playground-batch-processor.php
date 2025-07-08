<?php
/**
 * BuddyPress Playground Batch Processor
 *
 * @package BuddyPress_Playground
 * @subpackage Core
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Batch processing system for large dataset operations
 *
 * @since 1.0.0
 */
class BP_Playground_Batch_Processor {

    /**
     * Default batch size
     *
     * @since 1.0.0
     * @var int
     */
    private $batch_size = 100;

    /**
     * Memory limit for operations
     *
     * @since 1.0.0
     * @var string
     */
    private $memory_limit = '1024M';

    /**
     * Time limit for operations
     *
     * @since 1.0.0
     * @var int
     */
    private $time_limit = 0;

    /**
     * Enable progress tracking
     *
     * @since 1.0.0
     * @var bool
     */
    private $enable_progress = true;

    /**
     * Current batch operation statistics
     *
     * @since 1.0.0
     * @var array
     */
    private $current_operation = [];

    /**
     * Constructor
     *
     * @since 1.0.0
     * @param array $config Optional configuration
     */
    public function __construct($config = []) {
        $this->configure($config);
    }

    /**
     * Configure batch processor
     *
     * @since 1.0.0
     * @param array $config Configuration options
     * @return void
     */
    public function configure($config = []) {
        $core = bp_playground_get_module('core');
        $settings = $core ? $core->get_settings() : [];

        $defaults = [
            'batch_size' => isset($settings['batch_size']) ? $settings['batch_size'] : 100,
            'memory_limit' => isset($settings['memory_limit']) ? $settings['memory_limit'] : '1024M',
            'time_limit' => isset($settings['time_limit']) ? $settings['time_limit'] : 0,
            'enable_progress' => isset($settings['enable_progress_tracking']) ? $settings['enable_progress_tracking'] : true,
        ];

        $config = wp_parse_args($config, $defaults);

        $this->batch_size = max(1, intval($config['batch_size']));
        $this->memory_limit = $config['memory_limit'];
        $this->time_limit = max(0, intval($config['time_limit']));
        $this->enable_progress = (bool) $config['enable_progress'];
    }

    /**
     * Process items in batches
     *
     * @since 1.0.0
     * @param int $total_items Total number of items to process
     * @param callable $callback Processing callback function
     * @param callable|null $progress_callback Optional progress callback
     * @param array $options Additional processing options
     * @return array Processing results
     */
    public function process_in_batches($total_items, $callback, $progress_callback = null, $options = []) {
        // Validate parameters
        if ($total_items <= 0) {
            return new WP_Error('invalid_total', __('Total items must be greater than 0.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        if (!is_callable($callback)) {
            return new WP_Error('invalid_callback', __('Processing callback must be callable.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        // Initialize operation
        $this->init_operation($total_items, $options);

        // Set system limits
        $this->set_system_limits();

        $results = [
            'total_items' => $total_items,
            'processed_items' => 0,
            'successful_items' => 0,
            'failed_items' => 0,
            'errors' => [],
            'batches_processed' => 0,
            'start_time' => microtime(true),
            'end_time' => null,
            'duration' => null,
            'memory_usage' => [],
        ];

        try {
            // Process in batches
            for ($i = 0; $i < $total_items; $i += $this->batch_size) {
                $batch_size = min($this->batch_size, $total_items - $i);
                $batch_start = $i;
                $batch_end = $i + $batch_size - 1;

                // Execute batch
                $batch_result = $this->process_batch($batch_start, $batch_size, $callback, $options);

                // Update results
                $results['processed_items'] += $batch_result['processed'];
                $results['successful_items'] += $batch_result['successful'];
                $results['failed_items'] += $batch_result['failed'];
                $results['batches_processed']++;

                if (!empty($batch_result['errors'])) {
                    $results['errors'] = array_merge($results['errors'], $batch_result['errors']);
                }

                // Track memory usage
                $results['memory_usage'][] = $this->get_memory_usage();

                // Progress callback
                if ($this->enable_progress && $progress_callback && is_callable($progress_callback)) {
                    $progress_data = [
                        'processed' => $results['processed_items'],
                        'total' => $total_items,
                        'percentage' => round(($results['processed_items'] / $total_items) * 100, 2),
                        'batch' => $results['batches_processed'],
                        'memory' => $this->get_memory_usage(),
                    ];
                    $progress_callback($progress_data);
                }

                // Memory management
                if ($results['batches_processed'] % 10 === 0) {
                    $this->cleanup_memory();
                }

                // Check for stop conditions
                if ($this->should_stop_processing($results)) {
                    break;
                }
            }

        } catch (Exception $e) {
            $results['errors'][] = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        // Finalize results
        $results['end_time'] = microtime(true);
        $results['duration'] = $results['end_time'] - $results['start_time'];
        $results['success_rate'] = $results['processed_items'] > 0 
            ? round(($results['successful_items'] / $results['processed_items']) * 100, 2) 
            : 0;

        $this->finish_operation($results);

        return $results;
    }

    /**
     * Process a single batch
     *
     * @since 1.0.0
     * @param int $start_index Starting index
     * @param int $batch_size Batch size
     * @param callable $callback Processing callback
     * @param array $options Processing options
     * @return array Batch results
     */
    private function process_batch($start_index, $batch_size, $callback, $options = []) {
        $batch_results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        try {
            // Call the processing function for this batch
            $result = call_user_func($callback, $start_index, $batch_size, $options);

            if (is_wp_error($result)) {
                $batch_results['errors'][] = [
                    'batch_start' => $start_index,
                    'message' => $result->get_error_message(),
                    'code' => $result->get_error_code(),
                ];
                $batch_results['failed'] = $batch_size;
            } else {
                // Handle different return formats
                if (is_array($result)) {
                    $batch_results['processed'] = isset($result['processed']) ? $result['processed'] : $batch_size;
                    $batch_results['successful'] = isset($result['successful']) ? $result['successful'] : $batch_size;
                    $batch_results['failed'] = isset($result['failed']) ? $result['failed'] : 0;
                    
                    if (isset($result['errors'])) {
                        $batch_results['errors'] = array_merge($batch_results['errors'], $result['errors']);
                    }
                } else {
                    // Simple success case
                    $batch_results['processed'] = $batch_size;
                    $batch_results['successful'] = $batch_size;
                }
            }

        } catch (Exception $e) {
            $batch_results['errors'][] = [
                'batch_start' => $start_index,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            $batch_results['failed'] = $batch_size;
        }

        return $batch_results;
    }

    /**
     * Process items with custom iterator
     *
     * @since 1.0.0
     * @param array|Iterator $items Items to process
     * @param callable $callback Processing callback
     * @param callable|null $progress_callback Progress callback
     * @param array $options Processing options
     * @return array Processing results
     */
    public function process_items($items, $callback, $progress_callback = null, $options = []) {
        if (!is_array($items) && !($items instanceof Iterator)) {
            return new WP_Error('invalid_items', __('Items must be an array or Iterator.', BP_PLAYGROUND_TEXT_DOMAIN));
        }

        $total_items = is_array($items) ? count($items) : iterator_count($items);
        
        if ($total_items === 0) {
            return [
                'total_items' => 0,
                'processed_items' => 0,
                'successful_items' => 0,
                'failed_items' => 0,
                'errors' => [],
            ];
        }

        // Convert to array chunks for batch processing
        $chunks = is_array($items) ? array_chunk($items, $this->batch_size) : $this->chunk_iterator($items, $this->batch_size);

        $wrapper_callback = function($start_index, $batch_size, $options) use ($chunks, $callback) {
            $chunk_index = intval($start_index / $this->batch_size);
            
            if (!isset($chunks[$chunk_index])) {
                return new WP_Error('invalid_chunk', 'Chunk not found');
            }

            $chunk = $chunks[$chunk_index];
            $results = [
                'processed' => 0,
                'successful' => 0,
                'failed' => 0,
                'errors' => [],
            ];

            foreach ($chunk as $item) {
                try {
                    $item_result = call_user_func($callback, $item, $options);
                    $results['processed']++;
                    
                    if (is_wp_error($item_result)) {
                        $results['failed']++;
                        $results['errors'][] = $item_result->get_error_message();
                    } else {
                        $results['successful']++;
                    }
                } catch (Exception $e) {
                    $results['processed']++;
                    $results['failed']++;
                    $results['errors'][] = $e->getMessage();
                }
            }

            return $results;
        };

        return $this->process_in_batches(
            $total_items, 
            $wrapper_callback, 
            $progress_callback, 
            $options
        );
    }

    /**
     * Initialize batch operation
     *
     * @since 1.0.0
     * @param int $total_items Total items to process
     * @param array $options Operation options
     * @return void
     */
    private function init_operation($total_items, $options = []) {
        $this->current_operation = [
            'total_items' => $total_items,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'options' => $options,
        ];

        // Log operation start
        $this->log(sprintf(
            'Starting batch operation: %d items, batch size: %d',
            $total_items,
            $this->batch_size
        ), 'info');
    }

    /**
     * Finish batch operation
     *
     * @since 1.0.0
     * @param array $results Operation results
     * @return void
     */
    private function finish_operation($results) {
        $duration = round($results['duration'], 2);
        
        // Fix: Extract actual memory values from the memory_usage array
        $memory_peak = 0;
        if (!empty($results['memory_usage'])) {
            foreach ($results['memory_usage'] as $memory_info) {
                if (is_array($memory_info) && isset($memory_info['peak'])) {
                    $memory_peak = max($memory_peak, $memory_info['peak']);
                } elseif (is_numeric($memory_info)) {
                    $memory_peak = max($memory_peak, $memory_info);
                }
            }
        }
        
        // Fallback to current peak memory if no recorded values
        if ($memory_peak === 0) {
            $memory_peak = memory_get_peak_usage(true);
        }
        
        $items_per_second = $results['duration'] > 0 ? round($results['processed_items'] / $results['duration'], 2) : 0;

        $operation = isset($this->current_operation['operation']) ? $this->current_operation['operation'] : 'Unknown';
        $this->log(sprintf(
            'Batch operation completed: %d/%d items processed in %s seconds (%.2f items/sec), peak memory: %s',
            $results['successful_items'],
            $results['total_items'],
            $duration,
            $items_per_second,
            $this->format_bytes($memory_peak)
        ), 'info');

        if (!empty($results['errors'])) {
            $this->log(sprintf('Batch operation had %d errors', count($results['errors'])), 'warning');
        }

        $this->current_operation = [];
    }

    /**
     * Set system limits for batch processing
     *
     * @since 1.0.0
     * @return void
     */
    private function set_system_limits() {
        // Set memory limit
        if ($this->memory_limit) {
            ini_set('memory_limit', $this->memory_limit);
        }

        // Set time limit
        if ($this->time_limit !== null) {
            set_time_limit($this->time_limit);
        }

        // Disable WordPress object cache if processing large datasets
        if (function_exists('wp_suspend_cache_addition')) {
            wp_suspend_cache_addition(true);
        }
    }

    /**
     * Clean up memory during processing
     *
     * @since 1.0.0
     * @return void
     */
    private function cleanup_memory() {
        // Flush WordPress caches
        wp_cache_flush();
        
        // Clear object cache if available
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group('bp_playground');
        }

        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        // Log memory usage
        $this->log(sprintf(
            'Memory cleanup: current usage %s, peak usage %s',
            $this->format_bytes(memory_get_usage(true)),
            $this->format_bytes(memory_get_peak_usage(true))
        ), 'debug');
    }

    /**
     * Get current memory usage
     *
     * @since 1.0.0
     * @return array Memory usage information
     */
    private function get_memory_usage() {
        return [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => $this->parse_size(ini_get('memory_limit')),
            'formatted' => [
                'current' => $this->format_bytes(memory_get_usage(true)),
                'peak' => $this->format_bytes(memory_get_peak_usage(true)),
                'limit' => ini_get('memory_limit'),
            ],
        ];
    }

    /**
     * Check if processing should stop
     *
     * @since 1.0.0
     * @param array $results Current processing results
     * @return bool True if processing should stop
     */
    private function should_stop_processing($results) {
        // Check memory usage
        $memory_usage = memory_get_usage(true);
        $memory_limit = $this->parse_size(ini_get('memory_limit'));
        
        if ($memory_limit > 0 && $memory_usage > ($memory_limit * 0.9)) {
            $this->log('Stopping processing: approaching memory limit', 'warning');
            return true;
        }

        // Check error rate
        $error_rate = $results['processed_items'] > 0 
            ? ($results['failed_items'] / $results['processed_items']) 
            : 0;
            
        if ($error_rate > 0.5) { // Stop if more than 50% failures
            $this->log('Stopping processing: high error rate', 'error');
            return true;
        }

        return false;
    }

    /**
     * Convert iterator to chunks
     *
     * @since 1.0.0
     * @param Iterator $iterator Iterator to chunk
     * @param int $chunk_size Chunk size
     * @return array Array of chunks
     */
    private function chunk_iterator($iterator, $chunk_size) {
        $chunks = [];
        $current_chunk = [];
        $count = 0;

        foreach ($iterator as $item) {
            $current_chunk[] = $item;
            $count++;

            if ($count >= $chunk_size) {
                $chunks[] = $current_chunk;
                $current_chunk = [];
                $count = 0;
            }
        }

        // Add remaining items
        if (!empty($current_chunk)) {
            $chunks[] = $current_chunk;
        }

        return $chunks;
    }

    /**
     * Parse memory size string to bytes
     *
     * @since 1.0.0
     * @param string $size Size string (e.g., "128M", "1G")
     * @return int Size in bytes
     */
    private function parse_size($size) {
        $unit = strtoupper(substr($size, -1));
        $value = (int) $size;

        switch ($unit) {
            case 'G':
                $value *= 1024;
            case 'M':
                $value *= 1024;
            case 'K':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Format bytes to human readable string
     *
     * @since 1.0.0
     * @param int|float $bytes Number of bytes
     * @return string Formatted string
     */
    private function format_bytes($bytes) {
        // Ensure $bytes is numeric
        if (!is_numeric($bytes)) {
            return '0 B';
        }
        
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Log message
     *
     * @since 1.0.0
     * @param string $message Log message
     * @param string $level Log level
     * @return void
     */
    private function log($message, $level = 'info') {
        $core = bp_playground_get_module('core');
        if ($core) {
            $core->log($message, $level);
        }
    }

    /**
     * Get batch size
     *
     * @since 1.0.0
     * @return int Current batch size
     */
    public function get_batch_size() {
        return $this->batch_size;
    }

    /**
     * Set batch size
     *
     * @since 1.0.0
     * @param int $size New batch size
     * @return void
     */
    public function set_batch_size($size) {
        $this->batch_size = max(1, intval($size));
    }

    /**
     * Get current operation info
     *
     * @since 1.0.0
     * @return array Current operation information
     */
    public function get_current_operation() {
        return $this->current_operation;
    }
}