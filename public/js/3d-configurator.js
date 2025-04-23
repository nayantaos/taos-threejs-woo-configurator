jQuery(document).ready(function () {
	var three_config_input_check = jQuery('#3d_config_input').val();
	if(three_config_input_check === "value_if_set"){
		var rawVal = jQuery('#edit_config_all_data').val();

		if (rawVal) {
				var configData = JSON.parse(rawVal);

				var configuredData = configData.configured_data;
				if(configuredData){
					var partsObjects = configuredData.filter(item => 
						Array.isArray(item.parts) && item.parts.length > 0
					);
					partsObjects.forEach(function(item, index) {
						var optionMainName = item.name.toLowerCase().replace(/\s+/g, '-');
						let contentShow = jQuery(`.content-show[id="${optionMainName}"]`);
						var partsStr = item.parts.join(',');
						contentShow.find('.starp-li').removeClass('active-list');
						var targetLi = contentShow.find('.starp-li a').filter(function () {
							return jQuery(this).data('parts') === partsStr;
						}).closest('.starp-li');
						if (targetLi.length) {
							setDefaultVal();
							changeImage();
							targetLi.addClass('active-list');
							let targetAnchor = targetLi.find('a');
						
							let partValue = targetAnchor.data("parts") || "";
							let dataValue = partValue.toString().split(',');
						
							let typelabel = targetAnchor.data("label");
							let typeprice = targetAnchor.data("price");
						
							contentShow.find('#part_type').val(typelabel);
							contentShow.find('#part_price').val(typeprice);
							contentShow.find('#part_all_value').val(JSON.stringify(dataValue));
						}
						
						if (targetLi.length) {
							targetLi.addClass('active-list');
						}
						if(item.materials){
							contentShow.find('.stich-content').removeClass('stitch-active').each(function () {
								var materialValue = jQuery(this).data('material');
								if (item.materials === materialValue) {
									jQuery(this).addClass('stitch-active');
									var dataValue = jQuery(this).data("material");
									var price = jQuery(this).data("price");
									contentShow.find('#part_materials').val(dataValue);
									contentShow.find('#material_price').val(price);
									setDefaultVal();
									changeImage();

								}
							});

						}
					});
				}
				

				var mainData = configData.custom_data?.main_data_for_custom;
				if (mainData) {
					Object.keys(mainData).forEach(function (key) {
						var item = mainData[key];
						let contentShow = jQuery(`.content-show[id="${key}"]`);
					
						if (item.color) {
							// color_section
							if (Array.isArray(item.color.color_section)) {
								item.color.color_section.forEach(function (sectionItem) {
									if(sectionItem.section === "color"){
										contentShow.find('.material-custom-change').val(sectionItem.value);
									}
									if(sectionItem.section === "match part"){
										contentShow.find('.toggle-checkbox.toggle_match_color').addClass('on').prop('checked', true);
									}
								});
							}
					
							// pattern_section
							if (Array.isArray(item.color.pattern_section)) {
								item.color.pattern_section.forEach(function (patternItem) {
									if (patternItem.section === "pattern_material") {
										contentShow.find('.texture-option img').removeClass('selected');
										contentShow.find(`.texture-option img[data-material="${patternItem.value}"]`).addClass('selected');
									}								
									if(patternItem.section === "pattern_color"){
										contentShow.find('.pattern-color-change').val(patternItem.value);

									}
								});
							}
					
							// criteria_options
						
						}
						if (key === "criteria_options") {
							item.criteria_options?.criteria_options?.forEach(({ section, value }) => {
								const selectBox = jQuery(`#${section}_select`);
								const matchedOption = selectBox.find('option').filter(function () {
									return jQuery(this).text().trim() === value;
								});
								if (matchedOption.length) {
									selectBox.val(matchedOption.val()).trigger('change');
								}
							});
						}
										
						
					});
				}
		
		} else {
			console.warn('No data found');
		}
	}
});


jQuery(document).ready(function () {
	setDefaultVal();
	function slideContent($container, direction) {
		var $items = $container.find('.stich-content, li');
		var itemWidth = $items.outerWidth(true);
		var totalItems = $items.length;
	
		var currentScroll = $container.scrollLeft();
		var closestIndex = Math.round(currentScroll / itemWidth); // Use round instead of floor
	
		var newIndex = direction === 'next' ? closestIndex + 1 : closestIndex - 1;
		if (newIndex >= totalItems) newIndex = 0;
		if (newIndex < 0) newIndex = totalItems - 1;
	
		var offset = newIndex * itemWidth;
		$container.stop().animate({ scrollLeft: offset }, 300);
	}
	jQuery('.prev-btn').click(function () {
	  var $stichData = jQuery(this).closest('.inner-tab-datas').find('.stich-data');
	  var $starpOl = jQuery(this).closest('.starp-mainsection').find('.starp-ol');
	  var $starpUL = jQuery(this).closest('.tab-mains').find('.d3-data-tabul');
	  slideContent($stichData, 'prev');
	  slideContent($starpOl, 'prev');
	  slideContent($starpUL, 'prev');
	});
	jQuery('.next-btn').click(function () {
	  var $stichData = jQuery(this).closest('.inner-tab-datas').find('.stich-data');
	  var $starpOl = jQuery(this).closest('.starp-mainsection').find('.starp-ol');
	  var $starpUL = jQuery(this).closest('.tab-mains').find('.d3-data-tabul');
	  slideContent($stichData, 'next');
	  slideContent($starpOl, 'next');
	  slideContent($starpUL, 'next');
	});  
	var activeTabs = jQuery(".d3-data-tabli").length;

    if (activeTabs === 1) {
        jQuery(".button-section-done").text("Finish Customisation");
        jQuery(".button-section-button:not(.button-section-done)").hide();
    } 
});
jQuery(document).ready(function () {
	// if(productData.poroduct_type == "threedium_module_threejs"){
			if (productData.poroduct_type == "threedium_module_threejs") {
				var three_config_input = jQuery('#3d_config_input').val();
				if(three_config_input === "value_if_set"){
					var materials = document.getElementById('selected_parts_json').value;
					var hiddenParts = document.getElementById('non_selected_parts_json').value;
					var customConfig = document.getElementById('custom_config').value;
					
					// Parse JSON
					materials = JSON.parse(materials);
					hiddenParts = JSON.parse(hiddenParts);
					customConfig = JSON.parse(customConfig);
				}
				

				const canvas = document.getElementById("renderCanvas");
			
				canvas.addEventListener("wheel", (event) => event.preventDefault(), { passive: false });
				canvas.addEventListener("touchmove", (event) => event.preventDefault(), { passive: false });
			
				document.addEventListener("click", function (event) {
					if (!canvas.contains(event.target)) {
						document.body.style.overflow = "auto";
					}
				});
			
				// Initialize Three.js Scene
				const scene = new THREE.Scene();
				scene.background = new THREE.Color(0xffffff);
			
				// Renderer
				const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
				renderer.physicallyCorrectLights = true;
				renderer.shadowMap.enabled = true;
				renderer.shadowMap.type = THREE.PCFSoftShadowMap;
			
				function setRendererSize() {
					renderer.setSize(canvas.clientWidth, canvas.clientHeight);
					renderer.setPixelRatio(window.devicePixelRatio);
				}
				setRendererSize();
			
				// Camera
				const camera = new THREE.PerspectiveCamera(45, canvas.clientWidth / canvas.clientHeight, 0.1, 100);
				camera.position.set(0, 2, 10);
			
				// Controls
				const controls = new THREE.OrbitControls(camera, renderer.domElement);
				controls.enableDamping = true;
				controls.dampingFactor = 0.05;
				controls.screenSpacePanning = false;
				controls.minDistance = 2;
				controls.maxDistance = 30;
			
				scene.children.forEach((child) => {
					if (child.isLight) {
						scene.remove(child);
					}
				});
			
				// ✅ **Boosted Lighting Setup**
				
				// 1️⃣ Ambient Light - Softens the shadows, increases base brightness
				const ambientLight = new THREE.AmbientLight(0xffffff, 2); // Increased intensity
				scene.add(ambientLight);
			
				// 2️⃣ Hemisphere Light - Mimics natural outdoor lighting
				const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 2);
				hemiLight.position.set(0, 20, 0);
				scene.add(hemiLight);
			
				// 3️⃣ Directional Light - Bright overhead light (like the Sun)
				const directionalLight = new THREE.DirectionalLight(0xffffff, 4);
				directionalLight.position.set(5, 10, 5);
				// directionalLight.castShadow = true;
				scene.add(directionalLight);
			
				// 4️⃣ Additional Directional Light for more clarity
				const directionalLight2 = new THREE.DirectionalLight(0xffffff, 3);
				directionalLight2.position.set(-5, 10, -5);
				scene.add(directionalLight2);
			
				// 5️⃣ Spotlight for dramatic highlights
				const spotLight = new THREE.SpotLight(0xffffff, 5);
				spotLight.position.set(0, 10, 10);
				spotLight.angle = Math.PI / 4;
				spotLight.penumbra = 0.5;
				spotLight.decay = 2;
				spotLight.distance = 50;
				scene.add(spotLight);
				const originalMaterialsOnLoad = new Map();
				const selectedPartsOnLoad = new Map();
				const namedMaterialsOnLoad = new Map();
				const originalMaterialsOnLoadm = new Map();
			
				// Load GLB Model
				const loader = new THREE.GLTFLoader();
				loader.load(
					window.location.origin + "/wp-content/uploads/woo-threejs-module/" + productData.threedium_module_data,
					function (gltf) {
						let model = gltf.scene;
						scene.add(model);

						window.scene = scene;
			
						model.traverse((child) => {
								child.castShadow = true;
								child.receiveShadow = true;
								if (child.material) {
									child.material.needsUpdate = true;
									child.material.metalness = 0.3;
									child.material.roughness = 0.2;
								}
							if (child.isMesh) {
								if(three_config_input === "value_if_set"){
									if (hiddenParts.includes(child.name)) {
										child.visible = false;
										return;
									}
								}
								
								
								const matArray = Array.isArray(child.material) ? child.material : [child.material];
								// if(three_config_input === "value_if_set"){
									matArray.forEach((mat) => {
										if (mat?.name) {
											if (mat?.name && !originalMaterialsOnLoadm.has(mat.name)) 
												originalMaterialsOnLoadm.set(mat.name, mat.clone());{
												// originalMaterialsOnLoad.set(mat.name, mat.clone());
											}
											if (mat?.name && !namedMaterialsOnLoad.has(mat.name)) {
												namedMaterialsOnLoad.set(mat.name, mat.clone());
											}
										}
									});
								// }
			
								
							}
						});
						if (three_config_input === "value_if_set") {
							if (Array.isArray(materials)) {
								materials.forEach((item) => {
									const partNames = item.parts;
									const matName = item.materials;

									if (!Array.isArray(partNames) || !matName) return;

									const originalMat = originalMaterialsOnLoadm.get(matName);
									if (!originalMat) return;

									partNames.forEach((partName) => {
										model.traverse((child) => {
											if (child.isMesh && child.name === partName) {
												
												// Check if this material is already applied on any other mesh
												let isAlreadyUsed = false;

												model.traverse((otherChild) => {
													if (
														otherChild.isMesh &&
														otherChild !== child &&
														otherChild.material === originalMat
													) {
														isAlreadyUsed = true;
													}
												});

												// ✅ If already used, apply clone — else original
												child.material = isAlreadyUsed ? originalMat.clone() : originalMat;
											}
										});
									});
								});
							}
						}

						if (three_config_input === "value_if_set") {
							if (Array.isArray(customConfig)) {
								customConfig.forEach((item) => {
									const partName = item.parts;
									const matName = item.material;
									const hexColor = item.color;
						
									if (!partName) {
										return;
									}
						
									model.traverse((child) => {
										if (!child.isMesh || child.name !== partName) return;
						
										let materialToApply = null;
						
										// Step 1: Try to get named material
										if (matName) {
											const namedMat = namedMaterialsOnLoad.get(matName);
											if (namedMat) {
												// Always clone named material to avoid sharing
												materialToApply = namedMat.clone();
												materialToApply._isCloned = true;
											} else {
											}
										}
						
										// Step 2: If no named material, clone current or use fallback
										if (!materialToApply) {
											if (child.material) {
												materialToApply = child.material.clone();
												materialToApply._isCloned = true;
											} else {
												// Ultimate fallback: default MeshStandardMaterial
												materialToApply = new THREE.MeshStandardMaterial({ color: 0xffffff });
											}
										}
						
										// Step 3: Assign final cloned material
										child.material = materialToApply;
						
										// Step 4: Apply hex color
										if (hexColor && materialToApply.color) {
											try {
												const colorChange = new THREE.Color(hexColor);
												colorChange.convertSRGBToLinear();
						
												if (child.material.map) child.material.map = null;
						
												materialToApply.color.copy(colorChange);
												materialToApply.needsUpdate = true;
						
											} catch (err) {
											}
										}
									});
								});
							}
						}
			
						scaleModelToFit(model);
						model.position.set(0, 0, 0);
					
						animate();
					},
					function (xhr) {
					},
					function (error) {
					}
				);
			
				function animate() {
					requestAnimationFrame(animate);
					controls.update();
					renderer.render(scene, camera);
				}
			
			function scaleModelToFit(model) {
					const boundingBox = new THREE.Box3().setFromObject(model);
					const modelSize = boundingBox.getSize(new THREE.Vector3());

					const maxSize = Math.max(modelSize.x, modelSize.y, modelSize.z);
					const scaleFactor = 10 / maxSize;  // Scale the model to fit within a max size of 10

					model.scale.set(scaleFactor, scaleFactor, scaleFactor);

					// Recalculate bounding box after scaling
					const scaledBoundingBox = new THREE.Box3().setFromObject(model);
					const scaledSize = scaledBoundingBox.getSize(new THREE.Vector3());

					// Adjust the camera position to make sure the entire model is visible
					const fov = camera.fov * (Math.PI / 180);  // Convert FOV to radians
					const cameraDistance = Math.max(scaledSize.x, scaledSize.y, scaledSize.z) / (2 * Math.tan(fov / 2));
					camera.position.set(0, 0, cameraDistance * 1.5);  // Add padding for better framing

					camera.lookAt(new THREE.Vector3(0, 0, 0));  // Ensure the camera looks at the center of the model
				}
				animate();
			}
			if (window.innerWidth <= 1024) {
				jQuery(".mobile-custom-section").on("click touchend", function(e) { 
					e.preventDefault();
			
					jQuery(".mian-mobile-div-3d").css("display", "block"); 
					jQuery(".mobile-custom-container").css("display", "none"); 
					var prts_name = jQuery(this).data("change_n_section");
					console.log(prts_name);
					jQuery(".d3-data-tabli").each(function() {
						jQuery(this).find("a[data-parts='" + prts_name + "']").trigger("click");
						scrollToCenter(jQuery('li.d3-data-tabli.active'));
					});
				});
				function scrollToCenter($li) {
					let $container = jQuery('.d3-data-tabul');
					let containerWidth = $container.width();
					let liOffset = $li.position().left + $container.scrollLeft();
					let liWidth = $li.outerWidth();
					let scrollPosition = liOffset - (containerWidth / 2) + (liWidth / 2);
			
					$container.animate({ scrollLeft: scrollPosition }, 300);
				}
			}
		
		
	jQuery('.variation-button').on('click', function (event) {
		event.preventDefault();

		jQuery(this).toggleClass('active');
		var part = jQuery(this).data('part');
		if (jQuery(this).hasClass('active')) {
			hide(part);
		} else {
			show(part);
		}
	});

	// jQuery(document).on('click', '.show-hide-parts', show_hide_part)
	
	jQuery(document).on('click', '.parts-items', function () {
		var eleId = jQuery(this).data('id');
		jQuery(".parts-items").removeClass('active');
		jQuery(this).addClass('active');
		jQuery(".material-data").hide();
		jQuery(".material-show-" + eleId).css('display', "block");
		jQuery(".material-show-" + eleId).removeClass('hide-material');
	});

	jQuery(document).on('click', '.show-material-data', function () {
		var partName = jQuery(this).data('part');
		var material = jQuery(this).data('material');

		
		jQuery(this).find('.select-material').prop("checked", true);
		jQuery(this).find('.material-display-name').prop("checked", true);
		jQuery(this).find('.material-price').prop("checked", true);
	});
	jQuery('.parts-wrapper .parts').click(function() {
		jQuery('.parts-wrapper .parts').removeClass('active');
		jQuery(this).addClass('active');
        var partName = jQuery(this).data('part_name');
        
		jQuery('.part_details').hide();
		jQuery('.part_detail-'+partName).show();
    });

	setTimeout(function () {
		loadingContent.style.display = "flex";
		jQuery(".content-show").each(function () {
			var activeListItem = jQuery(this).find('li.active-list');
			var anc = activeListItem.find('a');
			var all_parts = jQuery(this).find('#all_parts').val();
			var dataValue = jQuery(anc).data("parts");
			var selectedMaterial = jQuery(this).find(".stich-content.stitch-active").data("material");
		});
		setTimeout(function () {
			loadingContent.style.display = "none";
		}, "1500");
	}, "2000");
	setTimeout(function () {
		changeImage();
	}, "3000");
	logica_function()


	jQuery('a[data-configlink]').on('click', function (e) {
		//here code
    });
});
function setDefaultVal() {

	setTimeout(() => {
		var pricenew = parseFloat(jQuery(".pro-actual-price").val());
		jQuery(".starp-li.active-list > a").each(function () {
			if(jQuery(this).data("price") != ''){
				pricenew += jQuery(this).data("price");
			}
		});
		jQuery(".stich-content.stitch-active").each(function () {
			if(jQuery(this).data("price") != ''){
				pricenew += jQuery(this).data("price");
			}
		});
		
		jQuery(".custom").html(productData.priceSymbol + parseFloat(pricenew).toFixed(2));
		jQuery(".pro-updated-price").val(pricenew);
		jQuery("#loading-overlay").hide();
	}, "100");
}

function changeImage(hide,show,material) {
	
	let newData = { hide: hide,show: show,material: material};
	let jsonString = jQuery('#pro_image_cus').val();
	let dataArray = jsonString ? JSON.parse(jsonString) : [];
	dataArray.push(newData);
	let updatedJsonString = JSON.stringify(dataArray);
	jQuery('#pro_image_cus').val(updatedJsonString);
}
jQuery(document).on('click', ".d3-data-tabli > a", function() {
	var dataValue = jQuery(this).data("parts");
	var transition = jQuery(this).data("transition");
	jQuery('.d3data-tab-content .content-show').removeClass('active-data');
	jQuery('#'+dataValue).addClass('active-data');
	jQuery(".d3-data-tabli").removeClass('active');
	jQuery(this).parent('li').addClass('active');
	if ( jQuery(".d3-data-tabli:last").hasClass("active") ) {
		jQuery(".button-section-done")
			.removeClass("button-section-done")
			.addClass("button-section-finish")
			.text("Finish Customisation");
		jQuery(".button-section-button").not(".button-section-finish").hide();
	} else {
		jQuery(".button-section-finish")
			.removeClass("button-section-finish")
			.addClass("button-section-done")
			.text("DONE");
		jQuery(".button-section-button").not(".button-section-done").show();
	}
});
jQuery(document).on("click", ".change_tabs", function() {
	var find = jQuery("#" + jQuery(this).data("parts").toLowerCase().replace(/\s+/g, '-'));
	var tab = jQuery(this).data("sub-tab");
	if (find.length) {
		find.find(".tab-content").hide();
		find.find("#"+tab).show();
		find.find(".change_tabs").removeClass("active").addClass("inactive");
		jQuery(this).removeClass("inactive").addClass("active");
	}

});
document.querySelectorAll('.color-iteams').forEach(colorInput => {
	colorInput.addEventListener('click', function (event) {
		event.preventDefault(); 
		const selectedColor = this.querySelector('input').value; 
		var textSectiondFind = jQuery(this).closest('.content-show');
		textSectiondFind.find('.material-custom-change').val(selectedColor);
		var match_with_card = textSectiondFind.find('.toggle_match_color');
		if (match_with_card.length) {
			match_with_card.prop("checked", false);
		}
		document.querySelectorAll('.color-iteams').forEach(item => item.classList.remove('disabled-color'));
		this.classList.add('disabled-color');
		jQuery(this).closest(".content-show").find(".apply-c-color").trigger("click");
	});
});
document.querySelectorAll('.pattern-iteams').forEach(colorInput => {
	colorInput.addEventListener('click', function (event) {
		event.preventDefault(); 
		const selectedColor = this.querySelector('input').value; 
		var textSectiondFind = jQuery(this).closest('.content-show');
		textSectiondFind.find('.pattern-color-change').val(selectedColor);
		document.querySelectorAll('.pattern-iteams').forEach(item => item.classList.remove('disabled-color'));
		this.classList.add('disabled-color');
		jQuery(this).closest(".content-show").find(".apply-p-color").trigger("click");
	});
});
jQuery(".pattern-color-change").on("click", function () { 
	var find = jQuery("#" + jQuery('.d3-data-tabli.active a').data('parts').toLowerCase().replace(/\s+/g, '-'));
    var selectedColor = jQuery(this).val();
    if (find.find(".patterns-section .color-custom-section").length === 0) {
        var $colorSection = jQuery("<div>", { class: "color-custom-section" }).append(
            jQuery("<h5>").text("Custom Color Picker"),
            jQuery("<h5>").text("Choose your color").addClass("color-heading-your"),
            jQuery("<div>", { class: "color-picker-wrapper" }).append(
                jQuery("<canvas>", { class: "color-canvas" }),
                jQuery("<canvas>", { class: "hue-slider" })
            ),
            jQuery("<h5>").text("Custom Color Picker"),
            jQuery("<div>", { class: "color-preview-box" }).append(
                jQuery("<span>", { class: "color-preview" }).css("background-color", selectedColor),
                jQuery("<input>", { type: "text", class: "color-input pattern-color-on-change", value: '', readonly: true }),
				jQuery("<input>", { 
					type: "hidden", // Set input type to hidden
					class: "main-color-hidden pattern-main-change-color",
					value: "" // Default empty value
				})
            )
        );
		if (jQuery(window).width() <= 768) {
            jQuery("body").css("background-color", "#535353A3");
            jQuery("body").css("overflow", "hidden"); 

            var $overlay = jQuery("<div>", { class: "body-overlay" });
            jQuery("body").append($overlay); 

            var $closeButton = jQuery("<button>", { 
                class: "color-popup-close", 
                text: "×" 
            });

            $colorSection.append($closeButton); 

            $closeButton.on("click", function () {
                $colorSection.remove();  
                $overlay.fadeOut();  
                jQuery("body").css("background-color", "");
                jQuery("body").css("overflow", "auto"); 
            });
            find.find('.pattern-options').after($colorSection);
            $overlay.fadeIn();  
            $colorSection.addClass("show"); 
            new CustomColorPicker($colorSection);
        } else {
            jQuery("body").css("background-color", "#ffffff");
			find.find('.pattern-options').after($colorSection);
			new CustomColorPicker($colorSection);
		}
		
    }
});
jQuery(document).on("change", ".pattern-main-change-color", function () { 
	var selectedColor = jQuery(this).val(); 

	var find = jQuery("#" + jQuery('.d3-data-tabli.active a').data('parts')
		.toLowerCase()
		.replace(/\s+/g, '-'));

	find.find('.pattern-color-change').val(selectedColor);
	jQuery(this).closest(".content-show").find(".apply-p-color").trigger("click");
});
let selectedPartsColorPat = new Map();
let appliedMaterialColorPat = new Map();


// Event: Apply material and color to selected parts
jQuery('.apply-p-color').on('click', function () {
    jQuery('#loading-message').html("Applying your changes").css("display", "flex");

    var materialName = jQuery(this).attr('data-material');
    var parts = jQuery(this).data('selected');
    let targetDiv = jQuery(`.patterns-section[data-selected="${parts}"]`);
    var color = targetDiv.find('.pattern-color-change').val().trim();
    let checkedCheckbox = jQuery(".embossing-checkbox:checked");
    let embossValue = checkedCheckbox.data("emboss");


    if (!materialName) {
        alert("Please select a material before applying.");
        jQuery(".embossing-checkbox:checked").prop("checked", false);
        return;
    } else {
        if (checkedCheckbox.length) {
            materialName = materialName + embossValue;
        }
    }

    if (originalMaterialscard.size === 0) {
        collectOriginalMaterialsCard();
    }


    // ✅ Get the material object from original list
    let selectedMaterial = originalMaterialscard.get(materialName);
    if (!selectedMaterial) {
        return;
    }

    // ✅ Ensure parts is an array
    if (typeof parts === "string") {
        parts = [parts];
    }

    // ✅ Apply material to selected parts (clone for each mesh)
    parts.forEach(partName => {
        let mesh = window.scene.getObjectByName(partName.trim());
        if (mesh && mesh.isMesh) {
            if (!selectedPartscard.has(mesh)) {
                selectedPartscard.set(mesh, mesh.material);
            }

            let clonedMat = selectedMaterial.clone();
            clonedMat._isCloned = true;
            mesh.material = clonedMat;
            appliedMaterialscard.add(clonedMat.name);
        }
    });

    // ✅ Apply color change (to the cloned materials)
    if (color) {
        let colorChange = new THREE.Color(color).convertSRGBToLinear();

        parts.forEach((partName) => {
            let mesh = window.scene.getObjectByName(partName.trim());
            if (!mesh || !mesh.material) return;

            if (mesh.material.map) mesh.material.map = null;

            (Array.isArray(mesh.material) ? mesh.material : [mesh.material]).forEach(mat => {
                if (mat.color) {
                    mat.color.copy(colorChange);
                    mat.needsUpdate = true;
                }
            });
        });
    }

    // ✅ Reset UI
    setTimeout(function () {
        jQuery('.apply-p-color').prop('disabled', false);
        jQuery("#loading-message").css("display", "none");
    }, 2500);
});


// ✅ Track original, selected, and applied materials
let selectedPartscard = new Map();
let appliedMaterialscard = new Set();
let originalMaterialscard = new Map();

// ✅ Collect original materials
function collectOriginalMaterialsCard() {
    originalMaterialscard.clear();

    window.scene.traverse((child) => {
        if (child.isMesh) {
            const materials = Array.isArray(child.material) ? child.material : [child.material];

            materials.forEach((material) => {
                if (material && material.name && !originalMaterialscard.has(material.name)) {
                    originalMaterialscard.set(material.name, material);
                }
            });
        }
    });

}




let selectedPartsColor = new Map(), selectedMatchPartsColor = new Map();
let appliedMaterialColor = null, appliedMatchMaterialColor = null;

jQuery('.apply-c-color').on('click', function () {
    jQuery('#loading-message').html("Applying your changes");
    loadingContent.style.display = "flex";

    let partName = jQuery(this).data('selected');
    let color = jQuery(`#${jQuery(this).data("parts").toLowerCase().replace(/\s+/g, '-')}`)
        .find('.material-custom-change').val();

    let mesh = window.scene.getObjectByName(partName.trim());
    if (!mesh || !mesh.material) return console.warn("❌ Mesh or material missing:", partName);

    let colorChange = new THREE.Color(color);

    // Clone material if needed
    if (!mesh.material._isCloned) {
        mesh.material = mesh.material.clone();
        mesh.material._isCloned = true;
    }

    // Save original material
    if (!selectedPartsColor.has(mesh)) {
        selectedPartsColor.set(mesh, mesh.material);
    }

    if (mesh.material.map) mesh.material.map = null;

    (Array.isArray(mesh.material) ? mesh.material : [mesh.material]).forEach(mat => {
        if (mat.color) {
            mat.color.copy(colorChange);
            mat.needsUpdate = true;
        }
    });

    appliedMaterialColor = mesh.material.name;

    // Handle matched part color
    let checkbox = document.querySelector(`.toggle-checkbox[data-match_color_part="${partName}"]`);
    if (checkbox?.checked) {
        let matchPart = checkbox.getAttribute("data-parts");
        let matchMesh = window.scene.getObjectByName(matchPart.trim());
        if (!matchMesh || !matchMesh.material) return console.warn("Match mesh/material missing");

        // Clone material if not already
        if (!matchMesh.material._isCloned) {
            matchMesh.material = matchMesh.material.clone();
            matchMesh.material._isCloned = true;
        }

        if (!selectedMatchPartsColor.has(matchMesh)) {
            selectedMatchPartsColor.set(matchMesh, matchMesh.material);
        }

        if (matchMesh.material.map) matchMesh.material.map = null;

        (Array.isArray(matchMesh.material) ? matchMesh.material : [matchMesh.material]).forEach(mat => {
            if (mat.color) {
                mat.color.copy(colorChange);
                mat.needsUpdate = true;
            }
        });

        appliedMatchMaterialColor = matchMesh.material.name;
    }

    setTimeout(() => loadingContent.style.display = "none", 2500);
});



// let selectedMatchPartsColor = new Map(); // Stores original materials for color changes
// let appliedMatchMaterialColor = null;    // Currently applied material for color parts
jQuery(document).on("click", ".toggle_match_color", function (event) {
    jQuery('#loading-message').html("Applying your changes");
    loadingContent.style.display = "flex";

    jQuery(this).toggleClass("on");

    var matchColor = jQuery(this).is(":checked") ? 1 : 0;
    var custom_color = jQuery(this).closest('.color-section').find('.material-custom-change').val() || '#FFFFFF';

    var parts = jQuery(this).data("match_color_part");
    var change_part = jQuery(this).data("parts");

    let targetDiv = jQuery(`.color-section.tab-content[data-part-name="${parts}"]`);
    var inputs = targetDiv.find('.material-custom-change');

    if (inputs.length == 0) {
        alert("No color input fields found.");
        loadingContent.style.display = "none";
        return;
    }
    var color = inputs.val();
    if (matchColor != 1) {
        color = custom_color;
    }

    if (!color) {
        alert("Please provide a color before applying changes.");
        loadingContent.style.display = "none";
        jQuery(this).prop("checked", false);
        return;
    }
	let colorChange = new THREE.Color(color);
    colorChange.convertSRGBToLinear(); 
    let mesh = window.scene.getObjectByName(change_part.trim());

    if (mesh && mesh.material) {
		if (appliedMatchMaterialColor && appliedMatchMaterialColor !== mesh.material.name) {
			selectedMatchPartsColor.forEach((originalMat, storedMesh) => {
				storedMesh.material = originalMat;
			});
			selectedMatchPartsColor.clear();
		}
	
		// ✅ Store original material
		if (!selectedMatchPartsColor.has(mesh)) {
			selectedMatchPartsColor.set(mesh, mesh.material);
		}
	
		// ✅ Remove map if it exists, to show color
		if (mesh.material.map) {
			mesh.material.map = null;
		}
	
		// ✅ Handle both single and multi-material
		if (Array.isArray(mesh.material)) {
			mesh.material.forEach(mat => {
				if (mat.color) {
					mat.color.copy(colorChange);
					mat.needsUpdate = true;
				}
			});
		} else {
			if (mesh.material.color) {
				mesh.material.color.copy(colorChange);
				mesh.material.needsUpdate = true;
			} else {
			}
		}
	
		appliedMatchMaterialColor = mesh.material.name;
    } else {
    }

    setTimeout(function () {
        loadingContent.style.display = "none";
    }, 1000);
});

jQuery(document).on("click", ".toggle_border", function() {
	jQuery('#loading-message').html("Applying your changes");
	loadingContent.style.display = "flex";
	jQuery(this).toggleClass("on");
	var newColor = jQuery(this).hasClass("on") ? "#e8c37e" : "#ffffff";

	var parts = jQuery(this).data("golden_border")?.split(",").filter(Boolean);
	parts.forEach(part => {
        let mesh = window.scene.getMeshByName(part);
        if (mesh && mesh.material) {
            let existingMaterial = mesh.material;
            let colorChange = BABYLON.Color3.FromHexString(newColor);
            let newMaterial;
            if (existingMaterial instanceof BABYLON.StandardMaterial) {
                newMaterial = existingMaterial.clone(`${part}_custom_material`);
                newMaterial.diffuseColor = colorChange;
            } else if (existingMaterial instanceof BABYLON.PBRMaterial) {
                newMaterial = existingMaterial.clone(`${part}_custom_material`);
                newMaterial.albedoColor = colorChange;
            }
            if (newMaterial) {
                mesh.material = newMaterial;
            }
        } else {
        }
    });
	setTimeout(function () {
		loadingContent.style.display = "none";
	}, "1000");
});

jQuery(document).on('click', '.texture-option img', function() {
	var optionName = jQuery(this).data('parts');
	let contentShow = jQuery(`.content-show[id="${optionName}"]`);
    contentShow.find('img').removeClass('selected');
	jQuery(this).addClass('selected');
	var find = jQuery("#" + jQuery(this).data("parts").toLowerCase().replace(/\s+/g, '-'));
	var material = jQuery(this).data('material');
	find.find('.apply-p-color').removeAttr('data-material');
	find.find('.apply-p-color').attr('data-material', material); 
	jQuery(this).closest(".content-show").find(".apply-p-color").trigger("click");
});

jQuery(".material-custom-change").on("click", function () { 
	var selectedColor = jQuery(this).val();
	var find = jQuery("#" + jQuery('.d3-data-tabli.active a').data('parts').toLowerCase().replace(/\s+/g, '-'));
	if (find.find(".color-section .color-custom-section").length === 0) {
		var $colorSection = jQuery("<div>", { class: "color-custom-section" }).append(
			jQuery("<h5>").text("Custom Color Picker"),
			jQuery("<h5>").text("Choose your color").addClass("color-heading-your"),
			jQuery("<div>", { class: "color-picker-wrapper" }).append(
				jQuery("<canvas>", { class: "color-canvas" }),
				jQuery("<canvas>", { class: "hue-slider" })
			),
			jQuery("<h5>").text("Custom Color Picker"),
			jQuery("<div>", { class: "color-preview-box" }).append(
				jQuery("<span>", { class: "color-preview" }).css("background-color", selectedColor),
				jQuery("<input>", { type: "text", class: "color-input main-color-on-change", value: '', readonly: true }),
				jQuery("<input>", { 
					type: "hidden", // Set input type to hidden
					class: "main-color-hidden color-main-change-color",
					value: "" // Default empty value
				})
			)
		);
		if (jQuery(window).width() <= 768) {
            jQuery("body").css("background-color", "#535353A3");
            jQuery("body").css("overflow", "hidden"); 

            var $overlay = jQuery("<div>", { class: "body-overlay" });
            jQuery("body").append($overlay); 

            var $closeButton = jQuery("<button>", { 
                class: "color-popup-close", 
                text: "×" 
            });

            $colorSection.append($closeButton); 

            $closeButton.on("click", function () {
                $colorSection.remove();  
                $overlay.fadeOut();  
                jQuery("body").css("background-color", "");
                jQuery("body").css("overflow", "auto"); 
            });
            find.find('.color-options').after($colorSection);
            $overlay.fadeIn();  
            $colorSection.addClass("show"); 
            new CustomColorPicker($colorSection);
        } 
        else {
            jQuery("body").css("background-color", "#ffffff");
            find.find('.color-options').after($colorSection);
            new CustomColorPicker($colorSection);
        }
	}
});
jQuery(".text-color-change").on("click", function () { 
	var selectedColor = jQuery(this).val();
	var find = jQuery("#" + jQuery('.d3-data-tabli.active a').data('parts').toLowerCase().replace(/\s+/g, '-'));
	if (find.find(".text-section .color-custom-section").length === 0) {
		var $colorSection = jQuery("<div>", { class: "color-custom-section" }).append(
			jQuery("<h5>").text("Custom Color Picker"),
			jQuery("<h5>").text("Choose your color").addClass("color-heading-your"),
			jQuery("<div>", { class: "color-picker-wrapper" }).append(
				jQuery("<canvas>", { class: "color-canvas" }),
				jQuery("<canvas>", { class: "hue-slider" })
			),
			jQuery("<h5>").text("Custom Color Picker"),
			jQuery("<div>", { class: "color-preview-box" }).append(
				jQuery("<span>", { class: "color-preview" }).css("background-color", selectedColor),
				jQuery("<input>", { type: "text", class: "color-input text-color-on-change", value: '', readonly: true }),
				jQuery("<input>", { 
					type: "hidden", // Set input type to hidden
					class: "main-color-hidden text-main-change-color",
					value: "" // Default empty value
				})
			)
		);
		if (jQuery(window).width() <= 768) {
            jQuery("body").css("background-color", "#535353A3");
            jQuery("body").css("overflow", "hidden"); 

            var $overlay = jQuery("<div>", { class: "body-overlay" });
            jQuery("body").append($overlay); 

            var $closeButton = jQuery("<button>", { 
                class: "color-popup-close", 
                text: "×" 
            });

            $colorSection.append($closeButton); 

            $closeButton.on("click", function () {
                $colorSection.remove();  
                $overlay.fadeOut();  
                jQuery("body").css("background-color", "");
                jQuery("body").css("overflow", "auto"); 
            });
            find.find('.text-options').after($colorSection);
            $overlay.fadeIn();  
            $colorSection.addClass("show"); 
            new CustomColorPicker($colorSection);
        } else {
            jQuery("body").css("background-color", "#ffffff");
			find.find('.text-options').after($colorSection);
			new CustomColorPicker($colorSection);
		}
		
	}
});
jQuery(document).on("change", ".text-main-change-color", function () { 
	var selectedColor = jQuery(this).val(); 

	var find = jQuery("#" + jQuery('.d3-data-tabli.active a').data('parts')
		.toLowerCase()
		.replace(/\s+/g, '-'));

	find.find('.text-color-change').val(selectedColor);
	jQuery(this).closest(".content-show").find(".apply-text").trigger("click");
});
document.querySelectorAll('.text-color-iteams').forEach(colorInput => {
	colorInput.addEventListener('click', function (event) {
		event.preventDefault(); 
		const selectedColor = this.querySelector('input').value; 
		var textSectiondFind = jQuery(this).closest('.content-show');
		textSectiondFind.find('.text-color-change').val(selectedColor);
		document.querySelectorAll('.text-color-iteams').forEach(item => item.classList.remove('disabled-color'));
		this.classList.add('disabled-color');
		jQuery(this).closest(".content-show").find(".apply-text").trigger("click");
	});
});
jQuery(document).on("change", ".color-main-change-color", function () { 
	var selectedColor = jQuery(this).val(); 

	var find = jQuery("#" + jQuery('.d3-data-tabli.active a').data('parts')
		.toLowerCase()
		.replace(/\s+/g, '-'));

	find.find('.material-custom-change').val(selectedColor);
	var match_with_card = find.find('.toggle_match_color');
	if (match_with_card.length) {
		match_with_card.prop("checked", false);
	}	
	jQuery(this).closest(".content-show").find(".apply-c-color").trigger("click");
});
jQuery(document).on('click', '.active-data .starp-li > a', function () {
	jQuery('#loading-message').html("Applying your changes");
	loadingContent.style.display = "flex";

	let all_parts = jQuery('.active-data #all_parts').val().split(',');
	let id = jQuery('.active-data').attr('id');
	let dataValue = jQuery(this).data("parts").split(',');
	let typelabel = jQuery(this).data("label");
	let typeprice = jQuery(this).data("price");


	jQuery('.active-data #part_type').val(typelabel);
	jQuery('.active-data #part_price').val(typeprice);
	jQuery('.active-data #part_all_value').val(JSON.stringify(dataValue));



	let selectedMaterial = jQuery(".active-data .stich-content.stitch-active").data("material");

	jQuery(".active-data .starp-li").removeClass('active-list');
	jQuery(this).parent('li').addClass('active-list');
	let show_hide_change = jQuery(this).data("show-hide-parts-change");

	// 🧼 Hide all parts
	if(show_hide_change == 1){
		all_parts.forEach(partName => {
			let mesh = scene.getObjectByName(partName.trim());
			if (mesh) mesh.visible = false;
		});

		// 🎯 Show target parts with selected material
		dataValue.forEach(partName => {
			let mesh = scene.getObjectByName(partName.trim());
			if (mesh) {
				mesh.visible = true;

				// Optional: Apply selected material (if stored/referenced somewhere)
				if (selectedMaterial && typeof selectedMaterial === "object") {
					mesh.material = selectedMaterial;
				}
			}
		});
	}

	setDefaultVal();
	changeImage(all_parts.join(','), dataValue.join(','), selectedMaterial);

	setTimeout(() => {
		loadingContent.style.display = "none";
	}, 1500);

	// Additional logic
	logica_function();
});

let selectedParts = new Map(); // Stores original materials for selected parts
let appliedMaterial = null; // Track the currently applied material
let originalMaterials = new Map(); // Stores original material list

function collectOriginalMaterials() {
    originalMaterials.clear(); // Reset to avoid duplicates

    window.scene.traverse((child) => {
        if (child.isMesh) {
            const materials = Array.isArray(child.material) ? child.material : [child.material];

            materials.forEach((material) => {
                if (material && material.name && !originalMaterials.has(material.name)) {
                    originalMaterials.set(material.name, material);
                }
            });
        }
    });

}

function applyMaterialToPart(partsArray, materialName) {
    if (!window.scene) {
        return;
    }

    if (originalMaterials.size === 0) {
        collectOriginalMaterials();
    }

    let selectedMaterial = originalMaterials.get(materialName);
    if (!selectedMaterial) {
        return;
    }

    // ❌ Don't clear previously applied materials
    // selectedParts.clear(); ❌

    // ✅ Apply material to selected parts (keep previously applied parts)
    window.scene.traverse((child) => {
        if (child.isMesh && partsArray.includes(child.name)) {
            if (!selectedParts.has(child)) {
                selectedParts.set(child, child.material); // Store original
            }

            child.material = selectedMaterial; // Apply shared material
        }
    });

    appliedMaterial = selectedMaterial;

}


function logica_function(){
	jQuery('.logic').each(function(index){
		
		var logic = jQuery(this).val();
		var logic_cond = JSON.parse(logic);
		var logicArray = {};

		jQuery.each(logic_cond, function(key, value) {
			var logicArrayKey = [];
            jQuery.each(value, function(nestedKey, nestedValue) {
				if(nestedValue[1] == '=='){
					if(nestedValue[2] == jQuery('#'+nestedValue[0].toLowerCase().replace(/ /g, '-')).find('.active-list a').data('label')){
						logicArrayKey.push(1);
					}else{
						logicArrayKey.push(0);
					}
				}
				if(nestedValue[1] == '!='){
					if(nestedValue[2] == jQuery('#'+nestedValue[0].toLowerCase().replace(/ /g, '-')).find('.active-list a').data('label')){
						logicArrayKey.push(0);
					}else{
						logicArrayKey.push(1);
					}
				}
            });
			logicArray[key] = logicArrayKey;
        });
		if(Object.keys(logicArray).length > 0){
			function checkAllValuesAreOne(logicArray) {
				for (let key in logicArray) {
					if (!logicArray[key].every(value => value === 1)) {
						return false;
					}
				}
				return true;
			}	
			//setTimeout(function() {
				if (checkAllValuesAreOne(logicArray)) {
					jQuery('a[data-part="' + jQuery(this).data('part') + '"][data-label="' + jQuery(this).data('label') + '"]').show();
				} else {
					jQuery('a[data-part="' + jQuery(this).data('part') + '"][data-label="' + jQuery(this).data('label') + '"]').hide();
					//jQuery('a[data-part="' + jQuery(this).data('part') + '"][data-label="' + jQuery(this).data('label') + '"]').parent().next().next().find('a').click();
					console.log(jQuery(this).data('part') + ' => '+ jQuery(this).data('label') )
					console.log('Next Class: ',jQuery('a[data-part="' + jQuery(this).data('part') + '"][data-label="' + jQuery(this).data('label') + '"]').parent().next().next().attr('class'));
				}
			//}, 2000 * index);
		}
	});
}
	jQuery(document).on('click', '.stich-content', function(){
		jQuery('#loading-message').html("Applying your changes");
		loadingContent.style.display = "flex";
		jQuery(".active-data .stich-content").removeClass('stitch-active');
		jQuery(this).addClass('stitch-active');
		var dataValue = jQuery(this).data("material");
		var price = jQuery(this).data("price");
		var partValue = jQuery('.active-data .starp-li.active-list > a').data("parts");
		jQuery('.active-data #part_materials').val(dataValue);
		jQuery('.active-data #material_price').val(price);


		var all_parts = jQuery('.active-data #all_parts').val();
		if(productData.poroduct_type == "threedium_module_threejs"){
			if (partValue && dataValue) {
				applyMaterialToPart(partValue, dataValue);
			}
		}
		setDefaultVal();
		changeImage(all_parts,partValue,dataValue);
		setTimeout(function () {
			loadingContent.style.display = "none";
		}, "1500");
    });
	jQuery(document).on('click', '.button-section-done', function() {
		var mainTab = jQuery('.d3-data-tabul');
		var activeTab = mainTab.find('.d3-data-tabli.active');
		var nextTab = activeTab.nextAll('.d3-data-tabli').first();
		
		if (nextTab.length) {
			nextTab.find('a').trigger('click');
			setTimeout(() => {
				scrollToCenter(jQuery('.d3-data-tabul .d3-data-tabli.active'));
			}, 50);
		}
	
		function scrollToCenter($li) {
			let $container = jQuery('.d3-data-tabul');
			let containerOffset = $container.offset().left;
			let containerWidth = $container.width();
			let liOffset = $li.offset().left;
			let liWidth = $li.outerWidth();
			
			let scrollPosition = $container.scrollLeft() + (liOffset - containerOffset) - (containerWidth / 2) + (liWidth / 2);
	
			$container.stop().animate({ scrollLeft: scrollPosition }, 300);
		}
	});
	jQuery(document).on('click', '.button-section-finish', function() {
		jQuery('.d3-data-tab').hide();
		var grampage = jQuery('#grampage_select option:selected').text().trim() === "Select" 
		? "Not Selected" 
		: jQuery('#grampage_select option:selected').text().trim();
		var paper_type = jQuery('#paper_type_select option:selected').text().trim() === "Select" 
			? "Not Selected" 
			: jQuery('#paper_type_select option:selected').text().trim();
		var finishing = jQuery('#finishing_select option:selected').text().trim() === "Select" 
			? "Not Selected" 
			: jQuery('#finishing_select option:selected').text().trim();
		var quantity = jQuery('#quantity_select option:selected').text().trim() === "Select" 
			? "Not Selected" 
			: jQuery('#quantity_select option:selected').text().trim();
		var change_effect = jQuery(".emboss-text-center.embossed-text-selected").data("changemat") ?? 'NO EFFECTS';
		var finalModuleHTML = '' +
		'<div class="final-module-container">' +
			'<div class="final-module-header">' +
				'<h1>YOUR DESIGN</h1>' +
			'</div>' +
			'<div class="final-module-details">' +
				'<div class="final-module-detail">' +
					'<span>FORMAT</span>' +
					'<span>Landscape</span>' +
				'</div>' +
				'<div class="final-module-detail">' +
					'<span>TEXT EFFECT</span>' +
					'<span>'+change_effect+'</span>' +
				'</div>';
				if (jQuery("#grampage_select").length) {
					finalModuleHTML += 
						'<div class="final-module-detail">' +
							'<span>GRAMMAGE</span>' +
							'<span>'+grampage+'</span>' +
						'</div>';
				}
	
				if (jQuery("#paper_type_select").length) {
					finalModuleHTML += 
						'<div class="final-module-detail">' +
							'<span>PAPER TYPE</span>' +
							'<span>'+paper_type+'</span>' +
						'</div>';
				}
	
				if (jQuery("#finishing_select").length) {
					finalModuleHTML += 
						'<div class="final-module-detail">' +
							'<span>FINISHING</span>' +
							'<span>'+finishing+'</span>' +
						'</div>';
				}
	
				if (jQuery("#quantity_select").length) {
					finalModuleHTML += 
						'<div class="final-module-detail">' +
							'<span>QUANTITY</span>' +
							'<span>'+quantity+'</span>' +
						'</div>';
				}
				finalModuleHTML += 
						'</div>' +
					'</div>';
		jQuery('.d3-data-tab').after(finalModuleHTML);
		jQuery('.button-section-finish').css('display','none');
		jQuery('.single_add_to_cart_button').attr('style', 'display: block !important;');
	});
	jQuery(document).on("click", ".single_add_to_cart_button", function() {
		jQuery('.apply_data').trigger('click');
	});
	jQuery(document).on("click", ".apply_data", function() {
		let mainDataForCustom = [];
		let requests = [];
	
		jQuery(".d3-data-tabli").each(function () {	
			let optionName = jQuery(this).find("a").data("parts");
			let contentShow = jQuery(`.content-show[id="${optionName}"]`);
	
			let partNames = contentShow.find(".starp-mainsection").data("part-name");
			let colorText = contentShow.find(".text-section").find(".text-color-change").val() || null;
			let card_color_section_parts = contentShow.find(".color-section.tab-content").data("part-name") || null;
			let card_section_color = contentShow.find(".color-section.tab-content").find('.material-custom-change').val() || null;
			let toggle_border = contentShow.find(".color-section.tab-content").find('#toggle_border_color').is(':checked') ? 1 : 0;
			let card_patterns_section_parts = contentShow.find(".patterns-section.tab-content").data("selected") || null;
			let card_patterns_selecetd_matireal = contentShow.find(".texture-option img.selected").data("material") || null;
	
			let patterns_section_color = contentShow.find(".patterns-section.tab-content").find('.pattern-color-change').val() || null;
			let toggle_p_border = contentShow.find(".patterns-section.tab-content").find('#toggle_border').is(':checked') ? 1 : 0;
			let embossing_p_border = contentShow.find(".patterns-section.tab-content").find('.embossing-checkbox:checked').data("emboss");
			let toggle_match_parts = contentShow.find(".color-section.tab-content").find('.toggle_match_color:checked').data("parts");
			let toggle_match_color_parts = contentShow.find(".color-section.tab-content").find('.toggle_match_color:checked').data("match_color_part");
			let mat_text = contentShow.find(".emboss-text-center.embossed-text-selected").data("changemat");
			var grampage = jQuery('#grampage_select option:selected').text().trim() === "Select" 
				? "Not Selected" 
				: jQuery('#grampage_select option:selected').text().trim();
			var paper_type = jQuery('#paper_type_select option:selected').text().trim() === "Select" 
				? "Not Selected" 
				: jQuery('#paper_type_select option:selected').text().trim();
			var finishing = jQuery('#finishing_select option:selected').text().trim() === "Select" 
				? "Not Selected" 
				: jQuery('#finishing_select option:selected').text().trim();
			var quantity = jQuery('#quantity_select option:selected').text().trim() === "Select" 
				? "Not Selected" 
				: jQuery('#quantity_select option:selected').text().trim();
			if (embossing_p_border) {
				card_patterns_selecetd_matireal = card_patterns_selecetd_matireal+embossing_p_border;
			}
			if (typeof partNames === "undefined") return;
	
			let partsData = partNames.includes(",")
				? partNames.split(",").map((name) => name.trim())
				: [partNames];
	
			let request = new Promise((resolve) => {
				// Unlimited3D.getPartOverlays({ part: partNames }, function (error, result) {
					let overlays = [];
					let text_data = {
						parts: partsData, // Include partsData inside text_data
						overlays: [], // Overlays will be added inside text_data
					};
	
					// if (!error && result && result.length > 0) {
						// overlays = result.map((entryitem) => {
						// 	let entries = (entryitem.entries || []).reduce((acc, entry) => {
						// 		let textElement = contentShow.find(".text-area-fields")
						// 			.find(`.editable-area[data-label="${entry.name}"]`);
						// 		let textValue = textElement.length ? textElement.val().trim() : "";
	
						// 		acc[entry.name] = { text: textValue, name: entry.name };
	
						// 		return acc;
						// 	}, {});
						// 	var new_name = entryitem.name;
						// 	if (new_name.endsWith("_embossed") || new_name.endsWith("_debossed")) {
						// 		var baseName = new_name.includes('_') ? new_name.substring(0, new_name.lastIndexOf('_')) : new_name;
						// 	} else {
						// 		var baseName = new_name;
						// 	}
						// 	var material_name = (mat_text && mat_text !== 'no_effect') ? `${baseName}_${mat_text}` : baseName;
	
	
						// 	return { name: material_name, entries: entries , text_effect : mat_text};
						// });
	
						// if (contentShow.find(".text-section").length) {
						// 	text_data.text_color = colorText;
						// }
	
						// text_data.overlays = overlays;
					// }
					let color = {
						color_section: [],
						pattern_section: []
					};
					let text_effects = {
						text_effects:[],
					}
					if (mat_text) {
						text_effects.text_effects.push({
							section: "text_effect",
							value: mat_text
						});
					}
					let criteria_options = {
						criteria_options:[],
					}
					if (grampage) {
						criteria_options.criteria_options.push({
							section: "grampage",
							value: grampage
						});
					}
					if (quantity) {
						criteria_options.criteria_options.push({
							section: "quantity",
							value: quantity
						});
					}
					if (finishing) {
						criteria_options.criteria_options.push({
							section: "finishing",
							value: finishing
						});
					}
					if (paper_type) {
						criteria_options.criteria_options.push({
							section: "paper_type",
							value: paper_type
						});
					}
	
	
					if (card_patterns_section_parts) {
						color.pattern_section.push({
							section: "pattern_parts",
							value: card_patterns_section_parts
						});
					}
					if (embossing_p_border) {
						color.pattern_section.push({
							section: "emboss",
							value: embossing_p_border
						});
					}
					if (card_patterns_selecetd_matireal) {
						color.pattern_section.push({
							section: "pattern_material",
							value: card_patterns_selecetd_matireal
						});
					}
					if (patterns_section_color) {
						color.pattern_section.push({
							section: "pattern_color",
							value: patterns_section_color
						});
					}
					if (toggle_p_border) {
						color.pattern_section.push({
							section: "pattern_p_border",
							value: toggle_p_border
						});
					}
					if (card_color_section_parts) {
						color.color_section.push({
							section: "color_parts",
							value: card_color_section_parts
						});
					}
	
					if (card_section_color) {
						color.color_section.push({
							section: "color",
							value: card_section_color
						});
					}
					if (toggle_border) {
						color.color_section.push({
							section: "golden border",
							value: toggle_border
						});
					}
					if (toggle_match_color_parts) {
						color.color_section.push({
							section: "match color part",
							value: toggle_match_color_parts
						});
					}
					if (toggle_match_parts) {
						color.color_section.push({
							section: "match part",
							value: toggle_match_parts
						});
					}
	
					resolve({
						option_name: optionName,
						text_data: text_data,
						color: color,
						text_effects:text_effects,
						criteria_options:criteria_options
					});
				// });
			});
	
			requests.push(request);
		});
	
		Promise.all(requests).then((results) => {
			mainDataForCustom = results; 
			jQuery('input[name="main_data_for_custom"]').val(JSON.stringify(mainDataForCustom));
		});
		
	
	});
	class CustomColorPicker {
		constructor(container) {
			this.container = jQuery(container);
			this.colorCanvas = this.container.find(".color-canvas")[0];
			this.hueSlider = this.container.find(".hue-slider")[0];
			this.colorCtx = this.colorCanvas.getContext("2d");
			this.hueCtx = this.hueSlider.getContext("2d");
			this.colorPreview = this.container.find(".color-preview");
			this.colorInput = this.container.find(".color-input");
			this.colormainhidden = this.container.find(".main-color-hidden");
		
			this.hue = 0;
			this.colorX = 10;
			this.colorY = 10;
			this.hueY = 0;
			this.isDragging = false;
		
			this.init();
		}
		
		init() {
			this.drawHueSlider();
			this.drawColorCanvas();
			this.bindEvents();
		}
		
		drawColorCanvas() {
			// Draw color gradient
			const gradient = this.colorCtx.createLinearGradient(0, 0, this.colorCanvas.width, 0);
			gradient.addColorStop(0, "white");
			gradient.addColorStop(1, `hsl(${this.hue}, 100%, 50%)`);
			this.colorCtx.fillStyle = gradient;
			this.colorCtx.fillRect(0, 0, this.colorCanvas.width, this.colorCanvas.height);
		
			// Draw black gradient overlay
			const gradientBlack = this.colorCtx.createLinearGradient(0, 0, 0, this.colorCanvas.height);
			gradientBlack.addColorStop(0, "rgba(0, 0, 0, 0)");
			gradientBlack.addColorStop(1, "black");
			this.colorCtx.fillStyle = gradientBlack;
			this.colorCtx.fillRect(0, 0, this.colorCanvas.width, this.colorCanvas.height);
		
			// Now draw the indicator AFTER the canvas is drawn
			this.drawColorIndicator();
		}
		
		drawHueSlider() {
			const gradient = this.hueCtx.createLinearGradient(0, 0, 0, this.hueSlider.height);
			for (let i = 0; i <= 360; i += 10) {
				gradient.addColorStop(i / 360, `hsl(${i}, 100%, 50%)`);
			}
			this.hueCtx.fillStyle = gradient;
			this.hueCtx.fillRect(0, 0, this.hueSlider.width, this.hueSlider.height);
		}
		
		drawColorIndicator() {
			// Remove recursive call to `drawColorCanvas()`
			// Just draw the indicator on top of the existing canvas
			this.colorCtx.beginPath();
			this.colorCtx.arc(this.colorX, this.colorY, 7, 0, 2 * Math.PI);
			this.colorCtx.strokeStyle = "#000";
			this.colorCtx.lineWidth = 2;
			this.colorCtx.stroke();
			this.colorCtx.closePath();
		}
		
		bindEvents() {
			// Drag and Select Color
			this.colorCanvas.addEventListener("mousedown", (event) => {
				this.isDragging = true;
				this.updateColorSelection(event);
			});
		
			this.colorCanvas.addEventListener("mousemove", (event) => {
				if (this.isDragging) this.updateColorSelection(event);
			});
		
			this.colorCanvas.addEventListener("mouseup", () => {
				this.isDragging = false;
				this.colormainhidden.val(this.colorInput.val()).trigger("change");
			});
		
			this.colorCanvas.addEventListener("mouseleave", () => {
				this.isDragging = false;
			});
		
			// Click to Select Hue
			this.hueSlider.addEventListener("click", (event) => {
				this.hueY = event.offsetY;
				this.hue = (this.hueY / this.hueSlider.height) * 360;
				this.drawColorCanvas();
			});
			this.colorCanvas.addEventListener("touchstart", (event) => {
				this.isDragging = true;
				this.updateColorSelection(event.touches[0]);
			});
			
			this.colorCanvas.addEventListener("touchmove", (event) => {
				if (this.isDragging) {
					this.updateColorSelection(event.touches[0]);
					event.preventDefault(); // Prevent scrolling while dragging
				}
			});
			
			this.colorCanvas.addEventListener("touchend", () => {
				this.isDragging = false;
				this.colormainhidden.val(this.colorInput.val()).trigger("change");
			});
			
			this.colorCanvas.addEventListener("touchcancel", () => {
				this.isDragging = false;
			});
			
			// Click to Select Hue (for mobile)
			this.hueSlider.addEventListener("touchstart", (event) => {
				let touch = event.touches[0];
				this.hueY = touch.clientY - this.hueSlider.getBoundingClientRect().top;
				this.hue = (this.hueY / this.hueSlider.clientHeight) * 360;
				this.drawColorCanvas();
				event.preventDefault(); // Prevent scrolling
			});
		}
		
		updateColorSelection(event) {
			this.colorX = event.offsetX;
			this.colorY = event.offsetY;
			const color = this.getColorAtPosition(this.colorX, this.colorY);
			const hexColor = this.rgbToHex(color);
		
			this.colorPreview.css("background-color", hexColor);
			this.colorInput.val(hexColor);
			this.drawColorCanvas();
		}
		
		getColorAtPosition(x, y) {
			const imageData = this.colorCtx.getImageData(x, y, 1, 1).data;
			return `rgb(${imageData[0]}, ${imageData[1]}, ${imageData[2]})`;
		}
		
		rgbToHex(rgb) {
			const result = rgb.match(/\d+/g);
			if (!result) return "#000000";
			const [r, g, b] = result.map(Number);
			return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase()}`;
		}
		}