<?php

if (!defined('ABSPATH')) {

    exit; // Exit if accessed directly

}



class WC_Product_Configurator_Threejs extends WC_Product {

    public function __construct($product) {

        $this->product_type = 'threedium_module_threejs';

        parent::__construct($product);

    }

}



?>