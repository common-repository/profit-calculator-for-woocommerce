<?php

  if(!defined('ABSPATH'))
  {
    exit;
  }

  /**
   * Add the product cost price option to the general product pricing tab
   */
  function pcfw_create_simple_product_cost_price()
  {
    // Don't show field on external products
    if(!wc_get_product()->is_type('external'))
    {
      woocommerce_wp_text_input(array(
        'id'        => 'cost_price',
        'name'      => 'cost_price',
        'label'     => 'Cost price (' . get_woocommerce_currency_symbol() . ')',
        'data_type' => 'price',
        'value'     => get_post_meta(sanitize_text_field($_GET['post']), '_cost_price', true)
      ));
    }
  }
  add_action('woocommerce_product_options_pricing', 'pcfw_create_simple_product_cost_price');

  /**
   * Add the product cost price option for variations
   *
   * @param int     $loop           Position in the loop.
   * @param array   $variation_data Variation data.
   * @param WP_Post $variation      Post data.
   */
  function pcfw_create_variable_product_cost_price($loop, $variation_data, $variation)
  {
    woocommerce_wp_text_input(array(
      'id'                => '_cost_price[' . $variation->ID . ']',
      'name'              => '_cost_price[' . $variation->ID . ']',
      'label'             => 'Variable Cost Price (' . get_woocommerce_currency_symbol() . ')',
      'data_type'         => 'price',
      'value'             => get_post_meta($variation->ID, '_cost_price', true),
      'custom_attributes' => array(
        'step' => 'any',
        'min'  => '0'
      )
    ));
  }
  add_action('woocommerce_variation_options_pricing', 'pcfw_create_variable_product_cost_price', 10, 3);

  /**
   * Save the cost price field on product update
   *
   * @param	int	$post_id	The product id which is being updated
   */
  function pcfw_save_simple_product_cost_price($post_id)
  {
    update_post_meta($post_id, '_cost_price', sanitize_text_field($_POST['cost_price']));
  }
  add_action('woocommerce_process_product_meta', 'pcfw_save_simple_product_cost_price', 20, 1);

  /**
   * Save the cost price field on product update for variations
   *
   * @param int $post_id The product id which is being updated
   */
  function pcfw_save_variable_product_cost_price($post_id)
  {
    update_post_meta($post_id, '_cost_price', sanitize_text_field($_POST['_cost_price'][$post_id]));
  }
  add_action('woocommerce_save_product_variation', 'pcfw_save_variable_product_cost_price', 10, 2);

  /**
   * Add cost price column to the product admin
   *
   * @param   array $columns Current columns
   *
   * @return  array $columns Updated columns with cost price
   */
  function pcfw_add_admin_products_cost_price_column($columns)
  {
    unset($columns['date']);

    $columns['cost_price'] = 'Cost Price';
    $columns['date']       = 'Date';

    return $columns;
  }
  add_filter('manage_edit-product_columns', 'pcfw_add_admin_products_cost_price_column');

  /**
   * Add cost price to the column
   *
   * @param   string $column      Current column
   * @param   int    $product_id  Current product id
   */
  function pcfw_add_admin_products_cost_price_column_content($column, $product_id)
  {
    $cost_price = get_post_meta($product_id, '_cost_price', true);

    if($column == 'cost_price' && !empty($cost_price))
    {
      echo get_woocommerce_currency_symbol() . $cost_price;
    }
  }
  add_action('manage_product_posts_custom_column', 'pcfw_add_admin_products_cost_price_column_content', 10, 2);

  /**
   * Add the order profits to the admin order overview
   *
   * @param 	array $columns 			Current columns for the orders overview page
   *
   * @return 	array $new_columns 	Columns with profit added
   */
  function pcfw_add_order_profit_column($columns)
  {
    $new_columns = (is_array($columns)) ? $columns : array();

    $new_columns['order_profit'] = 'Profit';

    return $new_columns;
  }
  add_filter('manage_edit-shop_order_columns', 'pcfw_add_order_profit_column');

  /**
   * Add the data for the order profit column
   *
   * @param array $columns Current columns for the orders overview page
   */
  function pcfw_add_order_profit_column_data($column)
  {
    global $post;

    $profit = get_post_meta($post->ID, '_order_profit', true);

    if($column == 'order_profit')
    {
      echo (isset($profit) ? $profit : '');
    }
  }
  add_action('manage_shop_order_posts_custom_column', 'pcfw_add_order_profit_column_data', 2);

  /**
   * Add profit collumn onto the order information
   *
   * @param	int	$order_id	Calculate the profits from the order id
   */
  function pcfw_display_admin_order_profit_information($order_id)
  {
    $profit = get_post_meta($order_id, '_order_profit', true);

    if($profit != false) : ?>
      <tr>
        <td class="label">Profit:</td>
        <td width="1%"></td>
        <td class="total"><?php echo $profit; ?></td>
      </tr>
    <?php endif;
  }
  add_action('woocommerce_admin_order_totals_after_tax', 'pcfw_display_admin_order_profit_information', 10, 1);

?>
