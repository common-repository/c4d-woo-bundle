<?php
add_action( 'admin_enqueue_scripts', 'c4d_woo_bundle_load_scripts_admin' );
add_action( 'wp_enqueue_scripts', 'c4d_woo_bundle_load_scripts_site' );
add_action( 'admin_enqueue_scripts', 'c4d_woo_bundle_load_scripts_admin' );
add_action( 'c4d-plugin-manager-section', 'c4d_woo_bundle_section_options', 1000 );
add_filter( 'plugin_row_meta', 'c4d_woo_bundle_plugin_row_meta', 10, 2 );
add_action( 'plugins_loaded', 'c4d_woo_bundle_load_textdomain' );

function c4d_woo_bundle_load_textdomain() {
    load_plugin_textdomain( 'c4d-woo-bundle', false, dirname(dirname(plugin_basename( __FILE__ ))) . '/languages' );
}

function c4d_woo_bundle_load_scripts_site() {
    $js = C4DWOOBUNDLE_PLUGIN_URI . '/assets/default.min.js';
    if (WP_DEBUG) {
        $js = C4DWOOBUNDLE_PLUGIN_URI . '/assets/default.js';
    }
    wp_enqueue_script( 'c4d-woo-bundle-site-js', $js, array( 'jquery' ), false, true );
    wp_enqueue_style( 'c4d-woo-bundle-site-style', C4DWOOBUNDLE_PLUGIN_URI.'/assets/default.css' );
}

function c4d_woo_bundle_load_scripts_admin($hook) {
    if (in_array($hook, array('post.php', 'post-new.php'))) {
        $js = C4DWOOBUNDLE_PLUGIN_URI . '/assets/admin.min.js';
        if (WP_DEBUG) {
            $js = C4DWOOBUNDLE_PLUGIN_URI . '/assets/admin.js';
        }
        wp_enqueue_script( 'c4d-woo-bundle-admin-js', $js );
        wp_enqueue_style( 'c4d-woo-bundle-admin-style', C4DWOOBUNDLE_PLUGIN_URI.'/assets/admin.css' );
    }
}

function c4d_woo_bundle_body_class($classes) {
    global $c4d_plugin_manager;
    if (isset($c4d_plugin_manager['c4d-woo-bundle-prefix-class'])) {
        $classes = array_merge($classes, array( $c4d_plugin_manager['c4d-woo-bundle-prefix-class'] ));
    }
    if (isset($c4d_plugin_manager['c4d-woo-bundle-load-more']) && $c4d_plugin_manager['c4d-woo-bundle-load-more'] != 'off') {
         $classes = array_merge($classes, array('c4d-woo-bundle-load-more-active', 'c4d-woo-bundle-load-more-'.$c4d_plugin_manager['c4d-woo-bundle-load-more']));
    }
    if (isset($c4d_plugin_manager['c4d-woo-bundle-columns'])) {
         $classes = array_merge($classes, array('c4d-woo-bundle-columns-'.$c4d_plugin_manager['c4d-woo-bundle-columns']));
    }
    return $classes;
}

function c4d_woo_bundle_section_options(){
    $opt_name = 'c4d_plugin_manager';
    $userRoleJson = plugin_dir_path(dirname(__FILE__)) . '/userrole.json';
    $userRoles = array();
    $userRoles[] = array(
                'id'       => 'c4d-woo-bundle-discount-user-role-guest',
                'type'     => 'text',
                'title'    => esc_html__('Discount for', 'c4d-woo-bundle') . ' Guest',
                'subtitle' => esc_html__('Enable when discount type is user role', 'c4d-woo-bundle'),
                'default'  => '0'
            );

    if (file_exists($userRoleJson)) {
        ob_start();
        include $userRoleJson;
        $userRoleJson = ob_get_contents();
        $userRoleJson = json_decode($userRoleJson);
        ob_end_clean();

        foreach($userRoleJson as $user) {

            $userRoles[] = array(
                'id'       => 'c4d-woo-bundle-discount-user-role-' . $user->value,
                'type'     => 'text',
                'title'    => esc_html__('Discount for', 'c4d-woo-bundle') . ' ' . $user->text,
                'subtitle' => esc_html__('Enable when discount type is user role', 'c4d-woo-bundle'),
                'default'  => '5'
            );
        }
    }

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Bundle Products', 'c4d-mega-menu' ),
        'desc'             => '',
        'customizer_width' => '400px',
        'icon'             => 'el el-home',
    ));

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Global', 'c4d-mega-menu' ),
        'id'               => 'section-bundle-layout',
        'desc'             => '',
        'customizer_width' => '400px',
        'icon'             => '',
        'subsection'       => true,
        'fields'           => array(
            array(
                'id'       => 'c4d-woo-bundle-discount',
                'type'     => 'text',
                'title'    => esc_html__('Discount', 'c4d-woo-bundle'),
                'subtitle' => esc_html__('Default Discount', 'c4d-woo-bundle'),
                'default'  => ''
            ),
            array(
                'id'       => 'c4d-woo-bundle-discount-type',
                'type'     => 'button_set',
                'title'    => esc_html__('Discount Type', 'c4d-woo-bundle'),
                'subtitle' => esc_html__('Percent or Fixed Price', 'c4d-woo-bundle'),
                'options' => array(
                    'percent' => esc_html__('Percent', 'c4d-woo-bundle'),
                    'price' => esc_html__('Price', 'c4d-woo-bundle'),
                    'user'  => esc_html__('User Role', 'c4d-woo-bundle')
                 ),
                'default' => 'percent'
            ),
            array(
                'id'       => 'c4d-woo-bundle-title',
                'type'     => 'text',
                'title'    => esc_html__('Title', 'c4d-woo-bundle'),
                'subtitle' => esc_html__('Global Title', 'c4d-woo-bundle'),
                'default'  => ''
            ),
            array(
                'id'          => 'c4d-woo-bundle-title-typo',
                'type'        => 'typography',
                'title'       => esc_html__('Title Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-title h3'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                'line-height' => false
            ),
            array(
                'id'       => 'c4d-woo-bundle-desc',
                'type'     => 'textarea',
                'title'    => esc_html__('Description', 'c4d-woo-bundle'),
                'subtitle' => esc_html__('Global Description', 'c4d-woo-bundle'),
                'default'  => ''
            ),
            array(
                'id'          => 'c4d-woo-bundle-desc-typo',
                'type'        => 'typography',
                'title'       => esc_html__('Description Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-description'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                'line-height' => false
            ),
            array(
                'id'       => 'c4d-woo-bundle-after-desc',
                'type'     => 'textarea',
                'title'    => esc_html__('After Bundle Desc', 'c4d-woo-bundle'),
                'default'  => ''
            ),
            array(
                'id'       => 'c4d-woo-bundle-show-order',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Order Bundle', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'       => 'c4d-woo-bundle-end-time',
                'type'     => 'text',
                'title'    => esc_html__('Set & Show End Time ', 'c4d-woo-bundle'),
                'subtitle'    => esc_html__('Show end time for bundle', 'c4d-woo-bundle'),
                'default' => '3'
            ),
            array(
                'id'       => 'c4d-woo-bundle-button',
                'type'     => 'text',
                'title'    => esc_html__('Add Bundle Text', 'c4d-woo-bundle'),
                'default' => esc_html__('Add bundle to cart', 'c4d-woo-bundle')
            ),
            array(
                'id'          => 'c4d-woo-bundle-button-typo',
                'type'        => 'typography',
                'title'       => esc_html__('Add Bundle Button Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-add-to-cart'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                'line-height' => false,
                'color'         => false
            ),
            array(
                'id'       => 'c4d-woo-bundle-button-color',
                'type'     => 'color',
                'title'    => esc_html__('Add Bundle Button Text Color', 'c4d-woo-bundle'),
                'default'  => '#fff',
                'transparent' => false,
                'validate' => 'color',
                'output'    => array(
                    'color' => '.c4d-woo-bundle-add-to-cart'
                )
            ),
            array(
                'id'       => 'c4d-woo-bundle-button-color-hover',
                'type'     => 'color',
                'title'    => esc_html__('Add Bundle Button Text Color Hover', 'c4d-woo-bundle'),
                'default'  => '#fff',
                'transparent' => false,
                'validate' => 'color',
                'output'    => array(
                    'color' => '.c4d-woo-bundle-add-to-cart:hover'
                )
            ),
            array(
                'id'       => 'c4d-woo-bundle-button-background',
                'type'     => 'color',
                'title'    => esc_html__('Add Bundle Button Background', 'c4d-woo-bundle'),
                'default'  => '#008000',
                'transparent' => false,
                'validate' => 'color',
                'output'    => array(
                    'background-color' => '.c4d-woo-bundle-add-to-cart'
                )
            ),
            array(
                'id'       => 'c4d-woo-bundle-button-background-hover',
                'type'     => 'color',
                'title'    => esc_html__('Add Bundle Button Background Hover', 'c4d-woo-bundle'),
                'default'  => '#059c05',
                'transparent' => false,
                'validate' => 'color',
                'output'    => array(
                    'background-color' => '.c4d-woo-bundle-add-to-cart:hover'
                )
            )
        )
    ));

     Redux::setSection( $opt_name,
        array(
            'title'            => esc_html__( 'Discount User Role', 'c4d-woo-bundle' ),
            'id'               => 'section-bundle-user-role',
            'desc'             => '',
            'customizer_width' => '400px',
            'icon'             => '',
            'subsection'       => true,
            'fields'           => $userRoles
        )
    );

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Item', 'c4d-woo-bundle' ),
        'id'               => 'section-bundle-item',
        'desc'             => '',
        'customizer_width' => '400px',
        'icon'             => '',
        'subsection'       => true,
        'fields'           => array(
            array(
                'id'       => 'c4d-woo-bundle-item-init-check',
                'type'     => 'button_set',
                'title'    => esc_html__('Init Check', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-thumb',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Thumb', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-title',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Title', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'          => 'c4d-woo-bundle-item-title-size',
                'type'        => 'typography',
                'title'       => esc_html__('Title Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-items .c4d-woo-bundle-item .item-title'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                // 'font-weight' => false,
                'line-height' => false,
                'default'     => array(
                    'font-size'   => '14px',
                ),
            ),
            array(
                'id'          => 'c4d-woo-bundle-item-backorder',
                'type'        => 'typography',
                'title'       => esc_html__('Backorder Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-items .c4d-woo-bundle-item .item-stock'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                // 'font-weight' => false,
                'line-height' => false,
                'default'     => array(
                    'font-size'   => '14px',
                ),
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-desc',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Description', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'          => 'c4d-woo-bundle-item-desc-size',
                'type'        => 'typography',
                'title'       => esc_html__('Desc Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-items .c4d-woo-bundle-item .item-description'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                // 'font-weight' => false,
                'line-height' => false,
                'default'     => array(
                    'font-size'   => '12px',
                ),
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-cate',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Category', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '0'
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-attr',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Attribute', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '0'
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-tag',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Tag', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '0'
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-qty',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Qty', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'          => 'c4d-woo-bundle-item-qty-size',
                'type'        => 'typography',
                'title'       => esc_html__('Qty Label Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-items .c4d-woo-bundle-item .item-label'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                // 'font-weight' => false,
                'line-height' => false,
                'default'     => array(
                    'font-size'   => '12px',
                ),
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-variable',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Variable', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-variable-stock',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Item Variable Stock Status', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'       => 'c4d-woo-bundle-item-link',
                'type'     => 'button_set',
                'title'    => esc_html__('Enable Item Link', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'          => 'c4d-woo-bundle-item-price-discount',
                'type'        => 'typography',
                'title'       => esc_html__('Discount Price Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-price-discount', '.c4d-woo-bundle-price-discount .amount', '.c4d-woo-bundle-items .c4d-woo-bundle-item .c4d-woo-item-price-discount',
                                        '.c4d-woo-bundle-items .c4d-woo-bundle-item .c4d-woo-item-price-discount .amount', '.c4d-woo-bundle-total .total-discount'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                'line-height' => false,
                'default'     => array(
                    'color' => '#57bf6d',
                    'font-size'   => '14px',
                ),
            ),
            array(
                'id'          => 'c4d-woo-bundle-item-price-org',
                'type'        => 'typography',
                'title'       => esc_html__('Original Price Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-price-original', '.c4d-woo-bundle-price-original .amount', '.c4d-woo-bundle-items .c4d-woo-bundle-item .c4d-woo-item-price-original', '.c4d-woo-bundle-items .c4d-woo-bundle-item .c4d-woo-item-price-original .amount', '.c4d-woo-bundle-total .total-price'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                'line-height' => false,
                'default'     => array(
                    'color' => '#57bf6d',
                    'font-size'   => '14px',
                ),
            ),
            array(
                'id'          => 'c4d-woo-bundle-item-percent',
                'type'        => 'typography',
                'title'       => esc_html__('Percent Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-price-percent', '.c4d-woo-bundle-items .c4d-woo-bundle-item .c4d-woo-item-price-percent', '.c4d-woo-bundle-total .total-save'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                'line-height' => false,
                'default'     => array(
                    'color' => 'ff0000',
                    'font-size'   => '14px',
                ),
            ),
            array(
                'id'       => 'c4d-woo-bundle-layout',
                'type'     => 'textarea',
                'title'    => esc_html__('Item Layout', 'c4d-woo-bundle'),
                'rows'     => 20,
                'default' => '<div class="item-row">
                        <div class="item-col width-30">
                            [mustby]
                            [thumbnail]
                        </div>
                        <div class="item-col width-70">
                            [title][stock]
                            [price]
                            <div class=item-qty-variations>
                            [qty]
                            [variation]
                            </div>
                            [description]
                        </div>
                    </div>'
            )
        )
    ));
     Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Listing Page', 'c4d-mega-menu' ),
        'id'               => 'section-bundle-listing',
        'desc'             => '',
        'customizer_width' => '400px',
        'icon'             => '',
        'subsection'       => true,
        'fields'           => array(
            array(
                'id'       => 'c4d-woo-bundle-listing-view-bundle',
                'type'     => 'button_set',
                'title'    => esc_html__('View Bundle Button', 'c4d-woo-bundle'),
                'subtitle' => esc_html__('Show View Bundle button instead of Add To Cart button on listing page', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'       => 'c4d-woo-bundle-listing-view-bundle-text',
                'type'     => 'text',
                'title'    => esc_html__('Button Text', 'c4d-woo-bundle'),
                'default' => 'View Bundle'
            ),
            array(
                'id'       => 'c4d-woo-bundle-listing-view-bundle-class',
                'type'     => 'text',
                'title'    => esc_html__('Button Class', 'c4d-woo-bundle'),
                'default' => ''
            ),
            array(
                'id'          => 'c4d-woo-bundle-view-bundle-font-size',
                'type'        => 'typography',
                'title'       => esc_html__('Button Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-view-bundle-button'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                // 'font-weight' => false,
                'color' => false,
                'line-height' => false,
                'default'     => array(
                    'color'       => '#fff',
                    'font-size'   => '14px',
                ),
            ),
            array(
                'id'       => 'c4d-woo-bundle-view-bundle-color',
                'type'     => 'link_color',
                'title'    => esc_html__('Button Text Color', 'c4d-woo-bundle'),
                'default'  => array(
                    'regular'  => '#FFFFFF', // blue
                    'hover'    => '#FFFFFF'
                ),
                'active'    => false,
                'output'    => array(
                    'regular'  => '.c4d-woo-bundle-view-bundle-button', // blue
                    // 'hover'    => '.c4d-woo-bundle-view-bundle-button', // red
                )
            ),
            array(
                'id'       => 'c4d-woo-bundle-listing-view-bundle-background',
                'type'     => 'color',
                'title'    => esc_html__('Button Background', 'c4d-woo-bundle'),
                'default'  => '#e82323',
                'transparent' => false,
                'validate' => 'color',
                'output'    => array(
                    'background-color' => '.c4d-woo-bundle-view-bundle-button'
                )
            ),
            array(
                'id'       => 'c4d-woo-bundle-listing-view-bundle-background-hover',
                'type'     => 'color',
                'title'    => esc_html__('Button Background Hover', 'c4d-woo-bundle'),
                'default'  => '#e02929',
                'transparent' => false,
                'validate' => 'color',
                'output'    => array(
                    'background-color' => '.c4d-woo-bundle-view-bundle-button:hover'
                )
            ),
             array(
                'id'             => 'c4d-woo-bundle-view-bundle-spacing',
                'type'           => 'spacing',
                'mode'           => 'padding',
                'units'          => array('px'),
                'title'          => esc_html__('Button Padding', 'c4d-woo-bundle'),
                'default'            => array(
                    'padding-top'     => '8',
                    'padding-right'   => '15',
                    'padding-bottom'  => '8',
                    'padding-left'    => '15',
                    'units'   => 'px'
                ),
                'output'         => array(
                    'padding-top'     => '.c4d-woo-bundle-view-bundle-button',
                    'padding-right'   => '.c4d-woo-bundle-view-bundle-button',
                    'padding-bottom'  => '.c4d-woo-bundle-view-bundle-button',
                    'padding-left'    => '.c4d-woo-bundle-view-bundle-button'
                )
            ),
    )));

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Badge Page', 'c4d-mega-menu' ),
        'id'               => 'section-bundle-badge',
        'desc'             => '',
        'customizer_width' => '400px',
        'icon'             => '',
        'subsection'       => true,
        'fields'           => array(
            array(
                'id'       => 'c4d-woo-bundle-badge-show',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Badge', 'c4d-woo-bundle'),
                'subtitle'    => esc_html__('Show Badge on listing page', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'       => 'c4d-woo-bundle-badge-price-show',
                'type'     => 'button_set',
                'title'    => esc_html__('Show Badge by Discount', 'c4d-woo-bundle'),
                'subtitle'    => esc_html__('Show Badge by Discount on listing page', 'c4d-woo-bundle'),
                'options' => array(
                    '1' => esc_html__('Yes', 'c4d-woo-bundle'),
                    '0' => esc_html__('No', 'c4d-woo-bundle')
                 ),
                'default' => '1'
            ),
            array(
                'id'       => 'c4d-woo-bundle-badge-text',
                'type'     => 'text',
                'title'    => esc_html__('Badge Text', 'c4d-woo-bundle'),
                'default' =>  esc_html__('Bundle', 'c4d-woo-bundle')
            ),
            array(
                'id'       => 'c4d-woo-bundle-badge-class',
                'type'     => 'text',
                'title'    => esc_html__('Badge Class', 'c4d-woo-bundle')
            ),
            array(
                'id'          => 'c4d-woo-bundle-badge-font-size',
                'type'        => 'typography',
                'title'       => esc_html__('Typography', 'c4d-woo-bundle'),
                'output'      => array('.c4d-woo-bundle-badge'),
                'units'       =>'px',
                'text-align'  => false,
                'subsets'     => false,
                'font-family' => false,
                'font-style'  => false,
                'text-transform' => true,
                'letter-spacing' => true,
                'line-height' => false,
                'default'     => array(
                    'color'       => '#fff',
                    'font-size'   => '12px',
                ),
            ),
            array(
                'id'       => 'c4d-woo-bundle-badge-background',
                'type'     => 'color',
                'title'    => esc_html__('Badge Background', 'c4d-woo-bundle'),
                'default'  => '#e82323',
                'transparent' => false,
                'validate' => 'color',
                'output'    => array(
                    'background-color' => '.c4d-woo-bundle-badge'
                )
            ),
            array(
                'id'             => 'c4d-woo-bundle-badge-spacing',
                'type'           => 'spacing',
                'mode'           => 'padding',
                'units'          => array('px'),
                'title'          => esc_html__('Button Padding', 'c4d-woo-bundle'),
                'default'            => array(
                    'padding-top'     => '2px',
                    'padding-right'   => '8px',
                    'padding-bottom'  => '2px',
                    'padding-left'    => '8px',
                    'units'   => 'px'
                ),
                'output'         => array(
                    'padding-top'     => '.c4d-woo-bundle-badge',
                    'padding-right'   => '.c4d-woo-bundle-badge',
                    'padding-bottom'  => '.c4d-woo-bundle-badge',
                    'padding-left'    => '.c4d-woo-bundle-badge'
                )
            ),
            array(
                'id'             => 'c4d-woo-bundle-badge-pos',
                'type'           => 'spacing',
                'mode'           => 'absolute',
                'units'          => array('px'),
                'title'          => esc_html__('Button Position', 'c4d-woo-bundle'),
                'top'            => true,
                'right'          => false,
                'bottom'         => false,
                'left'           => true,
                'default'        => array(
                    'top'     => '15',
                    'left'    => '15',
                    'units'   => 'px'
                ),
                'output'         => array(
                    'top'     => '.c4d-woo-bundle-badge',
                    'left'    => '.c4d-woo-bundle-badge',

                )
            )
        )
    ));
}

function c4d_woo_bundle_plugin_row_meta($links, $file) {
    if ( 'c4d-woo-bundle/c4d-woo-bundle.php' == $file ) {
        $new_links = array(
            'demo' => '<a target="blank" href="http://demo.coffee4dev.com/product/long-sleeve-tee/">' . esc_html__( 'View Demo', 'c4d-woo-bundle' ) . '</a>',
            'pro' => '<a target="blank" href="http://coffee4dev.com/woocommerce-product-bundle/">' . esc_html__( 'Premium Version', 'c4d-woo-bundle' ) . '</a>',
            'options' => '<a target="blank" href="admin.php?page=c4d-plugin-manager">Settings</<a>'
        );
        if (!defined('C4DPMANAGER_PLUGIN_URI')) {
            $new_links['options'] = '<a target="blank" href="https://wordpress.org/plugins/c4d-plugin-manager/">Settings</<a>';
        }
        $links = array_merge( $links, $new_links );
    }
    return $links;
}



