<?php
namespace WCProductsWizard;

/**
 * Core Class
 *
 * @class Core
 * @version 13.1.1
 */
class Core
{
    // <editor-fold desc="Properties">
    /**
     * Self instance variable
     * @var Core The single instance of the class
     */
    protected static $instance = null;

    /**
     * Is WooCommerce active
     * @var boolean
     */
    public static $wcIsActive = false;

    /**
     * Storage instance variable
     * @var Storage
     */
    public $storage = null;

    /**
     * Cart instance variable
     * @var Cart
     */
    public $cart = null;

    /**
     * Admin part instance variable
     * @var Admin
     */
    protected $admin = null;

    /**
     * Template class instance variable
     * @var Template
     */
    public $template = null;

    /**
     * Wizard class instance variable
     * @var Entities\Wizard
     */
    public $wizard = null;

    /**
     * Wizard step class instance variable
     * @var Entities\WizardStep
     */
    public $wizardStep = null;

    /**
     * Product class instance variable
     * @var Entities\Product
     */
    public $product = null;

    /**
     * Product category class instance variable
     * @var Entities\ProductCategory
     */
    public $productCategory = null;

    /**
     * Product variation class instance variable
     * @var Entities\ProductVariation
     */
    public $productVariation = null;

    /**
     * Settings class instance variable
     * @var Settings
     */
    public $settings = null;

    /**
     * Form class instance variable
     * @var Form
     */
    public $form = null;
    // </editor-fold>

    /** Class Constructor */
    public function __construct()
    {
        $this->loadClasses();

        // actions
        add_action('plugins_loaded', [$this, 'loadTextDomain']);
        add_action('plugins_loaded', [$this, 'pluginsLoadedAction']);
        add_action('woocommerce_init', [$this, 'wcInitAction'], 1);
        add_action('before_woocommerce_init', [$this, 'declareWcCompatibility']);
        add_action('wcpw_before_output', [$this, 'beforeOutputAction']);
        add_action('wcpw_after_output', [$this, 'afterOutputAction']);

        // scripts and styles
        add_action('wcpw_before', [$this, 'enqueueAssets']);

        do_action('wcpw_init', $this);
    }

    /** Load main classes */
    public function loadClasses()
    {
        // include base slave classes
        $requiredClasses = [
            'DataBase/Entity',
            'DataBase/Option',
            'DataBase/Term',
            'Traits/Settings',
            'Interfaces/Settings',
            'Entities/Interfaces/PostType',
            'Entities/Traits/PostType',
            'Entities/Extensions/Admin',
            'Entities/Wizard',
            'Entities/WizardStep',
            'Entities/Product',
            'Entities/ProductCategory',
            'Entities/ProductVariation',
            'Utils',
            'Settings',
            'Storage',
            'Cart',
            'Template',
            'Form',
        ];

        foreach ($requiredClasses as $requiredClass) {
            if (!class_exists(__NAMESPACE__ . $requiredClass)
                && file_exists(__DIR__ . DIRECTORY_SEPARATOR . $requiredClass . '.php')
            ) {
                require_once(__DIR__ . DIRECTORY_SEPARATOR . $requiredClass . '.php');
            }
        }

        $this->settings = new Settings();
        $this->storage = new Storage();
        $this->cart = new Cart();
        $this->template = new Template();
        $this->wizard = new Entities\Wizard();
        $this->wizardStep = new Entities\WizardStep();
        $this->product = new Entities\Product();
        $this->productCategory = new Entities\ProductCategory();
        $this->productVariation = new Entities\ProductVariation();
        $this->form = new Form();
    }

    /**
     * Get single class instance
     *
     * @static
     * @return Core
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** Fires on plugins are loaded */
    public function pluginsLoadedAction()
    {
        self::$wcIsActive = class_exists('\WooCommerce');

        if (!self::$wcIsActive) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-warning is-dismissible"><p>'
                    . esc_html__('WooCommerce is required for WC Products Wizard', 'products-wizard-lite-for-woocommerce')
                    . '</p></div>';
            });
        }
    }

    /** Fires on woocommerce plugin is loaded */
    public function wcInitAction()
    {
        // if is admin page
        if (is_admin() || wp_doing_cron()) {
            if (!class_exists('\\WCProductsWizard\\Admin')) {
                require_once(__DIR__ . DIRECTORY_SEPARATOR . 'Admin.php');
            }

            $this->admin = new Admin();
        }
    }

    /** Declare compatibility with WC features */
    public function declareWcCompatibility()
    {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', WC_PRODUCTS_WIZARD_ROOT_FILE);
        }
    }

    /**
     * Fires on output call
     *
     * @param array $args
     */
    public function beforeOutputAction($args)
    {
        if (!empty($args['id'])) {
            $id = (int) $args['id'];
            $this->wizard->setCurrentId($id);
        }
    }

    /** Fires after output call */
    public function afterOutputAction()
    {
        $this->wizard->setCurrentId(0);
        $this->wizardStep->setCurrentId('');
    }

    /** Styles and scripts enqueue */
    public function enqueueAssets()
    {
        wp_enqueue_script(
            'woocommerce-products-wizard',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/front/js/app.min.js',
            [],
            WC_PRODUCTS_WIZARD_VERSION,
            true
        );

        wp_enqueue_style(
            'woocommerce-products-wizard-full',
            WC_PRODUCTS_WIZARD_PLUGIN_URL . 'assets/front/css/app-full.min.css',
            [],
            WC_PRODUCTS_WIZARD_VERSION
        );
    }

    /** Load text domain */
    public function loadTextDomain()
    {
        load_plugin_textdomain(
            'products-wizard-lite-for-woocommerce',
            false,
            basename(WC_PRODUCTS_WIZARD_PLUGIN_PATH) . '/languages/'
        );
    }
}
