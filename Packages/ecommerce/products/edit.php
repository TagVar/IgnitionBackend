<?php
  include("../../../functions/login_check.php");
  include("../../../config/main_config.php");
  include("../config/config.php");
  $category = $_GET["category"];
  if (isset($_GET["edit"])) {
    $is_edit = true;
    $identifier = $_GET["which"];
  } else {
    $is_edit = false;
  }
  if ($is_edit === true) {
    $product_query = "SELECT * FROM `" . $ecom_tables['product'] . "` WHERE `id` = '$identifier'";
    $product_result = $connection->query($product_query);
    $product = $product_result->fetch_assoc();
    $product_name_value = $product["product"];
  	$cost_value = $product["cost"];
    $requires_shipping_value = $product["requires-shipping"];
    $shipping_cost_value = $product["shipping"];
    $product_caption_value = $product["caption"];
    $product_description_value = $product["description"];
    $stock_value = $product["stock"];
    $unlimited_stock_value = $product["unlimited-stock"];
    if ($unlimited_stock_value == "1") {
      $unlimited_stock_checked = "checked";
      $stock_value = "";
    }
    $db_attributes_string = $product["attribute"];
    $db_attributes_array = array_filter(explode(">", $db_attributes_string));
    $attribute_names_value = [];
    $attribute_contents_value = [];
    foreach($db_attributes_array as $db_attribute) {
    	$db_attribute_parts = explode(":", $db_attribute);
    	$attribute_names_value[] = str_replace("&#x0003E;", ">", str_replace("&#x0003A;", ":", $db_attribute_parts[0]));
    	$attribute_contents_value[] = str_replace("&#x0003E;", ">", str_replace("&#x0003A;", ":", $db_attribute_parts[1]));
    }
    $image_filenames_value = $product["images"];
    if ($requires_shipping_value == "1") {
      $requires_shipping_checked = "checked";
    } else {
      $shipping_cost_value = "";
    }
  }
  if (isset($_POST["submit_product"])) {
  	$product_name_value = $_POST["product_name"];
  	$cost_value = $_POST["cost"];
    $requires_shipping_value = $_POST["requires_shipping"];
    $shipping_cost_value = $_POST["shipping_cost"];
    $product_caption_value = $_POST["product_caption"];
    $product_description_value = $_POST["product_description"];
    $attribute_names_value = $_POST["attribute_names"];
    $attribute_contents_value = $_POST["attribute_contents"];
    $image_filenames_value = $_POST["image_filenames"];
    $stock_value = $_POST["stock"];
    $unlimited_stock_value = $_POST["unlimited_stock"];
    if ($unlimited_stock_value == "1") {
      $unlimited_stock_checked = "checked";
    }
    if ($requires_shipping_value == "1") {
      $requires_shipping_checked = "checked";
    }
    if (str_replace(" ", "", $product_name_value) != "") {
    	$duplicate_product_query = "SELECT * FROM " . $ecom_tables['product'] . " WHERE `product` = '" . $product_name_value . "'";
    	$duplicate_product_result = $connection->query($duplicate_product_query);
    	if ($is_edit == true) {
    		if ((mysqli_num_rows($duplicate_product_result) <= 0) || ($product["product"] == $product_name_value)) {
    			$duplicate_validation = true;
    		}
    	} elseif ($is_edit == false) {
    		if (mysqli_num_rows($duplicate_product_result) <= 0) {
    			$duplicate_validation = true;
    		}
    	}
    	if ($duplicate_validation) {
	    	if (str_replace(" ", "", $cost_value)) {
	    		$cost_array = explode(".", $cost_value);
	    		if ((ctype_digit($cost_array[0])) && (strlen($cost_array[1]) == 2) && (ctype_digit($cost_array[1]))) {
		    		if ($requires_shipping_value == "1") {
		    			if (str_replace(" ", "", $shipping_cost_value) != "") {
		    				$shipping_cost_array = explode(".", $shipping_cost_value);
		    				if ((ctype_digit($shipping_cost_array[0])) && (strlen($shipping_cost_array[1]) == 2) && (ctype_digit($shipping_cost_array[1]))) {
		    					$shipping_cost_formatted = true;
		    				} else {
		    					$shipping_cost_error = "Your shipping cost must be in D.CC format.";
		    				}
		    			} else {
	    					$shipping_cost_error = "You did not specify a shipping cost.";
	    				}
		    		} else {
	    				$shipping_cost_formatted = true;
	    				$requires_shipping_value = "0";
	    				$shipping_cost_value = "";
	    			}
	    			if ($shipping_cost_formatted) {
              if ($unlimited_stock_value == "1") {
                  $stock_value = "";
                  $stock_validated = true;
              } else {
                if (ctype_digit($stock_value)) {
                    $unlimited_stock_value = "0";
                    $stock_validated = true;
                  } else {
                    $stock_error = "Please provide a natural number for current stock.";
                  }
              }
            if ($stock_validated) {
  						if (!empty($attribute_names_value)) {
  							$final_attribute_array = [];
  							foreach($attribute_names_value as $attribute_index=>$attribute_name_string) {
  								$safe_attribute_name_string = str_replace(">", "&#x0003E;", str_replace(":", "&#x0003A;", $attribute_name_string));
  								$safe_attribute_content_string = str_replace(">", "&#x0003E;", str_replace(":", "&#x0003A;", $attribute_contents_value[$attribute_index]));
  								$final_attribute_array[] = $safe_attribute_name_string . ":" . $safe_attribute_content_string;
  								if ((str_replace(" ", "", $safe_attribute_name_string) == "") || (str_replace(" ", "", $safe_attribute_content_string) == "")) {
  									$empty_attribute = true;
  								}
  							}
  							$final_attribute_string = implode(">", $final_attribute_array);
  						} else {
  							$final_attribute_string = "";
  						}
  						if ($empty_attribute != true) {
  							if ($is_edit == true) {
  								$update_product_query = "UPDATE " . $ecom_tables["product"] . " SET `product` = '" . $product_name_value . "', `images` = '" . $image_filenames_value . "', `caption` = '" . $product_caption_value . "', `description` = '" . $product_description_value . "', `cost` = '" . $cost_value . "', `requires-shipping` = '" . $requires_shipping_value . "', `shipping` = '" . $shipping_cost_value . "', `stock` = '" . $stock_value . "', `unlimited-stock` = '" . $unlimited_stock_value . "', `attribute` = '" . $final_attribute_string . "' WHERE `id` = '" . $identifier . "'";
  								if ($connection->query($update_product_query)) {
  									header("location: category.php?category=" . $category);
  								} else {
  									$error = "An error occured. Please try again.";
  								}
  							} elseif ($is_edit == false) {
  								$create_product_query = "INSERT INTO " . $ecom_tables['product'] . " (`product`, `category`, `images`, `caption`, `description`, `cost`, `requires-shipping`, `shipping`, `attribute`, `unlimited-stock`, `stock`) VALUES ('" . $product_name_value . "', '" . $category . "', '" . $image_filenames_value . "', '" . $product_caption_value . "', '" . $product_description_value . "', '" . $cost_value . "', '" . $requires_shipping_value . "', '" . $shipping_cost_value . "', '" . $final_attribute_string . "', '" . $unlimited_stock_value . "', '" . $stock_value . "')";
  			    				if ($connection->query($create_product_query)) {
  			    					$current_products_query = "SELECT `products` FROM " . $ecom_tables['category'] . " WHERE `category` = '$category'";
  			    					$current_products_result = $connection->query($current_products_query);
  			    					$current_products_db = mysqli_fetch_assoc($current_products_result);
  			    					$current_products_array = array_filter(explode(">", $current_products_db['products']));
  			    					$product_id_result = $connection->query("SELECT `id` FROM " . $ecom_tables['product'] . " WHERE `product` = '$product_name_value'");	
  			    					$product_id_db = mysqli_fetch_assoc($product_id_result);
  			    					$current_products_array[] = $product_id_db['id'];
  			    					$new_products_string = implode(",", $current_products_array);
  			    					$add_to_category_query = "UPDATE " . $ecom_tables['category'] . " SET `products`='$new_products_string' WHERE `category`='$category'";
  			    					if ($connection->query($add_to_category_query)) {
  			    						header("location: category.php?category=" . $category);
  			    					} else {
  			    						$error = "An error occured. Possibility of data corruption.";
  			    					}
  			    				} else {
  			    					$error = "An error occured. Please try again.";
  			    				}
  							}
  		    			} else {
  		    				$error = "Both fields must be filled for each attribute.";
  		    			}
  	    			} else {
  	    				$error = $stock_error;
  	    			}
            } else {
              $error = $shipping_cost_error;
            } 
	    		} else {
	    			$error = "Your product cost must be in D.CC format.";
	    		}
	    	} else {
	    		$error = "You must specify a price for the product.";
	    	}
	    } else {
	    	$error = "A product with that name already exists.";
	    }
    } else {
    	$error = "You must provide a product name.";
    }
  }
  if (isset($_POST["add_image"])) {
  	$product_name_value = $_POST["image_post_product_name"];
  	$cost_value = $_POST["image_post_cost"];
    $requires_shipping_value = $_POST["image_post_requires_shipping"];
    $shipping_cost_value = $_POST["image_post_shipping_cost"];
    $product_caption_value = $_POST["image_post_product_caption"];
    $product_description_value = $_POST["image_post_product_description"];
    $image_filenames_value = $_POST["image_post_image_filenames"];
    $attribute_names_value = $_POST["hidden_attribute_names"];
    $attribute_contents_value = $_POST["hidden_attribute_contents"];
    $stock_value = $_POST["image_post_stock"];
    $unlimited_stock_value = $_POST["image_post_unlimited_stock"];
    if ($unlimited_stock_value == "1") {
      $unlimited_stock_checked = "checked";
    }
    if ($requires_shipping_value == "1") {
      $requires_shipping_checked = "checked";
    }
    $upload_input = $_FILES["upload_input"];
    $current_file_location = $upload_input["tmp_name"];
    $current_file_name = $upload_input["name"];
    $current_file_size = $upload_input["size"];
    $image_exploded_filename = explode(".", $current_file_name);
    $image_file_type = strtolower(array_pop($image_exploded_filename));
    $image_accepted_filetypes = [
     "png",
     "jpg",
     "gif",
     "jpeg"
    ];
    if ($current_file_size <= 20000000) {
      if (in_array($image_file_type, $image_accepted_filetypes)) {
        $new_file_name = uniqid('', true) . "." . $image_file_type;
        if (file_exists($_SERVER["DOCUMENT_ROOT" . "/nodes/images/" . $new_file_name])) {
          $new_file_name = uniqid('', true) . $image_file_type;
        }
        if (file_exists($_SERVER["DOCUMENT_ROOT" . "/nodes/images/" . $new_file_name])) {
          $image_upload_error = "An error occured. Please try again.";
          $reshow = "true";
        } else {
          if (move_uploaded_file($current_file_location, $_SERVER["DOCUMENT_ROOT"] . "/nodes/images/" . $new_file_name)) {
            $add_to_selection = $new_file_name;
          } else {
            $image_upload_error = "An error occured. Please try again.";
            $reshow = "true";
          }
        }
      } else {
        $image_upload_error = "Only PNG, JPG, JPEG, and GIF file extensions are accepted.";
        $reshow = "true";
      }
    } else {
      $image_upload_error = "Images may not exceed 20 megabytes in size.";
      $reshow = "true";
    }
  }
?>
<html>
  <head>
    <title><?php echo $title ?></title>
    <link rel="stylesheet" type="text/css" href="../css/ecom.css">
  </head>
  <body onload="<?php if ($image_upload_error != "") { echo "uploadAlert('" . $image_upload_error . "');"; } ?>">
  	<div id="image-alert">
      <div class="heading">Upload an Image</div>
      <br />
      <form name="image_form" action="" enctype="multipart/form-data" method="post">
        <input type="hidden" name="image_post_product_name" />
        <input type="hidden" name="image_post_cost" />
        <input type="hidden" name="image_post_requires_shipping" />
        <input type="hidden" name="image_post_shipping_cost" />
        <input type="hidden" name="image_post_product_caption" />
        <input type="hidden" name="image_post_product_description" />
        <input type="hidden" name="image_post_stock" />
        <input type="hidden" name="image_post_unlimited_stock" />
        <input type="hidden" name="image_post_image_filenames" />
        <input type="hidden" name="image_post_name" value="<?php echo $add_to_selection; ?>"/>
        <input type="hidden" name="image_reshow" value="<?php echo $reshow; ?>" />
        <div id="hidden-attribute-container">
        	<?php
            if (!empty($attribute_names_value)) {
              foreach($attribute_names_value as $index=>$attribute_name) {
                echo '
                  <div id="hidden-attribute-form-' . $index . '">
                    <input name="hidden_attribute_names[]" type="hidden" value="' . $attribute_name . '"/>
                    <input name="hidden_attribute_contents[]" type="hidden" value="' . $attribute_contents_value[$index] . '"/>
                  </div>';
              }
            }
          ?>
        </div>
        <input type="radio" name="image_src" value="outside" checked>Use an URL
        <br />
        <input type="radio" name="image_src" value="previous">Use a Previously Uploaded Image
        <br />
        <input type="radio" name="image_src" id="upload" value="upload">Upload a New Image
        <br />
        <br />
        <?php
        $image_array = glob($_SERVER['DOCUMENT_ROOT'] . "/nodes/images/" . "*.{jpg,png,gif,jpeg}", GLOB_BRACE);
        foreach($image_array as $image) {
          $path_to_image = str_replace($_SERVER['DOCUMENT_ROOT'], '', $image);
          echo '<img src="' . $path_to_image . '" class="upload-thumbnail"/>';
        }
        ?>
        <input type="text" name="url_box" placeholder="URL" />
        <input type="file" name="upload_input" />
        <input type="submit" value="Add Image" name="add_image" />
      </form>
      <button name="cancel_image_upload">Cancel</button>
    </div>
    <div id="main-container" class="content-border">
      <span class="heading"><?php if ($is_edit === true) { echo "Edit Product"; } elseif ($is_edit === false) { echo "Add Product"; } ?></span>
      <br />
      <form method="post" action="">
        <input type="text" name="product_name" placeholder="Product Name" value="<?php echo $product_name_value; ?>"/>
        <input type="text" name="cost" placeholder="Product Cost (D.CC)" value="<?php echo $cost_value; ?>"/>
        <div id="add-picture-heading">
	        <div class="sub-heading">
	        	Add Photos
	        </div>
	        <p class="flat-paragraph">
	    		You can add up to 5 photos up to 20 megabytes per photo. Photos must be formatted as .JPG, .JPEG, .PNG, .BMP, or .GIF.
	    	</p>
    	</div>
        <div class="content-border" id="picture-container">
        	<?php
        		if ($image_filenames_value != "") {
        			$image_filenames_array = explode(">", $image_filenames_value);
        			foreach($image_filenames_array as $image_data) {
        				if (strpos($image_data,':main') !== false) {
        					$final_image_path = str_replace(":main", "", $image_data);
        					$main_image_style = ' style="border: 5px solid #006bfa;"';
        				} else {
        					$final_image_path = $image_data;
        					$main_image_style = "";
        				}
        				echo '<div class="thumbnail-container"' . $main_image_style . '>
        						<img class="preview-thumbnail" src="' . $final_image_path . '"/>
        						<div class="remove-thumbnail">
        							X
        						</div>
        					  </div>
        					 ';
        			}
        		}
        	?>
        	<input type="button" id="add-picture" value="+"/>
        	<input type="hidden" name="image_filenames" value="<?php echo $image_filenames_value; ?>"/>
       	</div>
        <input name="requires_shipping" type="checkbox" value="1" <?php echo $requires_shipping_checked; ?>/> Requires Shipping
        <input type="text" name="shipping_cost" placeholder="Shipping Cost (D.CC)" value="<?php echo $shipping_cost_value; ?>"/>
        <br />
        <br />
        <input name="unlimited_stock" type="checkbox" value="1" <?php echo $unlimited_stock_checked; ?>/> This Product has Unlimited Stock
        <input type="text" name="stock" placeholder="Current Stock" value="<?php echo $stock_value; ?>"/>
        <br />
        <br />
        <textarea name="product_caption" id="caption-textarea" placeholder="Product Caption"><?php echo $product_caption_value; ?></textarea>
        <textarea name="product_description" placeholder="Product Description"><?php echo $product_description_value; ?></textarea>
        <br />
        <br />
        <input type="button" name="add_attribute" value="Add Attribute" />
        <div id="attribute-container">
          <?php
            if (!empty($attribute_names_value)) {
              foreach($attribute_names_value as $index=>$attribute_name) {
                echo '
                  <div id="attribute-form-' . $index . '">
                    <input name="attribute_names[]" class="buttoned-input" type="text" placeholder="Attribute Name" value="' . $attribute_name . '"/>
                    <div onclick="removeAttributeForm(' . $index . ')" class="remove-button">
                      X
                    </div>
                    <textarea name="attribute_contents[]" class="short-textarea" placeholder="Attribute Content">' . $attribute_contents_value[$index] . '</textarea>
                    <br />
                    <br />
                  </div>';
              }
            }
          ?>
        </div>
        <?php
          if ($error != "") {
            echo "<br /><br /><font color='red'>" . $error . "</font><br /><br />";
          }
        ?>
        <input type="submit" name="submit_product" value="<?php if ($is_edit === true) { echo "Save Changes"; } elseif ($is_edit === false) { echo "Add Product"; } ?>" />
      </form>
      <form action="category.php" method="get">
        <input type="submit" value="Back To Product Selection">
        <input type="hidden" name="category" value="<?php echo $category; ?>"/>
      </form>
    </div>
    <br />
    <br />
    <script type="text/javascript" src="../javascript/jquery.js"></script>
    <script type="text/javascript" src="../javascript/products.js"></script>
  </body>
</html>