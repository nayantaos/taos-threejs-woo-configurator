<?php 
$post_id = $post->ID;
$product_configurator_data = get_post_meta( $post_id, 'threejs_product_configurator_data', true);
?>
<input type="hidden" id="3d_config_input" name="3d_config_input" 
       value="<?php echo isset( $_GET['edit_config_3d'] ) ? 'value_if_set' : 'value_if_not_set'; ?>" />
	   <?php 
// echo "<pre>dddd"; print_r($product_configurator_data); exit;

if(isset($_POST) && !empty($_POST) && !empty($_POST['pro_image_cus'])){
	$value = str_replace('\\','',$_POST['pro_image_cus']);
	$array = json_decode($value, true);

	$uniqueArray = [];
	foreach ($array as $item) {
		if (!empty($item) && isset($item['hide'])) {
			$uniqueArray[$item['hide']] = $item;
		}
	}

	$hide = array();
	
	$uniqueArray = array_values($uniqueArray);

	foreach($uniqueArray as $sVal){
		if(!isset($hide['hide'])){
			$hide['hide'] = $sVal['hide'];
		}else{
			$hide['hide'] = $hide['hide'].','.$sVal['hide'] ;
		}
		$hide['show'][] = array($sVal['show'],$sVal['material']);			
	}
	
	$Hide_parts = '"' . str_replace(',', '","', $hide['hide']) . '"';
	
	$output = [];
	foreach ($hide['show'] as $item) {
		// Split the first element (parts) by comma if needed
		$parts = explode(',', $item[0]);
	
		// Prepare the formatted item with 'parts'
		$formattedItem = [
			'parts' => $parts
		];
	
		// If the second element exists, treat it as 'material'
		if (isset($item[1])) {
			$formattedItem['material'] = $item[1];
		}
	
		// Add to output array
		$output[] = $formattedItem;
	}
	$jsonOutput = json_encode($output);
	?>
	<?php
}
?>

<input type="hidden" name="is_config_data" value="1">


<?php  $option = 2; ?>
<?php if($option == 1){ ?>
<section class="configurator3d-wrapper">
    <div class="d3-data-tab">
        <ul class="d3-data-tabul">
		<?php
			$check = $product_configurator_data;
			
			$parts = json_decode($check); 
			
			$active = 1;
			
			foreach($parts->parts as $partname => $partData){
				
				?> 
				<input type="hidden" id="part_name" name="display[<?=$partname;?>][name]" value="<?php echo $partname; ?>">
				<li class="d3-data-tabli <?php echo ($active == 1) ? 'active' : '' ; $active++; ?>">
				   <a href="javascript:void(0)" data-parts="<?php echo sanitize_title($partname); ?>" data-transition="<?php echo $trans; ?>">
						<label class="main-label"><?php echo $partname; ?></label> 
					</a>
				</li>
				<?php
			}
		?>
        </ul>
        <div class="d3data-tab-content">
		
		<?php 
			$active_ = 1;
			
			foreach($parts->parts as $partname => $partData){
				?>
				
				<div class="content-show <?php echo ($active_ == 1) ? 'active-data' : '' ; $active_++; ?>" id="<?php echo sanitize_title($partname); ?>">
					<h2 class="inner-titles"><?php echo $partname; ?> Type</h2>
					<ol class="starp-ol">
					<?php 
					$active_list = 1;
					$all_parts = array();
					$all_parts_new = array();
					//echo '<pre>';print_r($partData);
					foreach($partData as $key => $value){
						if($key == 'material' || $key ==  'animation' || $key == 'image' || $key == 'show_hide_parts_change'){
							continue;
						}
						if($key == 0) {
                            $part_lable = $value->lable;
							$part_price = $value->price;
                        }
						$all_parts[] = $value->parts;
						$all_parts_new[] = implode(',', $value->parts);
					}
                    ?>
                    <input type="hidden" id="all_parts" value="<?php echo implode(',', $all_parts_new); ?>">
						<?php //echo '<pre>';print_r($partData); ?>
					<input type="hidden" id="part_type" class="part_type" name="display[<?=$partname;?>][type]" value="<?php echo $part_lable; ?>">
					<input type="hidden" id="part_price" class="part_price" name="display[<?=$partname;?>][price]" value="">
					<input type="hidden" id="part_all_value" class="part_all_value" name="display[<?=$partname;?>][part_all_value]" value="">
                    <?php
                    // echo '<pre>'; print_r($partData);
					foreach($partData as $key => $value){
						if($key == 'material'|| $key ==  'animation' || $key == 'image'  || $key == 'show_hide_parts_change'){
							continue;
						}
						?>
						<input type="hidden" class="logic " data-part="<?php echo $partname; ?>" data-label="<?php echo $value->lable; ?>" value='<?php echo json_encode($value->logic); ?>'> 
						
						<li class="starp-li <?php echo $partname.' '. str_replace(' ','-',$value->lable).' '; ?><?php echo ($active_list == 1) ? 'active-list' : '' ; $active_list++; ?>" data-price="<?php echo $value->price; ?>">
							<a href="javascript:void(0)" data-price="<?php echo $value->price; ?>" data-part="<?php echo $partname;  ?>" data-label="<?php echo $value->lable; ?>" data-price="<?=$value->price;?>" data-parts="<?php echo implode(',', $value->parts); ?>"  data-logic_status="<?php echo $value->logic_status; ?>" data-logic='<?php echo json_encode($value->logic); ?>' >
								<span><?php echo $value->lable; ?></span>
							</a>
						</li>
						<?php
						
					} ?>
					</ol>
					<div class="inner-tab-datas">
						<h2 class="inner-titles">Materials</h2>				
						<div class="stich-data ">
						<input type="hidden" id="part_materials" name="display[<?=$partname;?>][materials]" value="<?php echo $partData->material[0]; ?>">
						<?php 
							$mesh_collection = json_decode(get_post_meta( $post_id, '_threejs_mesh_collection', true)); 
							$check = $partData->material[0];
						?>
						<input type="hidden" id="material_price" name="display[<?=$partname;?>][material_price]" value="<?php echo $mesh_collection->$check->price; ?>">
						<?php 
						$switch_mat = 1;
				
						foreach($partData->material as $material){
							$material_price = $mesh_collection->$material->price;
							?>
							<div class="stich-content <?php echo ($switch_mat == 1) ? 'stitch-active' : '' ; $switch_mat++; ?>" data-material="<?=$material?>" data-price="<?=$material_price?>">
								<?php if($mesh_collection->$material->image_id != ''){
									$img_url = wp_get_attachment_image_src($mesh_collection->$material->image_id, 'thumbnail');
								?>
								<img src="<?=$img_url[0];?>" alt="" class="swtch-image">
								<?php
								}else{ ?>
								<img src="../../wp-content/plugins/3DConfigurator-Woo-ThreeJs/public/images/material-1.jpg" alt="" class="swtch-image">
								<?php } ?>
								<span><?php echo $mesh_collection->$material->name; ?></span>
							</div>							
							<?php
						} ?>
						</div> 
					</div>
				</div>
				<?php
			} ?>

        </div>
    </div>
</section>
<?php } ?>

<?php if($option == 2){ ?>
	<?php if (wp_is_mobile()): ?>
		<div class="mobile-custom-container">
			<?php
			$parts = json_decode($product_configurator_data);
			$part_types = ['baby_parts', 'text_parts', 'color_parts'];

			foreach ($part_types as $type) {
				if (!empty($parts->$type)) {
					foreach ($parts->$type as $partname => $partData) {
						echo '<div class="mobile-custom-section" data-change_n_section="' . sanitize_title($partname) . '">
								<p>' . esc_html($partname) . '</p>
							</div>';
					}
				}
			}
			
			if (!empty($parts->text_parts)) {
				echo '<div class="mobile-custom-section" data-change_n_section="text_effects">
						<p>TEXT EFFECTS</p>
					</div>';
			}
			if (!empty($parts->paper_types) || !empty($parts->quantity) || !empty($parts->grampage) || !empty($parts->finishing)) {
				echo '<div class="mobile-custom-section" data-change_n_section="criteria_options">
						<p>CRITERIA OPTIONS</p>
					  </div>';
			}
			?>
		</div>
	<?php endif; ?>

<section class="configurator3d-wrapper mian-mobile-div-3d" style="<?= wp_is_mobile() ? 'display: none;' : ''; ?>">
    <div class="d3-data-tab">
		<div class="tab-mains">
			<div class="prev-btn slide-btn">
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="34" viewBox="0 0 18 34" fill="none">
				<path d="M17.5163 2.86664C17.6708 2.70444 17.7916 2.51363 17.8718 2.30511C17.952 2.09659 17.9899 1.87445 17.9836 1.65137C17.9772 1.42829 17.9266 1.20863 17.8347 1.00495C17.7428 0.80126 17.6113 0.617532 17.4478 0.464251C17.2843 0.31097 17.0919 0.191139 16.8817 0.111599C16.6715 0.0320597 16.4476 -0.00563108 16.2227 0.000679604C15.9978 0.00699029 15.7764 0.0571785 15.5711 0.148379C15.3658 0.239579 15.1805 0.370006 15.026 0.532211L0.468152 15.8232C0.167512 16.1387 0 16.5563 0 16.9905C0 17.4246 0.167512 17.8422 0.468152 18.1577L15.026 33.4504C15.1795 33.6161 15.3647 33.75 15.5708 33.8442C15.7769 33.9383 15.9997 33.9909 16.2265 33.9989C16.4532 34.0069 16.6793 33.9701 16.8916 33.8907C17.1039 33.8113 17.2982 33.6908 17.4632 33.5363C17.6281 33.3818 17.7605 33.1963 17.8526 32.9905C17.9447 32.7848 17.9947 32.563 17.9996 32.338C18.0045 32.113 17.9644 31.8892 17.8814 31.6797C17.7984 31.4702 17.6743 31.2792 17.5163 31.1177L4.06823 16.9905L17.5163 2.86664Z" fill="#C1C6CA"></path>
				</svg>
			</div>
			<ul class="d3-data-tabul">
			<?php

				$check = $product_configurator_data;
				$parts = json_decode($check); 
				$active = 1;
				if (isset($parts->baby_parts) && !empty($parts->baby_parts)) {
					foreach($parts->baby_parts as $partname => $partData){
						?> 
						<input type="hidden" id="part_name" name="display[<?=$partname;?>][name]" value="<?php echo $partname; ?>">
						<li class="d3-data-tabli <?php echo ($active == 1) ? 'active' : '' ; $active++; ?>">
							<?php  ?>
						<a href="javascript:void(0)" data-parts="<?php echo sanitize_title($partname); ?>" data-transition="<?php echo $trans;?>">
								<!-- <img src="<?php echo wp_get_attachment_image_url($partData->image,'full'); ?> " alt="<?php basename(wp_get_attachment_image_url($partData->image)); ?>"  style="width: 50px; height: 50px;"> -->
								<label class="main-label"><?php echo $partname; ?></label>
							</a>
						</li>
						<?php
					}
				}
				if (isset($parts->text_parts) && !empty($parts->text_parts)) {
					foreach($parts->text_parts as $partname => $partData){
						?> 
						<input type="hidden" id="part_name" name="display[<?=$partname;?>][name]" value="<?php echo $partname; ?>"  class= "option_name_text_find">
						<li class="d3-data-tabli <?php echo ($active == 1) ? 'active' : '' ; $active++; ?>">
						<a href="javascript:void(0)" data-parts="<?php echo sanitize_title($partname); ?>" data-transition="<?php echo $trans;?>">
								<!--<img src="<?php echo wp_get_attachment_image_url($partData->option_icon,'full'); ?> " alt="<?php basename(wp_get_attachment_image_url($partData->option_icon)); ?>"> -->
								<label class="main-label"><?php echo $partname; ?>
							</a>
						</li>
						<?php
					}
				}
				if (isset($parts->text_parts) && !empty($parts->text_parts)) {
					?> 
					<input type="hidden" id="part_name" name="display['text_effects'][name]" class= "option_name_text_find" value="text_effects">
					<li class="d3-data-tabli <?php echo ($active == 1) ? 'active' : '' ; $active++; ?>">
					<a href="javascript:void(0)" data-parts="text_effects" >
							<label class="main-label">TEXT EFFECTS </label>
						</a>
					</li>
					<?php
			}
				if (isset($parts->color_parts) && !empty($parts->color_parts)) {
					foreach($parts->color_parts as $partname => $partData){
						?> 
						<input type="hidden" id="part_name" name="display[<?=$partname;?>][name]" value="<?php echo $partname; ?>"  class= "option_name_text_find">
						<li class="d3-data-tabli <?php echo ($active == 1) ? 'active' : '' ; $active++; ?>">
						<a href="javascript:void(0)" data-parts="<?php echo sanitize_title($partname); ?>" >
								<!-- <img src="<?php echo wp_get_attachment_image_url($partData->option_icon,'full'); ?> " alt="<?php basename(wp_get_attachment_image_url($partData->option_icon)); ?>"> -->
						<label class="main-label"><?php echo $partname; ?>
							</a>
						</li>
						<?php
					}
				}
				if (!empty($parts->paper_types) || !empty($parts->quantity) || !empty($parts->grampage) || !empty($parts->finishing)) { ?>
					<input type="hidden" id="part_name" name="display['criteria_options'][name]" class= "option_name_text_find" value="criteria_options">
					<li class="d3-data-tabli <?php echo ($active == 1) ? 'active' : '' ; $active++; ?>">
					<a href="javascript:void(0)" data-parts="criteria_options" >
							<label class="main-label">CRITERIA OPTIONS </label>
						</a>
					</li>
				<?php }
			?>
			
			</ul>
			<div class="next-btn slide-btn">
				<svg xmlns="http://www.w3.org/2000/svg" width="18" height="34" viewBox="0 0 18 34" fill="none">
				<path d="M0.483719 31.1334C0.329203 31.2956 0.208409 31.4864 0.128227 31.6949C0.0480463 31.9034 0.0100519 32.1255 0.0164129 32.3486C0.0227739 32.5717 0.0733682 32.7914 0.165302 32.9951C0.257238 33.1987 0.388714 33.3825 0.552227 33.5357C0.715738 33.689 0.908085 33.8089 1.11828 33.8884C1.32848 33.9679 1.55241 34.0056 1.77729 33.9993C2.00217 33.993 2.22359 33.9428 2.42892 33.8516C2.63425 33.7604 2.81946 33.63 2.97397 33.4678L17.5318 18.1768C17.8325 17.8613 18 17.4437 18 17.0095C18 16.5754 17.8325 16.1578 17.5319 15.8423L2.97398 0.549605C2.82048 0.383849 2.63531 0.249999 2.42923 0.155833C2.22315 0.061667 2.00026 0.00905851 1.77351 0.0010667C1.54676 -0.00692511 1.32066 0.02986 1.10836 0.109282C0.896064 0.1887 0.701789 0.309176 0.53682 0.463709C0.371854 0.618239 0.239482 0.803748 0.147397 1.00946C0.0553122 1.21517 0.00534914 1.43698 0.000409092 1.66201C-0.00453096 1.88703 0.0356492 2.11079 0.118619 2.32028C0.201589 2.52978 0.325692 2.72083 0.483722 2.88234L13.9318 17.0095L0.483719 31.1334Z" fill="#C1C6CA"/>
				</svg>
			</div>
		</div>
        <div class="d3data-tab-content">
		
		<?php 
			$active_ = 1;
			if (isset($parts->baby_parts) && !empty($parts->baby_parts)) {
			foreach($parts->baby_parts as $partname => $partData){
				?>
				<div class="content-show <?php echo ($active_ == 1) ? 'active-data' : '' ; $active_++; ?>" id="<?php echo sanitize_title($partname); ?>">
					<!-- <h2 class="inner-titles"><?php echo $partname; ?> Type</h2> -->
					<div class="starp-mainsection">
						<div class="prev-btn slide-btn">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="34" viewBox="0 0 18 34" fill="none">
							<path d="M17.5163 2.86664C17.6708 2.70444 17.7916 2.51363 17.8718 2.30511C17.952 2.09659 17.9899 1.87445 17.9836 1.65137C17.9772 1.42829 17.9266 1.20863 17.8347 1.00495C17.7428 0.80126 17.6113 0.617532 17.4478 0.464251C17.2843 0.31097 17.0919 0.191139 16.8817 0.111599C16.6715 0.0320597 16.4476 -0.00563108 16.2227 0.000679604C15.9978 0.00699029 15.7764 0.0571785 15.5711 0.148379C15.3658 0.239579 15.1805 0.370006 15.026 0.532211L0.468152 15.8232C0.167512 16.1387 0 16.5563 0 16.9905C0 17.4246 0.167512 17.8422 0.468152 18.1577L15.026 33.4504C15.1795 33.6161 15.3647 33.75 15.5708 33.8442C15.7769 33.9383 15.9997 33.9909 16.2265 33.9989C16.4532 34.0069 16.6793 33.9701 16.8916 33.8907C17.1039 33.8113 17.2982 33.6908 17.4632 33.5363C17.6281 33.3818 17.7605 33.1963 17.8526 32.9905C17.9447 32.7848 17.9947 32.563 17.9996 32.338C18.0045 32.113 17.9644 31.8892 17.8814 31.6797C17.7984 31.4702 17.6743 31.2792 17.5163 31.1177L4.06823 16.9905L17.5163 2.86664Z" fill="#C1C6CA"></path>
							</svg>
						</div>
						<ol class="starp-ol">
							<?php 
							$active_list = 1;
							$all_parts = array();
							$all_parts_new = array();
							foreach($partData as $key => $value){
								// echo '<pre>';print_r(); exit;
								if($key == 'material' || $key ==  'animation' || $key == 'image' || $key == 'show_hide_parts_change'){
									continue;
								}
								if($key == 0) {
									$part_lable = $value->lable;
									$part_price = $value->price;
								}
								$all_parts[] = $value->parts;
								if (is_array($value->parts)) {
									$all_parts_new[] = implode(',', $value->parts);
								} else {
									$all_parts_new[] = $value->parts; // Or maybe just skip? depends on expected structure
								}
								//$all_parts_new[] = implode(',', $value->parts);
							}
							?>
							<input type="hidden" id="all_parts" value="<?php echo implode(',', $all_parts_new); ?>">
								<?php //echo '<pre>';print_r($partData); ?>
							<input type="hidden" id="part_type" class="part_type" name="display[<?=$partname;?>][type]" value="<?php echo $part_lable; ?>">
							<input type="hidden" id="part_price" class="part_price" name="display[<?=$partname;?>][price]" value="">
							<input type="hidden" id="part_all_value" class="part_all_value"  name="display[<?= esc_attr($partname); ?>][part_all_value]" value='<?= esc_attr(json_encode($partData->{0}->parts)); ?>'>
							<input type="hidden" id="change_show_hide_fun" class="change_show_hide_fun" name="display[<?= $partname; ?>][change_show_hide_fun]" value="<?= isset($partData->show_hide_parts_change) ? $partData->show_hide_parts_change : '' ?>">

							
							<?php
							// echo '<pre>'; print_r($partData);
							foreach($partData as $key => $value){
								if($key == 'material'|| $key ==  'animation' || $key == 'image'|| $key == 'show_hide_parts_change'){
									continue;
								}
							?>
								<input type="hidden" class="logic " data-part="<?php echo $partname; ?>" data-label="<?php echo $value->lable; ?>" value='<?php echo json_encode($value->logic); ?>'> 
								
								<li class="starp-li <?php echo $partname.' '. str_replace(' ','-',$value->lable).' '; ?><?php echo ($active_list == 1) ? 'active-list' : '' ; $active_list++; ?>" data-price="<?php echo $value->price; ?>">
									<a href="javascript:void(0)" data-price="<?php echo $value->price; ?>" data-part="<?php echo $partname;  ?>" data-label="<?php echo $value->lable; ?>" data-price="<?=$value->price;?>" data-parts="<?php echo implode(',', $value->parts); ?>"data-show-hide-parts-change="<?php echo $partData->show_hide_parts_change == 1 ? 1 : 0; ?>" data-logic_status="<?php echo $value->logic_status; ?>" data-logic='<?php echo json_encode($value->logic); ?>' >
										<span><?php echo $value->lable; ?></span>
									</a>
								</li>
							<?php
							}
						?>
						</ol>
						<div class="next-btn slide-btn">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="34" viewBox="0 0 18 34" fill="none">
							<path d="M0.483719 31.1334C0.329203 31.2956 0.208409 31.4864 0.128227 31.6949C0.0480463 31.9034 0.0100519 32.1255 0.0164129 32.3486C0.0227739 32.5717 0.0733682 32.7914 0.165302 32.9951C0.257238 33.1987 0.388714 33.3825 0.552227 33.5357C0.715738 33.689 0.908085 33.8089 1.11828 33.8884C1.32848 33.9679 1.55241 34.0056 1.77729 33.9993C2.00217 33.993 2.22359 33.9428 2.42892 33.8516C2.63425 33.7604 2.81946 33.63 2.97397 33.4678L17.5318 18.1768C17.8325 17.8613 18 17.4437 18 17.0095C18 16.5754 17.8325 16.1578 17.5319 15.8423L2.97398 0.549605C2.82048 0.383849 2.63531 0.249999 2.42923 0.155833C2.22315 0.061667 2.00026 0.00905851 1.77351 0.0010667C1.54676 -0.00692511 1.32066 0.02986 1.10836 0.109282C0.896064 0.1887 0.701789 0.309176 0.53682 0.463709C0.371854 0.618239 0.239482 0.803748 0.147397 1.00946C0.0553122 1.21517 0.00534914 1.43698 0.000409092 1.66201C-0.00453096 1.88703 0.0356492 2.11079 0.118619 2.32028C0.201589 2.52978 0.325692 2.72083 0.483722 2.88234L13.9318 17.0095L0.483719 31.1334Z" fill="#C1C6CA"/>
							</svg>
						</div>
					</div>
					<div class="inner-tab-datas">
						<!-- <h2 class="inner-titles">Materials</h2>				 -->
						<div class="prev-btn slide-btn">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="34" viewBox="0 0 18 34" fill="none">
							<path d="M17.5163 2.86664C17.6708 2.70444 17.7916 2.51363 17.8718 2.30511C17.952 2.09659 17.9899 1.87445 17.9836 1.65137C17.9772 1.42829 17.9266 1.20863 17.8347 1.00495C17.7428 0.80126 17.6113 0.617532 17.4478 0.464251C17.2843 0.31097 17.0919 0.191139 16.8817 0.111599C16.6715 0.0320597 16.4476 -0.00563108 16.2227 0.000679604C15.9978 0.00699029 15.7764 0.0571785 15.5711 0.148379C15.3658 0.239579 15.1805 0.370006 15.026 0.532211L0.468152 15.8232C0.167512 16.1387 0 16.5563 0 16.9905C0 17.4246 0.167512 17.8422 0.468152 18.1577L15.026 33.4504C15.1795 33.6161 15.3647 33.75 15.5708 33.8442C15.7769 33.9383 15.9997 33.9909 16.2265 33.9989C16.4532 34.0069 16.6793 33.9701 16.8916 33.8907C17.1039 33.8113 17.2982 33.6908 17.4632 33.5363C17.6281 33.3818 17.7605 33.1963 17.8526 32.9905C17.9447 32.7848 17.9947 32.563 17.9996 32.338C18.0045 32.113 17.9644 31.8892 17.8814 31.6797C17.7984 31.4702 17.6743 31.2792 17.5163 31.1177L4.06823 16.9905L17.5163 2.86664Z" fill="#C1C6CA"/>
							</svg>
						</div>
						<div class="stich-data ">

						<input type="hidden" id="part_materials" name="display[<?=$partname;?>][materials]" value="<?php echo $partData->material[0]; ?>">
						<?php 
							$mesh_collection = json_decode(get_post_meta( $post_id, '_threejs_mesh_collection', true)); 
							$check = $partData->material[0];
							// echo "<pre>"; print_r($check); exit;
						?>
						<input type="hidden" id="material_price" name="display[<?=$partname;?>][material_price]" value="<?php echo $mesh_collection->$check->price; ?>">
						<?php 
						$switch_mat = 1;
				
						foreach($partData->material as $material){
							$material_price = $mesh_collection->$material->price;
							?>
							<div class="stich-content <?php echo ($switch_mat == 1) ? 'stitch-active' : '' ; $switch_mat++; ?>" data-material="<?=$material?>" data-price="<?=$material_price?>">
								<?php if($mesh_collection->$material->image_id != ''){ 
									$img_url = wp_get_attachment_image_src($mesh_collection->$material->image_id, 'thumbnail');
								?>
								<img src="<?=$img_url[0];?>" alt="" class="swtch-image">
								
								<?php
								}else{ ?>
								<img src="../../wp-content/plugins/3DConfigurator-Woo-ThreeJs/public/images/material-1.jpg" alt="" class="swtch-image">
								<?php } ?>
								<!-- <span><?php echo $mesh_collection->$material->name; ?></span> 
								<br />-->
								<span><?php echo '+ '.$material_price.' '.get_woocommerce_currency_symbol(); ?></span>
							</div>							
							<?php
						} ?>
						</div> 
						<div class="next-btn slide-btn">
							<svg xmlns="http://www.w3.org/2000/svg" width="18" height="34" viewBox="0 0 18 34" fill="none">
							<path d="M0.483719 31.1334C0.329203 31.2956 0.208409 31.4864 0.128227 31.6949C0.0480463 31.9034 0.0100519 32.1255 0.0164129 32.3486C0.0227739 32.5717 0.0733682 32.7914 0.165302 32.9951C0.257238 33.1987 0.388714 33.3825 0.552227 33.5357C0.715738 33.689 0.908085 33.8089 1.11828 33.8884C1.32848 33.9679 1.55241 34.0056 1.77729 33.9993C2.00217 33.993 2.22359 33.9428 2.42892 33.8516C2.63425 33.7604 2.81946 33.63 2.97397 33.4678L17.5318 18.1768C17.8325 17.8613 18 17.4437 18 17.0095C18 16.5754 17.8325 16.1578 17.5319 15.8423L2.97398 0.549605C2.82048 0.383849 2.63531 0.249999 2.42923 0.155833C2.22315 0.061667 2.00026 0.00905851 1.77351 0.0010667C1.54676 -0.00692511 1.32066 0.02986 1.10836 0.109282C0.896064 0.1887 0.701789 0.309176 0.53682 0.463709C0.371854 0.618239 0.239482 0.803748 0.147397 1.00946C0.0553122 1.21517 0.00534914 1.43698 0.000409092 1.66201C-0.00453096 1.88703 0.0356492 2.11079 0.118619 2.32028C0.201589 2.52978 0.325692 2.72083 0.483722 2.88234L13.9318 17.0095L0.483719 31.1334Z" fill="#C1C6CA"/>
							</svg>
						</div>
					</div>
				</div>
				<?php
			} }
			if (isset($parts->text_parts) && !empty($parts->text_parts)) {
				
				$materials = array_column((array)$parts->text_parts, 'material');

				// Merge all arrays into a single array and remove duplicates
				$materialNames = array_unique(array_merge(...$materials));
				?>
				 <input type="hidden" id="all_parts_for_get_overaly_text" value='<?php echo json_encode($materialNames); ?>'>
				<?php 
			foreach($parts->text_parts as $partname => $partData){
				?>
				<div class="content-show <?php echo ($active_ == 1) ? 'active-data' : '' ; $active_++; ?>" id="<?php echo sanitize_title($partname); ?>" >
					<!-- <h2 class="inner-titles"><?php echo $partname; ?> Type</h2> -->
					<div class="starp-mainsection text-section-check-for-parts" data-part-name ="<?php echo ($partData->material)? implode(",",$partData->material)."" : '' ; ?>">
						
					<div class="inner-tab-datas text-area-fields" style="width:100%;">					
					</div>
				
				</div>
				<div class="inner-tab-datas">
					<div class="text-section tab-content" id="text-section">
						<h5 class="text-lg mb-2">TEXT COLOR</h5>
						<div class="text-options">	
							<div>
								<label class="custom-color">
									<input type="text" title="Custom Color" class="text-color-change" style="opacity: 0; position: absolute;"  value="<?php echo $parts->color_option ?? '';?>">
								</label>
								<span>Custom</span>
							</div>
							<div  class="text-color-iteams">
								<input type="color" value="#FFFFFF" title="White" >
								<span>White</span>
							</div>
							<div class="text-color-iteams">
								<input type="color" value="#D60000" title="Red" >
								<span>Red</span>
							</div>
							<div class="text-color-iteams">
								<input type="color" value="#0045AC" title="Blue" >
								<span>Blue</span>
							</div>
							<div class="text-color-iteams">
								<input type="color" value="#FFD900" title="Yellow" >
								<span>Yellow</span>
							</div>
							<div class="text-color-iteams">
								<input type="color" value="#FF8706" title="Orange" >
								<span>Orange</span>
							</div>
							<div class="text-color-iteams">
								<input type="color" value="#7BC9FA" title="Sky Blue" >
								<span>Sky Blue</span>
							</div>
							<div class="text-color-iteams">
								<input type="color" value="#16461C" title="Green" >
								<span>Green</span>
							</div>
							<div class="text-color-iteams">
								<input type="color" value="#070707" title="Black" >
								<span>Black</span>
							</div>
						</div>
					</div>
				</div>
				<button style="display:none;" type="button" class="apply-text button" data-selected="<?php echo ($partData->material)? implode(",",$partData->material)."" : '' ; ?>">Done</button>
				</div>
				<?php
			} }
			if (isset($parts->text_parts) && !empty($parts->text_parts)) {
				?>
				<div class="content-show <?php echo ($active_ == 1) ? 'active-data' : '' ; $active_++; ?>" id="text_effects" >
				<div class="starp-mainsection"  data-part-name ="text_effects">
				<div class="inner-tab-datas">
					<div class="text-section-emboss tab-content emboss-flex" id="text-section-emboss">
						<div class="emboss-text-center" data-changemat = "no_effect">
							<img alt="No effects on text" height="100" src="<?php echo plugin_dir_url(__FILE__) . '../images/default.png'; ?>" width="100"/>
							<p>NO EFFECTS</p>
						</div>
						<div class="emboss-text-center" data-changemat = "embossed">
							<img alt="Text with embossing effect" height="100" src="<?php echo plugin_dir_url(__FILE__) . '../images/emboss.png'; ?>" width="100"/>
							<p class="underline">EMBOSSING</p>
						</div>
						<div class="emboss-text-center" data-changemat = "debossed">
							<img alt="Text with debossing effect" height="100" src="<?php echo plugin_dir_url(__FILE__) . '../images/deboss.jpg'; ?>" width="100"/>
							<p>DEBOSSING</p>
						</div>
					</div>
				</div>
				</div>
				</div>
			<?php }
			if (isset($parts->color_parts) && !empty($parts->color_parts)) {
				foreach($parts->color_parts as $partname => $partData){
					?>
					<div class="content-show <?php echo ($active_ == 1) ? 'active-data' : '' ; $active_++; ?>" id="<?php echo sanitize_title($partname); ?>">
						<!-- <h2 class="inner-titles"><?php echo $partname; ?> Type</h2> -->
						<div class="starp-mainsection" data-part-name ="<?php echo ($partData->part)? implode(",",$partData->part)."" : '' ; ?>">
							<div class="inner-tab-datas text-area-fields">
								<div class="sub-tabs">
									<a href="javascript:void(0);" class="active change_tabs" data-sub-tab="color-section" data-parts="<?php echo $partname; ?>">COLOR</a>
									<a href="javascript:void(0);" class="inactive change_tabs" data-sub-tab="patterns-section" data-parts="<?php echo $partname; ?>" >PATTERNS</a>
								</div>
							</div>
						</div>
						<div class="color-section tab-content" id="color-section" data-part-name ="<?php echo ($partData->part)? implode(",",$partData->part)."" : '' ; ?>">
							<div class="color-options">
								<div>
									<label class="custom-color">
										<input type="text" title="Custom Color" class="material-color-change material-custom-change" style="opacity: 0; position: absolute;">
									</label>
									<span>Custom</span>
								</div>
								<div class="color-iteams">
									<input type="color" value="#FFFFFF" title="White" class="material-color-change">
									<span>White</span>
								</div>
								<div class="color-iteams">
									<input type="color" value="#D60000" title="Red" class="material-color-change">
									<span>Red</span>
								</div>
								<div class="color-iteams">
									<input type="color" value="#0045AC" title="Blue" class="material-color-change">
									<span>Blue</span>
								</div>
								<div class="color-iteams">
									<input type="color" value="#FFD900" title="Yellow" class="material-color-change">
									<span>Yellow</span>
								</div>
								<div class="color-iteams">
									<input type="color" value="#FF8706" title="Orange" class="material-color-change">
									<span>Orange</span>
								</div>
								<div class="color-iteams">
									<input type="color" value="#7BC9FA" title="Sky Blue" class="material-color-change">
									<span>Sky Blue</span>
								</div>
								<div class="color-iteams">
									<input type="color" value="#16461C" title="Green" class="material-color-change">
									<span>Green</span>
								</div>
								<div class="color-iteams">
									<input type="color" value="#070707" title="Black" class="material-color-change">
									<span>Black</span>
								</div>
							</div>
							<?php if (isset($partData->gold_part) || !empty($partData->gold_part)) { ?>
							<?php if(!isset($partData->match_with_card) || $partData->match_with_card != 1){ ?>
							<div class="toggle-container">
								<input type="checkbox" name="toggle" id="toggle_border_color" class="toggle-checkbox toggle_border" data-golden_border="<?php echo ($partData->gold_part)? implode(",",$partData->gold_part)."," : '' ; ?>"/>
								<label for="toggle_border_color" class="toggle-label"></label>
								<span>Gold border</span>
							</div>
							<?php } }?>
							<?php if(isset($partData->match_with_card) && $partData->match_with_card == 1){ ?>
								<div class="toggle-container">
									<input type="checkbox" name="toggle" id="toggle_match_color" class="toggle-checkbox toggle_match_color" data-parts="<?php echo ($partData->part)? implode(",",$partData->part)."" : '' ; ?>"
										data-match_color_part="<?php echo ($partData->match_color_part_name)? implode(",",$partData->match_color_part_name)."" : '' ; ?>"/>
									<label for="toggle_match_color" class="toggle-label"></label>
									<span>Match with <?= isset($partData->match_with_label) ? htmlspecialchars($partData->match_with_label) : ''; ?></span>
								</div>
							<?php } ?>
							
							<button type="button" class="apply-c-color button" style="display:none;" data-selected="<?php echo ($partData->part)? implode(",",$partData->part)."" : '' ; ?>" data-parts="<?php echo $partname; ?>" data-material="">Done</button>
						</div>
						<div class="patterns-section hidden tab-content" id="patterns-section" style="display: none;" data-selected="<?php echo ($partData->pattern_part)? implode(",",$partData->pattern_part)."" : '' ; ?>">
							<div class="texture-options">
								<?php 
									$mesh_collection = json_decode(get_post_meta( $post_id, '_threejs_mesh_collection', true)); 
									$check = $partData->pattern[0];
									foreach($partData->pattern as $material){
								?>
								<div class="texture-option">
									<?php 
									if($mesh_collection->$material->image_id != ''){ 
										$img_url = wp_get_attachment_image_src($mesh_collection->$material->image_id, 'thumbnail');
									?>
									<img src="<?=$img_url[0];?>" data-parts="<?php echo $partname; ?>" alt="Waves pattern" data-material="<?php echo $mesh_collection->$material->name; ?>"/>
									<?php }else{ ?>
										<img src="../../wp-content/plugins/3DConfigurator-Woo-ThreeJs/public/images/material-1.jpg" alt="" class="swtch-image" data-parts="<?php echo sanitize_title($partname); ?>" data-material="<?php echo $mesh_collection->$material->name; ?>" >
									<?php } ?>
									<span class="wrap-text"><?php echo $mesh_collection->$material->name; ?></span>
								</div>
								<?php } ?>
							</div>
							<h5 class="text-lg mb-2">PATTERN COLOR</h5>
							<div class="pattern-options">	
								<div>
									<label class="custom-color">
										<input type="text" title="Custom Color" class="pattern-color-change" style="opacity: 0; position: absolute;">
									</label>
									<span>Custom</span>
								</div>
								<div class="pattern-iteams">
									<input type="color" value="#FFFFFF" title="White">
									<span>White</span>
								</div>
								<div class="pattern-iteams">
									<input type="color" value="#D60000" title="Red">
									<span>Red</span>
								</div>
								<div class="pattern-iteams">
									<input type="color" value="#0045AC" title="Blue">
									<span>Blue</span>
								</div>
								<div class="pattern-iteams">
									<input type="color" value="#FFD900" title="Yellow">
									<span>Yellow</span>
								</div>
								<div class="pattern-iteams">
									<input type="color" value="#FF8706" title="Orange">
									<span>Orange</span>
								</div>
								<div class="pattern-iteams">
									<input type="color" value="#7BC9FA" title="Sky Blue">
									<span>Sky Blue</span>
								</div>
								<div class="pattern-iteams">
									<input type="color" value="#16461C" title="Green">
									<span>Green</span>
								</div>
								<div class="pattern-iteams">
									<input type="color" value="#000000" title="Black">
									<span>Black</span>
								</div>
							</div>
							<?php if (isset($partData->gold_part) || !empty($partData->gold_part)) { ?>
							<div class="toggle-container">
								<input type="checkbox" name="toggle" id="toggle_border" class="toggle-checkbox toggle_border" data-golden_border="<?php echo ($partData->gold_part)? implode(",",$partData->gold_part)."," : '' ; ?>"/>
								<label for="toggle_border" class="toggle-label"></label>
								<span>Gold border</span>
							</div>
							<?php } ?>
							<?php if($partData->embossing_debossing_effect == 1) { ?>
							<div class="toggle-container">
								<input type="checkbox" data-emboss="Embossed" name="toggle" id="toggle_embossing" class="toggle-checkbox embossing-checkbox"/>
								<label for="toggle_embossing" class="toggle-label"></label>
								<span>Embossing by pattern</span>
							</div>
							<div class="toggle-container">
								<input type="checkbox" name="toggle" id="toggle_debossing" data-emboss="Debossed" class="toggle-checkbox embossing-checkbox"/>
								<label for="toggle_debossing" class="toggle-label"></label>
								<span>Debossing by pattern</span>
							</div>
							<?php } ?>
							<button type="button" style="display:none;"  class="apply-p-color button" data-selected="<?php echo ($partData->pattern_part)? implode(",",$partData->pattern_part)."" : '' ; ?>" data-parts="<?php echo $partname; ?>" data-material="">Done</button>
						</div>
					</div>
					<?php
				} }
				global $wpdb;
				function get_option_name($table, $id) {
					global $wpdb;
					return $wpdb->get_var($wpdb->prepare("SELECT name FROM {$wpdb->prefix}{$table} WHERE id = %d", $id));
				}

				if (
					!empty($parts->paper_types) || !empty($parts->quantity) || !empty($parts->grampage) || !empty($parts->finishing)) {
					?>
					<div class="content-show <?php echo ($active_ == 1) ? 'active-data' : '' ; $active_++; ?>" id="criteria_options" >
					<div class="starp-mainsection"  data-part-name ="criteria_options">
					<div class="inner-tab-datas">
					<div class="criteria-options-container">
						<?php if (!empty($parts->grampage)) { ?>
							<div class="criteria-options">
								<label>GRAMPAGE</label>
								<div class="relative">
									<select id="grampage_select">
										<option value="">Select</option>
										<?php foreach ($parts->grampage as $value) {
											$name = get_option_name('grampage', $value);
											echo "<option value='{$value}'>{$name}</option>";
										} ?>
									</select>
									<div class="icon">
										<i class="fas fa-chevron-down"></i>
									</div>
								</div>
							</div>
						<?php } ?>
						<?php if (!empty($parts->paper_types)) { ?>
							<div class="criteria-options">
								<label>PAPER TYPE</label>
								<div class="criteria-options-relative">
									<select id="paper_type_select">
										<option value="">Select</option>
										<?php foreach ($parts->paper_types as $value) {
											$name = get_option_name('paper_type', $value);
											echo "<option value='{$value}'>{$name}</option>";
										} ?>
									</select>
									<div class="icon">
										<i class="fas fa-chevron-down"></i>
									</div>
								</div>
							</div>
						<?php } ?>
						<?php if (!empty($parts->finishing)) { ?>
							<div class="criteria-options">
								<label>FINISHING</label>
								<div class="relative">
									<select id="finishing_select">
										<option value="">Select</option>
										<?php foreach ($parts->finishing as $value) {
											$name = get_option_name('finishing', $value);
											echo "<option value='{$value}'>{$name}</option>";
										} ?>
									</select>
									<div class="icon">
										<i class="fas fa-chevron-down"></i>
									</div>
								</div>
							</div>
						<?php } ?>
						<?php if (!empty($parts->quantity)) { ?>
							<div class="criteria-options">
								<label>QUANTITY</label>
								<div class="relative">
									<select id="quantity_select">
										<option value="">Select</option>
										<?php foreach ($parts->quantity as $value) {
											$name = get_option_name('quantity', $value);
											echo "<option value='{$value}'>{$name}</option>";
										} ?>
									</select>
									<div class="icon">
										<i class="fas fa-chevron-down"></i>
									</div>
								</div>
							</div>
						<?php } ?>
					</div>
					</div>
					</div>
					</div>
					<?php } ?>

        </div>
    </div>
</section>
<?php } ?>