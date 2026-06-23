<?php
/**
 * Synonym Map
 *
 * Each key is a search term (lowercase). The value is an array of synonyms
 * that will be added as OR conditions to the search query.
 *
 * Used by Search_Algorithm::expand_synonyms().
 * Extend via the `nivo_search_synonyms` filter.
 *
 * @package NivoSearch
 * @since 3.0.0
 */

defined( 'ABSPATH' ) || exit;

return array(

    // -------------------------
    // Electronics & Devices
    // -------------------------
    'phone'          => array( 'mobile', 'smartphone', 'cell phone', 'cellphone', 'handset' ),
    'mobile'         => array( 'phone', 'smartphone', 'cell phone', 'handset' ),
    'smartphone'     => array( 'phone', 'mobile', 'cell phone', 'handset' ),
    'tv'             => array( 'television', 'screen', 'monitor', 'display', 'tele' ),
    'television'     => array( 'tv', 'screen', 'display' ),
    'laptop'         => array( 'notebook', 'computer', 'pc', 'macbook', 'chromebook' ),
    'notebook'       => array( 'laptop', 'computer', 'pc' ),
    'computer'       => array( 'laptop', 'notebook', 'pc', 'desktop' ),
    'pc'             => array( 'computer', 'desktop', 'laptop' ),
    'tablet'         => array( 'ipad', 'pad', 'slate' ),
    'ipad'           => array( 'tablet', 'pad' ),
    'earphones'      => array( 'earbuds', 'headphones', 'in-ear', 'earpiece' ),
    'earbuds'        => array( 'earphones', 'headphones', 'in-ear' ),
    'headphones'     => array( 'earphones', 'earbuds', 'headset', 'cans' ),
    'headset'        => array( 'headphones', 'earphones', 'earbuds' ),
    'speaker'        => array( 'speakers', 'soundbar', 'audio', 'bluetooth speaker' ),
    'speakers'       => array( 'speaker', 'soundbar', 'audio' ),
    'soundbar'       => array( 'speaker', 'speakers', 'audio bar' ),
    'charger'        => array( 'adapter', 'power supply', 'charging cable', 'cable' ),
    'cable'          => array( 'wire', 'cord', 'lead', 'charger' ),
    'cord'           => array( 'cable', 'wire', 'lead' ),
    'router'         => array( 'wifi router', 'modem', 'network router', 'wireless router' ),
    'modem'          => array( 'router', 'wifi router' ),
    'camera'         => array( 'cam', 'dslr', 'mirrorless', 'digital camera' ),
    'dslr'           => array( 'camera', 'digital camera', 'slr' ),
    'printer'        => array( 'inkjet', 'laser printer', 'all-in-one printer' ),
    'projector'      => array( 'beamer', 'display projector' ),
    'drone'          => array( 'quadcopter', 'uav', 'flying camera' ),
    'smartwatch'     => array( 'smart watch', 'watch', 'wearable', 'fitness watch' ),
    'watch'          => array( 'smartwatch', 'timepiece', 'wristwatch' ),
    'power bank'     => array( 'portable charger', 'battery pack', 'external battery' ),
    'keyboard'       => array( 'keys', 'typing device', 'mechanical keyboard' ),
    'mouse'          => array( 'trackpad', 'pointing device', 'wireless mouse' ),
    'hard drive'     => array( 'hdd', 'hard disk', 'external drive', 'storage drive' ),
    'hdd'            => array( 'hard drive', 'hard disk', 'storage' ),
    'ssd'            => array( 'solid state drive', 'flash storage', 'nvme' ),
    'memory'         => array( 'ram', 'storage', 'sd card' ),
    'ram'            => array( 'memory', 'ddr' ),
    'flash drive'    => array( 'usb stick', 'pen drive', 'thumb drive', 'usb drive' ),
    'usb stick'      => array( 'flash drive', 'pen drive', 'thumb drive' ),

    // -------------------------
    // Clothing & Accessories
    // -------------------------
    'shoes'          => array( 'footwear', 'sneakers', 'boots', 'sandals', 'trainers' ),
    'sneakers'       => array( 'shoes', 'trainers', 'runners', 'athletic shoes', 'sports shoes' ),
    'trainers'       => array( 'sneakers', 'shoes', 'athletic shoes', 'runners' ),
    'boots'          => array( 'shoes', 'footwear', 'ankle boots', 'knee boots' ),
    'sandals'        => array( 'shoes', 'footwear', 'flip flops', 'slides' ),
    'flip flops'     => array( 'sandals', 'slides', 'thongs' ),
    'jacket'         => array( 'coat', 'blazer', 'outerwear', 'windbreaker', 'hoodie' ),
    'coat'           => array( 'jacket', 'outerwear', 'overcoat', 'parka' ),
    'hoodie'         => array( 'sweatshirt', 'hooded sweatshirt', 'pullover', 'jacket' ),
    'sweatshirt'     => array( 'hoodie', 'pullover', 'sweater' ),
    'sweater'        => array( 'jumper', 'pullover', 'knitwear', 'sweatshirt' ),
    'jumper'         => array( 'sweater', 'pullover', 'knitwear' ),
    'jeans'          => array( 'denim', 'trousers', 'pants', 'denims' ),
    'trousers'       => array( 'pants', 'jeans', 'slacks', 'bottoms' ),
    'pants'          => array( 'trousers', 'jeans', 'slacks', 'bottoms' ),
    'shorts'         => array( 'short pants', 'bottoms', 'bermuda' ),
    'dress'          => array( 'gown', 'frock', 'outfit' ),
    'skirt'          => array( 'mini skirt', 'midi skirt', 'maxi skirt' ),
    't-shirt'        => array( 'tee', 'tshirt', 'top', 'shirt' ),
    'tee'            => array( 't-shirt', 'tshirt', 'top' ),
    'shirt'          => array( 't-shirt', 'blouse', 'top', 'polo' ),
    'blouse'         => array( 'shirt', 'top' ),
    'top'            => array( 'shirt', 't-shirt', 'blouse', 'tank top' ),
    'underwear'      => array( 'lingerie', 'underpants', 'briefs', 'boxer' ),
    'socks'          => array( 'stockings', 'hosiery' ),
    'gloves'         => array( 'mittens', 'hand warmers' ),
    'hat'            => array( 'cap', 'beanie', 'headwear', 'headgear' ),
    'cap'            => array( 'hat', 'baseball cap', 'beanie' ),
    'beanie'         => array( 'hat', 'cap', 'winter hat', 'skull cap' ),
    'scarf'          => array( 'shawl', 'wrap', 'neck warmer' ),
    'belt'           => array( 'strap', 'waistband' ),
    'bag'            => array( 'handbag', 'purse', 'tote', 'backpack', 'satchel' ),
    'handbag'        => array( 'bag', 'purse', 'tote', 'clutch' ),
    'purse'          => array( 'handbag', 'bag', 'clutch', 'wallet' ),
    'backpack'       => array( 'bag', 'rucksack', 'knapsack' ),
    'wallet'         => array( 'purse', 'cardholder', 'billfold' ),
    'sunglasses'     => array( 'shades', 'glasses', 'eyewear', 'sunnies' ),
    'glasses'        => array( 'sunglasses', 'spectacles', 'eyeglasses', 'eyewear' ),
    'jewelry'        => array( 'jewellery', 'accessories', 'ornament' ),
    'jewellery'      => array( 'jewelry', 'accessories', 'ornament' ),
    'necklace'       => array( 'chain', 'pendant', 'choker', 'jewelry' ),
    'ring'           => array( 'band', 'jewelry', 'jewellery' ),
    'bracelet'       => array( 'bangle', 'wristband', 'jewelry' ),
    'earrings'       => array( 'earring', 'studs', 'hoops', 'jewelry' ),

    // -------------------------
    // Home & Living
    // -------------------------
    'sofa'           => array( 'couch', 'settee', 'loveseat', 'divan', 'furniture' ),
    'couch'          => array( 'sofa', 'settee', 'loveseat', 'furniture' ),
    'chair'          => array( 'seat', 'armchair', 'furniture' ),
    'table'          => array( 'desk', 'surface', 'furniture' ),
    'desk'           => array( 'table', 'workstation', 'furniture' ),
    'bed'            => array( 'bedframe', 'cot', 'furniture' ),
    'mattress'       => array( 'bed', 'sleeping surface', 'memory foam' ),
    'shelf'          => array( 'shelves', 'bookshelf', 'shelving', 'rack', 'storage' ),
    'shelves'        => array( 'shelf', 'bookshelf', 'shelving', 'rack' ),
    'lamp'           => array( 'light', 'lighting', 'floor lamp', 'table lamp' ),
    'light'          => array( 'lamp', 'lighting', 'bulb', 'led' ),
    'rug'            => array( 'carpet', 'mat', 'floor covering' ),
    'carpet'         => array( 'rug', 'mat', 'floor covering' ),
    'curtains'       => array( 'drapes', 'blinds', 'window covering', 'curtain' ),
    'drapes'         => array( 'curtains', 'blinds', 'window covering' ),
    'blinds'         => array( 'curtains', 'drapes', 'shutters' ),
    'pillow'         => array( 'cushion', 'throw pillow' ),
    'cushion'        => array( 'pillow', 'throw pillow' ),
    'blanket'        => array( 'throw', 'quilt', 'comforter', 'duvet' ),
    'quilt'          => array( 'blanket', 'comforter', 'duvet' ),
    'duvet'          => array( 'quilt', 'comforter', 'blanket' ),
    'mirror'         => array( 'looking glass', 'wall mirror', 'vanity mirror' ),
    'clock'          => array( 'timepiece', 'wall clock', 'alarm clock' ),
    'candle'         => array( 'tealight', 'pillar candle', 'scented candle' ),
    'vase'           => array( 'flower vase', 'pot', 'planter' ),
    'frame'          => array( 'picture frame', 'photo frame', 'wall frame' ),
    'storage'        => array( 'organizer', 'container', 'box', 'shelf', 'rack' ),
    'organizer'      => array( 'storage', 'holder', 'container', 'rack' ),

    // -------------------------
    // Kitchen
    // -------------------------
    'kettle'         => array( 'electric kettle', 'jug kettle', 'water boiler' ),
    'toaster'        => array( 'bread toaster', 'toaster oven' ),
    'blender'        => array( 'mixer', 'food processor', 'juicer', 'smoothie maker' ),
    'mixer'          => array( 'blender', 'hand mixer', 'stand mixer', 'food processor' ),
    'coffee maker'   => array( 'coffee machine', 'espresso machine', 'cafetiere', 'french press' ),
    'coffee machine' => array( 'coffee maker', 'espresso machine' ),
    'microwave'      => array( 'microwave oven', 'microwave cooker' ),
    'fridge'         => array( 'refrigerator', 'cooler', 'mini fridge' ),
    'refrigerator'   => array( 'fridge', 'cooler' ),
    'pan'            => array( 'frying pan', 'skillet', 'saucepan', 'cookware' ),
    'pot'            => array( 'saucepan', 'cooking pot', 'stockpot', 'cookware' ),
    'cookware'       => array( 'pots', 'pans', 'kitchen set', 'cooking set' ),
    'knife'          => array( 'blade', 'chef knife', 'kitchen knife', 'cutlery' ),
    'cutlery'        => array( 'silverware', 'utensils', 'flatware', 'knife', 'fork', 'spoon' ),
    'cups'           => array( 'mugs', 'glasses', 'drinkware' ),
    'mug'            => array( 'cup', 'coffee mug', 'tea mug' ),
    'plate'          => array( 'dish', 'dinnerware', 'plates' ),
    'bowl'           => array( 'dish', 'serving bowl', 'mixing bowl' ),

    // -------------------------
    // Beauty & Personal Care
    // -------------------------
    'shampoo'        => array( 'hair wash', 'hair cleanser' ),
    'conditioner'    => array( 'hair conditioner', 'hair treatment', 'hair mask' ),
    'moisturizer'    => array( 'lotion', 'cream', 'face cream', 'skin cream', 'body lotion' ),
    'lotion'         => array( 'moisturizer', 'cream', 'body lotion', 'skin lotion' ),
    'cream'          => array( 'lotion', 'moisturizer', 'ointment' ),
    'sunscreen'      => array( 'sun cream', 'spf', 'sun protection', 'sunblock' ),
    'sunblock'       => array( 'sunscreen', 'sun cream', 'spf' ),
    'lipstick'       => array( 'lip color', 'lip colour', 'lip gloss', 'lip product' ),
    'lip gloss'      => array( 'lipstick', 'lip balm', 'lip product' ),
    'mascara'        => array( 'eye makeup', 'lash mascara' ),
    'foundation'     => array( 'base makeup', 'face foundation', 'bb cream' ),
    'perfume'        => array( 'fragrance', 'cologne', 'scent', 'eau de parfum', 'edp' ),
    'cologne'        => array( 'perfume', 'fragrance', 'scent', 'aftershave' ),
    'deodorant'      => array( 'antiperspirant', 'body spray', 'deo' ),
    'toothpaste'     => array( 'dental paste', 'tooth gel' ),
    'toothbrush'     => array( 'electric toothbrush', 'dental brush', 'teeth brush' ),
    'razor'          => array( 'shaver', 'shaving razor', 'electric razor', 'epilator' ),
    'shaver'         => array( 'razor', 'electric shaver', 'trimmer' ),
    'trimmer'        => array( 'shaver', 'beard trimmer', 'hair trimmer', 'clipper' ),
    'vitamins'       => array( 'supplements', 'vitamin', 'multivitamin', 'minerals' ),
    'supplement'     => array( 'vitamins', 'protein', 'capsules', 'tablets' ),
    'protein'        => array( 'protein powder', 'whey', 'supplement' ),

    // -------------------------
    // Sports & Fitness
    // -------------------------
    'gym'            => array( 'fitness', 'workout', 'exercise', 'training' ),
    'fitness'        => array( 'gym', 'workout', 'exercise', 'training', 'sport' ),
    'exercise'       => array( 'workout', 'fitness', 'training', 'sport' ),
    'workout'        => array( 'exercise', 'fitness', 'training', 'gym' ),
    'dumbbell'       => array( 'weights', 'free weights', 'hand weights', 'dumbbells' ),
    'weights'        => array( 'dumbbell', 'dumbbells', 'barbell', 'free weights' ),
    'yoga'           => array( 'pilates', 'stretching', 'meditation', 'yoga mat' ),
    'yoga mat'       => array( 'exercise mat', 'gym mat', 'workout mat' ),
    'bicycle'        => array( 'bike', 'cycle', 'cycling', 'road bike', 'mountain bike' ),
    'bike'           => array( 'bicycle', 'cycle', 'cycling' ),
    'running'        => array( 'jogging', 'running shoes', 'marathon', 'sprint' ),
    'swimming'       => array( 'swim', 'pool', 'swimwear', 'swimsuit' ),
    'football'       => array( 'soccer', 'ball', 'sport' ),
    'soccer'         => array( 'football', 'ball', 'sport' ),
    'basketball'     => array( 'ball', 'sport', 'hoop' ),
    'tennis'         => array( 'racket', 'ball', 'sport', 'badminton' ),
    'camping'        => array( 'outdoor', 'hiking', 'trekking', 'backpacking' ),
    'hiking'         => array( 'trekking', 'walking', 'camping', 'outdoor' ),
    'tent'           => array( 'camping tent', 'shelter', 'canopy' ),
    'sleeping bag'   => array( 'camp bed', 'bivouac', 'sleeping mat' ),

    // -------------------------
    // Baby & Kids
    // -------------------------
    'baby'           => array( 'infant', 'newborn', 'toddler', 'kids', 'child' ),
    'toddler'        => array( 'baby', 'child', 'infant', 'kids' ),
    'kids'           => array( 'children', 'baby', 'toddler', 'boys', 'girls' ),
    'children'       => array( 'kids', 'child', 'baby', 'toddler' ),
    'toy'            => array( 'toys', 'game', 'plaything', 'kids toy' ),
    'toys'           => array( 'toy', 'games', 'playthings' ),
    'game'           => array( 'toy', 'board game', 'puzzle', 'play' ),
    'puzzle'         => array( 'jigsaw', 'game', 'toy' ),
    'stroller'       => array( 'pram', 'buggy', 'pushchair', 'baby carriage' ),
    'pram'           => array( 'stroller', 'buggy', 'pushchair' ),
    'diaper'         => array( 'nappy', 'nappies', 'diapers' ),
    'nappy'          => array( 'diaper', 'nappies', 'diapers' ),

    // -------------------------
    // Food & Grocery
    // -------------------------
    'snack'          => array( 'snacks', 'treat', 'nibbles', 'crisps' ),
    'snacks'         => array( 'snack', 'treats', 'nibbles', 'crisps', 'chips' ),
    'drink'          => array( 'beverage', 'drinks', 'juice', 'water', 'soda' ),
    'beverage'       => array( 'drink', 'drinks', 'juice', 'water' ),
    'coffee'         => array( 'espresso', 'latte', 'cappuccino', 'brew', 'instant coffee' ),
    'tea'            => array( 'herbal tea', 'green tea', 'black tea', 'infusion' ),
    'chocolate'      => array( 'candy', 'sweets', 'confectionery', 'cocoa' ),
    'candy'          => array( 'sweets', 'chocolate', 'confectionery', 'lollipop', 'gummy' ),
    'sweets'         => array( 'candy', 'chocolate', 'confectionery', 'dessert' ),
    'organic'        => array( 'natural', 'eco', 'chemical-free', 'bio' ),
    'natural'        => array( 'organic', 'eco', 'pure', 'wholesome' ),

    // -------------------------
    // Office & Stationery
    // -------------------------
    'pen'            => array( 'ballpoint', 'marker', 'pencil', 'writing', 'biro' ),
    'pencil'         => array( 'pen', 'writing', 'graphite' ),
    'notebook'       => array( 'notepad', 'journal', 'diary', 'exercise book' ),
    'notepad'        => array( 'notebook', 'pad', 'journal' ),
    'diary'          => array( 'journal', 'planner', 'notebook', 'calendar' ),
    'planner'        => array( 'diary', 'organizer', 'calendar', 'scheduler' ),
    'calendar'       => array( 'planner', 'diary', 'scheduler', 'wall calendar' ),
    'stapler'        => array( 'binding', 'office supply' ),
    'scissors'       => array( 'shears', 'cutter' ),
    'tape'           => array( 'adhesive tape', 'sellotape', 'duct tape', 'masking tape' ),
    'folder'         => array( 'binder', 'file', 'document folder' ),
    'binder'         => array( 'folder', 'ring binder', 'file' ),
    'desk'           => array( 'table', 'workstation', 'work desk', 'study desk' ),
    'office chair'   => array( 'desk chair', 'ergonomic chair', 'work chair' ),
);
