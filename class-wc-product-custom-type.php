<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class WC_Product_Custom_Type
 */
class WC_Product_Configurator_Threejs extends WC_Product {
    public function get_type() {
        return 'custom_type';
    }
}
