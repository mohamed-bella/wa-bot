<?php
/**
 * Plugin Name:  Offer Form
 * Description: Single-page Cash on Delivery order form with product management and quantity-based offers.
 * Version:     2.0.0
 * Author:      MOHAMED BELLA
 * Author URI:  https://mohamedbella.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cod-smart-offer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ═══════════════════════════════════════════════
   1. CONSTANTS
═══════════════════════════════════════════════ */
define( 'CSO_VERSION',        '2.0.0' );
define( 'CSO_TABLE_ORDERS',   'cso_orders' );
define( 'CSO_TABLE_PRODUCTS', 'cso_products' );

/* ═══════════════════════════════════════════════
   2. ACTIVATION — Create DB tables
═══════════════════════════════════════════════ */
register_activation_hook( __FILE__, 'cso_activate' );
function cso_activate() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    /* ── Products table ── */
    $products = $wpdb->prefix . CSO_TABLE_PRODUCTS;
    $sql1 = "CREATE TABLE {$products} (
  id  bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  name  varchar(255) NOT NULL DEFAULT '',
  description  longtext,
  base_price  decimal(10,2) NOT NULL DEFAULT 0,
  offer_prices  text,
  colors  text,
  sizes  text,
  images  longtext,
  status  varchar(20) NOT NULL DEFAULT 'active',
  created_at  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) {$charset};";
    dbDelta( $sql1 );

    /* ── Orders table ── */
    $orders = $wpdb->prefix . CSO_TABLE_ORDERS;
    $sql2 = "CREATE TABLE {$orders} (
  id  bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id  bigint(20) UNSIGNED DEFAULT 0,
  full_name  varchar(255) NOT NULL,
  phone  varchar(50) NOT NULL,
  address  text NOT NULL,
  city  varchar(150) DEFAULT '',
  notes  text NOT NULL,
  offer_id  tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  quantity  tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  products_json  text NOT NULL,
  subtotal  decimal(10,2) NOT NULL DEFAULT 0,
  discount  decimal(10,2) NOT NULL DEFAULT 0,
  delivery_fee  decimal(10,2) NOT NULL DEFAULT 0,
  total  decimal(10,2) NOT NULL DEFAULT 0,
  status  varchar(20) NOT NULL DEFAULT 'new',
  created_at  datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (id)
) {$charset};";
    dbDelta( $sql2 );
}

/* ═══════════════════════════════════════════════
   3. ADMIN MENUS
═══════════════════════════════════════════════ */
add_action( 'admin_menu', 'cso_admin_menu' );
function cso_admin_menu() {
    add_menu_page( 'COD Shop', 'COD Shop', 'manage_options', 'cso-dashboard', 'cso_page_dashboard', 'dashicons-cart', 56 );
    add_submenu_page( 'cso-dashboard', 'Overview',   'Overview',   'manage_options', 'cso-dashboard',   'cso_page_dashboard' );
    add_submenu_page( 'cso-dashboard', 'Orders',     'Orders',     'manage_options', 'cso-orders',      'cso_page_orders' );
    add_submenu_page( 'cso-dashboard', 'Add New Product', 'Add New Product', 'manage_options', 'post-new.php?post_type=cso_product' );
    add_submenu_page( 'cso-dashboard', 'All Products', 'All Products', 'manage_options', 'edit.php?post_type=cso_product' );
    add_submenu_page( 'cso-dashboard', 'Forms & Codes', 'Forms & Codes', 'manage_options', 'cso-forms', 'cso_page_forms' );
    add_submenu_page( 'cso-dashboard', 'Mail Settings', 'Mail Settings', 'manage_options', 'cso-mail',  'cso_page_email_settings' );
    add_submenu_page( 'cso-dashboard', 'Bot WhatsApp',  'Bot WhatsApp',  'manage_options', 'cso-whatsapp', 'cso_page_whatsapp_settings' );
}

/** ── Sidebar Styling Overlay ── **/
add_action('admin_head', function(){
    ?>
    <style>
        /* Minimal layout for dynamic rows */
        .cso-offer-row, .cso-var-row { background:#fff; border:1px solid #c3c4c7; padding:15px; margin-bottom:10px; border-radius:4px; box-shadow:0 1px 1px rgba(0,0,0,0.04); }
        .cso-offer-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-weight:600; font-size:13px; color:#1d2327; }
        .cso-grid-3 { display:grid; grid-template-columns:80px 1fr 120px; gap:10px; margin-bottom:10px; align-items:end; }
        .cso-grid-3b { display:grid; grid-template-columns:120px 1fr 1fr; gap:10px; align-items:center; }
        .cso-pv-card { border:1px solid #dcdcde; background:#fff; padding:10px; margin-bottom:6px; display:flex; gap:10px; border-radius:4px; align-items:center; }
        .cso-pv-card.active { border-color:#2271b1; background:#f0f6fc; }
        .cso-pv-qty { width:32px; height:32px; background:#f0f0f1; display:flex; align-items:center; justify-content:center; font-weight:bold; border-radius:3px; }
        .cso-pv-info { flex:1; }
        .cso-pv-prices { text-align:right; font-weight:bold; }
        .cso-img-item { float:left; margin:0 10px 10px 0; position:relative; width:80px; height:80px; border:1px solid #c3c4c7; }
        .cso-img-item img { width:100%; height:100%; object-fit:cover; }
        .cso-remove-img { position:absolute; top:-8px; right:-8px; background:#d63638; color:#fff; border-radius:50%; width:20px; height:20px; text-align:center; line-height:20px; cursor:pointer; font-size:12px; }
    </style>
    <?php
});

/* ── Enqueue WP Media on product pages ── */
add_action( 'admin_enqueue_scripts', 'cso_admin_scripts' );
function cso_admin_scripts( $hook ) {
    global $post_type;
    $is_cso_page = ( strpos( $hook, 'cso-dashboard' ) !== false ) || ( strpos( $hook, 'cso-forms' ) !== false );
    $is_cso_post = ( $post_type === 'cso_product' );

    if ( $is_cso_page || $is_cso_post ) {
        wp_enqueue_media();
        // Removed custom font-awesome and bootstrap for native look
    }
}

/* ── Auto-create tables + handle save/delete BEFORE headers ── */
/* ═══════════════════════════════════════════════
   3a. HELPER FUNCTIONS
═══════════════════════════════════════════════ */
function cso_get_product_data( $product_id ) {
    $data = array(
        'name' => 'Product',
        'description' => '',
        'base_price' => 0,
        'offers' => [],
        'vars' => [],
        'images' => [],
        'status' => 'active',
        'layout_mode' => 'full'
    );

    if ( get_post_type( $product_id ) === 'cso_product' ) {
        $data['name'] = get_the_title( $product_id );
        $data['description'] = get_post_field( 'post_content', $product_id );
        $data['base_price'] = floatval( get_post_meta( $product_id, '_cso_base_price', true ) );
        
        // Offers
        $raw_offers = get_post_meta( $product_id, '_cso_offers_json', true );
        $doc = json_decode( $raw_offers, true );
        if ( is_array( $doc ) && !empty($doc) ) {
            foreach($doc as $idx => $o){ $o['id'] = $idx+1; $data['offers'][] = $o; }
        } else {
            // Default "Buy 1" offer if none configured
            $data['offers'][] = array(
                'id' => 1, 
                'qty' => 1, 
                'label' => 'Acheter 1 Article', 
                'price' => $data['base_price'], 
                'delivery_fee' => 0, 
                'free_delivery' => true, 
                'discount_enabled' => false
            );
        }

        // Vars
        $raw_vars = get_post_meta( $product_id, '_cso_variations_json', true );
        $data['vars'] = json_decode( $raw_vars, true ) ?: [];

        // Images
        $thumb = get_the_post_thumbnail_url( $product_id, 'full' );
        if ( $thumb ) $data['images'][] = $thumb;
        $raw_imgs = get_post_meta( $product_id, '_cso_images_json', true );
        $gallery = json_decode( $raw_imgs, true ) ?: [];
        $data['images'] = array_merge( $data['images'], $gallery );

        $data['layout_mode'] = get_post_meta( $product_id, '_cso_layout_mode', true ) ?: 'full';

    } elseif ( $product_id > 0 ) {
        // Legacy fallback
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . CSO_TABLE_PRODUCTS . " WHERE id = %d", $product_id ), ARRAY_A );
        if ( $row ) {
            $data['name'] = $row['name'];
            $data['description'] = $row['description'];
            $data['base_price'] = floatval( $row['base_price'] );
            $data['status'] = $row['status'];
            
            // Legacy Offers Logic
            $opr = json_decode( $row['offer_prices'] ?? '', true );
            if ( is_array( $opr ) ) {
                if ( isset($opr['offer1']) || isset($opr['offer2']) ) {
                     for($i=1; $i<=3; $i++){
                        $p = floatval($opr['offer'.$i] ?? 0);
                        if($p > 0) {
                            $l = $i===1 ? 'Buy 1' : ( $i===2 ? 'Buy 2' : 'Buy 3' );
                            $data['offers'][] = array('id'=>$i, 'qty'=>$i, 'label'=>$l, 'price'=>$p, 'delivery_fee'=>0, 'free_delivery'=>true, 'discount_enabled'=>true);
                        }
                     }
                } else {
                    foreach($opr as $idx => $o){ $o['id'] = $idx + 1; $data['offers'][] = $o; }
                }
            }
            if(empty($data['offers'])){
                 $data['offers'][] = array('id'=>1, 'qty'=>1, 'label'=>'Buy 1', 'price'=>$data['base_price'], 'delivery_fee'=>0, 'free_delivery'=>true, 'discount_enabled'=>true);
            }

            // Legacy Vars
            $colors_raw = $row['colors'] ?? '';
            $vtest = json_decode($colors_raw, true);
            if ( is_array($vtest) && !empty($vtest) && isset($vtest[0]['key']) ) { $data['vars'] = $vtest; }
            else {
                if ( !empty($colors_raw) ) $data['vars'][] = array('key'=>'Color', 'values'=>array_map('trim', explode(',', $colors_raw)));
                if ( !empty($row['sizes']) ) $data['vars'][] = array('key'=>'Size', 'values'=>array_map('trim', explode(',', $row['sizes'])));
            }

            // Legacy Images
            $imgs = json_decode( $row['images'], true );
            if ( is_array( $imgs ) ) $data['images'] = $imgs;
        }
    }
    return $data;
}

/* ═══════════════════════════════════════════════
   3b. CPT REGISTRATION (Dynamic Products)
═══════════════════════════════════════════════ */
add_action( 'init', 'cso_register_cpt' );
function cso_register_cpt() {
    register_post_type( 'cso_product', array(
        'labels' => array(
            'name'          => 'COD Products',
            'singular_name' => 'COD Product',
            'menu_name'     => 'COD Products',
            'add_new'       => 'Add New Product',
            'add_new_item'  => 'Add New COD Product',
            'edit_item'     => 'Edit COD Product',
        ),
        'public'        => true,
        'has_archive'   => true,
        'supports'      => array( 'title', 'editor', 'thumbnail' ), // Thumb used for main image
        'show_in_menu'  => false,
        'rewrite'       => array( 'slug' => 'produit', 'with_front' => false ),
    ) );
}

/* ═══════════════════════════════════════════════
   3b. META BOXES (Configuration)
═══════════════════════════════════════════════ */
add_action( 'add_meta_boxes', 'cso_add_meta_boxes' );
function cso_add_meta_boxes() {
    add_meta_box( 'cso_product_config', 'Product Configuration', 'cso_render_meta_box', 'cso_product', 'normal', 'high' );
}

function cso_render_meta_box( $post ) {
    wp_nonce_field( 'cso_save_meta_box', 'cso_meta_box_nonce' );

    $base_price = get_post_meta( $post->ID, '_cso_base_price', true );
    $offers_json = get_post_meta( $post->ID, '_cso_offers_json', true );
    $vars_json   = get_post_meta( $post->ID, '_cso_variations_json', true );
    $imgs_json   = get_post_meta( $post->ID, '_cso_images_json', true ); 
    $layout_mode = get_post_meta( $post->ID, '_cso_layout_mode', true ) ?: 'full';

    // Fallback defaults
    if ( empty( $base_price ) ) $base_price = 0;
    if ( empty( $offers_json ) ) $offers_json = '[]';
    if ( empty( $vars_json ) ) $vars_json = '[]';
    if ( empty( $imgs_json ) ) $imgs_json = '[]';

    // Decode for JS
    $offers_data = json_decode( $offers_json, true ) ?: [];
    // Auto-migrate legacy if raw object (rare here, but safe)
    if ( isset($offers_data['offer1']) ) {
        $migrated = [];
        for($i=1; $i<=3; $i++){
            $p = floatval($offers_data['offer'.$i] ?? 0);
            if($p > 0) $migrated[] = ['qty'=>$i, 'label'=>"Buy $i", 'price'=>$p, 'delivery_fee'=>0];
        }
        $offers_data = $migrated;
    }
    
    $vars_data = json_decode( $vars_json, true ) ?: [];
    $images_data = json_decode( $imgs_json, true ) ?: [];
    ?>
    <style>
        /* Minimal layout for dynamic rows inside Meta Box */
        .cso-offer-row, .cso-var-row { background:#fff; border:1px solid #c3c4c7; padding:15px; margin-bottom:10px; border-radius:4px; box-shadow:0 1px 1px rgba(0,0,0,0.04); }
        .cso-offer-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-weight:600; font-size:13px; color:#1d2327; }
        .cso-grid-3 { display:grid; grid-template-columns:80px 1fr 120px; gap:10px; margin-bottom:10px; align-items:end; }
        .cso-grid-3b { display:grid; grid-template-columns:120px 1fr 1fr; gap:10px; align-items:center; }
        .cso-img-item { float:left; margin:0 10px 10px 0; position:relative; width:80px; height:80px; border:1px solid #c3c4c7; }
        .cso-img-item img { width:100%; height:100%; object-fit:cover; }
        .cso-remove-img { position:absolute; top:-8px; right:-8px; background:#d63638; color:#fff; border-radius:50%; width:20px; height:20px; text-align:center; line-height:20px; cursor:pointer; font-size:12px; }
    </style>

    <p><strong>Use this shortcode:</strong> <code>[cod_smart_offer_form]</code> (Auto-detects active product)</p>

    <table class="form-table">
        <tr>
            <th><label>Display Layout</label></th>
            <td>
                <select name="cso_layout_mode">
                    <option value="full" <?php selected($layout_mode, 'full'); ?>>Full Product (Gallery + Form)</option>
                    <option value="form_only" <?php selected($layout_mode, 'form_only'); ?>>Form Only (No Gallery/Title)</option>
                </select>
                <p class="description">Choose how the product page should appear by default.</p>
            </td>
        </tr>
        <tr>
            <th><label>Base Price (DH)</label></th>
            <td><input type="number" name="cso_base_price" value="<?php echo esc_attr( $base_price ); ?>" step="0.01" class="small-text" id="cso-baseprice"></td>
        </tr>
    </table>

    <hr>

    <h3><i class="fa-solid fa-box-open"></i> Offers Configuration</h3>
    <div id="cso-offers-container"></div>
    <p><button type="button" class="button button-secondary" id="cso-add-offer"><span class="dashicons dashicons-plus-alt2"></span> Add Offer</button></p>

    <hr>

    <h3><i class="fa-solid fa-palette"></i> Variations</h3>
    <div id="cso-vars-container"></div>
    <p><button type="button" class="button button-secondary" id="cso-add-var"><span class="dashicons dashicons-plus-alt2"></span> Add Variation</button></p>
    
    <hr>

    <h3><i class="fa-solid fa-images"></i> Additional Images</h3>
    <div id="cso-images-preview" style="overflow:hidden; margin-bottom:10px;"></div>
    <button type="button" id="cso-add-images" class="button button-secondary">Add Images</button>
    <p class="description">Main thumbnail is managed via standard "Featured Image" box.</p>

    <!-- Hidden Inputs for JSON -->
    <input type="hidden" name="cso_offers_json" id="cso-offers-data" value="">
    <input type="hidden" name="cso_vars_json" id="cso-variations-data" value="">
    <input type="hidden" name="cso_images_json" id="cso-images-json" value="">

    <script>
    jQuery(function($){
        // ── OFFERS ──
        var offers = <?php echo wp_json_encode($offers_data); ?>;
        var MAX_OFFERS = 5;
        function renderOffers(){
            var c = $('#cso-offers-container'); c.empty();
            offers.forEach(function(o, i){
                var html = '<div class="cso-offer-row" data-idx="'+i+'">';
                html += '<div class="cso-offer-head"><span>Offer #'+(i+1)+'</span><a href="#" class="cso-remove-offer" data-idx="'+i+'" style="color:#b32d2e;text-decoration:none;">Remove</a></div>';
                html += '<div class="cso-grid-3">';
                html += '<div><label>Qty</label><input type="number" class="small-text cso-of-qty" data-idx="'+i+'" value="'+(o.qty||1)+'" min="1"></div>';
                html += '<div><label>Label</label><input type="text" class="regular-text cso-of-label" style="width:100%" data-idx="'+i+'" value="'+escHtml(o.label||'')+'" placeholder="e.g. Buy 1 Article"></div>';
                html += '<div><label>Price</label><input type="number" class="small-text cso-of-price" data-idx="'+i+'" value="'+(o.price||0)+'" step="0.01"></div>';
                html += '</div>';
                html += '<div class="cso-grid-3b">';
                html += '<div><label>Delivery Fee</label><input type="number" class="small-text cso-of-delfee" data-idx="'+i+'" value="'+(o.delivery_fee||0)+'" step="0.01"></div>';
                html += '<label><input type="checkbox" class="cso-of-freedel" data-idx="'+i+'" '+(o.free_delivery?'checked':'')+' /> Free Delivery</label>';
                html += '<label><input type="checkbox" class="cso-of-disc" data-idx="'+i+'" '+(o.discount_enabled?'checked':'')+' /> Show Discount</label>';
                html += '</div></div>';
                c.append(html);
            });
            $('#cso-offers-data').val(JSON.stringify(offers));
        }
        function escHtml(s){ return $('<div>').text(s).html(); }
        $('#cso-add-offer').on('click', function(){
            if(offers.length >= MAX_OFFERS) return;
            var q = offers.length + 1;
            offers.push({qty:q, label:'Buy '+q, price:0, delivery_fee:0, free_delivery:true, discount_enabled:true});
            renderOffers();
        });
        $(document).on('click', '.cso-remove-offer', function(e){ e.preventDefault(); offers.splice($(this).data('idx'), 1); renderOffers(); });
        $(document).on('input change', '.cso-of-qty', function(){ offers[$(this).data('idx')].qty = parseInt($(this).val())||1; $('#cso-offers-data').val(JSON.stringify(offers)); });
        $(document).on('input change', '.cso-of-label', function(){ offers[$(this).data('idx')].label = $(this).val(); $('#cso-offers-data').val(JSON.stringify(offers)); });
        $(document).on('input change', '.cso-of-price', function(){ offers[$(this).data('idx')].price = parseFloat($(this).val())||0; $('#cso-offers-data').val(JSON.stringify(offers)); });
        $(document).on('input change', '.cso-of-delfee', function(){ offers[$(this).data('idx')].delivery_fee = parseFloat($(this).val())||0; $('#cso-offers-data').val(JSON.stringify(offers)); });
        $(document).on('change', '.cso-of-freedel', function(){ offers[$(this).data('idx')].free_delivery = $(this).is(':checked'); $('#cso-offers-data').val(JSON.stringify(offers)); });
        $(document).on('change', '.cso-of-disc', function(){ offers[$(this).data('idx')].discount_enabled = $(this).is(':checked'); $('#cso-offers-data').val(JSON.stringify(offers)); });

        // ── VARS ──
        var vars = <?php echo wp_json_encode($vars_data); ?>;
        function renderVars(){
            var c = $('#cso-vars-container'); c.empty();
            vars.forEach(function(v, i){
                var html = '<div class="cso-var-row" data-idx="'+i+'">';
                html += '<input type="text" class="regular-text cso-var-key" data-idx="'+i+'" value="'+escHtml(v.key||'')+'" placeholder="Key (Size)"> ';
                html += '<input type="text" class="large-text cso-var-vals" data-idx="'+i+'" value="'+escHtml((v.values||[]).join(','))+'" placeholder="Values (S,M,L)"> ';
                html += '<button type="button" class="button cso-var-remove" data-idx="'+i+'">&times;</button></div>';
                c.append(html);
            });
            $('#cso-variations-data').val(JSON.stringify(vars));
        }
        $('#cso-add-var').on('click', function(){ vars.push({key:'', values:[]}); renderVars(); });
        $(document).on('click', '.cso-var-remove', function(){ vars.splice($(this).data('idx'), 1); renderVars(); });
        $(document).on('input', '.cso-var-key', function(){ vars[$(this).data('idx')].key = $(this).val(); $('#cso-variations-data').val(JSON.stringify(vars)); });
        $(document).on('input', '.cso-var-vals', function(){
            vars[$(this).data('idx')].values = $(this).val().split(',').map(function(s){return s.trim()}).filter(function(s){return s!==''});
            $('#cso-variations-data').val(JSON.stringify(vars));
        });

        // ── IMAGES ──
        var images = <?php echo wp_json_encode($images_data); ?>;
        function renderImg(){
            var c = $('#cso-images-preview'); c.empty();
            images.forEach(function(url, i){
                c.append('<div class="cso-img-item"><img src="'+url+'"><span class="cso-remove-img" data-idx="'+i+'">&times;</span></div>');
            });
            $('#cso-images-json').val(JSON.stringify(images));
        }
        $('#cso-add-images').on('click', function(e){
            e.preventDefault();
            var frame = wp.media({ title:'Select Images', multiple:true, library:{type:'image'} });
            frame.on('select', function(){
                frame.state().get('selection').each(function(att){ images.push(att.attributes.url); });
                renderImg();
            });
            frame.open();
        });
        $(document).on('click', '.cso-remove-img', function(){ images.splice($(this).data('idx'), 1); renderImg(); });

        // Init
        renderOffers(); renderVars(); renderImg();
    });
    </script>
    <?php
}

/* ═══════════════════════════════════════════════
   3c. SAVE META BOX
═══════════════════════════════════════════════ */
add_action( 'save_post', 'cso_save_meta_box_data' );
function cso_save_meta_box_data( $post_id ) {
    if ( ! isset( $_POST['cso_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['cso_meta_box_nonce'], 'cso_save_meta_box' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['cso_base_price'] ) ) update_post_meta( $post_id, '_cso_base_price', floatval( $_POST['cso_base_price'] ) );
    if ( isset( $_POST['cso_layout_mode'] ) ) update_post_meta( $post_id, '_cso_layout_mode', sanitize_text_field( $_POST['cso_layout_mode'] ) );
    if ( isset( $_POST['cso_offers_json'] ) ) update_post_meta( $post_id, '_cso_offers_json', wp_kses_post( stripslashes( $_POST['cso_offers_json'] ) ) );
    if ( isset( $_POST['cso_vars_json'] ) ) update_post_meta( $post_id, '_cso_variations_json', wp_kses_post( stripslashes( $_POST['cso_vars_json'] ) ) );
    if ( isset( $_POST['cso_images_json'] ) ) update_post_meta( $post_id, '_cso_images_json', wp_kses_post( stripslashes( $_POST['cso_images_json'] ) ) );
}

/* ═══════════════════════════════════════════════
   3d. DUPLICATE PRODUCT FEATURE
═══════════════════════════════════════════════ */

// Add "Duplicate" row action link on the product list screen
add_filter( 'post_row_actions', 'cso_duplicate_product_link', 10, 2 );
function cso_duplicate_product_link( $actions, $post ) {
    if ( $post->post_type !== 'cso_product' ) return $actions;
    if ( ! current_user_can( 'edit_posts' ) ) return $actions;

    $url = wp_nonce_url(
        admin_url( 'admin.php?action=cso_duplicate_product&post=' . $post->ID ),
        'cso_duplicate_' . $post->ID
    );
    $actions['duplicate'] = '<a href="' . esc_url( $url ) . '">Duplicate</a>';
    return $actions;
}

// Handle the actual duplication
add_action( 'admin_action_cso_duplicate_product', 'cso_handle_duplicate_product' );
function cso_handle_duplicate_product() {
    if ( ! isset( $_GET['post'] ) || ! isset( $_GET['_wpnonce'] ) ) {
        wp_die( 'Missing parameters.' );
    }

    $original_id = intval( $_GET['post'] );

    if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'cso_duplicate_' . $original_id ) ) {
        wp_die( 'Security check failed.' );
    }

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_die( 'You do not have permission to duplicate products.' );
    }

    $original = get_post( $original_id );
    if ( ! $original || $original->post_type !== 'cso_product' ) {
        wp_die( 'Original product not found.' );
    }

    // Create the duplicate post as a draft
    $new_id = wp_insert_post( array(
        'post_title'   => $original->post_title . ' (Copy)',
        'post_content' => $original->post_content,
        'post_status'  => 'draft',
        'post_type'    => 'cso_product',
        'post_author'  => get_current_user_id(),
    ) );

    if ( is_wp_error( $new_id ) ) {
        wp_die( 'Error creating duplicate: ' . $new_id->get_error_message() );
    }

    // Copy all custom meta fields
    $meta_keys = array(
        '_cso_base_price',
        '_cso_offers_json',
        '_cso_variations_json',
        '_cso_images_json',
        '_cso_layout_mode',
        '_thumbnail_id',
    );

    foreach ( $meta_keys as $key ) {
        $value = get_post_meta( $original_id, $key, true );
        if ( $value !== '' ) {
            update_post_meta( $new_id, $key, $value );
        }
    }

    // Redirect to the edit page of the new duplicate
    wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_id ) );
    exit;
}

add_action( 'admin_init', 'cso_admin_init' );
function cso_admin_init() {
    /* Auto-create tables if missing */
    global $wpdb;
    $pt = $wpdb->prefix . CSO_TABLE_PRODUCTS;
    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$pt}'" ) !== $pt ) {
          // Keep legacy table creation for old data safety
          cso_activate();
    }
    // ... Legacy handle code continues below ...
}

/* ═══════════════════════════════════════════════
   3e. ADMIN — OVERVIEW DASHBOARD
   ═══════════════════════════════════════════════ */
function cso_page_dashboard() {
    global $wpdb;
    $ot = $wpdb->prefix . CSO_TABLE_ORDERS;
    
    // Stats gathering
    $total_orders = $wpdb->get_var( "SELECT COUNT(*) FROM {$ot}" ) ?: 0;
    $new_orders   = $wpdb->get_var( "SELECT COUNT(*) FROM {$ot} WHERE status = 'new'" ) ?: 0;
    $confirmed    = $wpdb->get_var( "SELECT COUNT(*) FROM {$ot} WHERE status = 'confirmed'" ) ?: 0;
    $delivered    = $wpdb->get_var( "SELECT COUNT(*) FROM {$ot} WHERE status = 'delivered'" ) ?: 0;
    $total_rev    = $wpdb->get_var( "SELECT SUM(total) FROM {$ot} WHERE status IN ('confirmed','delivered','shipped')" ) ?: 0;
    
    $product_count = wp_count_posts('cso_product')->publish;
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">COD Shop Overview</h1>
        <hr class="wp-header-end">

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-top: 20px;">
            <!-- Revenue Widget -->
            <div class="card" style="background: #10b981; color: #fff; padding: 20px; border-radius: 12px; border: none; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);">
                <div style="font-size: 14px; font-weight: 600; opacity: 0.9;">Total Revenue</div>
                <div style="font-size: 32px; font-weight: 900; margin: 10px 0;"><?php echo number_format($total_rev, 2); ?> <small style="font-size: 16px;">dh</small></div>
                <div style="font-size: 12px; opacity: 0.8;">From confirmed & delivered orders</div>
            </div>

            <!-- Orders Widget -->
            <div class="card" style="padding: 20px; border-radius: 12px; border: 1px solid #ccd0d4; background: #fff;">
                <div style="font-size: 14px; color: #64748b; font-weight: 600;">Active Orders</div>
                <div style="font-size: 32px; font-weight: 900; color: #1e293b; margin: 10px 0;"><?php echo $total_orders; ?> <small style="font-size: 16px; color: #94a3b8;">Total</small></div>
                <div style="display: flex; gap: 10px; font-size: 12px;">
                    <span style="color: #3b82f6; font-weight: 700;"><?php echo $new_orders; ?> New</span>
                    <span style="color: #f59e0b; font-weight: 700;"><?php echo $confirmed; ?> Confirmed</span>
                </div>
            </div>

            <!-- Products Widget -->
            <div class="card" style="padding: 20px; border-radius: 12px; border: 1px solid #ccd0d4; background: #fff;">
                <div style="font-size: 14px; color: #64748b; font-weight: 600;">Catalog Size</div>
                <div style="font-size: 32px; font-weight: 900; color: #1e293b; margin: 10px 0;"><?php echo $product_count; ?> <small style="font-size: 16px; color: #94a3b8;">Products</small></div>
                <a href="<?php echo admin_url('edit.php?post_type=cso_product'); ?>" style="text-decoration: none; font-size: 12px; color: #3b82f6; font-weight: 600;">Manage Products &rarr;</a>
            </div>
        </div>

        <div style="margin-top: 30px; display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
            <!-- Quick Actions -->
            <div class="card" style="padding: 25px; border-radius: 12px; border: 1px solid #ccd0d4; background: #fff;">
                <h2 style="margin-top: 0; font-size: 18px;">Quick Actions</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px;">
                    <a href="<?php echo admin_url('post-new.php?post_type=cso_product'); ?>" class="button button-primary button-large" style="height: 50px; line-height: 48px; text-align: center; border-radius: 8px;">Create New Product</a>
                    <a href="<?php echo admin_url('admin.php?page=cso-orders'); ?>" class="button button-large" style="height: 50px; line-height: 48px; text-align: center; border-radius: 8px;">View All Orders</a>
                    <a href="<?php echo admin_url('admin.php?page=cso-forms'); ?>" class="button button-large" style="height: 50px; line-height: 48px; text-align: center; border-radius: 8px;">Get Shortcodes</a>
                </div>
            </div>

            <!-- Getting Started -->
            <div class="card" style="padding: 25px; border-radius: 12px; border: 1px solid #ccd0d4; background: #fff; position: relative; overflow: hidden;">
                <div style="position: absolute; top: -10px; right: -10px; font-size: 60px; color: #f1f5f9; z-index: 0;"><i class="fa-solid fa-rocket"></i></div>
                <div style="position: relative; z-index: 1;">
                    <h2 style="margin-top: 0; font-size: 18px;">Performance Tip</h2>
                    <p style="color: #64748b; font-size: 14px; line-height: 1.5;">To increase conversion, try setting a product to <strong>"Form Only"</strong> layout when running Facebook Ads directly to the landing page.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════
   4. ADMIN — FORMS & SHORTCODES DASHBOARD
   ═══════════════════════════════════════════════ */
function cso_page_forms() {
    $args = array( 'post_type' => 'cso_product', 'posts_per_page' => -1, 'post_status' => 'any' );
    $q = new WP_Query( $args );
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Forms & Shortcodes</h1>
        <a href="<?php echo admin_url('post-new.php?post_type=cso_product'); ?>" class="page-title-action">Create New Product</a>
        <hr class="wp-header-end">

        <table class="wp-list-table widefat fixed striped mt-4">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th style="width: 20%;">Product Name</th>
                    <th style="width: 15%;">Layout Mode</th>
                    <th style="width: 10%;">Base Price</th>
                    <th>Form Shortcodes</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( $q->have_posts() ) : ?>
                <?php while ( $q->have_posts() ) : $q->the_post(); 
                    $pid  = get_the_ID();
                    $data = cso_get_product_data( $pid );
                    $is_form_only = ($data['layout_mode'] === 'form_only');
                    $row_style = $is_form_only ? 'background-color: #fff8e5;' : '';
                ?>
                <tr style="<?php echo $row_style; ?>">
                    <td><code style="font-weight: bold; background: #eee; padding: 2px 5px; border-radius: 3px;">#<?php echo $pid; ?></code></td>
                    <td><strong><a href="<?php echo get_edit_post_link($pid); ?>" style="font-size: 14px;"><?php the_title(); ?></a></strong></td>
                    <td>
                        <?php if ( $is_form_only ) : ?>
                            <span class="badge" style="background: #f59e0b; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; text-transform: uppercase;">Form Only</span>
                        <?php else : ?>
                            <span class="badge" style="background: #10b981; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: 800; text-transform: uppercase;">Full Product</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo number_format($data['base_price'], 2); ?> dh</strong></td>
                    <td>
                        <div style="margin-bottom: 8px;">
                            <span class="description" style="font-weight: 600; color: #555;">Full Page Card:</span> <code style="display: block; margin-top: 3px;">[cod_smart_offer_form product="<?php echo $pid; ?>"]</code>
                        </div>
                        <div>
                            <span class="description" style="font-weight: 600; color: #555;">Form Only (Clean):</span> <code style="display: block; margin-top: 3px;">[cso_checkout_form product="<?php echo $pid; ?>"]</code>
                        </div>
                    </td>
                    <td>
                        <a href="<?php echo get_edit_post_link($pid); ?>" class="button button-small">Edit Logic</a>
                        <a href="<?php echo get_permalink($pid); ?>" target="_blank" class="button button-small">View</a>
                    </td>
                </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            <?php else : ?>
                <tr><td colspan="6">No products found. Start by creating your first COD Product.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div class="card" style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); border-radius: 8px;">
            <h2 style="margin-top: 0;">Quick Tips & Integration</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <p><strong>1. Full Product Form:</strong> Use the default shortcode or just point customers to the product's permalink. It behaves like a traditional landing page.</p>
                </div>
                <div>
                    <p><strong>2. Checkout Form Only:</strong> This shortcode is stripped of design headers. It's best used when you're building a custom page with Elementor or Gutenberg.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════
   5. ADMIN — ORDERS PAGE
═══════════════════════════════════════════════ */
function cso_page_orders() {
    /* Handle status update */
    if (
        isset( $_POST['cso_update_status'], $_POST['cso_order_id'], $_POST['cso_new_status'] ) &&
        check_admin_referer( 'cso_update_status_' . intval( $_POST['cso_order_id'] ) )
    ) {
        $new_status = sanitize_text_field( $_POST['cso_new_status'] );
        $order_id = intval( $_POST['cso_order_id'] );
        
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . CSO_TABLE_ORDERS,
            array( 'status' => $new_status ),
            array( 'id'     => $order_id ),
            array( '%s' ),
            array( '%d' )
        );

        // Map WP internal statuses to Bot required statuses
        $status_map = array(
            'new' => 'Pending',
            'confirmed' => 'Confirmed',
            'shipped' => 'Confirmed',
            'delivered' => 'Completed',
            'cancelled' => 'Cancelled'
        );
        $mapped_status = $status_map[$new_status] ?? 'Pending';
        
        // Sync to WhatsApp Bot
        wp_remote_request( "https://996b-2a01-4f9-c013-e885-00-1.ngrok-free.app/api/confirmation", array(
            'method'   => 'POST',
            'blocking' => false, // Async
            'headers'  => array('Content-Type' => 'application/json', 'x-api-key' => 'basma_api_secret_2024'),
            'body'     => wp_json_encode(array(
                'phone'        => $o['phone'],
                'customerName' => $o['full_name'],
                'orderNumber'  => $order_id,
            ))
        ));

        echo '<div class="notice notice-success is-dismissible"><p>Statut mis à jour et client notifié sur WhatsApp !</p></div>';
    }

    global $wpdb;
    $ot = $wpdb->prefix . CSO_TABLE_ORDERS;
    $orders = $wpdb->get_results( "SELECT * FROM {$ot} ORDER BY created_at DESC", ARRAY_A );
    $statuses = array( 'new', 'confirmed', 'shipped', 'delivered', 'cancelled' );

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Orders Dashboard</h1>
        <hr class="wp-header-end">

        <table class="wp-list-table widefat fixed striped mt-4">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Customer</th>
                    <th>Products & Qty</th>
                    <th>Financials</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $orders ) ) : ?>
                <tr><td colspan="6">No orders found.</td></tr>
            <?php else : ?>
                <?php foreach ( $orders as $o ) : ?>
                <tr>
                    <td><strong>#<?php echo $o['id']; ?></strong></td>
                    <td>
                        <strong><?php echo esc_html($o['full_name']); ?></strong><br>
                        <code><?php echo esc_html($o['phone']); ?></code><br>
                        <span class="description"><?php echo esc_html($o['address']); ?></span>
                    </td>
                    <td>
                        <div style="margin-top: 5px; font-size: 13px; color: #333;">
                            <?php
                            $prods = json_decode( $o['products_json'] ?? '[]', true );
                            
                            // Check if it's the new multi-product format
                            if ( is_array( $prods ) && count( $prods ) > 0 && isset($prods[0]['id']) ) {
                                echo '<ul style="margin:0; padding-left:15px;">';
                                foreach ( $prods as $pr ) {
                                    $qty = intval($pr['qty'] ?? 1);
                                    $name = esc_html($pr['name'] ?? 'Article');
                                    
                                    // Parse variations into a string if they exist
                                    $var_str = '';
                                    if ( !empty($pr['variations']) && is_array($pr['variations']) ) {
                                        $v_pairs = [];
                                        foreach($pr['variations'] as $k => $v) { $v_pairs[] = "$k: $v"; }
                                        $var_str = ' <small style="color:#666;">(' . implode(', ', $v_pairs) . ')</small>';
                                    }
                                    
                                    echo "<li><strong>{$qty}x</strong> {$name}{$var_str}</li>";
                                }
                                echo '</ul>';
                            } else {
                                // Fallback to legacy single product display
                                $pdata = cso_get_product_data($o['product_id']);
                                $pname = esc_html($pdata['name']);
                                echo "<span class='badge' style='background:#ddd; padding: 2px 6px; border-radius: 3px; font-size: 11px;'>Qty: {$o['quantity']}</span> ";
                                echo "<strong>{$pname}</strong>";
                            }
                            ?>
                        </div>
                    </td>
                    <td>
                        <strong><?php echo number_format($o['total'], 2); ?> dh</strong><br>
                        <small class="description">Disc: <?php echo number_format($o['discount'], 2); ?></small>
                    </td>
                    <td>
                        <strong><?php echo strtoupper($o['status']); ?></strong><br>
                        <small class="description"><?php echo date_i18n( 'M j, H:i', strtotime( $o['created_at'] ) ); ?></small>
                    </td>
                    <td>
                        <form method="post" style="display: flex; gap: 5px; align-items: center;">
                            <?php wp_nonce_field( 'cso_update_status_' . $o['id'] ); ?>
                            <input type="hidden" name="cso_order_id" value="<?php echo $o['id']; ?>">
                            <input type="hidden" name="cso_update_status" value="1">
                            <select name="cso_new_status" style="height: 30px; line-height: 1;">
                                <?php foreach ( $statuses as $s ) : ?>
                                    <option value="<?php echo $s; ?>" <?php selected( $o['status'], $s ); ?>><?php echo ucfirst( $s ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="button button-small">Update</button>
                            <button type="button" class="button button-small wap-msg-btn" data-phone="<?php echo esc_attr($o['phone']); ?>" data-name="<?php echo esc_attr($o['full_name']); ?>" style="color: #25D366; border-color: #25D366;">
                                <i class="fab fa-whatsapp"></i> Message
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- WAP MODAL FOR CUSTOM MESSAGES -->
    <div id="wap-msg-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:99999; align-items:center; justify-content:center;">
        <div style="background:#fff; width:450px; border-radius:12px; padding:25px; box-shadow:0 10px 40px rgba(0,0,0,0.2);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="margin:0; font-size:18px; color:#1e293b;">Message à <span id="wap-msg-name" style="color:#25d366;">Client</span></h2>
                <button type="button" id="wap-msg-close" style="background:none; border:none; font-size:20px; cursor:pointer; color:#64748b;">&times;</button>
            </div>
            <p style="font-size:13px; color:#64748b; margin-bottom:15px;">Le message sera envoyé immédiatement sur WhatsApp au : <strong id="wap-msg-phone"></strong></p>
            <textarea id="wap-msg-body" rows="5" style="width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:12px; font-size:14px; margin-bottom:20px;" placeholder="Écrivez votre message ici... (ex: Bonjour, votre commande a un retard...)"></textarea>
            <button type="button" id="wap-msg-send" class="button button-primary button-large" style="width:100%; background:#25d366; border-color:#25d366; color:#fff;">
                Envoyer le Message <i class="fas fa-paper-plane" style="margin-left:5px;"></i>
            </button>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('wap-msg-modal');
        const closeBtn = document.getElementById('wap-msg-close');
        const sendBtn = document.getElementById('wap-msg-send');
        const msgBody = document.getElementById('wap-msg-body');
        
        let currentPhone = '';
        const ajaxUrl = '<?php echo esc_js(admin_url("admin-ajax.php")); ?>';
        const msgNonce = '<?php echo esc_js(wp_create_nonce("cso_wa_msg_nonce")); ?>';

        // Open modal
        document.querySelectorAll('.wap-msg-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                let phone = this.dataset.phone;
                // clean up phone just in case to ensure it's valid format
                phone = phone.replace(/\\D/g, '');
                if(phone.startsWith('0')) { phone = '212' + phone.substring(1); }
                currentPhone = phone;
                
                document.getElementById('wap-msg-name').textContent = this.dataset.name;
                document.getElementById('wap-msg-phone').textContent = phone;
                msgBody.value = ''; // clear previous
                modal.style.display = 'flex';
            });
        });

        // Close modal
        closeBtn.addEventListener('click', () => modal.style.display = 'none');
        modal.addEventListener('click', (e) => {
            if(e.target === modal) modal.style.display = 'none';
        });

        // Send Message
        sendBtn.addEventListener('click', async function() {
            const txt = msgBody.value.trim();
            if(!txt) return alert("Veuillez écrire un message.");
            
            const originalText = sendBtn.innerHTML;
            sendBtn.innerHTML = 'Envoi... <i class="fas fa-spinner fa-spin"></i>';
            sendBtn.disabled = true;

            const fd = new FormData();
            fd.append('action', 'cso_send_manual_wa_msg');
            fd.append('nonce', msgNonce);
            fd.append('phone', currentPhone);
            fd.append('message', txt);

            try {
                const res = await fetch(ajaxUrl, {
                    method: 'POST',
                    body: fd
                });
                const data = await res.json();
                
                if(data.success) {
                    alert('Message envoyé avec succès !');
                    modal.style.display = 'none';
                } else {
                    alert('Erreur: ' + (data.data || "Impossible de l'envoyer"));
                }
            } catch(e) {
                alert('Erreur réseau ou connexion au serveur.');
            }
            
            sendBtn.innerHTML = originalText;
            sendBtn.disabled = false;
        });
    });
    </script>
    <?php
}

/* ═══════════════════════════════════════════════
   5b. AJAX: MANUAL WHATSAPP MESSAGE
═══════════════════════════════════════════════ */
add_action( 'wp_ajax_cso_send_manual_wa_msg', 'cso_send_manual_wa_msg_handler' );
function cso_send_manual_wa_msg_handler() {
    check_ajax_referer( 'cso_wa_msg_nonce', 'nonce' );
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied.' );
        wp_die();
    }

    $phone   = sanitize_text_field( $_POST['phone'] ?? '' );
    $message = sanitize_textarea_field( $_POST['message'] ?? '' );

    if ( empty( $phone ) || empty( $message ) ) {
        wp_send_json_error( 'Phone and message are required.' );
        wp_die();
    }

    if ( function_exists( 'basma_bot_send_message' ) ) {
        $success = basma_bot_send_message( $phone, $message );
        if ( $success ) {
            wp_send_json_success( 'Message Sent' );
        } else {
            wp_send_json_error( 'Failed to dispatch to WhatsApp Bot API.' );
        }
    } else {
        wp_send_json_error( 'Basma Bot plugin is missing or inactive.' );
    }
    wp_die();
}

/* ═══════════════════════════════════════════════
   6. SHORTCODE — [cod_smart_offer_form product="ID"]
═══════════════════════════════════════════════ */
add_shortcode( 'cod_smart_offer_form', 'cso_render_form' );
add_shortcode( 'cso_checkout_form', 'cso_render_checkout_form' );

function cso_render_checkout_form( $atts ) {
    $atts['layout'] = 'form_only';
    return cso_render_form( $atts );
}

function cso_render_form( $atts ) {
    $atts = shortcode_atts( array(
        'product'      => 0,
        'currency'     => 'dh',
        'delivery_fee' => 0,
        'layout'       => 'full', // 'full' or 'form_only'
    ), $atts, 'cod_smart_offer_form' );

    $product_id   = intval( $atts['product'] );
    // Auto-detect dynamic ID
    if ( $product_id === 0 && ( is_single() || is_page() ) ) {
        $product_id = get_the_ID();
    }

    $currency     = esc_attr( $atts['currency'] );
    $delivery_fee = floatval( $atts['delivery_fee'] );

    $ajax_url     = admin_url( 'admin-ajax.php' );
    $nonce        = wp_create_nonce( 'cso_submit_nonce' );

    $product_name  = 'Product';
    $base_price    = 299;
    $description   = '';
    $images        = array();
    $offers_data   = array();
    $vars_data     = array();

    // Use Helper
    $data = cso_get_product_data( $product_id );
    if ( ! empty( $data['name'] ) ) {
        $product_name = $data['name'];
        $base_price   = $data['base_price'];
        $description  = $data['description'];
        $images       = $data['images'];
        $offers_data  = $data['offers'];
        $vars_data    = $data['vars'];
    
    // Auto-switch layout based on product settings if not explicitly forced in shortcode
    if ( $atts['layout'] === 'full' && $data['layout_mode'] === 'form_only' ) {
         $atts['layout'] = 'form_only';
    }
    
    // Apply override delivery fee if set in shortcode but verify legacy logic
        if ( $delivery_fee > 0 ) {
             foreach($offers_data as &$OD){ 
                 if(empty($OD['delivery_fee'])) $OD['delivery_fee'] = $delivery_fee; 
                 if($OD['delivery_fee'] > 0) $OD['free_delivery'] = false;
             }
        }
    }

    $images_j    = wp_json_encode( $images );
    $offers_j    = wp_json_encode( $offers_data );
    $vars_j      = wp_json_encode( $vars_data );
    $first_img   = ! empty( $images ) ? esc_url( $images[0] ) : '';

    ob_start();
    ?>
    <!-- ═══ Bootstrap 5 CDN ═══ -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">

    <style>
    :root {
        --cso-primary: #1e293b;
        --cso-accent: #ef4444;
        --cso-accent-hover: #dc2626;
        --cso-bg: #f8fafc;
        --cso-card-bg: #ffffff;
        --cso-text: #1e293b;
        --cso-text-muted: #64748b;
        --cso-border: #e2e8f0;
        --cso-radius-lg: 24px;
        --cso-radius-md: 16px;
        --cso-radius-sm: 12px;
        --cso-shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        --cso-shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        --cso-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* ── BASE & LAYOUT ── */
    .cso-product-page *{box-sizing:border-box;margin:0;padding:0}
    .cso-product-page{font-family:'Inter', sans-serif;max-width:1300px;margin:40px auto;padding:0 24px;color:var(--cso-text);line-height:1.6;direction:ltr;text-align:left;overflow-x:hidden;width:100%}
    .cso-grid{display:grid;grid-template-columns:minmax(0, 1.3fr) minmax(0, 1fr);gap:60px;align-items:start;width:100%}

    /* ── LEFT COLUMN: GALLERY ── */
    .cso-gallery{position:sticky;top:30px;min-width:0;max-width:100%;overflow:hidden}
    .cso-main-img-wrap{width:100%;max-width:100%;background:var(--cso-bg);border-radius:var(--cso-radius-lg);overflow:hidden;position:relative;aspect-ratio:4/5;box-shadow:var(--cso-shadow-md);border:1px solid var(--cso-border)}
    .cso-main-img-wrap img{display:block;width:100%;height:100%;object-fit:cover;transition:transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1)}
    .cso-main-img-wrap:hover img{transform:scale(1.05)}

    .cso-thumbs{display:flex;gap:12px;margin-top:16px;overflow-x:auto;padding:4px 4px 12px;scrollbar-width:none}
    .cso-thumbs::-webkit-scrollbar{display:none}
    .cso-thumb{width:70px;height:70px;border-radius:var(--cso-radius-sm);border:2.5px solid transparent;cursor:pointer;overflow:hidden;transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);background:var(--cso-bg);flex-shrink:0;box-shadow:var(--cso-shadow-sm)}
    .cso-thumb.active{border-color:var(--cso-accent);transform:scale(1.05);box-shadow:var(--cso-shadow-md)}
    .cso-thumb img{width:100%;height:100%;object-fit:cover}

    /* ── RIGHT COLUMN: CONTENT ── */
    .cso-content{padding-top:0;min-width:0;max-width:100%;overflow:hidden}
    .cso-title{font-size: clamp(28px, 4vw, 42px);font-weight:900;margin-bottom:16px;line-height:1.1;letter-spacing:-1.5px}
    .cso-mobile-title{display:none;font-size:clamp(24px, 5vw, 34px);font-weight:900;line-height:1.1;letter-spacing:-1px;margin-bottom:4px;grid-column:1 / -1}

    /* ── DASHED FORM WRAP ── */
    .cso-unified-form{border:2px dashed var(--cso-border);padding:32px;border-radius:24px;background:#fff;margin-top:20px}
    
    /* ── OFFERS (Clear & Bold) ── */
    .cso-offers-list{display:flex;flex-direction:column;gap:12px;margin-bottom:32px}
    .cso-offer-card{border:2px solid #e5e7eb;border-radius:14px;padding:16px 18px;cursor:pointer;transition:all 0.2s ease;background:#fff;position:relative}
    .cso-offer-card:hover{border-color:#9ca3af}
    .cso-offer-card.active{border:2.5px solid #3b82f6;background:#eff6ff;box-shadow:0 2px 12px rgba(59,130,246,0.1)}
    
    .cso-offer-main-row{display:flex;align-items:center;gap:14px;width:100%}

    /* Stacked images container */
    .cso-offer-imgs-stack{position:relative;flex-shrink:0;width:64px;height:64px}
    .cso-offer-imgs-stack .cso-stack-img{width:54px;height:54px;border-radius:10px;overflow:hidden;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,0.1);position:absolute;top:0;left:0;background:#f1f5f9}
    .cso-offer-imgs-stack .cso-stack-img img{width:100%;height:100%;object-fit:cover;display:block}
    .cso-offer-imgs-stack .cso-stack-img:nth-child(1){z-index:3;top:0;left:0}
    .cso-offer-imgs-stack .cso-stack-img:nth-child(2){z-index:2;top:6px;left:8px}
    .cso-offer-imgs-stack .cso-stack-img:nth-child(3){z-index:1;top:12px;left:16px}
    .cso-offer-imgs-stack[data-count="1"]{width:54px;height:54px}
    .cso-offer-imgs-stack[data-count="2"]{width:64px;height:62px}
    .cso-offer-imgs-stack[data-count="3"]{width:72px;height:68px}

    .cso-offer-info{flex:1;min-width:0}
    .cso-offer-name{font-size:14px;font-weight:800;color:#111;margin-bottom:6px;line-height:1.35}
    .cso-offer-badge{display:inline-block;background:#ef0000;color:#fff;font-size:11px;font-weight:800;padding:3px 10px;border-radius:4px;letter-spacing:0.2px}

    .cso-offer-prices{text-align:right;flex-shrink:0;padding-left:8px}
    .cso-op-old{font-size:13px;color:#9ca3af;text-decoration:line-through;font-weight:500;margin-bottom:2px;white-space:nowrap}
    .cso-op-new{font-size:18px;font-weight:900;color:#ef0000;white-space:nowrap}

    .cso-row-item{background:#f8fafc;border:1px solid #f1f5f9;border-radius:16px;padding:16px;margin-bottom:12px;transition:all 0.3s ease;display:block}
    .cso-row-item:hover{border-color:#e2e8f0;background:#fff;box-shadow:0 10px 15px -3px rgba(0,0,0,0.04)}
    .cso-article-tag{display:inline-flex;align-items:center;font-size:10px;font-weight:900;color:#94a3b8;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;gap:8px}
    .cso-article-tag::after{content:'';height:1px;width:15px;background:#e2e8f0}
    .cso-row-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(120px, 1fr));gap:10px}
    .cso-select-group select{width:100%;height:44px;padding:0 12px;border:none;background:#fff;border-radius:10px;font-size:13px;font-weight:700;color:#1e293b;outline:none;cursor:pointer;appearance:none;box-shadow:inset 0 0 0 1px #f1f5f9;transition:all 0.2s}
    .cso-select-group select:focus{box-shadow:inset 0 0 0 2px var(--cso-accent);background:#fff}
    .cso-select-group{position:relative}
    .cso-select-group::after{content:'\f078';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:12px;top:50%;transform:translateY(-50%);font-size:10px;color:#94a3b8;pointer-events:none}

    /* ── DESCRIPTION ── */
    .cso-description-box{overflow:hidden}
    .cso-description-content{font-size:15px;color:#475569;line-height:1.7}
    .cso-description-content img{max-width:100% !important;height:auto !important;border-radius:12px;margin:15px 0}
    .cso-description-content p{margin-bottom:15px}
    .cso-description-content *{max-width:100%;word-wrap:break-word}

    /* ── CHECKOUT FIELDS (Modified to match reference) ── */
    .cso-checkout-fields{margin-top:32px}
    .cso-input-grp{margin-bottom:20px}
    .cso-field-wrap{width:100%;display:flex;border:1px solid #c8ccd0;border-radius:8px;overflow:hidden;background:#fff;height:56px;transition:border-color 0.2s}
    .cso-field-wrap:focus-within{border-color:#111}
    .cso-input-icon{width:56px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;border-right:1px solid #c8ccd0;color:#111;font-size:18px}
    .cso-field-wrap input{flex:1;border:none;padding:0 16px;font-size:16px;font-weight:600;outline:none;color:#333;background:transparent}
    .cso-field-wrap input::placeholder{color:#6b7280;font-weight:500}
    
    /* ── SUMMARY (Minimal) ── */
    .cso-summary-list{margin:32px 0 24px;padding:20px;background:#f8fafc;border-radius:12px}
    .cso-sum-row{display:flex;justify-content:space-between;margin-bottom:10px;font-size:14px;font-weight:600;color:#64748b}
    .cso-sum-row.total{margin-top:12px;padding-top:12px;border-top:1px solid #e2e8f0;font-size:18px;font-weight:900;color:#000}
    .cso-sum-row .val-red{color:#ff0000}

    /* ── BUTTON (Simple Black) ── */
    .cso-submit-btn{width:100%;padding:18px;background:#000;color:#fff;border:none;border-radius:12px;font-size:18px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px}
    .cso-error{color:#ff0000;font-size:12px;font-weight:700;margin-top:6px;display:none}

    /* ── FLOATING CTA ── */
    .cso-float-wrap{position:fixed;bottom:24px;left:0;width:100%;display:none;justify-content:center;z-index:9999;padding:0 20px;pointer-events:none}
    .cso-floating-btn{pointer-events:auto;width:100%;max-width:320px;padding:18px;background:#000;color:#fff;border:none;border-radius:50px;font-size:16px;font-weight:900;text-transform:uppercase;cursor:pointer;box-shadow:0 10px 20px rgba(0,0,0,0.2)}

    /* ── TABLET ≤1024px ── */
    @media (max-width: 1024px) {
        .cso-grid{grid-template-columns:1fr;gap:30px}
        .cso-product-page{margin:10px auto;padding:0 16px}
        .cso-gallery{position:relative;top:0;width:100%;min-width:0}
        .cso-main-img-wrap{aspect-ratio:1/1 !important;max-width:100%;width:100%}
        .cso-main-img-wrap img{width:100%;height:100%}
        .cso-unified-form{padding:20px;border-radius:20px}
        .cso-thumbs{gap:10px;margin-top:12px;padding-bottom:8px}
        .cso-thumb{width:60px;height:60px;border-radius:var(--cso-radius-sm);border:2.5px solid transparent;cursor:pointer;overflow:hidden;transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);background:var(--cso-bg);flex-shrink:0;box-shadow:var(--cso-shadow-sm)}
        .cso-thumb.active{border-color:var(--cso-accent);transform:scale(1.05);box-shadow:var(--cso-shadow-md)}
        .cso-thumb img{width:100%;height:100%;object-fit:cover}
        .cso-mobile-title{display:block}
        .cso-content > .cso-title{display:none}
    }

    /* ── LARGE PHONES ≤768px ── */
    @media (max-width: 768px) {
        .cso-product-page{padding:0 12px;margin:8px auto}
        .cso-grid{gap:20px}
        .cso-title{font-size:clamp(22px, 5vw, 32px);letter-spacing:-1px}
        .cso-main-img-wrap{aspect-ratio:3/4 !important;border-radius:16px}
        .cso-unified-form{padding:20px;border-radius:20px;border-width:1.5px;margin-top:12px}
        .cso-offer-card{padding:12px}
        .cso-offer-main-row{gap:10px}
        .cso-offer-imgs-stack{width:52px;height:52px}
        .cso-offer-imgs-stack .cso-stack-img{width:44px;height:44px;border-radius:8px}
        .cso-offer-imgs-stack .cso-stack-img:nth-child(2){top:5px;left:6px}
        .cso-offer-imgs-stack .cso-stack-img:nth-child(3){top:10px;left:12px}
        .cso-offer-imgs-stack[data-count="1"]{width:44px;height:44px}
        .cso-offer-imgs-stack[data-count="2"]{width:52px;height:50px}
        .cso-offer-imgs-stack[data-count="3"]{width:58px;height:56px}
        .cso-offer-name{font-size:13px}
        .cso-offer-badge{font-size:9px;padding:2px 6px}
        .cso-op-old{font-size:11px}
        .cso-op-new{font-size:15px}
        .cso-row-grid{grid-template-columns:1fr 1fr;gap:8px}
        .cso-select-group select{padding:8px;font-size:13px}
        .cso-field-wrap{height:50px}
        .cso-input-icon{width:48px;font-size:16px}
        .cso-field-wrap input{font-size:15px;padding:0 12px}
        .cso-summary-list{padding:16px;margin:24px 0 16px}
        .cso-sum-row{font-size:13px}
        .cso-sum-row.total{font-size:16px}
        .cso-submit-btn{padding:16px;font-size:16px;border-radius:10px}
        .cso-description-content{font-size:14px}
        .cso-float-wrap{display:flex}
    }

    /* ── SMALL PHONES ≤480px ── */
    @media (max-width: 480px) {
        .cso-product-page{padding:0 8px 100px;margin:4px auto}
        .cso-grid{gap:14px}
        .cso-title{font-size:22px;letter-spacing:-0.5px;margin-bottom:10px}
        .cso-main-img-wrap{aspect-ratio:1/1 !important;border-radius:14px;box-shadow:var(--cso-shadow-sm)}
        .cso-thumbs{gap:8px;margin-top:10px}
        .cso-thumb{width:52px;height:52px;border-radius:8px;border-width:2px}
        .cso-unified-form{padding:14px;margin-top:10px;border-radius:14px}
        .cso-offers-list{gap:10px;margin-bottom:20px}
        .cso-offer-card{padding:10px;border-radius:10px}
        .cso-offer-main-row{gap:8px}
        .cso-offer-imgs-stack{width:48px;height:48px}
        .cso-offer-imgs-stack .cso-stack-img{width:40px;height:40px;border-radius:7px}
        .cso-offer-imgs-stack .cso-stack-img:nth-child(2){top:4px;left:5px}
        .cso-offer-imgs-stack .cso-stack-img:nth-child(3){top:8px;left:10px}
        .cso-offer-imgs-stack[data-count="1"]{width:40px;height:40px}
        .cso-offer-imgs-stack[data-count="2"]{width:47px;height:46px}
        .cso-offer-imgs-stack[data-count="3"]{width:52px;height:50px}
        .cso-offer-name{font-size:12px;margin-bottom:2px}
        .cso-offer-badge{font-size:8px;padding:1px 5px;border-radius:3px}
        .cso-op-old{font-size:10px}
        .cso-op-new{font-size:14px}
        .cso-row-item{padding:12px;border-radius:12px}
        .cso-article-tag{margin-bottom:10px}
        .cso-select-group select{height:40px;font-size:12px}
        .cso-checkout-fields{margin-top:20px}
        .cso-input-grp{margin-bottom:14px}
        .cso-field-wrap{height:48px;border-radius:8px}
        .cso-input-icon{width:44px;font-size:15px}
        .cso-field-wrap input{font-size:14px;padding:0 10px}
        .cso-summary-list{padding:14px;margin:18px 0 14px;border-radius:10px}
        .cso-sum-row{font-size:12px;margin-bottom:8px}
        .cso-sum-row.total{font-size:15px;padding-top:10px;margin-top:10px}
        .cso-submit-btn{padding:14px;font-size:15px;border-radius:10px;gap:8px}
        .cso-floating-btn{padding:14px;font-size:14px;max-width:280px;border-radius:40px}
        .cso-float-wrap{bottom:16px;padding:0 16px}
        .cso-description-box{margin-top:16px}
        .cso-description-content{font-size:13px;line-height:1.6}
        .cso-description-content img{border-radius:8px;margin:10px 0}
    }

    /* ── VERY SMALL PHONES ≤360px ── */
    @media (max-width: 360px) {
        .cso-product-page{padding:0 6px 90px}
        .cso-title{font-size:20px}
        .cso-main-img-wrap{border-radius:12px}
        .cso-unified-form{padding:10px;border-radius:12px}
        .cso-offer-imgs-stack .cso-stack-img{width:36px;height:36px}
        .cso-offer-imgs-stack[data-count="1"]{width:36px;height:36px}
        .cso-offer-imgs-stack[data-count="2"]{width:43px;height:42px}
        .cso-offer-imgs-stack[data-count="3"]{width:48px;height:46px}
        .cso-offer-name{font-size:11px}
        .cso-op-new{font-size:13px}
        .cso-row-grid{grid-template-columns:1fr}
        .cso-field-wrap{height:44px}
        .cso-field-wrap input{font-size:13px}
        .cso-submit-btn{padding:12px;font-size:14px}
    }

    /* ── SUCCESS MODAL ── */
    @keyframes csoScaleIn {
        0% { transform: scale(0.9); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .cso-modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:99999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity 0.3s}
    .cso-modal-overlay.open{opacity:1;pointer-events:auto}
    .cso-modal-box{background:#fff;width:95%;max-width:420px;border-radius:24px;padding:50px 32px;text-align:center;position:relative;border: 3px dashed var(--cso-border);box-shadow: none;animation: csoScaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)}
    .cso-success-icon{width:80px;height:80px;background:#10b981;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:40px;margin:0 auto 24px;box-shadow:none}
    .cso-modal-box h2{font-size:26px;font-weight:900;color:#1e293b;margin-bottom:12px;line-height:1.2;letter-spacing:-0.5px}
    .cso-modal-box p{font-size:16px;color:#64748b;line-height:1.6;margin-bottom:30px}
    .cso-btn-close{background:#000;color:#fff;border:none;padding:16px 32px;font-size:16px;font-weight:800;border-radius:14px;cursor:pointer;width:100%;transition:transform 0.2s}
    .cso-btn-close:active{transform:scale(0.98)}
    </style>

    <div class="cso-product-page <?php echo ($atts['layout'] === 'form_only' ? 'cso-form-only' : ''); ?>" id="cso-form-top">
        <div class="cso-grid <?php echo ($atts['layout'] === 'form_only' ? 'd-block' : ''); ?>" id="cso-main-grid">
            <?php if ( $atts['layout'] !== 'form_only' ): ?>
                <h1 class="cso-mobile-title"><?php echo esc_html($product_name); ?></h1>
            <?php endif; ?>
            <?php if ( $atts['layout'] !== 'form_only' ): ?>
                <!-- LEFT: GALLERY -->
                <div class="cso-gallery">
                    <div class="cso-main-img-wrap">
                        <a href="<?php echo $first_img; ?>" data-lightbox="prod-img" id="cso-main-link">
                            <img src="<?php echo $first_img; ?>" alt="<?php echo esc_attr($product_name); ?>" id="cso-main-target">
                        </a>
                    </div>
                    <?php if (count($images) > 1): ?>
                    <div class="cso-thumbs">
                        <?php foreach ($images as $i => $url): ?>
                            <div class="cso-thumb <?php echo $i===0?'active':''; ?>" data-url="<?php echo esc_url($url); ?>">
                                <img src="<?php echo esc_url($url); ?>" alt="">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="cso-description-box" style="margin-top:30px">
                        <div class="cso-description-content">
                            <?php echo wp_kses_post($description); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- RIGHT/MAIN: CONTENT -->
            <div class="cso-content">
                <?php if ( $atts['layout'] !== 'form_only' ): ?>
                    <h1 class="cso-title"><?php echo esc_html($product_name); ?></h1>
                    <div class="cso-main-price-display" style="font-size: 32px; font-weight: 900; color: var(--cso-accent); margin-bottom: 25px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                        <?php echo number_format($base_price, 2); ?> <span style="font-size: 18px;"><?php echo $currency; ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="cso-unified-form" id="cso-checkout">
                    <div class="cso-offers-list" id="cso-offers-generated">
                        <!-- Offers injected by JS -->
                    </div>

                    <form id="cso-order-main">
                        <div class="cso-checkout-fields">
                            <div class="cso-input-grp">
                                <div class="cso-field-wrap">
                                    <div class="cso-input-icon"><i class="fa-solid fa-user"></i></div>
                                    <input type="text" name="full_name" placeholder="Nom Complet" required>
                                </div>
                                <div class="cso-error">Veuillez entrer votre nom complet</div>
                            </div>

                            <div class="cso-input-grp">
                                <div class="cso-field-wrap">
                                    <div class="cso-input-icon"><i class="fa-solid fa-phone"></i></div>
                                    <input type="tel" name="phone" placeholder="Téléphone" required>
                                </div>
                                <div class="cso-error">Veuillez entrer un numéro de téléphone valide</div>
                            </div>

                            <div class="cso-input-grp">
                                <div class="cso-field-wrap">
                                    <div class="cso-input-icon"><i class="fa-solid fa-location-dot"></i></div>
                                    <input type="text" name="address" placeholder="Adresse complète" required>
                                </div>
                                <div class="cso-error">L'adresse est requise</div>
                            </div>
                        </div>

                        <div class="cso-summary-list">
                            <div class="cso-sum-row">
                                <span>Sous-total</span>
                                <span id="sum-sub">0 dh</span>
                            </div>
                            <div class="cso-sum-row">
                                <span>Livraison</span>
                                <span>Gratuite</span>
                            </div>
                            <div class="cso-sum-row">
                                <span>Remises</span>
                                <span id="sum-disc" class="val-red">- 0 dh</span>
                            </div>
                            <div class="cso-sum-row total">
                                <span>Total</span>
                                <span id="sum-total">0 dh</span>
                            </div>
                        </div>

                        <button type="submit" class="cso-submit-btn" id="cso-submit">
                            Acheter Maintenant
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="cso-float-wrap">
            <button class="cso-floating-btn" id="cso-float-cta">Commander Maintenant</button>
        </div>
    </div>

    <!-- ═══ Lightbox2 JS ═══ -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    <script crossorigin="anonymous">
    (function(){
        /* ── CONFIG ── */
        const CFG={
            basePrice:<?php echo $base_price; ?>,
            currency:'<?php echo $currency; ?>',
            ajaxUrl:'<?php echo esc_url( $ajax_url ); ?>',
            nonce:'<?php echo $nonce; ?>',
            productId:<?php echo $product_id; ?>,
            images:<?php echo $images_j; ?>,
            offers:<?php echo $offers_j; ?>,
            vars:<?php echo $vars_j; ?>
        };

        const $=s=>document.querySelector(s);
        const $$=s=>document.querySelectorAll(s);
        const fmt=n=>n.toLocaleString().replace(/,/g,' ')+' '+CFG.currency;
        const fmtFixed=n=>n.toFixed(2)+' '+CFG.currency;

        let activeOfferId = CFG.offers.length > 0 ? CFG.offers[0].id : 0;
        const BADGES = ['Meilleure Vente', 'Plus Populaire', 'Meilleure Valeur', 'Offre Spéciale', 'Temps Limité'];

        /* ── LOGIC ── */
        function calcOffer(o){
            const bp = CFG.basePrice;
            const sub = bp * o.qty;
            const price = parseFloat(o.price) || 0;
            const delFee = o.free_delivery ? 0 : (parseFloat(o.delivery_fee) || 0);

            let discount = 0;
            let total = sub + delFee;

            if(price > 0){
                if(o.discount_enabled){
                    discount = sub - price;
                    if(discount < 0) discount = 0;
                }
                total = price + delFee;
            }

            const pct = (discount > 0 && sub > 0) ? Math.round((discount/sub)*100) : 0;
            return {subtotal:sub, discount:discount, total:total, pct:pct, price:price, delivery_fee: delFee};
        }

        function renderStackImages(qty){
            const firstImg = (CFG.images && CFG.images.length) ? CFG.images[0] : '';
            if(!firstImg) return '';
            let html = `<div class="cso-offer-imgs-stack" data-count="${Math.min(qty,3)}">`;
            for(let i=0; i<Math.min(qty,3); i++){
                html += `<div class="cso-stack-img"><img src="${firstImg}" alt=""></div>`;
            }
            html += `</div>`;
            return html;
        }

        function renderOffers(){
            const c = $('#cso-offers-generated');
            if(!c) return;
            c.innerHTML = '';
            
            CFG.offers.forEach((o, idx) => {
                const qty = parseInt(o.qty) || 1;
                const res = calcOffer(o);
                const isActive = (o.id === activeOfferId);
                const badgeText = BADGES[idx % BADGES.length];
                
                const div = document.createElement('div');
                div.className = `cso-offer-card ${isActive ? 'active' : ''}`;
                div.dataset.id = o.id;

                let badgeHtml = '';
                if(res.pct > 0 && res.discount > 0){
                    badgeHtml = `<span class="cso-offer-badge" style="display:inline-block">${res.pct}% de réduction</span>`;
                }

                div.innerHTML = `
                    <div class="cso-offer-main-row">
                        ${renderStackImages(o.qty)}
                        <div class="cso-offer-info">
                            <div class="cso-offer-name">${o.label || 'Offre'}</div>
                            ${badgeHtml}
                        </div>
                        <div class="cso-offer-prices">
                            ${res.discount > 0 ? `<div class="cso-op-old">${fmtFixed(res.subtotal)}</div>` : ''}
                            <div class="cso-op-new">${fmtFixed(res.total - res.delivery_fee)}</div>
                        </div>
                    </div>
                    <div class="cso-options-wrap" id="opts-${o.id}"></div>
                `;

                div.onclick = function(e){
                    // Ignore clicks on form elements
                    if(e.target.closest('.cso-options-wrap') || e.target.tagName==='SELECT' || e.target.tagName==='LABEL') return;
                    
                    // If already active, don't rebuild (prevents resetting vars)
                    if(this.classList.contains('active')) return;

                    $$('.cso-offer-card').forEach(x => {
                        x.classList.remove('active');
                        const opts = x.querySelector('.cso-options-wrap');
                        if(opts) opts.innerHTML = '';
                    });
                    this.classList.add('active');
                    activeOfferId = o.id;
                    buildRows(qty, '#opts-'+o.id);
                    updateSummary();
                };

                c.appendChild(div);

                if(isActive){
                     buildRows(qty, '#opts-'+o.id);
                }
            });
            updateSummary();
        }

        function buildRows(qty, containerId){
            const container = $(containerId);
            if(!container) return;
            container.innerHTML='';

            // Use CFG.vars to build selects
            // Structure: [{key:'Color', values:['Red','Blue']}, {key:'Size', values:['S','M']}]
            if(!CFG.vars || !CFG.vars.length) return;

            for(let i=1; i<=qty; i++){
                const row=document.createElement('div');
                row.className='cso-row-item';
                
                let selectsHtml = '';
                CFG.vars.forEach(v => {
                     let opts = `<option value="">${v.key}</option>`;
                     v.values.forEach(val => { opts += `<option value="${val}">${val}</option>`; });
                     selectsHtml += `
                        <div class="cso-select-group">
                            <select class="cso-dynamic-sel" data-key="${v.key}" required>
                                ${opts}
                            </select>
                        </div>
                     `;
                });

                row.innerHTML = `<div class="cso-article-tag">Item ${i.toString().padStart(2, '0')}</div><div class="cso-row-grid">${selectsHtml}</div>`;
                container.appendChild(row);
            }
        }

        function updateSummary(){
            const o = CFG.offers.find(x => x.id === activeOfferId);
            if(!o) return;
            const res = calcOffer(o);
            
            $('#sum-sub').textContent = fmt(res.subtotal);
            
            // Shipping
            const shipRow = $$('.cso-sum-row')[1]; // Assume 2nd row is shipping
            if(shipRow){
                const shipVal = shipRow.querySelectorAll('span')[1];
                if(shipVal) shipVal.textContent = res.delivery_fee > 0 ? fmt(res.delivery_fee) : 'Livraison Gratuite';
            }

            $('#sum-disc').textContent = '- ' + fmt(res.discount);
            $('#sum-total').textContent = fmt(res.total);
        }

        /* ── GALLERY ── */
        $$('.cso-thumb').forEach(t=>{
            t.onclick=function(){
                $$('.cso-thumb').forEach(x=>x.classList.remove('active'));
                this.classList.add('active');
                const url = this.dataset.url;
                $('#cso-main-target').src = url;
                $('#cso-main-link').href = url;
            };
        });

        /* ── FORM ── */
        const formMain = $('#cso-order-main');
        if(formMain){
            formMain.onsubmit=function(e){
                e.preventDefault();
                const btn=$('#cso-submit');
                btn.disabled=true;
                btn.classList.add('loading');
                btn.textContent='Traitement...';
    
                const activeCard=$('.cso-offer-card.active');
                const products=[];
                if(activeCard){
                    activeCard.querySelectorAll('.cso-row-item').forEach(row=>{
                        const item = {};
                        row.querySelectorAll('.cso-dynamic-sel').forEach(sel => {
                            item[sel.dataset.key] = sel.value;
                        });
                        products.push(item);
                    });
                }
    
                const formData=new FormData(this);
                formData.append('action', 'cso_submit_order');
                formData.append('nonce', CFG.nonce);
                formData.append('product_id', CFG.productId);
                formData.append('offer_id', activeOfferId);
                
                // Fallback: If no variations are selected/shown, use the offer's qty
                const o = CFG.offers.find(x => x.id === activeOfferId);
                const finalQty = products.length > 0 ? products.length : (o ? o.qty : 1);
                formData.append('quantity', finalQty);
                formData.append('products_json', JSON.stringify(products));

                fetch(CFG.ajaxUrl, { method:'POST', body:formData })
                .then(r=>r.json())
                .then(res=>{
                    if(res.success){
                        // Modal Success (WhatsApp is handled in backend)
                        const modalHtml = `
                        <div class="cso-modal-overlay open" id="cso-success-modal">
                            <div class="cso-modal-box">
                                <div class="cso-success-icon"><i class="fa-solid fa-check"></i></div>
                                <h2>Commande Passée!</h2>
                                <p>Merci pour votre commande. Nous vous contacterons bientôt.</p>
                                <button type="button" class="cso-btn-close" onclick="document.getElementById('cso-success-modal').remove()">Fermer</button>
                            </div>
                        </div>
                        `;
                        const div = document.createElement('div');
                        div.innerHTML = modalHtml;
                        document.body.appendChild(div.firstElementChild);
                        
                        // Reset form
                        formMain.reset();
                        btn.disabled=false;
                        btn.classList.remove('loading');
                        btn.textContent='Acheter Maintenant';

                        setTimeout(() => { window.location.href='<?php echo home_url("/"); ?>?order=success'; }, 2000);
                        
                    } else {
                        alert(res.data);
                        btn.disabled=false;
                        btn.classList.remove('loading');
                        btn.textContent='Acheter Maintenant';
                    }
                })
                .catch(err=>{
                    alert('Erreur de réseau. Veuillez réessayer.');
                    btn.disabled=false;
                    btn.classList.remove('loading');
                    btn.textContent='Acheter Maintenant';
                });
            };
        }

        /* ── FLOATING CTA ── */
        $('#cso-float-cta').onclick=function(){
            window.scrollTo({
                top: $('#cso-checkout').offsetTop - 20,
                behavior: 'smooth'
            });
        };

        /* ── INIT ── */
        renderOffers();
    })();
    </script>
    <?php
    return ob_get_clean();
}

/* ═══════════════════════════════════════════════
   7. AJAX HANDLER — Process order submission
═══════════════════════════════════════════════ */
add_action( 'wp_ajax_cso_submit_order',        'cso_handle_submit' );
add_action( 'wp_ajax_nopriv_cso_submit_order', 'cso_handle_submit' );

function cso_handle_submit() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'cso_submit_nonce' ) ) {
        wp_send_json_error( 'Security verification failed. Please reload the page.' );
    }

    $product_id    = intval( $_POST['product_id'] ?? 0 );
    $full_name     = sanitize_text_field( $_POST['full_name'] ?? '' );
    $phone         = sanitize_text_field( $_POST['phone']     ?? '' );
    $address       = sanitize_textarea_field( $_POST['address'] ?? '' );
    $city          = sanitize_text_field( $_POST['city']      ?? '' );
    $notes         = sanitize_textarea_field( $_POST['notes']  ?? '' );
    $offer_id      = intval( $_POST['offer_id']  ?? 1 );
    $quantity      = intval( $_POST['quantity']   ?? 1 );
    $products_json = wp_unslash( $_POST['products_json'] ?? '[]' );

    if ( empty( $full_name ) ) wp_send_json_error( 'Le nom complet est requis.' );
    if ( empty( $phone ) || strlen( preg_replace('/\D/', '', $phone) ) < 8 ) wp_send_json_error( 'Numéro de téléphone invalide.' );
    if ( empty( $address ) ) wp_send_json_error( 'L\'adresse est requise.' );

    $subtotal = 0;
    $total = 0;
    $delivery_fee = 0;
    $discount = 0;

    $cart_items = json_decode($products_json, true) ?: [];
    $is_multi_product = ( is_array($cart_items) && count($cart_items) > 0 && isset($cart_items[0]['id']) );

    if ( $is_multi_product ) {
        // Multi-Product Logic
        foreach ( $cart_items as &$item ) {
            $pid = intval($item['id'] ?? 0);
            if( $pid > 0 ) {
                $data = cso_get_product_data( $pid );
                
                // Extract qty from the offer selected
                $item_qty = 1;
                $off_id = intval($item['offer_id'] ?? 1);
                foreach( $data['offers'] ?? [] as $o ) {
                    if ( intval($o['id']) === $off_id ) {
                        $item_qty = intval($o['qty'] ?? 1);
                        break;
                    }
                }
                $item['qty'] = $item_qty; // Inject true qty so it's readable by the admin dashboard
                
                $row_price = floatval($item['price'] ?? 0);
                $total += $row_price;
                
                $base_val = floatval($data['base_price']);
                if ( $base_val > 0 ) {
                    $subtotal += ($base_val * $item_qty);
                } else {
                    $subtotal += $row_price;
                }
            }
        }
        $discount = max(0, $subtotal - $total);
        $products_json = wp_json_encode($cart_items); // Re-encode with qty added

    } else {
        // Fallback to legacy single product logic
        $data = cso_get_product_data( $product_id );
        $base_price = $data['base_price'];
        $offers     = $data['offers'];

        $subtotal     = $base_price * $quantity;
        $total        = $subtotal;

        $selected_offer = null;
        foreach($offers as $o){
            if(intval($o['id']) === $offer_id){
                $selected_offer = $o;
                break;
            }
        }

        if($selected_offer){
            $oprice = floatval($selected_offer['price']);
            if($oprice > 0){
                 $subtotal = $base_price * $quantity;
                 $discount = ($subtotal - $oprice > 0) ? $subtotal - $oprice : 0;
                 $delivery_fee = !empty($selected_offer['free_delivery']) ? 0 : floatval($selected_offer['delivery_fee'] ?? 0);
                 $total = $oprice + $delivery_fee;
            }
        }
    }

    global $wpdb;
    $inserted = $wpdb->insert(
        $wpdb->prefix . CSO_TABLE_ORDERS,
        array(
            'product_id'    => $product_id, // Keep for legacy/primary item reference
            'full_name'     => $full_name,
            'phone'         => $phone,
            'address'       => $address,
            'city'          => $city,
            'notes'         => $notes,
            'offer_id'      => $offer_id,
            'quantity'      => $quantity, // Total items or legacy qty
            'products_json' => wp_json_encode($cart_items), // Ensure solid JSON
            'subtotal'      => $subtotal,
            'discount'      => $discount,
            'delivery_fee'  => $delivery_fee,
            'total'         => $total,
            'status'        => 'new',
        ),
        array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%f', '%f', '%f', '%f', '%s' )
    );

    if ( $inserted ) {
        $order_id = $wpdb->insert_id;

        // 1. Send Success Response early to close connection if PHP-FPM is used
        if ( function_exists( 'fastcgi_finish_request' ) ) {
            $response = wp_json_encode( array( 'success' => true, 'data' => array( 'order_id' => $order_id ) ) );
            header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
            echo $response;
            fastcgi_finish_request();
            
            // Run heavy tasks in the background
            cso_send_admin_notification( $order_id );
            do_action( 'basma_order_placed', $order_id, $full_name, $phone, $total, $product_id );
            exit;
        }

        // 2. Fallback to Loopback Request (Fast parallel processing for standard servers)
        wp_remote_post( admin_url('admin-ajax.php'), array(
            'method'    => 'POST',
            'timeout'   => 0.1,
            'blocking'  => false,
            'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
            'body'      => array(
                'action'   => 'cso_async_notifications',
                'order_id' => $order_id,
            )
        ) );

        wp_send_json_success( array( 'order_id' => $order_id ) );
    } else {
        wp_send_json_error( 'Échec de l\'enregistrement de la commande. Veuillez réessayer.' );
    }
}

/* ═══════════════════════════════════════════════
   7.1 ASYNC HANDLER — Background Webhooks/Emails
═══════════════════════════════════════════════ */
add_action( 'wp_ajax_cso_async_notifications', 'cso_async_notifications_handler' );
add_action( 'wp_ajax_nopriv_cso_async_notifications', 'cso_async_notifications_handler' );
function cso_async_notifications_handler() {
    $order_id = intval( $_POST['order_id'] ?? 0 );
    if ( $order_id > 0 ) {
        global $wpdb;
        $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}" . CSO_TABLE_ORDERS . " WHERE id = %d", $order_id ) );
        if ( $order ) {
            cso_send_admin_notification( $order_id );
            do_action( 'basma_order_placed', $order_id, $order->full_name, $order->phone, $order->total, $order->product_id );
        }
    }
    wp_die();
}

/* ═══════════════════════════════════════════════
   8. [cso_products_grid] — Frontend display
 ═══════════════════════════════════════════════ */
add_shortcode( 'cso_products_grid', 'cso_products_grid_sc' );
function cso_products_grid_sc( $atts ) {
    $atts = shortcode_atts( array( 'limit' => 6 ), $atts );
    
    $args = array(
        'post_type'      => 'cso_product',
        'posts_per_page' => intval( $atts['limit'] ),
        'post_status'    => 'publish',
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_cso_layout_mode',
                'value'   => 'form_only',
                'compare' => '!=',
            ),
            array(
                'key'     => '_cso_layout_mode',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );
    $q = new WP_Query( $args );

    // CSS rules moved to global core.php
    $html = '';

    $html .= '<div class="cso-prod-grid">';
    
    if ( $q->have_posts() ) {
        while ( $q->have_posts() ) {
            $q->the_post();
            $pid = get_the_ID();
            $data = cso_get_product_data( $pid );
            $thumb = !empty($data['images']) ? $data['images'][0] : '';
            $price = $data['base_price'];
            
            $html .= '<div class="cso-prod-card">';
            $html .= '<button class="cso-wish-btn" data-id="'.$pid.'" data-name="'.esc_attr($data['name']).'" data-price="'.$price.'" data-img="'.esc_url($thumb).'"><i class="fas fa-heart"></i></button>';
            
            $html .= '<a href="'.get_permalink().'" class="cso-prod-thumb-wrap">';
            if($thumb) $html .= '<img src="'.esc_url($thumb).'" class="cso-prod-thumb">';
            else $html .= '<div class="cso-prod-thumb" style="display:flex;align-items:center;justify-content:center;color:#ccc"><i class="fa-solid fa-image fa-2x"></i></div>';
            $html .= '</a>';
            
            $html .= '<div class="cso-prod-body">';
            $html .= '<a href="'.get_permalink().'" style="text-decoration:none"><div class="cso-prod-title">'.esc_html($data['name']).'</div></a>';
            $html .= '<div class="cso-prod-price">'.number_format(floatval($price), 2).' dh</div>';
            $html .= '<div class="cso-prod-actions">';
            $html .= '<a href="'.get_permalink().'" class="cso-prod-btn">Détails</a>';
            $html .= '<button class="cso-add-cart-btn" data-id="'.$pid.'" data-name="'.esc_attr($data['name']).'" data-price="'.$price.'" data-img="'.esc_url($thumb).'"><i class="fas fa-cart-plus"></i> Panier</button>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        wp_reset_postdata();
    } else {
        $html .= '<p>Aucun produit trouvé.</p>';
    }
    
    $html .= '</div>';

    // Javascript moved to global core.php

    return $html;
}

// Auto-inject form into CPT content
add_filter( 'the_content', 'cso_auto_inject_form' );
function cso_auto_inject_form( $content ) {
    if ( is_singular( 'cso_product' ) && in_the_loop() && is_main_query() ) {
        // Avoid double injection if user manually placed shortcode
        if ( ! has_shortcode( $content, 'cod_smart_offer_form' ) ) {
            $content .= do_shortcode( '[cod_smart_offer_form]' );
        }
    }
    return $content;
}

/* ═══════════════════════════════════════════════
   9. ADMIN — SMTP & EMAIL SETTINGS
   ═══════════════════════════════════════════════ */
function cso_page_email_settings() {
    if ( isset( $_POST['cso_save_email_settings'] ) ) {
        check_admin_referer( 'cso_email_settings_nonce' );
        $settings = array(
            'smtp_host'   => sanitize_text_field( $_POST['smtp_host']   ?? '' ),
            'smtp_port'   => intval( $_POST['smtp_port'] ?? 587 ),
            'smtp_user'   => sanitize_text_field( $_POST['smtp_user']   ?? '' ),
            'smtp_pass'   => $_POST['smtp_pass'] ?? '', // Sensitive
            'smtp_enc'    => sanitize_text_field( $_POST['smtp_enc']    ?? 'tls' ),
            'from_email'  => sanitize_email( $_POST['from_email']       ?? get_option('admin_email') ),
            'from_name'   => sanitize_text_field( $_POST['from_name']   ?? get_bloginfo('name') ),
            'admin_dest'  => sanitize_email( $_POST['admin_dest']       ?? get_option('admin_email') ),
            'enabled'     => isset( $_POST['email_enabled'] ) ? 1 : 0,
        );
        update_option( 'cso_smtp_settings', $settings );
        echo '<div class="updated"><p>Settings saved successfully.</p></div>';
    }

    $settings = get_option( 'cso_smtp_settings', array() );
    ?>
    <div class="wrap">
        <h1>Paramètres Mail & SMTP</h1>
        <p class="description">Configurez comment la boutique envoie les notifications par email pour les nouvelles commandes.</p>
        
        <form method="post" action="" style="margin-top:20px; max-width:800px; background:#fff; padding:30px; border:1px solid #ccd0d4; border-radius:8px;">
            <?php wp_nonce_field( 'cso_email_settings_nonce' ); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Activer les Notifications</th>
                    <td>
                        <label>
                            <input type="checkbox" name="email_enabled" value="1" <?php checked( $settings['enabled'] ?? 0, 1 ); ?>>
                            Envoyer un email à l'admin lors d'une nouvelle commande.
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Email de l'Administrateur</th>
                    <td>
                        <input type="email" name="admin_dest" value="<?php echo esc_attr( $settings['admin_dest'] ?? get_option('admin_email') ); ?>" class="regular-text">
                        <p class="description">Les détails des commandes seront envoyés à cette adresse.</p>
                    </td>
                </tr>
                <tr><td colspan="2"><hr></td></tr>
                <tr>
                    <th scope="row">Hôte SMTP</th>
                    <td><input type="text" name="smtp_host" value="<?php echo esc_attr( $settings['smtp_host'] ?? '' ); ?>" class="regular-text" placeholder="smtp.gmail.com"></td>
                </tr>
                <tr>
                    <th scope="row">Port SMTP</th>
                    <td><input type="number" name="smtp_port" value="<?php echo esc_attr( $settings['smtp_port'] ?? 587 ); ?>" class="small-text"></td>
                </tr>
                <tr>
                    <th scope="row">Chiffrement</th>
                    <td>
                        <select name="smtp_enc">
                            <option value="tls" <?php selected( $settings['smtp_enc'] ?? 'tls', 'tls' ); ?>>TLS (Recommandé)</option>
                            <option value="ssl" <?php selected( $settings['smtp_enc'] ?? '', 'ssl' ); ?>>SSL</option>
                            <option value="none" <?php selected( $settings['smtp_enc'] ?? '', 'none' ); ?>>Aucun</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Nom d'utilisateur</th>
                    <td><input type="text" name="smtp_user" value="<?php echo esc_attr( $settings['smtp_user'] ?? '' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Mot de passe</th>
                    <td><input type="password" name="smtp_pass" value="<?php echo esc_attr( $settings['smtp_pass'] ?? '' ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Email de l'Expéditeur</th>
                    <td><input type="email" name="from_email" value="<?php echo esc_attr( $settings['from_email'] ?? get_option('admin_email') ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row">Nom de l'Expéditeur</th>
                    <td><input type="text" name="from_name" value="<?php echo esc_attr( $settings['from_name'] ?? get_bloginfo('name') ); ?>" class="regular-text"></td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="cso_save_email_settings" class="button button-primary" value="Enregistrer la Configuration Email">
            </p>
        </form>
    </div>
    <?php
}

/* ═══════════════════════════════════════════════
   9b. ADMIN — WHATSAPP SETTINGS
   ═══════════════════════════════════════════════ */
function cso_page_whatsapp_settings() {
    ?>
    <div class="wrap">
        <h1>Bot WhatsApp - État de connexion</h1>
        <p class="description">Gérez la connexion de votre bot au service WhatsApp. Scannez le QR Code depuis l'application WhatsApp de votre téléphone (Appareils connectés).</p>
        
        <div style="margin-top:20px; max-width:600px; background:#fff; padding:30px; border:1px solid #ccd0d4; border-radius:8px; text-align: center;">
            <h2 id="wap-status-text" style="font-weight: 800; font-size: 24px; color: #64748b;">Vérification du statut...</h2>
            
            <div id="wap-qr-container" style="margin: 30px auto; min-height: 250px; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                <span style="color:#94a3b8;"><i class="fas fa-spinner fa-spin fa-2x"></i></span>
            </div>
            
            <div style="margin-top: 25px; display: flex; gap: 10px; justify-content: center;">
                <button id="wap-refresh-btn" class="button button-large">
                    <i class="fas fa-sync-alt"></i> Rafraîchir
                </button>
                <button id="wap-test-btn" class="button button-primary button-large" style="background:#25d366; border-color:#25d366; color:#fff;" disabled>
                    <i class="fas fa-paper-plane"></i> Tester le message
                </button>
            </div>
            
            <p id="wap-test-status" style="margin-top: 15px; font-weight: 600; display: none;"></p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const BOT_BASE = 'https://996b-2a01-4f9-c013-e885-00-1.ngrok-free.app';
        const statusText = document.getElementById('wap-status-text');
        const qrContainer = document.getElementById('wap-qr-container');
        const refreshBtn = document.getElementById('wap-refresh-btn');

        async function fetchStatus() {
            refreshBtn.disabled = true;
            statusText.textContent = 'Vérification du statut...';
            statusText.style.color = '#10b981';
            qrContainer.innerHTML = '<div style="color:#10b981; padding: 40px;"><i class="fab fa-whatsapp" style="font-size: 80px;"></i><p style="margin-top:20px; font-weight: bold; font-size:16px;">Cloud API Connectée.</p></div>';
            
            // Assume the API is ready since WhatsApp Cloud API doesn't use the old QR polling
            testBtn.disabled = false;
            refreshBtn.disabled = false;
        }

        refreshBtn.addEventListener('click', fetchStatus);
        fetchStatus(); // initial load
        
        // TEST BUTTON LOGIC
        const testBtn = document.getElementById('wap-test-btn');
        const testStatus = document.getElementById('wap-test-status');
        
        testBtn.addEventListener('click', async function() {
            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi...';
            testStatus.style.display = 'block';
            testStatus.style.color = '#64748b';
            testStatus.textContent = 'Envoi du test en cours...';
            
            try {
                const res = await fetch(BOT_BASE + '/api/confirmation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        phone: '212704969534',
                        customerName: 'Test Admin',
                        orderNumber: '#TEST-999'
                    })
                });
                const data = await res.json();
                
                if (data.success) {
                    testStatus.style.color = '#10b981';
                    testStatus.textContent = '✅ Message de test envoyé avec succès au 212704969534!';
                } else {
                    testStatus.style.color = '#ef4444';
                    testStatus.textContent = '❌ Erreur: ' + (data.error || 'Impossible d\'envoyer le test');
                }
            } catch (err) {
                testStatus.style.color = '#ef4444';
                testStatus.textContent = '❌ Erreur de réseau avec le bot.';
            }
            
            testBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Tester le message';
            testBtn.disabled = false;
        });
    });
    </script>
    <?php
}

/* ── SMTP Integration ── */
add_action( 'phpmailer_init', 'cso_configure_smtp' );
function cso_configure_smtp( $phpmailer ) {
    $settings = get_option( 'cso_smtp_settings' );
    if ( empty( $settings ) || empty( $settings['smtp_host'] ) ) return;

    $phpmailer->isSMTP();
    $phpmailer->Host       = $settings['smtp_host'];
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Port       = $settings['smtp_port'];
    $phpmailer->Username   = $settings['smtp_user'];
    $phpmailer->Password   = $settings['smtp_pass'];
    $phpmailer->SMTPSecure = ( ($settings['smtp_enc']??'') === 'none' ) ? '' : ($settings['smtp_enc']??'tls');
    $phpmailer->From       = $settings['from_email'] ?? get_option('admin_email');
    $phpmailer->FromName   = $settings['from_name'] ?? get_bloginfo('name');
}

/* ── Order Notification Function ── */
function cso_send_admin_notification( $order_id ) {
    $settings = get_option( 'cso_smtp_settings' );
    if ( empty( $settings ) || empty( $settings['enabled'] ) || empty( $settings['admin_dest'] ) ) return;

    global $wpdb;
    $table = $wpdb->prefix . CSO_TABLE_ORDERS;
    $order = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $order_id ) );
    if ( ! $order ) return;

    $product_data = cso_get_product_data( $order->product_id );
    $subject = 'Nouvelle Commande Reçue : #' . $order_id . ' - ' . $order->full_name;
    
    $message  = "Une nouvelle commande a été passée sur votre boutique.\n\n";
    $message .= "--- DÉTAILS DE LA COMMANDE ---\n";
    $message .= "ID de Commande : #" . $order_id . "\n";
    $message .= "Produit : " . ($product_data['name'] ?? 'Produit Inconnu') . "\n";
    $message .= "Quantité : " . $order->quantity . "\n";
    $message .= "Total : " . number_format($order->total, 2) . " dh\n\n";
    
    $message .= "--- INFOS CLIENT ---\n";
    $message .= "Nom : " . $order->full_name . "\n";
    $message .= "Téléphone : " . $order->phone . "\n";
    $message .= "Adresse : " . $order->address . "\n";
    $message .= "Ville : " . $order->city . "\n";
    $message .= "Notes : " . $order->notes . "\n\n";
    
    $message .= "Gérer la Commande : " . admin_url('admin.php?page=cso-orders');

    wp_mail( $settings['admin_dest'], $subject, $message );
}

/**
 * Smart Checkout Redirect
 * Intercepts /checkout/ and serves the form dynamically to avoid 404s
 */
add_action('template_redirect', function() {
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    if ($path === 'checkout') {
        status_header(200);
        
        get_header();
        ?>
        <style>
            .multi-checkout-wrap { max-width: 1100px; margin: 50px auto; padding: 0 20px; display: grid; grid-template-columns: 1.4fr 1fr; gap: 40px; }
            .checkout-col { background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
            .multi-checkout-wrap h2 { margin-top: 0; font-size: 24px; font-weight: 800; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
            
            .chk-input-group { margin-bottom: 20px; }
            .chk-input-group label { display: block; font-weight: 700; margin-bottom: 8px; font-size: 14px; }
            .chk-input-group input, .chk-input-group textarea { width: 100%; padding: 12px 15px; border: 2px solid #eee; border-radius: 8px; font-family: inherit; font-size: 15px; transition: .3s; }
            .chk-input-group input:focus, .chk-input-group textarea:focus { border-color: var(--primary-red, #e74c3c); outline: none; }
            
            #chkProductsList { margin-bottom: 30px; }
            .chk-item { margin-bottom: 20px; padding: 20px; border: 1.5px solid #f0f0f0; border-radius: 12px; background: #fff; }
            .chk-item-top { display: flex; gap: 15px; align-items: start; margin-bottom: 15px; }
            .chk-item img { width: 80px; height: 80px; border-radius: 10px; object-fit: cover; background: #f5f5f5; border: 1px solid #eee; }
            .chk-item-info { flex: 1; }
            .chk-item-name { font-weight: 800; font-size: 16px; margin-bottom: 4px; color: #1e293b; }
            .chk-item-price-row { display: flex; justify-content: space-between; align-items: center; margin-top: 5px; }
            .chk-item-price { font-weight: 900; color: var(--primary-red, #e74c3c); font-size: 16px; }
            
            .chk-variations { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px dashed #eee; }
            .chk-var-select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; font-weight: 600; background: #fafafa; }
            
            .chk-offers-select { margin-top: 15px; width: 100%; padding: 12px; border: 2px solid #3b82f6; border-radius: 8px; background: #eff6ff; font-weight: 700; color: #1e40af; }
            
            .chk-summary-row { display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 16px; font-weight: 600; }
            .chk-summary-total { font-size: 24px; font-weight: 900; border-top: 2px solid #eee; padding-top: 15px; margin-top: 15px; color: #1e293b; }
            
            .chk-submit-btn { width: 100%; padding: 20px; border: none; background: #1e293b; color: #fff; font-size: 18px; font-weight: 800; border-radius: 12px; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: .3s; }
            .chk-submit-btn:hover { background: #e74c3c; transform: translateY(-2px); box-shadow: 0 10px 25px rgba(231,76,60,0.25); }
            
            @media(max-width: 991px) {
                .multi-checkout-wrap { grid-template-columns: 1fr; gap: 30px; }
                .checkout-col-form    { order: 2; }
                .checkout-col-summary { order: 1; }
            }
        </style>
        
        <div class="multi-checkout-wrap" id="multiCheckoutContainer" style="display:none;">
            <!-- Form Column -->
            <div class="checkout-col checkout-col-form">
                <h2 style="display:flex; align-items:center; gap:10px;"><i class="fas fa-truck" style="color:#e74c3c;"></i> Livraison</h2>
                <form id="multiCheckoutForm">
                    <div class="chk-input-group">
                        <label>Nom Complet *</label>
                        <input type="text" name="full_name" required placeholder="Ex: Ahmed Benani">
                    </div>
                    <div class="chk-input-group">
                        <label>Téléphone *</label>
                        <input type="tel" name="phone" required placeholder="Ex: 0612345678">
                    </div>
                    <div class="chk-input-group">
                        <label>Ville *</label>
                        <input type="text" name="city" required placeholder="Votre ville">
                    </div>
                    <div class="chk-input-group">
                        <label>Adresse *</label>
                        <textarea name="address" required rows="2" placeholder="Ex: Quartier Maârif, Rue 123..."></textarea>
                    </div>
                    <button type="submit" class="chk-submit-btn" id="chkSubmitBtn">Confirmer Ma Commande</button>
                </form>
            </div>
            
            <!-- Summary Column -->
            <div class="checkout-col checkout-col-summary" style="background:#f8fafc; border: 1.5px solid #eef2f6;">
                <h2 style="display:flex; align-items:center; gap:10px;"><i class="fas fa-shopping-bag" style="color:#1e293b;"></i> Résumé de la Commande</h2>
                <div id="chkProductsList"></div>
                <div class="chk-summary-area">
                    <div class="chk-summary-row">
                        <span>Sous-total:</span>
                        <span id="chkSubtotal">0.00 dh</span>
                    </div>
                    <div class="chk-summary-row">
                        <span>Livraison:</span>
                        <span style="color:#27ae60; font-weight:800;">GRATUITE</span>
                    </div>
                    <div class="chk-summary-row chk-summary-total">
                        <span>Total à Payer:</span>
                        <span id="chkGrandTotal">0.00 dh</span>
                    </div>
                </div>
                <div style="text-align:center; padding: 20px; background:#fff; border-radius:12px; margin-top:30px; border:1px solid #e2e8f0;">
                    <i class="fas fa-check-circle" style="color:#27ae60; font-size:24px; margin-bottom:10px;"></i>
                    <p style="font-size:14px; color:#475569; margin:0; font-weight:600;">Paiement Simple à la Livraison (COD)</p>
                </div>
            </div>
        </div>
        
        <div id="chkEmptyState" style="text-align:center; padding:120px 20px; display:none;">
            <div style="width:120px; height:120px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 30px;">
                <i class="fas fa-shopping-basket" style="font-size:50px; color:#cbd5e1;"></i>
            </div>
            <h2 style="font-weight:900; color:#1e293b; font-size:32px; margin-bottom:15px;">Votre panier est vide</h2>
            <p style="color:#64748b; font-size:18px; margin-bottom:40px; max-width:500px; margin-left:auto; margin-right:auto;">Découvrez nos nouveautés et commencez votre shopping aujourd'hui!</p>
            <a href="<?php echo home_url(); ?>" style="padding:18px 50px; background:#1e293b; color:#fff; border-radius:50px; text-decoration:none; font-weight:800; font-size:16px; box-shadow:0 10px 20px rgba(0,0,0,0.1);">Retourner à l'accueil</a>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cartItems = JSON.parse(localStorage.getItem('basma_cart')) || [];
            if(cartItems.length === 0) {
                document.getElementById('chkEmptyState').style.display = 'block';
                return;
            }
            
            document.getElementById('multiCheckoutContainer').style.display = 'grid';
            const listEl = document.getElementById('chkProductsList');
            
            // Build IDs for batch fetching variations
            const productIds = cartItems.map(i => i.id);
            const productMeta = {};

            function updateGlobalTotal() {
                let total = 0;
                $$('.chk-item').forEach(itemEl => {
                    const price = parseFloat(itemEl.dataset.currentPrice);
                    total += price;
                });
                document.getElementById('chkSubtotal').textContent = total.toFixed(2) + ' dh';
                document.getElementById('chkGrandTotal').textContent = total.toFixed(2) + ' dh';
            }

            const $$ = s => document.querySelectorAll(s);

            // Fetch product data (Variations/Offers)
            const fd = new FormData();
            fd.append('action', 'cso_get_batch_products');
            fd.append('ids', productIds.join(','));

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method:'POST', body:fd })
            .then(r => r.json())
            .then(res => {
                const dataMap = res.success ? res.data : {};
                
                cartItems.forEach((item, idx) => {
                    const meta = dataMap[item.id] || { offers:[], vars:[] };
                    const price = parseFloat(item.price);
                    
                    let html = `
                        <div class="chk-item" id="item-${idx}" data-pid="${item.id}" data-current-price="${price}">
                            <div class="chk-item-top">
                                <img src="${item.img}" alt="${item.name}">
                                <div class="chk-item-info">
                                    <div class="chk-item-name">${item.name}</div>
                                    <div class="chk-item-price-row">
                                        <div class="chk-item-qty">Qté: ${item.qty || 1}</div>
                                        <div class="chk-item-price" id="price-${idx}">${price.toFixed(2)} dh</div>
                                    </div>
                                </div>
                            </div>
                    `;

                    // Offers selection if available
                    if(meta.offers && meta.offers.length > 0) {
                        html += `<select class="chk-offers-select" data-idx="${idx}">`;
                        meta.offers.forEach(o => {
                            const isSelected = (parseInt(o.qty) === parseInt(item.qty)) ? 'selected' : '';
                            html += `<option value="${o.id}" data-price="${o.price}" ${isSelected}>${o.label} - ${o.price} dh</option>`;
                        });
                        html += `</select>`;
                    }

                    // Variations if available
                    if(meta.vars && meta.vars.length > 0) {
                        html += `<div class="chk-variations">`;
                        meta.vars.forEach(v => {
                            html += `
                                <div class="chk-var-group">
                                    <select class="chk-var-select" data-key="${v.key}">
                                        <option value="">${v.key}</option>
                                        ${v.values.map(val => `<option value="${val}">${val}</option>`).join('')}
                                    </select>
                                </div>
                            `;
                        });
                        html += `</div>`;
                    }

                    html += `</div>`;
                    listEl.innerHTML += html;
                });

                // Add quantity/offer change listeners
                $$('.chk-offers-select').forEach(sel => {
                    sel.addEventListener('change', function() {
                        const opt = this.options[this.selectedIndex];
                        const price = parseFloat(opt.dataset.price);
                        const idx = this.dataset.idx;
                        document.getElementById('price-'+idx).textContent = price.toFixed(2) + ' dh';
                        document.getElementById('item-'+idx).dataset.currentPrice = price;
                        updateGlobalTotal();
                    });
                });

                updateGlobalTotal();
            });
            
            // Handle Submit
            document.getElementById('multiCheckoutForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('chkSubmitBtn');
                const originalText = btn.textContent;
                btn.textContent = 'Traitement en cours...';
                btn.disabled = true;
                
                // Build products_json with variations
                const finalProducts = [];
                $$('.chk-item').forEach(itemEl => {
                    const rowId = itemEl.id.split('-')[1];
                    const productObj = {
                        id: itemEl.dataset.pid,
                        name: itemEl.querySelector('.chk-item-name').textContent,
                        price: parseFloat(itemEl.dataset.currentPrice)
                    };

                    // Get selections
                    const vars = {};
                    itemEl.querySelectorAll('.chk-var-select').forEach(vsel => {
                        if(vsel.value) vars[vsel.dataset.key] = vsel.value;
                    });
                    productObj.variations = vars;

                    // Get offer
                    const ofSel = itemEl.querySelector('.chk-offers-select');
                    if(ofSel) productObj.offer_id = ofSel.value;

                    finalProducts.push(productObj);
                });

                const formData = new FormData(this);
                formData.append('action', 'cso_submit_order');
                formData.append('nonce', '<?php echo wp_create_nonce("cso_submit_nonce"); ?>');
                formData.append('products_json', JSON.stringify(finalProducts));
                formData.append('product_id', finalProducts[0].id);
                
                fetch('<?php echo admin_url("admin-ajax.php"); ?>', { method:'POST', body:formData })
                .then(r => r.json())
                .then(res => {
                    if(res.success) {
                        // Order placed. Backend bot.php handles the WhatsApp notification.
                        btn.style.background = '#27ae60';
                        btn.innerHTML = '<i class="fas fa-check"></i> Commande Acceptée!';
                        localStorage.removeItem('basma_cart');
                        if(window.BasmaShop) { window.BasmaShop.cart = []; window.BasmaShop.save(); }
                        setTimeout(() => { window.location.href = '<?php echo home_url("/"); ?>?order=success'; }, 800);
                    } else {
                        alert("Note: " + res.data);
                        btn.textContent = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    alert('Erreur. Veuillez vérifier votre connexion.');
                    btn.textContent = originalText;
                    btn.disabled = false;
                });
            });
        });
        </script>
        <?php
        get_footer();
        exit;
    }
});

// New Batch Products AJAX
add_action('wp_ajax_cso_get_batch_products', function(){
    $ids = explode(',', $_POST['ids'] ?? '');
    $results = [];
    foreach($ids as $id) {
        $id = intval($id);
        if($id > 0) $results[$id] = cso_get_product_data($id);
    }
    wp_send_json_success($results);
});
add_action('wp_ajax_nopriv_cso_get_batch_products', function(){
    $ids = explode(',', $_POST['ids'] ?? '');
    $results = [];
    foreach($ids as $id) {
        $id = intval($id);
        if($id > 0) $results[$id] = cso_get_product_data($id);
    }
    wp_send_json_success($results);
});
