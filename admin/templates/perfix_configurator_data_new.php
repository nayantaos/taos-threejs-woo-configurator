<div id="overlay" style="display: none;"></div>
<div id="Threedium_product_data" class="panel woocommerce_options_panel">
    <div id="loading-overlay" class="new_3d_loading"><div class="loading-icon"></div></div>
    <div id="loading_3d_configrator"  class="new_3d_loading_config" style="width:200px;height:200px; display:none;"></div>
    <canvas id="renderCanvas" style="width: 100%; height: 100vh; display:none;"></canvas>

    <?php 
    global $post;
    $post_id = $post->ID;    
    $selected_module = get_post_meta($post_id, 'threedium_module_data', true);
    // echo "<pre>"; print_r($selected_module); exit;
    woocommerce_wp_hidden_input(array(
        'id' => 'selected_threedium_module',
        'value' => $selected_module,
    ));

    woocommerce_wp_text_input(array(
        'id' => 'threedium_module_data',
        'label' => __('Module Name 3D', 'woocommerce'),
        'desc_tip' => true,
        'description' => __('This field is read-only.', 'woocommerce'),
        'custom_attributes' => array('readonly' => 'readonly')
    ));

    woocommerce_wp_text_input( array(
        'id'            => '_regular_price_baby',
        'placeholder'   => __( 'Product Price', 'woocommerce' ),
        'label'         => __( 'Price', 'woocommerce' ),
        'description'   => __( 'Please enter a price', 'woocommerce' ),
        'desc_tip'      => true,
    ));

    woocommerce_wp_hidden_input( array(
        'id'            => '_hidden_input',
        'class'         => 'some_class',
    ));
    woocommerce_wp_hidden_input( array(
        'id'            => '_hidden_input_for_file_name',
        'class'         => 'some_class',
    ));
    function create_wc_select($id, $label, $table, $post_id) {
        global $wpdb;
        $product_configurator_data = get_post_meta($post_id, 'threejs_product_configurator_data', true);
        $selected_options = [];
    
        if ($product_configurator_data) { 
            $product_configurator_data = (array) json_decode($product_configurator_data, true);
            $selected_options = $product_configurator_data[$id] ?? [];
        }
    
        $options = [];
        $results = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}{$table}");
        if (!empty($results)) {
            foreach ($results as $row) {
                $options[$row->id] = $row->name;
            }
    
            woocommerce_wp_select([
                'id' => $id,
                'name' => "{$id}[]", 
                'label' => __($label, 'woocommerce'),
                'options' => $options,
                'value' => $selected_options,
                'custom_attributes' => ['multiple' => 'multiple']
            ]);
        }
    }
    create_wc_select('paper_types', 'Paper Types', 'paper_type', $post_id);
    create_wc_select('grampage', 'Grampage', 'grampage', $post_id);
    create_wc_select('quantity', 'Quantity', 'quantity', $post_id);
    create_wc_select('finishing', 'Finishing', 'finishing', $post_id);
    ?>
	<button type="button" class="change-module button">Change Module</button>
    <button type="button" class="file-upload-button button">Upload File</button>
    <input type="file" id="fileUploadMain" name="main_module_file_get" class="main_data_file" style="display:none" accept=".glb"
    >
    <div class="configuration-3d-wrap">
        <div>
            <div class="add-new-part">
                <a href="javascript:void(0)" class="addPartNewOption button">Add New Option +</a>
                 <a href="javascript:void(0)" id="addcolorOption" class="addcolorOption button">Add color Selection +</a>
                <!-- <a href="javascript:void(0)" id="addtextOption" class="addtextOption button">Add Text Selection +</a> -->
            </div>
            <div class="part-wrap configurator-start-color">
            <?php
                $product_configurator_data = get_post_meta( $post_id, 'threejs_product_configurator_data', true);
                $product_configurator_data  =  (array)json_decode($product_configurator_data);
                if($product_configurator_data){ 
                    if(isset($product_configurator_data['color_parts'])) {
                        foreach($product_configurator_data['color_parts'] as $key => $proData){ ?>
                    
                        <div class="form-field _color_section" id="color_option_<?= $key; ?>">
                            <span class="inner-sec1 inner-sec-custom1">
                                <div class="top-panel part-top-panel">
                                    <label class="label-name color_label_name" for="color_option_name_<?= $key; ?>">Color Option Name</label>
                                    <input type="text" name="color_option_name[]" value="<?= $key; ?>" class="color-option-name">
                                    <a href="javascript:void(0)" class="remove-color-option button" data-id="color_option_<?= $key; ?>">Remove Option -</a>
                                </div>

                                <div class="top-panel part-top-panel">
                                    <label class="label-name color_label_name" for="color_part_<?= $key; ?>">Color Part</label>
                                    <div class="multiSelect color_multi_select">
                                        <select name="color_parts[<?= $key; ?>][part][]" class="short color-part-select js-example-basic-multiple" data-selected="<?php echo ($proData->part)? implode(",",$proData->part)."," : '' ; ?>"></select>
                                    </div>
                                </div>
                                <div class="top-panel part-top-panel">
                                    <label class="label-name color_label_name" for="color_part_<?= $key; ?>">Color Pattern Part</label>
                                    <div class="multiSelect color_multi_select">
                                        <select name="color_parts[<?= $key; ?>][pattern_part][]" class="short color-pattern-part-select js-example-basic-multiple" data-selected="<?php echo ($proData->pattern_part)? implode(",",$proData->pattern_part)."," : '' ; ?>"></select>
                                    </div>
                                </div>
                                <div class="top-panel part-top-panel">
                                    <label class="label-name color_label_name" for="color_gold_part_<?= $key; ?>">Gold border Part</label>
                                    <div class="multiSelect color_multi_select">
                                        <select name="color_parts[<?= $key; ?>][gold_part][]" multiple="multiple" class="short color-gold-part-select js-example-basic-multiple" data-selected="<?php echo ($proData->gold_part)? implode(",",$proData->gold_part)."," : '' ; ?>" ></select>
                                    </div>
                                </div>
                                <div class="top-panel part-top-panel">
                                    <label class="label-name color_label_name" for="color_pattern_<?= $key; ?>">Color Pattern Material</label>
                                    <div class="multiSelect color_pattern_multi_select">
                                        <select name="color_parts[<?= $key; ?>][pattern][]" class="short color-pattern-select js-example-basic-multiple"  multiple="multiple" data-selected="<?php echo ($proData->pattern)? implode(",",$proData->pattern)."," : '' ; ?>"></select>
                                    </div>
                                </div>
                                <div class="top-panel part-top-panel">
                                    <label class="label-name text_label_name" for="color_match">Match with card</label>
                                    <label class="switch"><input type="hidden" class="match_with_card_main" name="color_parts[<?= $key; ?>][match_with_card][]" value="<?= $proData->match_with_card == 1 ? 1 : 0;?>"><input type="checkbox" class="match_with_card" <?php echo $proData->match_with_card == 1 ? "checked" : ""; ?>><span class="slider round"></span></label>
                                </div>
                                <?php if($proData->match_with_card == 1) { ?>
                                    <div class="top-panel part-top-panel added-new-field">
                                        <label class="label-name color_label_name" for="match_with_label_<?= $key; ?>">Match With Label</label>
                                        <div class="multiSelect color_multi_select">
                                            <input type="text" name="color_parts[<?= $key; ?>][match_with_label]" 
                                                class="short color-match-part-input" id="match_with_label_<?= $key; ?>"   value="<?= isset($proData->match_with_label) ? htmlspecialchars($proData->match_with_label) : ''; ?>" />
                                        </div>
                                    </div>
                                    <div class="top-panel part-top-panel added-field">
                                        <label class="label-name color_label_name" for="color_match_part_">Match Color Part</label>
                                        <div class="multiSelect color_multi_select">
                                            <select name="color_parts[<?= $key; ?>][match_color_part_name][]" class="short color-match-part-select js-example-basic-multiple" data-selected="<?php echo ($proData->match_color_part_name)? implode(",",$proData->match_color_part_name)."," : '' ; ?>"></select>
                                        </div>
                                    </div>
                                    <?php } ?>

                                <div class="top-panel part-top-panel">
                                    <label class="label-name color_label_name" for="color_option_icon_<?= $key; ?>">Color Icon</label>
                                    <div class="multiSelect">
                                        <?php  
                                        if (isset($proData->option_icon) && wp_get_attachment_image_url($proData->option_icon) && $proData->option_icon != '') { 
                                        ?>
                                            <a href="javascript:void(0)" class="upload-color-icon" id="color_icon_<?= $key; ?>"><?php echo wp_get_attachment_image($proData->option_icon, 'thumbnail', true); ?></a>
                                            <a href="javascript:void(0)" class="remove-images button">Remove Icon</a>
                                            <input type="hidden" class="color-option" name="color_parts[<?= $key; ?>][option_icon]" value="<?php echo isset($proData->option_icon) ? $proData->option_icon : ''; ?>">
                                        <?php 
                                        } else { 
                                        ?>
                                            <a href="javascript:void(0)" class="upload-color-icon" id="color_icon_<?= $key; ?>">Upload Icon</a>
                                            <a href="javascript:void(0)" class="remove-images button" style="display:none">Remove Icon</a>
                                            <input type="hidden" class="color-option" name="color_parts[<?= $key; ?>][option_icon]" value="">
                                        <?php } ?>
                                    </div>
                                </div>
                                    <div class="top-panel part-top-panel">
                                            <label class="label-name text_label_name" for="embossing_debossing_<?= $key; ?>">Embossing and Debossing Effect</label>
                                            <label class="switch">
                                                <input type="hidden" name="color_parts[<?= $key; ?>][embossing_debossing_effect]" value="0">
                                                <input type="checkbox" 
                                                    class="embossing-debossing-checkbox" 
                                                    id="embossing_debossing_<?= $key; ?>" 
                                                    name="color_parts[<?= $key; ?>][embossing_debossing_effect]" 
                                                     <?php echo $proData->embossing_debossing_effect == 1 ? "checked" : ""; ?>>
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                            </span>
                        </div>
                    <!-- </div> -->
                        <?php }  }}?>
            </div>
            <div class="part-wrap configurator-start-text">
                <?php
                    $product_configurator_data = get_post_meta( $post_id, 'threejs_product_configurator_data', true);
                        if($product_configurator_data){ 
                            $product_configurator_data  =  (array)json_decode($product_configurator_data);
                            if(isset($product_configurator_data['text_parts'])) {
                                foreach($product_configurator_data['text_parts'] as $key => $proData){ ?>
                            <div class="form-field _text_section">
                                <span class="inner-sec1 inner-sec-custom1">
                                    <div class="top-panel part-top-panel">
                                        <label class="label-name text_label_name" for="text_option_name_$textIndex">Text Option Name</label>
                                        <input type="text" name="text_option_name[]" value="<?= $key; ?>" class="text-option-name">
                                        <a href="javascript:void(0)" class="remove-text-option button">Remove Option -</a>
                                    </div>
                    
                                    <div class="top-panel part-top-panel">
                                        <label class="label-name text_label_name" for="text_material_$textIndex">Part</label>
                                        <div class="multiSelect text_multi_select">
                                            <select name="text_parts[<?php echo $key; ?>][material][]" class="short text-material-select js-example-basic-multiple" data-selected="<?php echo ($proData->material)? implode(",",$proData->material)."," : '' ; ?>"></select>
                                        </div>
                                    </div>
                    
                                    <div class="top-panel part-top-panel">
                                        <label class="label-name text_label_name" for="text_color_$textIndex">Default Text Color</label>
                                        <input type="color" name="text_parts[<?php echo $key; ?>][text_color]" class="text-color" value="<?php echo $proData->text_color ?? '' ;?>">
                                    </div>
                                    <div class="top-panel part-top-panel">
                                    <label class="label-name text_label_name" for="text_option_icon_<?=$key?>">Option Icon</label>
                                    <div class="multiSelect">
                                    <?php  
                                        if (isset($proData->option_icon) && wp_get_attachment_image_url($proData->option_icon) && $proData->option_icon != '') { 
                                    ?>
                                        <a href="javascript:void(0)" class="upload-option-icon" id="option_icon_<?=$key?>">
                                            <?php echo wp_get_attachment_image($proData->option_icon, 'thumbnail', true); ?>
                                        </a>
                                        <a href="javascript:void(0)" class="remove-images button">Remove Icon</a>
                                        <input type="hidden" class="icon-option" name="text_parts[<?php echo $key; ?>][option_icon]" value="<?php echo isset($proData->option_icon) ? $proData->option_icon : ''; ?>">
                                    <?php 
                                        } else { 
                                    ?>
                                        <a href="javascript:void(0)" class="button upload-option-icon" id="option_icon_<?=$key?>">Upload Icon</a>
                                        <a href="javascript:void(0)" class="remove-icon button" style="display:none">Remove Icon</a>
                                        <input type="hidden" class="icon-option" name="text_parts[<?php echo $key; ?>][option_icon]" value="">
                                    <?php 
                                        } 
                                    ?>
                                </div>  
                                    </div>
                                </span>
                            </div>
                        <?php }  
                    }
                }?>
            </div>
            <div class="part-wrap configurator-start-baby">
                <div class="all-select" data-selects=""></div>

                <?php
                $product_configurator_data = get_post_meta( $post_id, 'threejs_product_configurator_data', true);
                if($product_configurator_data){ 
                    $product_configurator_data  =  (array)json_decode($product_configurator_data);

                    if(isset($product_configurator_data['baby_parts'])) {
                        foreach($product_configurator_data['baby_parts'] as $key => $proData){ ?>
                            <div class="form-field _part_section_new">
                                <span class="inner-sec1 inner-sec-custom1">
                                    <div class="part-top-panel">
                                        <label class="part-name lable-part-name" for="_part_name1">Option Name</label>
                                        <input type="text" name="baby_option_name[]" class="new-option-name" value="<?= $key; ?>">
                                        <a href="javascript:void(0)" class="remove-parts-this button">Remove Option -</a>
                                        <a href="javascript:void(0)" class="show-hide-sec-new button">Show/Hide</a>
                                        <a href="javascript:void(0)" class="add-parts-this-baby button">Add Type</a>
                                    </div>
                                    <div class="part-top-panel">
                                        <label class="part-name" for="_material">Material</label>
                                        <div class="multiSelect">
                                            <select name="baby_parts[<?php echo $key; ?>][material][]" multiple="multiple" class="short baby-material-sec-select js-example-basic-multiple" data-selected="<?php echo ($proData->material)? implode(",", $proData->material)."," : '' ; ?>">
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="part-top-panel">
                                        <label class="part-name" for="_image">Image</label>
                                        <div class="multiSelect">
                                            <?php if(wp_get_attachment_image_url($proData->image) && $proData->image != ''){ ?>
                                                <a href="javascript:void(0)" class="upload-material-icon" id="material_image_<?=$key?>"><?php echo wp_get_attachment_image($proData->image,'thumbnail',true); ?></a>
                                                <a href="javascript:void(0)" class="remove-images button"> Remove image</a>
                                                <input type="hidden" class="icon-material" name="baby_parts[<?php echo $key; ?>][image]" value="<?php echo $proData->image  ?? ''?>">
                                            <?php } else { ?>
                                                <a href="javascript:void(0)" class="button upload-material-icon" id="material_image_<?=$key?>">Upload image</a>
                                                <a href="javascript:void(0)" class="remove-images button" style="display:none"> Remove image</a>
                                                <input type="hidden" class="icon-material" name="baby_parts[<?php echo $key; ?>][image]" value="">
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="top-panel part-top-panel">
                                        <label class="label-name text_label_name" for="show_hide_parts_<?= $key; ?>">Show/Hide Parts</label>
                                        <label class="switch">
                                            <input type="hidden" name="color_parts[<?= $key; ?>][show_hide_parts_change]" value="0">
                                            <input type="checkbox" 
                                                class="show-hide-parts-change-checkbox" 
                                                id="show_hide_parts_change_<?= $key; ?>" 
                                                name="baby_parts[<?= $key; ?>][show_hide_parts_change]" 
                                                 <?php echo $proData->show_hide_parts_change == 1 ? "checked" : ""; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <div class="part-sec-new">
                                        <div class="parts-section-new">
                                            <?php 
                                            foreach($proData as $partSec => $partVal){ 
                                                if(!is_numeric($partSec)){
                                                    continue;
                                                };
                                            ?>
                                                <div class="container-sec-new">
                                                    <div class="conditional-logic" data-name="<?php echo $key; ?>">
                                                        <div class="added-material">
                                                            <a href="javascript:void(0)" class="remove-sec-this button">Remove Type</a>
                                                        </div>
                                                    </div>
                                                    
                                                    <span class="inner-sec1 inner-sec-custom inner-options">
                                                        <label class="part-name" for="_material">Option label</label>
                                                        <input type="text" name="baby_parts[<?= $key; ?>][label][]" value="<?= $partVal->lable; ?>" class="parts-new-label">
                                                    </span>

                                                    <span class="inner-sec1 inner-sec-custom inner-prices">
                                                        <label class="part-name" for="_material">Option Price</label>
                                                        <input type="text" name="baby_parts[<?= $key; ?>][price][]" value="<?= $partVal->price; ?>" class="parts-new-price">
                                                    </span>

                                                    <span class="inner-sec inner-sec1 inner-maticons">
                                                        <label class="part-name" for="_material">Material icon</label>
                                                        <?php
                                                        if(wp_get_attachment_image_url($partVal->icon)){ ?>
                                                            <a href="javascript:void(0)" class="upload-material-icon" id="material_icon_<?=$key?>"><?php echo wp_get_attachment_image($partVal->icon,'thumbnail',true); ?></a>
                                                            <a href="javascript:void(0)" class="remove-images button"> Remove image</a>
                                                            <input type="hidden" class="icon-material" name="baby_parts[<?php echo $key; ?>][icon][]" value="<?php echo $partVal->icon ?>">
                                                        <?php } else { ?>
                                                            <a href="javascript:void(0)" class="button upload-material-icon" id="material_icon_<?=$key?>">Upload image</a>
                                                            <a href="javascript:void(0)" class="remove-images button" style="display:none"> Remove image</a>
                                                            <input type="hidden" class="icon-material" name="baby_parts[<?php echo $key; ?>][icon][]" value="">
                                                        <?php } ?>
                                                    </span>

                                                    <span class="inner-sec1 inner-sec-custom inner-parts">
                                                        <label class="part-name" for="_material">Select Part</label>
                                                        <div class="multiSelect">
                                                            <select multiple="multiple" name="baby_parts[<?php echo $key; ?>][part][<?= $partSec; ?>][]" id="_part_name" class="short js-example-basic-multiple baby-part-selected" data-selected="<?php echo implode(",", $partVal->parts).","; ?>">
                                                                <option value="">Select Parts</option>
                                                                <?php foreach($partVal->parts as $partKey => $value ){ ?>
                                                                    <option value="<?= $value ?>"><?= $value ?></option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                    </span>

                                                    <span class="inner-sec1 inner-sec-custom inner-parts">
                                                        <label class="part-name" for="_condition">Condition</label>
                                                        <div class="multiSelect">
                                                            <label class="switch">
                                                                <input type="hidden" class="logical_status_main" name="baby_parts[<?= $key; ?>][logic_status][]" value="<?php echo ($partVal->logic_status) ? '1' : '0'; ?>">
                                                                <input type="checkbox" class="logical_status" <?php echo ($partVal->logic_status) ? 'checked' : ''; ?>>
                                                                <span class="slider round"></span>
                                                            </label>
                                                        </div>
                                                    </span>
                                                    <div class="condi-sec-layout inner-sec1">
													<?php //echo '<pre>';print_r($product_configurator_data);echo '</pre>'; ?>
													<?php if(isset($partVal->logic) && !empty($partVal->logic)){ ?>
														<span class="show-if">Show this field if</span>
														<?php foreach($partVal->logic as $logicKey => $logicvalue ){ ?>
														<?php // echo '<pre>';print_r($logicKey);echo '</pre>'; ?>
                                                            <div class="condition-custom-layout">
															<?php
																foreach($logicvalue as $innerkey => $innerValue ){ ?>
																<?php //echo '<pre>';print_r($innerValue); ?>
																<div class="condi-wrapper" data-key="<?= $innerValue[0]?>" data-logic="<?= $innerValue[1]?>" data-value="<?= $innerValue[2]?>">
																	
																	<select name="baby_parts[<?= $key; ?>][logic][<?= $partVal->lable; ?>][<?= $logicKey; ?>][<?= $innerkey ?>][]" class="selected-logic-cond">
																	<?php 
																		$all_parts = $product_configurator_data['baby_parts_display_name'];
																		
																		foreach($all_parts as $key_ => $val){
																			?>
																			<option value="<?php echo $val; ?>" <?= ($innerValue[0] == $val)? 'selected': '' ; ?> <?= ($key == $val) ? 'disabled' : '' ; ?> ><?php echo $val; ?> <?= ($key == $val) ? ' (this field)' : '' ; ?> </option>
																			<?php
																		} ?>
																	</select>
																	<select name="baby_parts[<?= $key; ?>][logic][<?= $partVal->lable; ?>][<?= $logicKey; ?>][<?= $innerkey ?>][]">
																		
																		<option value="==" <?php echo ($innerValue[1] == '==') ? 'selected' : ''; ?>>Is equal to</option>
																		<option value="!=" <?php echo ($innerValue[1] == '!=') ? 'selected' : ''; ?>>Is not equal to</option>
																	</select>
																	<select name="baby_parts[<?= $key; ?>][logic][<?= $partVal->lable; ?>][<?= $logicKey; ?>][<?= $innerkey ?>][]" class="selected-logic-val" data-value_parts="<?=$innerValue[2]; ?>">
																		<option value="<?php echo $innerValue[2]; ?>"><?php echo $innerValue[2]; ?></option> 
																	</select>
																	<a href="javascript:void(0)" class="add-condi-this button" data-part_name="<?= $key; ?>" data-part_type="<?= $partVal->lable; ?>"> and </a>
																	<a href="javascript:void(0)" class="remove-condi-this button" data-part_name="<?= $key; ?>" data-part_type="<?= $partVal->lable; ?>"> - </a>
																</div>															
																<?php 
																
																} ?>
                                                            </div>
															
															<span class="condi-seprator">or</span>
															
															<?php  ?>
														<?php } ?>
															<a href="javascript:void(0)" class="add-condi-or button" data-part_name="<?= $key; ?>" data-part_type="<?= $partVal->lable; ?>">Add rule group</a>
													<?php } ?>
													</div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </span>
                            </div>
                        <?php } 
                    }
                } ?>
            </div>
        </div>
    </div>
</div>
<?php 
// Retrieve mesh collection from post meta
$mesh_collection = json_decode(get_post_meta($post_id, '_threejs_mesh_collection', true));
$mesh_added_value = !empty($mesh_collection) ? '1' : '0';
?>
<input type="hidden" name="threejs_mesh_added_or_notcheck" id="threejs_mesh_added_or_notcheck" value="<?php echo esc_attr($mesh_added_value); ?>" />

<div id="threejs_mesh_collection" class="panel woocommerce_options_panel">
    <div class="options_group">
        <?php if (!empty($mesh_collection)) : ?>
            <h2 style="font-weight:bold;"><?php _e('Mesh Collection', 'woocommerce'); ?></h2>
            <div class="mesh-table" id="mesh-table">
                <table>
                    <thead>
                        <tr>
                            <th><?php _e('Mesh Name', 'woocommerce'); ?></th>
                            <th><?php _e('Custom Mesh Name', 'woocommerce'); ?></th>
                            <th><?php _e('Mesh Price', 'woocommerce'); ?></th>
                            <th><?php _e('Image for Mesh', 'woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mesh_collection as $key => $value) : ?>
                            <tr class="mesh mesh_<?php echo esc_attr($key); ?>">
                                <td><?php echo esc_html($key); ?></td>
                                <td>
                                    <input type="text" name="baby_mesh[<?php echo esc_attr($key); ?>][name]" 
                                           value="<?php echo esc_attr($value->name); ?>" placeholder="<?php esc_attr_e('Enter mesh name', 'woocommerce'); ?>">
                                </td>
                                <td>
                                    <input type="number" name="baby_mesh[<?php echo esc_attr($key); ?>][price]" 
                                           value="<?php echo esc_attr($value->price); ?>" placeholder="<?php esc_attr_e('Enter mesh price', 'woocommerce'); ?>">
                                </td>
                                <td>
                                    <?php $img_url = wp_get_attachment_image_src($value->image_id, 'thumbnail'); ?>
                                    <a href="javascript:void(0);" 
                                       class="button mesh_img baby_mesh_image_<?php echo esc_attr($key); ?>" 
                                       data-image="<?php echo esc_attr($key); ?>">
                                        <?php if (!empty($value->image_id) && $img_url) : ?>
                                            <img src="<?php echo esc_url($img_url[0]); ?>" alt="<?php esc_attr_e('Mesh Image', 'woocommerce'); ?>">
                                        <?php else : ?>
                                            <?php _e('Choose Image', 'woocommerce'); ?>
                                        <?php endif; ?>
                                    </a>
                                    <input type="hidden" name="baby_mesh[<?php echo esc_attr($key); ?>][image_id]" 
                                           id="baby_mesh_image_<?php echo esc_attr($key); ?>" 
                                           value="<?php echo esc_attr($value->image_id); ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <h2 style="font-weight:bold; color: red;"><?php _e('Mesh not synced yet! Please sync it from the 3D Configurator tab.', 'woocommerce'); ?></h2>
        <?php endif; ?>
    </div>
</div>