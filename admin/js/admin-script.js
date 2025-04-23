var allparts = [];
// var allMaterials = [];
var allpartsfromthreejs = [];
var allMaterialsfromthreejs = [];
jQuery(document).ready(function () {
    function initializeSelect2(selector) {
        jQuery(selector).select2({
            placeholder: "Select an option",
            allowClear: true,
            closeOnSelect: false
        });
        jQuery(selector).on('select2:open', function () {
            jQuery(this).find('option[value=""]').remove();
        });
        jQuery(selector).on('select2:select', function (e) {
            let selectedValue = e.params.data.id;
            let select = jQuery(this);

            if (selectedValue === "select_all") {
                let allValues = select.find("option:not([value='select_all'])").map(function () {
                    return this.value;
                }).get();
                select.val(allValues).trigger("change");
            }
        });
        jQuery(selector).each(function () {
            let select = jQuery(this);
            if (select.find('option[value="select_all"]').length === 0) {
                select.prepend('<option value="select_all">Select All</option>');
            }
        });
    }
    ["#finishing", "#quantity", "#grampage", "#paper_types"].forEach(id => jQuery(id).length && initializeSelect2(id));
    jQuery("#_regular_price_baby").on('blur', function() {
        var decimalPoint = '.';
        var regexInvalidChars = new RegExp("[^-0-9%" + decimalPoint + "]+", "gi");
        var regexExtraDecimal = new RegExp("[^\\" + decimalPoint + "]", "gi");
        var errorMessage = "i18n_mon_decimal_error";
        
        var value = jQuery("#_regular_price_baby").val();
        var sanitizedValue = value.replace(regexInvalidChars, "");
        if (sanitizedValue.replace(regexExtraDecimal, "").length > 1) {
            sanitizedValue = sanitizedValue.replace(regexExtraDecimal, "");
        }
        jQuery("#_regular_price_baby").val(sanitizedValue);
        if (value !== sanitizedValue) {
            jQuery(document.body).triggerHandler("wc_add_error_tip", [jQuery("#_regular_price_baby"), errorMessage]);
        } else {
            jQuery(document.body).triggerHandler("wc_remove_error_tip", [jQuery("#_regular_price_baby"), errorMessage]);
        }
    });
    

    jQuery("#_regular_price_baby").on("input", function () {
        const value = jQuery(this).val();
        jQuery("[name='_regular_price']").val(value);
    });
	var productType = jQuery('#product-type').val();
    if (productType === 'threedium_module_threejs') {
        jQuery('a[href="#threedium_module-3d"]').trigger('click');
    }
    
    jQuery('#product-type').change(function() {
        var newProductType = jQuery(this).val();
        if (newProductType === 'threedium_module_threejs') {
            setTimeout(() => {
                jQuery('a[href="#Threedium_product_data"]').trigger('click');   
                jQuery("#Threedium_product_data").insertBefore("#3d_configurator_product_data");
                jQuery("#threejs_mesh_collection").insertBefore("#mesh_collection");
            }, 100);   
        }
    });

	var result = {};
	jQuery('._part_section_new').each(function() {
        var productType = jQuery('#product-type').val();
        var optionName = productType == "threedium_module_threejs" ? "new-option-name" : "new-option-name";
            var optionName = jQuery(this).find('.'+optionName).val();
            var partsValues = [];
            jQuery(this).find('.container-sec-new').each(function() {
                var partsLabel = jQuery(this).find('.parts-new-label').val();
                partsValues.push(partsLabel);
            });
            result[optionName] = partsValues;
    });
    var result = result;
	jQuery('.all-select').data('selects',result);
	jQuery('.multiSelect select').select2({
        closeOnSelect : false,
        
    });
    

    jQuery("#post").attr("enctype", "multipart/form-data");
    if (jQuery("#3d_configurator_product_data").length) {
        jQuery("#3d_configurator_product_data").prepend('<div id="loading-overlay"><div class="loading-icon"></div></div>');
    } 
    // Counter to keep track of the number of blocks added
    var blockCount = 1;
    var materialCount = 1;
    // Attach a click event listener to the button
	
	jQuery(document).on('click', '.remove-condi-this', function(){
		var part_name = jQuery(this).data('part_name');
		var part_type = jQuery(this).data('part_type');
		var total_select = jQuery(this).parent().parent().parent().find('select').length;
		if(total_select == 3){
			jQuery(this).parent().parent().parent().find('.show-if').remove();
			jQuery(this).parent().parent().parent().find('.add-condi-or').remove();
			jQuery(this).parent().parent().parent().parent().find('.logical_status').trigger('click');
		}
		var remaing_select = jQuery(this).parent().parent().find('.condi-wrapper select').length;
		if(remaing_select == 3){
			jQuery(this).parent().parent().next().remove();
			jQuery(this).parent().parent().remove();
		}
		jQuery(this).parent().parent().parent().find('select').length
		jQuery(this).parent().parent().find('.condi-wrapper').each(function(index) {	
            jQuery(this).find('select').attr('name', 'parts['+part_name+'][logic]['+part_type+'][and_1][' + (index + 1) + '][]');
        });
		jQuery(this).parent().remove();
	});
    // jQuery(document).on('click', '#save-post', function() {
    //     $('#post_status').val('draft');
    //     $('#original_publish').val('draft');
    // });
    
	
	jQuery(document).on('click', '.add-condi-this', function(e){
        var clickedHtml = jQuery(this).prop('outerHTML');
        var and_number_match = clickedHtml.match(/data-and_number="(\d+)"/);
        var and_number = and_number_match ? parseInt(and_number_match[1], 10) : null;
		var att_name = jQuery(this).prev().attr('name');        
		var matches = att_name.match(/\[([^\]]+)\]/g);
		if (matches && matches.length >= 5) {
			var fifthValue = matches[4].slice(1, -1);
			var value = parseInt(fifthValue);
			if (!isNaN(value)) {
				var newValue = value + 1;				
			}
		}
        
		e.preventDefault();
        var currentWrapper = jQuery(this).closest('.condi-wrapper');
        var newWrapper = currentWrapper.clone();
        
		var part_name = jQuery(this).data('part_name');
		var part_type = jQuery(this).data('part_type');
        var productType = jQuery('#product-type').val();
        var part_names = productType == "configurator" ? "parts" : "baby_parts";
        newWrapper.find('select[name*="and_"]').each(function (index) {
            var newName = part_names+'[' + part_name + '][logic][' + part_type + '][and_' + and_number + '][' + (newValue) + '][]';
            jQuery(this).attr('name', newName);
            jQuery(this).val(jQuery(this).find('option:first').val());
        });
        currentWrapper.after(newWrapper);
	});
	
	jQuery(document).on('click', '.logical_status', function(){
	    var productType = jQuery('#product-type').val();
        var optionName = productType == "configurator" ? "option-name" : "new-option-name";
        var part_names = productType == "configurator" ? "parts" : "baby_parts";
		var hiddenField = jQuery(this).prev('.logical_status_main');
		if (jQuery(this).is(':checked')) {
			hiddenField.val('1');
			jQuery(this).parent().parent().parent().parent().find('.condi-sec-layout').show()
			if(jQuery(this).parent().parent().parent().parent().find('.condi-sec-layout').find('.show-if').length == 0){
				var curr_type_name = jQuery(this).parent().parent().parent().parent().find('.parts-new-label').val();
				var curr_part_name = jQuery(this).parent().parent().parent().parent().parent().parent().parent().find("."+optionName).val();
				var keys_obj = jQuery('.all-select').data('selects'); 
				var options_key = '';
				Object.keys(keys_obj).forEach(key => {
                    if(curr_part_name == key){
                        options_key += '<option value="'+key+'" disabled>'+key+' (this field)</option>';						
					}else{
                        options_key += '<option value="'+key+'" >'+key+'</option>';						
					}
				});
				var firstKey = Object.keys(keys_obj)[0];
				var all_values = keys_obj[firstKey];
				var option_val = '';
				all_values.forEach(value => {   
                    option_val += '<option value="' + value + '">' + value + '</option>';
				});
                console.log(option_val);
				
				jQuery(this).parent().parent().parent().parent().find('.condi-sec-layout').html('<span class="show-if">Show this field if</span><div class="condition-custom-layout" ><div class="condi-wrapper" ><select  name='+part_names+'['+curr_part_name+'][logic]['+curr_type_name+'][and_1][1][]" class="selected-logic-cond ">'+options_key+'</select> <select name="'+part_names+'['+curr_part_name+'][logic]['+curr_type_name+'][and_1][1][]"><option value="==" selected="">Is equal to</option><option value="!=">Is not equal to</option></select><select name="'+part_names+'['+curr_part_name+'][logic]['+curr_type_name+'][and_1][1][]" class="selected-logic-val">'+option_val+'</select><a href="javascript:void(0)" class="add-condi-this button" data-and_number= "1" data-part_name="'+curr_part_name+'" data-part_type="'+curr_type_name+'" > and </a><a href="javascript:void(0)" class="remove-condi-this button"> - </a></div></div><span class="condi-seprator">or</span><a href="javascript:void(0)" class="add-condi-or button">Add rule group</a>');
				jQuery('.selected-logic-cond').each(function() {
					jQuery(this).trigger('change');
				});
			}
		} else {
			hiddenField.val('0');
			jQuery(this).parent().parent().parent().parent().find('.condi-sec-layout').hide()
		}
    });
	
    function removeOption() {
        jQuery(".new_3d_loading").show();
        jQuery(this).parents('._part_section_new').remove();
        setTimeout(function () {
            jQuery(".new_3d_loading").hide();
        }, 5000);
    }
    function removeTextOption() {
        jQuery(".new_3d_loading").show();
        jQuery(this).parents('._text_section').remove();
        setTimeout(function () {
            jQuery(".new_3d_loading").hide();
        }, 5000);
    }
    function removeColorOption() {
        jQuery(".new_3d_loading").show();
        jQuery(this).parents('._color_section').remove();
        setTimeout(function () {
            jQuery(".new_3d_loading").hide();
        }, 5000);
    }

    function log(name, evt) {
        var $this = jQuery(this);
        var optionName = jQuery($this).parents("._part_section_new").find(".option-name").val();
        var selectedOption = evt.params.data.text;
        jQuery($this).parents(".inner-sec-custom").prev(".part-dispay-name").first().find(".display-name-sec").append('<span class= "inner-sec1 inner-sec-custom" ><label class="part-name" for="_material">Option label</label><input type="text" name="parts[' + optionName + '][' + selectedOption + ']" value="" class="parts-new-label"></span>')
    }
    function removeSecThis() {
        jQuery(".new_3d_loading").show();
        jQuery(this).parents('.container-sec-new').remove();
        setTimeout(function () {
            jQuery(".new_3d_loading").hide();
        }, 1000);
    }

    jQuery(document).on("click", '.remove-sec-this', removeSecThis);

    jQuery(document).on("click", '.remove-text-option', removeTextOption);

    jQuery(document).on("click", '.remove-color-option', removeColorOption);

    jQuery(document).on("click", '.remove-parts-this', removeOption);


    jQuery(document).on("click", ".remove-mateiral", removeMaterial);

    jQuery(document).on("click", ".remove-sec", removeSec);

    jQuery(document).on("click", ".toggle-show", toggleSec);

    jQuery(document).on("click", ".remove-img", removeImgSec);

    jQuery(document).on("change", ".part-input", checkPartsName);

    jQuery(document).on("click", ".add-more-and", addConditionalField);

    jQuery(document).on('click', '.add-more-or', addConditionalFieldOr);

    jQuery(document).on('click', '.add-more-delete', deleteConditionaField);

    jQuery(document).on('change', '.conidtiona-part', checkConditionaLogic);
    
    

    function checkConditionaLogic() {
        var $this = jQuery(this);
        var $val = jQuery(this).val();
        var allDropDwon = jQuery($this).parents('.conditional-section').find('.conidtiona-part');
        var count = 0;
        jQuery(allDropDwon).each(function () {
            if (jQuery(this).val() == $val) {
                count++;
            }
        })
        if (count > 1) {
            alert("This logical condition is already added.  Please select different one");
            jQuery($this).val("");
            return false;
        }
    }

    function deleteConditionaField() {
        jQuery(this).parents('.condi-sec').remove();
    }
    function addConditionalFieldOr() {
        var part_name = jQuery(this).parents('._part_name_field').find('.part-input').val();
        if (part_name == "") {
            alert("Please Select part name");
            return false;
        } else {
            if (jQuery(".part-input").length > 1) {
                var totalAvail = jQuery(this).parents('._part_name_field').find('.condi-sec').length;
                var totalPartLenght = jQuery(".part-input").length;
                createCondLogicSelect = '<option value="">Show this field if</option>';
                var totalpart = 0;
                jQuery.each(jQuery(".part-input"), function (index, value) {
                    var name = jQuery(this).data('selected');
                    if (name != part_name && name != "") {
                        totalpart++;
                        createCondLogicSelect += '<option value="' + name + '">' + name + '</option>';
                    }
                })
  
                if ((totalAvail + 1) > totalpart) {
                    alert("You already added all the possible logic");
                    return false;
                }
                if (jQuery(this).parents('.conditional-section').find('.condi-sec').length) {
                    var orLenth = parseInt(jQuery(this).parents('.conditional-section').find('.data-or').length) + 1;
                    if (orLenth == 1) {
                        orLenth = orLenth + 1;
                    }
                    var confitionalSec = '<div class="condi-sec data-or"><label class="part-name" for="">OR</label><select name= "material[' + part_name + '][parts][or][]" id="" class="conidtiona-part">' + createCondLogicSelect + '</select><select name="material[' + part_name + '][value][or][]" id=""  class="conidtiona-value"> <option value="">Select Value</option><option value="1">Show</option><option value="0">Hide</option></select><a href="javascript:void(0)" id="" class="add-more-and button" data-group="' + orLenth + '">And</a><a href="javascript:void(0)" id="" class="add-more-delete button">-</a></div>';
                } else {
                    var confitionalSec = '<div class="condi-sec"><label class="part-name" for=""></label><select name= "material[' + part_name + '][parts][and][]" id="" class="conidtiona-part">' + createCondLogicSelect + '</select><select name="material[' + part_name + '][value][and][]" id="" class="conidtiona-value"> <option value="">Select Value</option><option value="1">Show</option><option value="0">Hide</option></select><a href="javascript:void(0)" id="" class="add-more-and button" data-group="1">And</a><a href="javascript:void(0)" id="" class="add-more-delete button">-</a></div>';
                }
                jQuery(this).parents('.conditional-section').append(confitionalSec);
            } else {
                alert("Please add more than 2 parts to add conditional logic");
                return false;
            }
        }
    }

    jQuery(document).on('change', ".selected-logic-cond", function () {    
        if (jQuery(this).val() != "") {
            var productType = jQuery('#product-type').val();
            var part_names = productType == "configurator" ? "parts" : "baby_parts";
            var allOption = document.getElementsByName(part_names+"[" + jQuery(this).val() + "][label][]");;
            var optionByselectPart = "";
            jQuery(allOption).each(function () {
                optionByselectPart += '<option value="' + jQuery(this).val() + '" >' + jQuery(this).val() + '</option>';
            });
            jQuery(this).parent().find('.selected-logic-val').find("option").remove().end().append(optionByselectPart);
        }
    })

    function addConditionalField() {
        var part_name = jQuery(this).parents('._part_name_field').find('.part-input').val();
        if (jQuery(".part-input").length > 1) {
            createCondLogicSelect = '<option value="">Show this field if</option>';
            var totalpart = 0;
            jQuery.each(jQuery(".part-input"), function (index, value) {
                var name = jQuery(this).data('selected');
                if (name != part_name && name != "") {
                    totalpart++;
                    createCondLogicSelect += '<option value="' + name + '">' + name + '</option>';
                }
            })
            var totalAvail = jQuery(this).parents('._part_name_field').find('.condi-sec').length;
            var totalPartLenght = jQuery(".part-input").length;
            if ((totalAvail + 1) > totalpart) {
                alert("You already added all the possible logic");
                return false;
            }
            var group = jQuery(this).data('group');
            var confitionalSec = '<div class="condi-sec"><label class="part-name" for=""></label><select name= "material[' + part_name + '][parts][and][]" id="" class="conidtiona-part">' + createCondLogicSelect + '</select><select name="material[' + part_name + '][value][and][]" id="" class="conidtiona-value"> <option value="">Select Value</option><option value="1">Show</option><option value="0">Hide</option></select><a href="javascript:void(0)" id="" class="add-more-and button" data-group="' + group + '">And</a><a href="javascript:void(0)" id="" class="add-more-delete button">-</a></div>';
            jQuery(this).parent('.condi-sec').after(confitionalSec);
        } else {
            alert("Please add more than 2 parts to add conditional logic");
            return false;
        }
    }

    function checkPartsName() {
        var selectedPart = jQuery(this).val();
        var selectEle = jQuery(this);
        var count = 0;
        jQuery('.part-input').each(function () {
            if (jQuery(this).val() == selectedPart) {
                count++;
            }
        })
        if (count >= 2) {
            alert("This Part is alread added");
            jQuery(selectEle).val("");
            jQuery(selectEle).parents('._part_name_field').find('.overlay-text').attr("name", "")
        } else {
            var oldVal = jQuery(this).data('old');
            jQuery(".conidtiona-part option[value='" + oldVal + "']").remove();
            jQuery('.conidtiona-part').each(function () {
                jQuery(this).append('<option value="' + selectedPart + '">' + selectedPart + '</option>');
            });
            jQuery(this).attr('data-old', jQuery(this).val());
            jQuery(this).attr('data-selected', jQuery(this).val());
            var allmaterial = jQuery(selectEle).parents('._part_name_field').find('.main-section');
            jQuery(selectEle).parents('._part_name_field').find('.overlay-text').attr("name", "material[" + selectedPart + "][overlat_text]")
            if (jQuery(allmaterial).find('.part-material').length) {
                var allMaterEle = jQuery(allmaterial).find('.part-material');
                jQuery(allMaterEle).each(function () {
                    jQuery(this).attr('name', "material[" + selectedPart + "][matrial][]");
                });
            }
            if (jQuery(allmaterial).find('.material-display-name').length) {
                var allMaterEle = jQuery(allmaterial).find('.material-display-name');

                jQuery(allMaterEle).each(function () {
                    jQuery(this).attr('name', "material[" + selectedPart + "][display_name][]");
                });
            }
            if (jQuery(allmaterial).find('.icon-material').length) {
                var allMaterEle = jQuery(allmaterial).find('.icon-material');

                jQuery(allMaterEle).each(function () {
                    jQuery(this).attr('name', "material[" + selectedPart + "][icon][]");
                });
            }
            if (jQuery(allmaterial).find('.material-price').length) {
                var allMaterEle = jQuery(allmaterial).find('.material-price');
                jQuery(allMaterEle).each(function () {
                    jQuery(this).attr('name', "material[" + selectedPart + "][price][]");
                });
            }
            jQuery(selectEle).parents('._part_name_field').find('.parts-show').attr("name", 'material[' + selectedPart + '][showinfrontend]');
        }
    }

    function removeImgSec() {
        var data_name = jQuery(this).data('name');
        jQuery(this).parents('span').append('<input type="file" class="material-icon" data-name="' + data_name + '" accept="image/*">')
        jQuery(this).parent(".remove-img-sec").remove();
    }

    function toggleSec() {
        jQuery(".main-section").hide();
        jQuery(".main-section").removeClass('active');
        if (jQuery(this).parents('._part_name_field').find('.main-section').hasClass('active')) {
            jQuery(this).parents('._part_name_field').find('.main-section').hide();
            jQuery(this).parents('._part_name_field').find('.main-section').removeClass('active');
        } else {
            jQuery(this).parents('._part_name_field').find('.main-section').show();
            jQuery(this).parents('._part_name_field').find('.main-section').addClass('active');
        }
    }
    function removeSec() {
        var checkval = jQuery(this).parents('.inner-sec').find("select.part-input").attr('data-old');

      
        //return false;
        if (checkval != "") {
            jQuery(".conidtiona-part option[value=" + checkval + "]").remove();
        }
        jQuery(this).parents('._part_name_field').remove();
    }

    function removeMaterial() {
        jQuery(this).parents('.inner-section').remove();
    }

    var form = jQuery("form[name='post']");
    jQuery(form).find("#publish").click(function (e) {
        var post_type = jQuery("#post_type").val();
       if(post_type == 'product' && jQuery("#product-type").val() == 'threedium_module_threejs'){
            e.preventDefault();
            var status = true;
            jQuery(".baby-part-input").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please enter all parts name");
                    return false;
                }
            });
            jQuery(".baby-parts-new-label").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please enter Option Label");
                    return false;
                }
            });
            jQuery(".baby-parts-new-price").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please enter Option Price");
                    return false;
                }
            });
            jQuery("input[name='post_title']").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please enter Product Name");
                    return false;
                }
            });
            

            jQuery(".baby-material-price").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please enter all materials price");
                    return false;
                }
            });

            jQuery(".baby-conidtiona-value").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select all conditional logic value");
                    return false;
                }
            });

            jQuery(".baby-conidtiona-part").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select all conditional part");
                    return false;
                }
            });
            jQuery(".baby-part-selected").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select atleast one part for all parts fields");
                    return false;
                }
            });

            jQuery(".baby-material-sec-select").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select atleast one material for all materials fieldss");  
                    return false;
                }
            });
            jQuery(".baby-icon-material").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select at least one image for all image fields");
                    return false;
                }
            });

            jQuery(".baby-overlay-text").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select all overlay text option");
                    return false;
                }
            });
            jQuery(".text-option-name").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please enter Text Option Name");
                    return false;
                }
            }); 
            jQuery(".text-material-select").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select atleast one material for all materials fields");
                    return false;
                }
            });
            jQuery(".icon-option").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select at least one image for all image fields");
                    return false;
                }
            });
            jQuery(".color-option-name").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please enter Text Option Name");
                    return false;
                }
            });
            jQuery(".color-part-select").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select atleast one part for all part fields");
                    return false;
                }
            });
            jQuery(".color-pattern-part-select").each(function () {
                let selectedValues = jQuery(this).val(); // Get the selected values (array)
    
                if (!selectedValues || selectedValues.length === 0) {
                    status = false;
                    alert("Please select at least one part for all part fields");
                    return false;
                }
            });
            jQuery(".color-pattern-select").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select atleast one pattern for all pattern fields");
                    return false;
                }
            });
            jQuery(".color-option").each(function () {
                if (jQuery(this).val() == "") {
                    status = false;
                    alert("Please select at least one image for all image fields");
                    return false;
                }
            });
            if (jQuery('input.short[name="_regular_price"]:not(.wc_input_price)').val() == '') {
                status = false;
                alert("Please enter regular price");
                return false;
            }
            
            if (status === false) {
                jQuery("#publish").removeClass().addClass("button-primary");
                jQuery("#ajax-loading").hide();
            } else {
                jQuery(".baby-part-input").each(function () {
                    var selectedPart = jQuery(this).val();
                    var selectEle = jQuery(this);

                    var allmaterial = jQuery(selectEle).parents('._part_name_field').find('.main-section');

                    if (jQuery(allmaterial).find('.part-material').length) {
                        var allMaterEle = jQuery(allmaterial).find('.part-material');

                        jQuery(allMaterEle).each(function () {
                            jQuery(this).attr('name', "baby_material[" + selectedPart + "][matrial][]");
                        });
                    }

                    if (jQuery(allmaterial).find('.material-display-name').length) {
                        var allMaterEle = jQuery(allmaterial).find('.material-display-name');

                        jQuery(allMaterEle).each(function () {
                            jQuery(this).attr('name', "baby_material[" + selectedPart + "][display_name][]");
                        });
                    }

                    if (jQuery(allmaterial).find('.baby-icon-material').length) {
                        var allMaterEle = jQuery(allmaterial).find('.baby-icon-material');

                        jQuery(allMaterEle).each(function () {
                            jQuery(this).attr('name', "baby_material[" + selectedPart + "][icon][]");
                        });
                    }
                    if (jQuery(allmaterial).find('.baby-material-price').length) {
                        var allMaterEle = jQuery(allmaterial).find('.baby-material-price');

                        jQuery(allMaterEle).each(function () {
                            jQuery(this).attr('name', "baby_material[" + selectedPart + "][price][]");
                        });
                    }
                })
                // $('#post_status').val('Publish');
                jQuery('<input>')
                .attr('type', 'hidden')
                .attr('name', 'post_status_value')
                .attr('value', 'publish') 
                .appendTo(jQuery(form));
                var form = jQuery("form[name='post']");
                jQuery(form).submit();  
            }

        }

    });
    jQuery('input.short[name="_regular_price"]:not(.wc_input_price)').on('change', function () {
        const value = jQuery(this).val();
        if (isNaN(value) || parseFloat(value) <= 0) {
            jQuery(this).val('');
        }
    });
    jQuery(".parts-new-price").on("change", function () {
        const value = jQuery(this).val();
        if (isNaN(value) || parseFloat(value) <= 0) {
            jQuery(this).val('');
        }
    });
	
	jQuery('.condi-wrapper').each(function() {
		jQuery(this).find('.selected-logic-cond').val(jQuery(this).data('key')).trigger('change');
    
		var thirdSelect = jQuery(this).find('.selected-logic-val');
		var dataValueParts = thirdSelect.data('value_parts');

		// Change the value of the third select element based on some condition
		// For demonstration, let's just set it to the value of data-value_parts
		thirdSelect.val(dataValueParts);

		// Trigger the change event on the third select element
		thirdSelect.trigger('change');
	});

    
    jQuery('.select2-parts').select2({
        templateResult: function (data) {
            var $result = jQuery('<span></span>').text(data.text);
            if (data.id > 1) {
                $result.text('Show More');
            }
            return $result;
        },
        templateSelection: function (data) {
            if (data.id > 1) {
                jQuery(this).select2('open');
            }
            return data.text;
        }
    });
    jQuery('.select2-material').select2({
        closeOnSelect : false,
        
    });
    
    jQuery('#post').on('submit', function (event) {
        jQuery('#publish').attr('disabled', true);
        let isValid = true; // Flag to track validity
        let firstErrorField = null; // Track the first error field

        // Iterate over required fields (text, textarea, select2, multiple selects)
        jQuery(this).find('.required').each(function () {
            const field = jQuery(this);
            const tagName = field.prop('tagName').toLowerCase();
            let isEmpty = false;

            // Validation for Select2 fields (both single and multiple select)
            if (field.hasClass('select2-hidden-accessible')) {
                const selectedValues = field.val(); // For multiple select, it will return an array

                // Check if at least one option is selected
                isEmpty = !selectedValues || selectedValues.length === 0;
            } else if (tagName === 'select') {
                // Check native select field for multiple select
                isEmpty = !field.val() || field.val() === '';
            } else if (tagName === 'input' || tagName === 'textarea') {
                // Check input and textarea
                isEmpty = !field.val().trim();
            }

            if (isEmpty) {
                isValid = false;
                const fieldName = field.data('name') || 'This field'; // Get field name

                // Create and append error message near the field
                const errorMessage = `<span class="error-message" style="color: red; font-size: 12px;">${fieldName} is required.</span>`;
                if(fieldName == 'Solution 3D ID'){
                    field.parent().next().after(errorMessage);
                }else if(fieldName == 'Parts'){
                    field.next().after(errorMessage);
                }else{
                    field.parent().after(errorMessage);
                }

                // Highlight the empty field (optional)
                field.next('.select2').find('.select2-selection').css('border', '2px solid red'); // For Select2
                field.css('border', '2px solid red'); // For other fields

                // Scroll to the first error field
                if (!firstErrorField) {
                    firstErrorField = field;
                }
            } else {
                // Remove error highlight if valid
                field.next('.select2').find('.select2-selection').css('border', ''); // For Select2
                field.css('border', '');
                field.next('.error-message').remove(); // Remove error message if field is valid
            }
        });

        if (!isValid) {
            // Prevent form submission
            event.preventDefault();

            // Scroll to the first error field
            if (firstErrorField) {
                jQuery('html, body').animate({
                    scrollTop: firstErrorField.offset().top - 100 // Adjust scroll position
                }, 500);

                // Remove error messages after 5 seconds
                setTimeout(function () {
                    jQuery('.error-message').fadeOut(500, function () {
                        jQuery(this).remove();
                    });
                }, 5000);
                // Enable Update button after 2 second
                setTimeout(function () {
                    jQuery('#publish').attr('disabled', false);
                }, 2000);
            }
        }else{
            jQuery('#publish').attr('disabled', false);
        }
    });

    jQuery('#range_value').text(jQuery('#background_range').val());

    // Update the value dynamically when the slider changes
    jQuery('#background_range, #background_range_input').on('input', function() {
        const target = this.id === 'background_range' ? '#background_range_input' : '#background_range';
        jQuery(target).val(jQuery(this).val());
    });

    jQuery('#add-variant').on('click', function () {
        const index = jQuery('#dynamic-parts-materials .row').length - 1;
		
		let matttt = jQuery('.select2-material:first').html();
        matttt = matttt.replace(/ data-select2-id="[^"]*"/g, '');
        matttt = matttt.replace(/ selected(="")?/g, '');
        const newRow = `
            <div class="row">
                <input type="text" data-name="Name" class="required" name="custom_p_name[]" value="" style="width:20%">
                <a href="javascript:void(0)" class="button choose_mf_name" style="width:15%; text-align:center;">
                    Select Image
                </a>
                <input type="hidden" name="mf_name[]" value="">
                <input type="color" class="required" name="custom_m_color[]" value="" style="width:20%">
                <select data-name="Material" name="material_c[${index}]" class="select2-material required" style="width:40%">
                    ${matttt}
                </select>
                <button type="button" class="remove-row button" style="width:10%">
                    Remove
                </button>
            </div>
            `;
        jQuery('#dynamic-parts-materials').append(newRow);
        jQuery('.select2-material').last().select2();
    });

    jQuery('#dynamic-parts-materials').on('click', '.remove-row', function () {
        console.log('Length Row',jQuery('#dynamic-parts-materials .row').length);
        if(jQuery('#dynamic-parts-materials .row').length > 2){
            jQuery(this).parent('.row').remove();
        }else{
            Swal.fire({
                title: "",
                text: "You can not delete. Atleast one attribute is required.",
                icon: "warning"
            });
        }
    });
});


jQuery(document).on('click', '.add-condi-or', function (e) {
    e.preventDefault();

    var lastConditionGroup = jQuery(this).parent().find('.condi-wrapper:last');
    var lastSeparator = jQuery('span.condi-seprator').last();

    var clonedSeparator = lastSeparator.clone();
    var clonedConditionGroup = lastConditionGroup.clone();
	var newIndex = jQuery(this).parent().find('.condition-custom-layout').length + 1;

    clonedConditionGroup.find('select[name*="and_"]').each(function() {
        var nameAttr = jQuery(this).attr('name');
         nameAttr = nameAttr.replace(/\[and_\d+\]/g, '[and_' + newIndex + ']');
         nameAttr = nameAttr.replace(/\[\d+\]/g, '[' + 1 + ']');
        jQuery(this).attr('name', nameAttr);
    });
    clonedConditionGroup.find('a.add-condi-this').each(function () {
        var andNumber = jQuery(this).data('and_number');
        if (andNumber !== undefined) {
            jQuery(this).attr('data-and_number', newIndex);
        }
    });

    var newConditionGroupWrapper = jQuery('<div class="condition-custom-layout"></div>');
    newConditionGroupWrapper.append(clonedConditionGroup);

    jQuery(this).before(newConditionGroupWrapper);
    jQuery(this).before(clonedSeparator);
});


jQuery(document).on('click', '.upload-material-icon', function (event) {
    event.preventDefault(); // prevent default link click and page refresh
	
    const button = jQuery(this)
    const imageId = button.next().next().val();
    const customUploader = wp.media({
        title: 'Upload Material Icon', // modal window title
        library: {
            type: 'image'
        },
        button: {
            text: 'Use this image' // button label text
        },
        default_tab: "library",
        returned_image_size: 'thumbnail',
        multiple: false
    }).on('select', function () { // it also has "open" and "close" events
        const attachment = customUploader.state().get('selection').first().toJSON();
     
        var src = attachment.url
        if (attachment.sizes.thumbnail) {
            src = attachment.sizes.thumbnail.url;
        }
        button.removeClass('button').html('<img src="' + src + '">'); // add image instead of "Upload Image"
        button.next().show(); // show "Remove image" link
        button.next().next().val(attachment.id); // Populate the hidden field with image ID
    })

    // already selected images
    customUploader.on('open', function () {
        if (imageId) {
            const selection = customUploader.state().get('selection')
            attachment = wp.media.attachment(imageId);
            attachment.fetch();
            selection.add(attachment ? [attachment] : []);
        }
    })

    customUploader.open()

});
jQuery(document).on('click', '.upload-option-icon', function (event) {
    event.preventDefault(); 
	
    const button = jQuery(this)
    const imageId = button.next().next().val();
    const customUploader = wp.media({
        title: 'Upload Option Icon', 
        library: {
            type: 'image'
        },
        button: {
            text: 'Use this image'
        },
        default_tab: "library",
        returned_image_size: 'thumbnail',
        multiple: false
    }).on('select', function () { 
        const attachment = customUploader.state().get('selection').first().toJSON();
     
        var src = attachment.url
        if (attachment.sizes.thumbnail) {
            src = attachment.sizes.thumbnail.url;
        }
        button.removeClass('button').html('<img src="' + src + '">');
        button.next().show(); // show "Remove image" link
        button.next().next().val(attachment.id); // Populate the hidden field with image ID
    })

    // already selected images
    customUploader.on('open', function () {
        if (imageId) {
            const selection = customUploader.state().get('selection')
            attachment = wp.media.attachment(imageId);
            attachment.fetch();
            selection.add(attachment ? [attachment] : []);
        }
    })

    customUploader.open()

});
jQuery(document).on('click', '.upload-color-icon', function (event) {
    event.preventDefault(); // prevent default link click and page refresh
	
    const button = jQuery(this)
    const imageId = button.next().next().val();
    const customUploader = wp.media({
        title: 'Upload Color Icon', // modal window title
        library: {
            type: 'image'
        },
        button: {
            text: 'Use this image' // button label text
        },
        default_tab: "library",
        returned_image_size: 'thumbnail',
        multiple: false
    }).on('select', function () { // it also has "open" and "close" events
        const attachment = customUploader.state().get('selection').first().toJSON();
     
        var src = attachment.url
        if (attachment.sizes.thumbnail) {
            src = attachment.sizes.thumbnail.url;
        }
        button.removeClass('button').html('<img src="' + src + '">'); // add image instead of "Upload Image"
        button.next().show(); // show "Remove image" link
        button.next().next().val(attachment.id); // Populate the hidden field with image ID
    })

    // already selected images
    customUploader.on('open', function () {
        if (imageId) {
            const selection = customUploader.state().get('selection')
            attachment = wp.media.attachment(imageId);
            attachment.fetch();
            selection.add(attachment ? [attachment] : []);
        }
    })

    customUploader.open()

});

//Mesh collection image upload
jQuery(document).on('click', '.mesh_img', function (event) {
    const button = jQuery(this)
    const imageId = button.next().next().val();
    const customUploader = wp.media({
        title: 'Upload Material', // modal window title
        library: {
            type: 'image'
        },
        button: {
            text: 'Use this image' // button label text
        },
        default_tab: "library",
        returned_image_size: 'thumbnail',
        multiple: false
    }).on('select', function () { // it also has "open" and "close" events
        const attachment = customUploader.state().get('selection').first().toJSON();
   
        var src = attachment.url
        var id = attachment.id
        if (attachment.sizes.thumbnail) {
            src = attachment.sizes.thumbnail.url;
        }
        button.removeClass('button').html('<img src="' + src + '">'); // add image instead of "Upload Image"
        button.next().show(); // show "Remove image" link
        button.next().val(attachment.id); // Populate the hidden field with image ID
    })

    // already selected images
    customUploader.on('open', function () {
        if (imageId) {
            const selection = customUploader.state().get('selection')
            attachment = wp.media.attachment(imageId);
            attachment.fetch();
            selection.add(attachment ? [attachment] : []);
        }
    })

    customUploader.open()

});


// on remove button click
jQuery(document).on('click', '.remove-images', function (event) {
    event.preventDefault();
    const button = jQuery(this);
    button.next().val(''); // emptying the hidden field
    button.hide().prev().addClass('button').html('Upload image'); // replace the image with text
});

jQuery(document).on('change', '#product-type', function () {
    var pro_type = jQuery(this).val();
    if (pro_type == "threedium_module_threejs") {
        jQuery('#Threedium_product_data').show();
        if (window.location.href.includes("post-new.php?post_type=product")) {
            jQuery('.change-module').css('display','none');
            jQuery('.file-upload-button').css('display','block');
            jQuery('#loading_3d_configrator').hide();   
        }
    } else {
        jQuery('#Threedium_product_data').hide();   
    }
});


jQuery(document).on('click', '.add-conditional-logic', function () {

    if (jQuery(this).parents('._part_section_new').find('.condi-logic').length) {
        alert("You have already added logical condition");
        return false();
    } else {
        createSelect = '<option value="">Select Parts</option>';
        jQuery.each(allparts, function (index, value) {
            createSelect += '<option value="' + value.shortName + '">' + value.shortName + '</option>';
        })
        jQuery(this).after('<span class="inner-sec1 inner-sec-custom condi-logic"><label class= "part-name" for= "" > Display Condition</label><a href="javascript:void(0)" id="" class="add-conditional-new-or button">Add new logic</a><div class="condi-sec"><select name= "material[<?php echo $key; ?>][parts][]" id="" class="conidtiona-part"><option value="">Show this field if</option>' + createSelect + '</select><select name= "parts[<?php echo $key; ?>][value][]" id="" class="conidtiona-value"><option value="">Show this field if</option><option value="1">Show</option><option value="0">Hide</option></select><a href="javascript:void(0)" id="" class="add-more-and-new button">And</a><a href="javascript:void(0)" id="" class="add-more-delete-new button">-</a></div></span>');
    }
})

jQuery(document).on("click", ".show-hide-sec-new", showHidesec);

function showHidesec() {
    //jQuery(".parts-section-new").hide();
    if (jQuery(this).parents('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').hasClass('active')) {
        jQuery(this).parents('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').removeClass("active");
        jQuery(this).parents('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').hide();
    } else {
        jQuery(this).parents('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').addClass("active");
        jQuery(this).parents('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').show();
    }
}

function setMultiselect() {
    jQuery('.multiSelect').each(function (e) {
        var self = jQuery(this);
        var field = self.find('.multiSelect_field');
        var fieldOption = field.find('option');
        var placeholder = field.attr('data-placeholder');

        field.hide().after(`<div class="multiSelect_dropdown"></div>
                        <span class="multiSelect_placeholder">` + placeholder + `</span>
                        <ul class="multiSelect_list"></ul>
                        <span class="multiSelect_arrow"></span>`);

        fieldOption.each(function (e) {
            jQuery('.multiSelect_list').append(`<li class="multiSelect_option" data-value="` + jQuery(this).val() + `">
                                            <a class="multiSelect_text">`+ jQuery(this).text() + `</a>
                                          </li>`);
        });

        var dropdown = self.find('.multiSelect_dropdown');
        var list = self.find('.multiSelect_list');
        var option = self.find('.multiSelect_option');
        var optionText = self.find('.multiSelect_text');

        dropdown.attr('data-multiple', 'true');
        list.css('top', dropdown.height() + 5);

        option.click(function (e) {
            var self = jQuery(this);
            e.stopPropagation();
            self.addClass('-selected');
            field.find('option:contains(' + self.children().text() + ')').prop('selected', true);
            dropdown.append(function (e) {
                return jQuery('<span class="multiSelect_choice">' + self.children().text() + '<svg class="multiSelect_deselect -iconX"><use href="#iconX"></use></svg></span>').click(function (e) {
                    var self = jQuery(this);
                    e.stopPropagation();
                    self.remove();
                    list.find('.multiSelect_option:contains(' + self.text() + ')').removeClass('-selected');
                    list.css('top', dropdown.height() + 5).find('.multiSelect_noselections').remove();
                    field.find('option:contains(' + self.text() + ')').prop('selected', false);
                    if (dropdown.children(':visible').length === 0) {
                        dropdown.removeClass('-hasValue');
                    }
                });
            }).addClass('-hasValue');
            list.css('top', dropdown.height() + 5);
            if (!option.not('.-selected').length) {
                list.append('<h5 class="multiSelect_noselections">No Selections</h5>');
            }
        });

        dropdown.click(function (e) {
            e.stopPropagation();
            e.preventDefault();
            dropdown.toggleClass('-open');
            list.toggleClass('-open').scrollTop(0).css('top', dropdown.height() + 5);
        });

        jQuery(document).on('click touch', function (e) {
            if (dropdown.hasClass('-open')) {
                dropdown.toggleClass('-open');
                list.removeClass('-open');
            }
        });
    });


}
jQuery(document).ready(function($) {
    $('.addPartNewOption').hide();
    $('#addcolorOption').hide();

    var productType = $('#product-type').val();
    var overlay = $('#loading_3d_configrator');
    
    if (productType == "threedium_module_threejs") {
        overlay.hide();
        $('#_hidden_input_for_file_name').val($('#threedium_module_data').val());
        load3DModelData($('#threedium_module_data').val());
        jQuery('.change-module').css('display','block');
        jQuery('.file-upload-button').css('display','none');
      
    }
        jQuery(".file-upload-button").on("click", function () {
            jQuery("#fileUploadMain").click();
        });
    
        jQuery("#fileUploadMain").on("change", function () {
            const overlays = jQuery('#overlay');
            const svg = `
                <div class="loader-container">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="200" height="200" style="shape-rendering: auto; display: block; background: transparent;" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <g>
                            <path stroke="none" fill="#7141ce" d="M21 50A29 29 0 0 0 79 50A29 31.6 0 0 1 21 50">
                                <animateTransform values="0 50 51.3;360 50 51.3" keyTimes="0;1" repeatCount="indefinite" dur="1.0101010101010102s" type="rotate" attributeName="transform"></animateTransform>
                            </path>
                        </g>
                    </svg>
                </div>
            `;
            overlays.html(svg);
            overlays.show();
            let input = this;
        
            if (input.files.length === 0) {
                console.warn("⚠ No file selected!");
                overlays.hide().empty(); 
                return;
            }
        
            var file = input.files[0];
            var originalFileName = file.name;
            var fileExtension = originalFileName.split('.').pop().toLowerCase();
            var nameWithoutExt = originalFileName.substring(0, originalFileName.lastIndexOf('.'));
            var sanitizedFileName = nameWithoutExt.replace(/[\s()]+/g, '_').replace(/^_+|_+$/g, '');
            var fileName = sanitizedFileName + '.' + fileExtension;

        
            if (fileExtension !== "glb") {
                Swal.fire({
                    title: "Invalid File!",
                    text: "Only .glb files are allowed.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
                input.value = "";
                overlays.hide().empty(); 
                return;
            }
        
            var oldFileName = $('#_hidden_input_for_file_name').val();
        
            function uploadFile() {
                var formData = new FormData();
                formData.append("action", "get_upload_module_files"); 
                formData.append("file_upload", file); 
                formData.append("filename", fileName);
        
                jQuery.ajax({
                    url: adminScripData.adminURL,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            
                            $('#threedium_module_data').val(fileName);
                            $('#_hidden_input_for_file_name').val(fileName);
                            jQuery(".remove-parts-this").trigger("click");
                            jQuery(".remove-sec-this").trigger("click");
                            jQuery(".remove-text-option").trigger("click");
                            jQuery(".remove-color-option").trigger("click");
                            jQuery("#threejs_mesh_added_or_notcheck").val(0);
                            load3DModelData(fileName);
                            checkandsetvaluebybaby();
                            checkandsetvaluefortext();
                            checkandsetvalueforcolor();
                            jQuery('.change-module').css('display', 'block');
                            jQuery('.file-upload-button').css('display', 'none');
                            Swal.fire({
                                title: "Success!",
                                text: "File uploaded successfully!",
                                icon: "success",
                                confirmButtonText: "OK"
                            });
                            overlays.hide().empty(); 
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.data.message,
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                            overlays.hide().empty(); 
                        }
                        input.value = "";
                        overlays.hide().empty(); 
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error:", error);
                        input.value = "";
                        overlays.hide().empty(); 
                    }
                });
            }
        
            if (oldFileName === fileName) {
                overlays.hide().empty(); 
                Swal.fire({
                    title: "Same File Selected",
                    text: "You have selected the same file. You can replace this file with the existing one.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, Replace",
                    cancelButtonText: "No, Cancel"
                }).then((result) => {
                    if (result.isConfirmed) {
                        uploadFile(); 
                    } else {
                        input.value = "";
                        overlays.hide().empty(); 
                    }
                });
            } else {
                uploadFile(); 
            }
        });
        
        
        function load3DModelData(selectedFile) {
            if (!selectedFile) return;
        
            var fileUrl = window.location.origin + '/wp-content/uploads/woo-threejs-module/' + selectedFile;
        
            var overlay = $('.new_3d_loading');
            overlay.show();
        
            $.ajax({
                url: fileUrl,
                method: 'GET',
                success: function(response) {
                    const canvas = document.getElementById("renderCanvas");
        
                    // Set up the Three.js scene
                    const scene = new THREE.Scene();
                    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
                    const renderer = new THREE.WebGLRenderer({ canvas: canvas });
                    renderer.setSize(window.innerWidth, window.innerHeight);
                    document.body.appendChild(renderer.domElement);
        
                    // Set up basic camera position
                    camera.position.z = 5;
        
                    // Set up basic lighting
                    const light = new THREE.HemisphereLight(0xffffff, 0x444444, 1);
                    scene.add(light);
        
                    // Set up the GLTFLoader
                    const loader = new THREE.GLTFLoader();
                    loader.load(
                        fileUrl,
                        function(gltf) {
                            // Check if gltf.scene exists
                            if (!gltf.scene) {
                                console.error('Error: No scene found in glTF file.');
                                overlay.hide();
                                return;
                            }
        
                            // Add the loaded model to the scene
                            const model = gltf.scene;
                            scene.add(model);
        
                            // Animation loop to render the scene
                            function animate() {
                                requestAnimationFrame(animate);
                                renderer.render(scene, camera);
                            }
                            animate();
        
                            // Get node names (meshes) and material names
                            const nodeNames = [];
                            const materialNames = [];
                            model.traverse(function(child) {
                                if (child.isMesh) {
                                    nodeNames.push(child.name);
        
                                    // Check if the child has multiple materials or a single material
                                    const materials = Array.isArray(child.material) ? child.material : [child.material];
                                    
                                    materials.forEach(function(material) {
                                        if (!materialNames.includes(material.name)) {
                                            materialNames.push(material.name);
                                        }
                                    });
                                }
                            });
        
                            // Clear previous data if any
                            if (allpartsfromthreejs.length > 0) {
                                allpartsfromthreejs.length = 0;
                            }
        
                            if (allMaterialsfromthreejs.length > 0) {
                                allMaterialsfromthreejs.length = 0;
                            }
        
                            // Collect node data
                            nodeNames.forEach(function(name) {
                                var data = { "name": name, "shortName": name };
                                allpartsfromthreejs.push(data);
                            });
        
                            // Collect material data
                            materialNames.forEach(function(name) {
                                var data = { "name": name, "shortName": name };
                                allMaterialsfromthreejs.push(data);
                            });
        
                            if ($('#threejs_mesh_added_or_notcheck').val() != 1) {
                                jQuery.ajax({
                                    type: "post",
                                    url: adminScripData.adminURL,
                                    data: {
                                        action: "get_materials_threejs",
                                        matrial: materialNames,
                                        post_id: adminScripData.post_id
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            var meshCollection = JSON.parse(response.data.mesh_collection);
                                            var meshTableHtml = '<h2 style="font-weight:bold;">Mesh Collection</h2><div class="mesh-table" id="mesh-table"><table><thead><tr><th>Mesh name</th><th>Custom mesh name</th><th>Mesh Price</th><th>Image for Mesh</th></tr></thead><tbody>';
        
                                            for (var key in meshCollection) {
                                                var mesh = meshCollection[key];
                                                var imgUrl = (mesh.image_id) ? wp_get_attachment_image_src(mesh.image_id, 'thumbnail')[0] : '';
        
                                                meshTableHtml += '<tr class="mesh mesh_' + key + '">' +
                                                    '<td>' + key + '</td>' +
                                                    '<td><input type="text" name="baby_mesh[' + key + '][name]" value="' + mesh.name + '" placeholder="Enter mesh name"></td>' +
                                                    '<td><input type="number" name="baby_mesh[' + key + '][price]" value="' + mesh.price + '" placeholder="Enter mesh price"></td>' +
                                                    '<td><a href="javascript:void(0);" class="' + (mesh.image_id ? 'button' : '') + ' button mesh_img baby_mesh_image_' + key + '" data-image="' + key + '">' +
                                                    (imgUrl ? '<img src="' + imgUrl + '">' : 'Choose Image') +
                                                    '</a><input type="hidden" name="baby_mesh[' + key + '][image_id]" id="threejs_mesh_image_' + key + '" value="' + mesh.image_id + '" placeholder="Enter mesh price"></td>' +
                                                    '</tr>';
                                            }
        
                                            meshTableHtml += '</tbody></table></div>';
                                            jQuery('#threejs_mesh_added_or_notcheck').val(1);
                                            jQuery('#threejs_mesh_collection').html(meshTableHtml);
                                        } else {
                                            jQuery('#threejs_mesh_added_or_notcheck').val(0);
                                            jQuery('#threejs_mesh_collection').html('<h2 style="font-weight:bold;">' + response.data.message + '</h2>');
                                        }
                                        console.log("AJAX Success:", response);
                                    },
                                    error: function(xhr, status, error) {
                                        console.error("AJAX Error:", error);
                                    },
                                    complete: function() {
                                        overlay.hide();
                                    }
                                });
                            }
        
                            // Additional logic for product creation page
                            if (window.location.href.includes("post-new.php?post_type=product")) {
                                overlay.hide();
                                $('.addPartNewOption').show();
                                $('#addcolorOption').show();

                            } else {
                                overlay.hide();
                                $('.addPartNewOption').show();
                                $('#addcolorOption').show();
                                checkandsetvaluebybaby();
                                checkandsetvaluefortext();
                                checkandsetvalueforcolor();
                            }
                        },
                        undefined,
                        function(error) {
                            console.error("Error loading model:", error);
                            overlay.hide();
                        }
                    );
                },
                error: function() {
                    overlay.hide();
                }
            });
        }
        
        
        $('.change-module').on('click', function() {
            Swal.fire({
                title: "Are you sure?",
                text: "Changing the module will reset all current configurations and you'll have to configurations them.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, change module",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    overlay.show();
                    jQuery('.change-module').css('display','none');
                    jQuery('.file-upload-button').css('display','block');
                    overlay.hide();
                }
            });
        });
        
jQuery(".addPartNewOption").on("click", addNewOptionBaby);
jQuery(".addtextOption").on("click", addtextOption);
jQuery("#addcolorOption").on("click", addcolorOption);
function addtextOption() {
    jQuery(".new_3d_loading").show();
    var threedium_module_data = jQuery("#threedium_module_data").val();

    if (threedium_module_data == "") {
        jQuery(".new_3d_loading").hide();
        alert("Please select a Module Name");
        return false;
    }
    if (allpartsfromthreejs.length) { 
        jQuery(".main-section").removeClass('active');
        jQuery(".main-section").hide();
        jQuery(".configurator-start-text").append(`
            <div class="form-field _text_section" id="text_option_$textIndex">
                <span class="inner-sec1 inner-sec-custom1">
                    <div class="top-panel part-top-panel">
                        <label class="label-name text_label_name" for="text_option_name_$textIndex">Text Option Name</label>
                        <input type="text" name="text_option_name[]" value="" class="text-option-name">
                        <a href="javascript:void(0)" class="remove-text-option button" data-id="text_option_$textIndex">Remove Option -</a>
                    </div>
    
                    <div class="top-panel part-top-panel">
                        <label class="label-name text_label_name" for="text_material_$textIndex">Part</label>
                        <div class="multiSelect text_multi_select">
                            <select name="text_parts[$textIndex][material][]" class="short text-material-select js-example-basic-multiple" data-selected=""></select>
                        </div>
                    </div>
    
                    <div class="top-panel part-top-panel">
                        <label class="label-name text_label_name" for="text_color_$textIndex">Default Text Color</label>
                        <input type="color" name="text_parts[$textIndex][text_color]" class="text-color">
                    </div>
                     <div class="top-panel part-top-panel">
                        <label class="label-name text_label_name" for="text_option_icon_$textIndex">Option Icon</label>
                        <div class="multiSelect">
                            <a href="javascript:void(0)" class="button upload-option-icon" id="option_icon_$textIndex">Upload Icon</a>
                            <a href="javascript:void(0)" class="remove-images button" style="display:none">Remove Icon</a>
                            <input type="hidden" class="icon-option" name="text_parts[$textIndex][option_icon]" value="">
                        </div>
                    </div>
                </span>
            </div>
        `);
        
        checkandsetvaluefortext();
    } else {
        jQuery(".main-section").removeClass('active');
        jQuery(".main-section").hide();
        alert("Part not found");
        $('.addPartNewOption').hide();
        $('#threedium_module_data').prop('disabled', false);
    }
    setTimeout(function () {
        jQuery(".new_3d_loading").hide();
    }, 5000);
}
function addcolorOption() {
    jQuery(".new_3d_loading").show();
    var threedium_module_data = jQuery("#threedium_module_data").val();

    if (threedium_module_data == "") {
        jQuery(".new_3d_loading").hide();
        alert("Please select a Module Name");
        return false;
    }
    if (allpartsfromthreejs.length) { 
        jQuery(".main-section").removeClass('active');
        jQuery(".main-section").hide();
        jQuery(".configurator-start-color").append(`
            <div class="form-field _color_section" id="color_option_$colorIndex">
                <span class="inner-sec1 inner-sec-custom1">
                    <div class="top-panel part-top-panel">
                        <label class="label-name color_label_name" for="color_option_name_$colorIndex">Color Option Name</label>
                        <input type="text" name="color_option_name[]" value="" class="color-option-name">
                        <a href="javascript:void(0)" class="remove-color-option button" data-id="color_option_$colorIndex">Remove Option -</a>
                    </div>

                    <div class="top-panel part-top-panel">
                        <label class="label-name color_label_name" for="color_part_$colorIndex">Color Part</label>
                        <div class="multiSelect color_multi_select">
                            <select name="color_parts[$colorIndex][part][]" class="short color-part-select js-example-basic-multiple" data-selected=""></select>
                        </div>
                    </div>
                    <div class="top-panel part-top-panel">
                        <label class="label-name color_label_name" for="color_part_$colorIndex">Color Pattern Part</label>
                        <div class="multiSelect color_multi_select">
                            <select name="color_parts[$colorIndex][pattern_part][]" class="short color-pattern-part-select js-example-basic-multiple" data-selected=""></select>
                        </div>
                    </div>
                    <div class="top-panel part-top-panel">
                        <label class="label-name color_label_name" for="color_gold_part_$colorIndex">Gold border Part</label>
                        <div class="multiSelect color_multi_select">
                            <select name="color_parts[$colorIndex][gold_part][]" multiple="multiple" class="short color-gold-part-select js-example-basic-multiple" data-selected=""></select>
                        </div>
                    </div>
                    <div class="top-panel part-top-panel">
                        <label class="label-name color_label_name" for="color_pattern_$colorIndex">Color Pattern Material</label>
                        <div class="multiSelect color_pattern_multi_select">
                            <select name="color_parts[$colorIndex][pattern][]" class="short color-pattern-select js-example-basic-multiple" multiple="multiple" data-selected=""></select>
                        </div>
                    </div>
                    <div class="top-panel part-top-panel">
                        <label class="label-name text_label_name" for="color_match_$textIndex">Match with card</label>
                        <label class="switch"><input type="hidden" class="match_with_card_main" name="color_parts[][match_with_card][]" value="0"><input type="checkbox" class="match_with_card"><span class="slider round"></span></label>
                    </div>
                    <div class="top-panel part-top-panel">
                        <label class="label-name color_label_name" for="color_option_icon_$colorIndex">Color Icon</label>
                        <div class="multiSelect">
                            <a href="javascript:void(0)" class="button upload-color-icon" id="color_icon_$colorIndex">Upload Icon</a>
                            <a href="javascript:void(0)" class="remove-images button" style="display:none">Remove Icon</a>
                            <input type="hidden" class="color-option" name="color_parts[$colorIndex][option_icon]" value="">
                        </div>
                    </div>
                    <div class="top-panel part-top-panel">
                        <label class="label-name text_label_name" for="embossing_debossing_<?= $textIndex; ?>">Embossing and Debossing Effect</label>
                        <label class="switch">
                            <input type="hidden" name="color_parts[<?= $textIndex; ?>][embossing_debossing_effect]" value="0">
                            <input type="checkbox" 
                                class="embossing-debossing-checkbox" 
                                id="embossing_debossing_<?= $textIndex; ?>" 
                                name="color_parts[<?= $textIndex; ?>][embossing_debossing_effect]" 
                                value="">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </span>
            </div>

        `);
        
        checkandsetvalueforcolor();
    } else {
        jQuery(".main-section").removeClass('active');
        jQuery(".main-section").hide();
        alert("Part not found");
        $('.addPartNewOption').hide();
        $('#threedium_module_data').prop('disabled', false);
    }
    setTimeout(function () {
        jQuery(".new_3d_loading").hide();
    }, 5000);
}

jQuery(document).on("click", '.add-parts-this-baby', addPartThisbaby);
function addNewOptionBaby() {
    jQuery(".new_3d_loading").show();
    var threedium_module_data = jQuery("#threedium_module_data").val();

    if (threedium_module_data == "") {
        jQuery(".new_3d_loading").hide();
        alert("Please select a Module Name");
        jQuery(".new_3d_loading").hide();
        return false;
    }
    if (allpartsfromthreejs.length) { 
        jQuery(".main-section").removeClass('active');
        jQuery(".main-section").hide();
        var createSelect = '<select name="_part_name" id="_part_name" class="short baby-part-input">';
        createSelect += '<option value="">Select Parts</option>';
        jQuery.each(allpartsfromthreejs, function (index, value) {
            createSelect += '<option value="' + value.shortName + '">' + value.shortName + '</option>';
        })
        createSelect += '</select>';
        jQuery(".configurator-start-baby").append(
            '<div class="form-field _part_section_new"><span class="inner-sec1 inner-sec-custom1"><div class="part-top-panel"><label class= "part-name lable-part-name" for= "_part_name1" >Option Name</label><input type="text" name="baby_option_name[]" value="" class="new-option-name"><a href="javascript:void(0)" id="add_parts_option" class="remove-parts-this button">Remove Option -</a><a href="javascript:void(0)" id="add_parts_option" class="show-hide-sec-new button">Show/Hide</a><a href="javascript:void(0)" id="add_parts_option" class="add-parts-this-baby button">Add parts +</a></div><div class="part-sec-new"><div class="parts-section-new"></div></div><div class="part-top-panel"><label class="part-name" for="_material">Material</label><div class="multiSelect"><select name="baby_parts[${key}][material][]" multiple="multiple" class="short baby-material-sec-select js-example-basic-multiple" data-selected=""></select></div></div><div class="part-top-panel"><label class="part-name" for="_image">Image</label><div class="multiSelect"><a href="javascript:void(0)" class="button upload-material-icon" id="material_image_${key}">Upload image</a><a href="javascript:void(0)" class="remove-images button" style="display:none">Remove image</a><input type="hidden" class="baby-icon-material" name="baby_[${key}][image]" value=""></div></div> <div class="top-panel part-top-panel"><label class="label-name text_label_name" for="show_hide_parts_<?= $key; ?>">Show/Hide Parts</label><label class="switch"><input type="hidden" name="color_parts[<?= $key; ?>][show_hide_parts_change]" value="0"><input type="checkbox" class="show-hide-parts-change-checkbox" id="show_hide_parts_change_<?= $key; ?>" name="baby_parts[<?= $key; ?>][show_hide_parts_change]"><span class="slider round"></span></label></div></div></div>'
        );
        
        checkandsetvaluebybaby();
        jQuery('.js-example-basic-multiple').select2({
            closeOnSelect : false,
        });
        jQuery("input.baby-part-input").remove();
    } else {
        jQuery(".main-section").removeClass('active');
        jQuery(".main-section").hide();
        alert("Part not found");
        $('.addPartNewOption').hide();
        $('#threedium_module_data').prop('disabled', false);
    }
    setTimeout(function () {
        jQuery(".new_3d_loading").hide();
    }, 5000);

}
function checkandsetvaluebybaby() {
    jQuery(".new_3d_loading").show();
    var i = 0;
    setTimeout(() => {
        jQuery('.baby-part-input').each(function () {
            var $this = jQuery(this);
            var partName = i !== 0 ? `_part_name_${i}` : "_part_name";
            
            $this.find('option').remove().end();

            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== ""); // Exclude empty values
            jQuery.each(allpartsfromthreejs, (index, value) => {
                if (index != 0) {
                    createSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
                }
            });
            $this.append(createSelect).val(mergedValues);
            i++;
        });

        // Handle selected parts
        jQuery('.baby-part-selected').each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");
            $this.find('option').remove().end();
            var createSelect = '<option value="">Select Parts</option>';
            jQuery.each(allpartsfromthreejs, (index, value) => {
                createSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createSelect).val(mergedValues).trigger('change');
        });


        jQuery(".part-material").each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");

            $this.find('option').remove().end();
            var createMaterialSelect = '<option value="">Select Material</option>';
            jQuery.each(allMaterialsfromthreejs, (index, value) => {
                createMaterialSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createMaterialSelect).val(mergedValues);
            i++;
        });

        // Handle material secondary selection
        jQuery(".baby-material-sec-select").each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");

            $this.find('option').remove().end();
            var createMaterialSelect = '<option value="">Select Material</option>';
            jQuery.each(allMaterialsfromthreejs, (index, value) => {
                createMaterialSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createMaterialSelect).val(mergedValues).trigger('change');
            $this.select2({ closeOnSelect: false });
        });
        jQuery(".new_3d_loading").hide();
    }, 5000);
}
function checkandsetvaluefortext() {
    var i = 0;
    setTimeout(() => {
        
        jQuery(".text-material-select").each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");
            $this.find('option').remove().end();
            var createSelect = '<option value="">Select Parts</option>';
            jQuery.each(allpartsfromthreejs, (index, value) => {
                createSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createSelect).val(mergedValues).trigger('change');
            $this.select2({ closeOnSelect: false });
            jQuery('span.select2-selection.select2-selection--single').css('min-width', '210px');
        });
    }, 3000);
}
function checkandsetvalueforcolor() {
    var i = 0;
    setTimeout(() => {
        
        jQuery(".color-pattern-select").each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");

            $this.find('option').remove().end();
            var createMaterialSelect = '<option value="">Select Material</option>';
            jQuery.each(allMaterialsfromthreejs, (index, value) => {
                createMaterialSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createMaterialSelect).val(mergedValues).trigger('change');
            $this.select2({ closeOnSelect: false });
            jQuery('span.select2-selection.select2-selection--single').css('min-width', '210px');
        });

        jQuery(".color-pattern-part-select").each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");

            $this.find('option').remove().end();
            var createSelect = '<option value="">Select Parts</option>';
            jQuery.each(allpartsfromthreejs, (index, value) => {
                createSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createSelect).val(mergedValues).trigger('change');
            $this.select2({ closeOnSelect: false });
            jQuery('span.select2-selection.select2-selection--single').css('min-width', '210px');
        });
        jQuery(".color-gold-part-select").each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");

            $this.find('option').remove().end();
            var createSelect = '<option value="">Select Parts</option>';
            jQuery.each(allpartsfromthreejs, (index, value) => {
                createSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createSelect).val(mergedValues).trigger('change');
            $this.select2({ closeOnSelect: false });
            jQuery('span.select2-selection.select2-selection--single').css('min-width', '210px');
        });
        

        jQuery(".color-part-select").each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");

            $this.find('option').remove().end();
            var createSelect = '<option value="">Select Parts</option>';
            jQuery.each(allpartsfromthreejs, (index, value) => {
                createSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createSelect).val(mergedValues).trigger('change');
            $this.select2({ closeOnSelect: false });
            jQuery('span.select2-selection.select2-selection--single').css('min-width', '210px');
        });
        jQuery(".color-match-part-select").each(function () {
            var $this = jQuery(this);
            var selectedData = $this.data('selected') || '';
            var selectedCurrent = $this.val() || '';
            const selectedArray = String(selectedData).trim() !== '' ? String(selectedData).split(',') : [];
            const currentArray = String(selectedCurrent).trim() !== '' ? String(selectedCurrent).split(',') : [];
            const mergedValues = [...new Set([...selectedArray, ...currentArray])].filter(value => value !== "");

            $this.find('option').remove().end();
            var createSelect = '<option value="">Select Parts</option>';
            jQuery.each(allpartsfromthreejs, (index, value) => {
                createSelect += `<option value="${value.shortName}">${value.shortName}</option>`;
            });
            $this.append(createSelect).val(mergedValues).trigger('change');
            $this.select2({ closeOnSelect: false });
            jQuery('span.select2-selection.select2-selection--single').css('min-width', '210px');
        });
    }, 3000);
}
function addPartThisbaby() {
    jQuery(".new_3d_loading").show();
    if (jQuery(this).parent('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').hasClass('active')) {
        jQuery(this).parent('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').removeClass("active");
        jQuery(this).parent('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').show();
    } else {
        jQuery(this).parent('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').addClass("active");
        jQuery(this).parent('.inner-sec-custom1').find('.part-sec-new').find('.parts-section-new').show();
    }
    var option_name = jQuery(this).parents('.inner-sec-custom1').find('.new-option-name').val();
    var length = 0;
    if (option_name == "") {
        alert("Please Enter option name before adding part")
        return false;
    } else {
        if (jQuery(this).parents('._part_section_new').find('.container-sec-new').length) {
            length = jQuery(this).parents('._part_section_new').find('.container-sec-new').length;
        } else {
            length = 0;
        }
    }
    var createSelect = '<div class="multiSelect">';
    createSelect += '<select multiple="multiple" name="baby_parts[' + option_name + '][part][' + length + '][]" id="_part_name" class="short js-example-basic-multiple baby-part-selected">';
    createSelect += '<option value="">Select Parts</option>';
    jQuery.each(allpartsfromthreejs, function (index, value) {
        createSelect += '<option value="' + value.shortName + '">' + value.shortName + '</option>';
    })
    createSelect += '</select>';
    createSelect += '</div>';

    jQuery(this).parents('._part_section_new').append(
        '<div class="container-sec-new testfgfgf"><div class="conditional-logic" data-name="' + option_name + '"><div class="added-material"><a href="javascript:void(0)" id="add_parts_option" class="remove-sec-this button">Remove Type</a></div></div ><span class="inner-sec1 inner-sec-custom inner-options"><label class="part-name" for="_material">Option label</label><input type="text" name="baby_parts[' + option_name + '][label][]" value="" class="baby-parts-new-label"></span> <span class="inner-sec1 inner-sec-custom inner-prices"><label class="part-name" for="_material">Option Price</label><input type="text" name="baby_parts[' + option_name + '][price][]" value="" class="baby-parts-new-price"></span><span class="inner-sec inner-sec1 inner-maticons"><label class="part-name" for="_material"> material icon</label><a href="javascript:void(0)" class="button upload-material-icon">Upload image</a><a href="javascript:void(0)" class="remove-images button" style="display:none"> Remove image</a><input type="hidden" class="baby-icon-material" name="baby_parts[' + option_name + '][icon][]" value=""></span><span class="inner-sec1 inner-sec-custom inner-parts"><label class="part-name" for="_material">Select Part</label>' + createSelect + '</span><span class="inner-sec1 inner-sec-custom inner-parts"><label class="part-name" for="_condition">Condition</label><div class="multiSelect"><label class="switch"><input type="hidden" class="logical_status_main" name="baby_parts['+ option_name +'][logic_status][]" value="0"><input type="checkbox" class="logical_status"><span class="slider round"></span></label></div></span></div>'
    );
    if (window.location.href.includes("post-new.php?post_type=product")) {
        document.querySelectorAll(".logical_status_main").forEach(function (element) {
            let nearestInner = element.closest(".inner-sec1, .inner-sec-custom");
            if (nearestInner) {
                nearestInner.style.display = "none";
            }
            let spanInsideParts = nearestInner?.querySelector(".inner-parts span");
            if (spanInsideParts) {
                spanInsideParts.style.display = "none";
            }
        });
    }
    jQuery('.js-example-basic-multiple').select2({
        closeOnSelect : false,
    });
    setTimeout(function () {
        jQuery(".new_3d_loading").hide();
    }, 5000);
}
jQuery(document).on('blur input', '.new-option-name', function () {
    let inputElement = jQuery(this);
    let optionNameValue = inputElement.val().trim();
    let parentDiv = inputElement.closest('.form-field._part_section_new');
    let materialSelect = parentDiv.find('select[name*="[material]"]');
    let animationSelect = parentDiv.find('select[name*="[animation]"]');
    let imageUploadButton = parentDiv.find('a.upload-material-icon');
    let imageInputField = parentDiv.find('input[name*="[image]"]');
    let showHidePartsChange = parentDiv.find('input[name*="[show_hide_parts_change]"]');


    if (materialSelect.length > 0) {
        let newMaterialName = `baby_parts[${optionNameValue}][material][]`;
        materialSelect.attr('name', newMaterialName);
    }

    if (animationSelect.length > 0) {
        let newAnimationName = `baby_parts[${optionNameValue}][animation]`;
        animationSelect.attr('name', newAnimationName);
    }

    if (imageUploadButton.length > 0) {
        let newImageId = `baby_material_image_${optionNameValue}`;
        imageUploadButton.attr('id', newImageId);
    }

    if (imageInputField.length > 0) {
        let newImageName = `baby_parts[${optionNameValue}][image]`;
        imageInputField.attr('name', newImageName);
    }
    if (showHidePartsChange.length > 0) {
        let newImageName = `baby_parts[${optionNameValue}][show_hide_parts_change]`;
        showHidePartsChange.attr('name', newImageName);
    }
});
jQuery(document).on('blur input', '.text-option-name', function () {
    let inputElement = jQuery(this);
    let optionNameValue = inputElement.val().trim(); 
    let parentDiv = inputElement.closest('.form-field._text_section');
    let materialSelect = parentDiv.find('select[name*="[material]"]');
    let textColor = parentDiv.find('input[name*="[text_color]"]');
    let imageUploadButton = parentDiv.find('a.upload-option-icon');
    let imageInputField = parentDiv.find('input[name*="[option_icon]"]');



    if (optionNameValue !== '') {
        if (materialSelect.length > 0) {
            let newMaterialName = `baby_parts[${optionNameValue}][material][]`;
            materialSelect.attr('name', newMaterialName);
        }

        if (textColor.length > 0) {
            let newTextColorName = `baby_parts[${optionNameValue}][text_color]`; 
            textColor.attr('name', newTextColorName);
        }
        if (imageUploadButton.length > 0) {
            let newImageId = `option_icon_${optionNameValue}`;
            imageUploadButton.attr('id', newImageId);
        }
    
        if (imageInputField.length > 0) {
            let newImageName = `baby_parts[${optionNameValue}][option_icon]`;
            imageInputField.attr('name', newImageName);
        }
       
    }
}); 
jQuery(document).on('blur input', '.color-option-name', function () {
    let inputElement = jQuery(this);
    let optionNameValue = inputElement.val().trim();
    let parentDiv = inputElement.closest('.form-field._color_section');
    let partSelect = parentDiv.find('select[name*="[part]"]');
    let patternPartSelect = parentDiv.find('select[name*="[pattern_part]"]');
    let goldPartSelect = parentDiv.find('select[name*="[gold_part]"]');
    let patternSelect = parentDiv.find('select[name*="[pattern]"]');
    let imageUploadButton = parentDiv.find('a.upload-color-icon');
    let imageInputField = parentDiv.find('input[name*="[option_icon]"]');
    let matchColorPartField = parentDiv.find('select[name*="[match_color_part_name]"]');
    let matchWithField = parentDiv.find('input[name*="[match_with_card]"]');
    let matchWithLabel = parentDiv.find('input[name*="[match_with_label]"]');
    let embossingDebossingEffect = parentDiv.find('input[name*="[embossing_debossing_effect]"]');




    if (optionNameValue !== '') {
        if (partSelect.length > 0) {
            let newMaterialName = `color_parts[${optionNameValue}][part][]`;
            partSelect.attr('name', newMaterialName);
        }
        if (matchColorPartField.length > 0) {
            let newMaterialName = `color_parts[${optionNameValue}][match_color_part_name][]`;
            matchColorPartField.attr('name', newMaterialName);
        }
        if (patternPartSelect.length > 0) {
            let newMaterialName = `color_parts[${optionNameValue}][pattern_part][]`;
            patternPartSelect.attr('name', newMaterialName);
        }
        if (goldPartSelect.length > 0) {
            let newMaterialName = `color_parts[${optionNameValue}][gold_part][]`;
            goldPartSelect.attr('name', newMaterialName);
        }
        if (patternSelect.length > 0) {
            let newMaterialName = `color_parts[${optionNameValue}][pattern][]`;
            patternSelect.attr('name', newMaterialName);
        }
        if (imageUploadButton.length > 0) {
            let newImageId = `color_icon_${optionNameValue}`;
            imageUploadButton.attr('id', newImageId);
        }
    
        if (imageInputField.length > 0) {
            let newImageName = `color_parts[${optionNameValue}][option_icon]`;
            imageInputField.attr('name', newImageName);
        }
        if (matchWithField.length > 0) {
            let newImageName = `color_parts[${optionNameValue}][match_with_card]`;
            matchWithField.attr('name', newImageName);
        }
        if (embossingDebossingEffect.length > 0) {
            let newImageName = `color_parts[${optionNameValue}][embossing_debossing_effect]`;
            embossingDebossingEffect.attr('name', newImageName);
        }
        if (matchWithLabel.length > 0) {
            let newImageName = `color_parts[${optionNameValue}][match_with_label]`;
            matchWithLabel.attr('name', newImageName);
        }
    }
});
jQuery(document).on("change", ".match_with_card", function () {
    let isChecked = jQuery(this).is(":checked") ? 1 : 0; 
    jQuery(this).siblings("input[type=hidden]").val(isChecked);

    let parentPanel = jQuery(this).closest(".top-panel"); 
    let color_section = jQuery(this).closest("._color_section");
    let colorIndex = color_section.find('.color-option-name').val().trim();

    let fieldHtml = `
        <div class="top-panel part-top-panel added-field">
            <label class="label-name color_label_name" for="color_match_part_${colorIndex}">Match Color Part</label>
            <div class="multiSelect color_multi_select">
                <select name="color_parts[${colorIndex}][match_color_part_name][]" class="short color-match-part-select js-example-basic-multiple" data-selected=""></select>
            </div>
        </div>
    `;

    let fieldNewHtml = `
        <div class="top-panel part-top-panel added-new-field">
            <label class="label-name color_label_name" for="match_with_label_${colorIndex}">Match With Label</label>
            <div class="multiSelect color_multi_select">
                <input type="text" name="color_parts[${colorIndex}][match_with_label]" 
                    class="short color-match-part-input" id="match_with_label_${colorIndex}" />
            </div>
        </div>
    `;

    if (jQuery(this).is(":checked")) {
        if (!color_section.find(".added-field").length) {
            parentPanel.after(fieldHtml);
            checkandsetvalueforcolor();
        }
        if (!color_section.find(".added-new-field").length) {
            parentPanel.after(fieldNewHtml);
        }
    } else {
        color_section.find(".added-field").remove();
        color_section.find(".added-new-field").remove();
    }
});


});