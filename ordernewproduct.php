<?php
if (!defined('_PS_VERSION_'))
    exit;

class OrderNewProduct extends Module
{
    public function __construct()
    {
        $this->name = 'ordernewproduct';
        $this->tab = 'content_management';
        $this->version = '1.0';
        $this->author = 'Altitude Creation';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Ordre des nouveaux produits');
        $this->description = $this->l('Modifie l\'ordre des nouveaux produits.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('ORDER_NEW_PRODUCT'))
            $this->warning = $this->l('No name provided');
    }

    public function install()
    {
        if (Shop::isFeatureActive())
            Shop::setContext(Shop::CONTEXT_ALL);

        if (!parent::install() ||
            !$this->registerHook('leftColumn') ||
            !$this->registerHook('header') ||
            !$this->registerHook('BackOfficeHeader') ||
            !Configuration::updateValue('ORDER_NEW_PRODUCT', 'my friend')
        )
            return false;
        $this->_clearCache('*');
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !Configuration::deleteByName('ORDER_NEW_PRODUCT')
        )
            return false;

        $this->_clearCache('*');
        return true;
    }

    public function getContent()
    {

        $output = null;
        $link = new LinkCore();

        if (Tools::isSubmit('submit' . $this->name)) {
            $ordernewproduct = strval(Tools::getValue('ORDER_NEW_PRODUCT'));
            if (!$ordernewproduct
                || empty($ordernewproduct)
            ) {
                $output .= $this->displayError('Erreur : ' . $ordernewproduct);
            } else {
                Configuration::updateValue('ORDER_NEW_PRODUCT', $ordernewproduct);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        $joursnouveaute = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');
        if (!empty($joursnouveaute)) {
            $output .= "<p class='ordernewproduct-info'>Les produits sont considérés comme nouveau pendant " . $joursnouveaute . " jours.</p>";
        }

        // Clear cache
        $this->_clearCache('*');

        // recuperation des nouveaux produits en cours - Ordre par date de mise a jour
        $newProducts = Product::getNewProducts((int)$this->context->language->id, 0, 9999, false, $order_by = 'date_upd',$order_way = 'ASC');
        //($id_lang, $page_number = 0, $nb_products = 10, $count = false, $order_by = null, $order_way = null, Context $context = null)

        // affichage de la liste des nouveautes
        $output .= '<ul id="sortablenewProducts">';
        if (!empty($newProducts)) {
            foreach ($newProducts as $newProduct) {
                // calcul jours restant en nouveaute
                $current_date = time();
                $date_creation = strtotime($newProduct['date_add']);
                $datediff = $current_date - $date_creation;
                $joursrestants = $joursnouveaute - (floor($datediff / (60 * 60 * 24)));
                $imagePath = $link->getImageLink($newProduct['link_rewrite'], $newProduct['id_image'], 'medium_default');
                $output .= '<li class="ordernewproduct-item"><div class="ordernewproduct-item-date" data-id="' . $newProduct['id_product'] . '" data-maj="' . $newProduct['date_upd'] . '"><img class="img-order-item" src="http://' . $imagePath . '" /><p><strong>' . $newProduct['name'] . '</strong><br/>Jours restants : ' . $joursrestants . ' j</p> <a class="link-order-item" href="' . $newProduct['link'] . '">Voir la fiche produit</a></div></li>';
                //$output .= p($newProduct);
            }
        } else {
            $output .= $this->displayError("Il n'y a pas de produit en nouveauté. ");
        }
        $output .= '</ul>';
        return $output;
    }

    /* CONFIGURATION
    _________________________________________________________ */

    public function displayForm()
    {
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');


        // Init Fields form array
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Configuration'),
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Configuration value'),
                    'name' => 'ORDER_NEW_PRODUCT',
                    'size' => 20,
                    'required' => true
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'button'
            )
        );

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = false; // false -> remove toolbar
        $helper->toolbar_scroll = true; // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                        '&token=' . Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Load current value
        $helper->fields_value['ORDER_NEW_PRODUCT'] = Configuration::get('ORDER_NEW_PRODUCT');

        return $helper->generateForm($fields_form);
    }

    /* HOOK DISPLAY
    _________________________________________________________ */

    public function hookDisplayLeftColumn($params)
    {
        $this->context->smarty->assign(
            array(
                'config_chiffre' => Configuration::get('ORDER_NEW_PRODUCT'),
                'lien' => $this->context->link->getModuleLink('ordernewproduct', 'display'),
                'message' => $this->l('This is a simple text message'),

            )
        );
        return $this->display(__FILE__, 'ordernewproduct.tpl');
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->hookDisplayLeftColumn($params);
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addCSS($this->_path . 'css/style.css', 'all');
    }

    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addCSS($this->_path . '/css/admin.css');
        //  $this->context->controller->addJs($this->_path.'../../js/plugins/growl/jquery.growl.js');
        $this->context->controller->addJs($this->_path . '/script/script.js');
    }

}