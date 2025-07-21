<?php

use Michal\Module\PromoBanner\Form\PromoBanner;

class AdminPromoBannerController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'promo_banner';
        $this->className = PromoBanner::class;
        $this->lang = true;
        $this->display = 'view';
        $this->allow_export = false;
        $this->identifier = 'id_promo_banner';
        $this->position_identifier = 'id_promo_banner';
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';
        parent::__construct();

        $this->actions = ['edit', 'delete'];

        $this->fields_list = [
            'id_promo_banner' => [
                'title' => $this->trans('ID', [], 'Modules.PsPromoBanner.Admin'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'position' => [
                'title'    => $this->trans('Position', [], 'Modules.PsPromoBanner.Admin'),
                'type'     => 'position',
                'align'    => 'center',
                'position' => 'position',
            ],
            'title' => [
                'title' => $this->trans('Title', [], 'Modules.PsPromoBanner.Admin'),
            ],
            'active' => [
                'title'   => $this->trans('Active', [], 'Modules.PsPromoBanner.Admin'),
                'align'   => 'text-center',
                'type'    => 'bool',
                'active'  => 'status',
                'orderby' => false,
            ],
            'hook_name' => [
                'title' => $this->trans('Display position', [], 'Modules.PsPromoBanner.Admin'),
                'align' => 'center',
            ],
            'currently_visible' => [
                'title'   => $this->trans('Currently visible', [], 'Modules.PsPromoBanner.Admin'),
                'type'    => 'text',
                'align'   => 'center',
                'callback'=> 'isBannerCurrentlyVisible',
                'orderby' => false,
                'search'  => false,
                'virtual' => true,
            ],
            'date_from' => [
                'title' => $this->trans('Date from', [], 'Modules.PsPromoBanner.Admin'),
            ],
            'date_to' => [
                'title' => $this->trans('Date to', [], 'Modules.PsPromoBanner.Admin'),
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text'  => $this->trans('Delete selected', [], 'Modules.PsPromoBanner.Admin'),
                'class' => 'btn-danger',
                'icon'  => 'delete',
            ]
        ];
    }
    public function initContent()
    {
        $this->context->smarty->assign('content', $this->renderView());
    }


    public function renderView()
    {
        $output = '';

        // 🔍 Sprawdź, czy ma być pokazany formularz:
        if (Tools::isSubmit('add' . $this->table) || Tools::isSubmit('update' . $this->table)) {
            $output .= '<div class="panel panel-add-edit">';
            $output .= '<h3><i class="icon-plus-square"></i> ' . $this->trans('Add / Edit Promo Banner', [], 'Modules.PsPromoBanner.Admin') . '</h3>';
            $output .= $this->renderForm();
            $output .= '</div>';
        } else {
            $output .= '<div class="panel clearfix">';
            $output .= '<a href="' . $this->context->link->getAdminLink($this->controller_name) . '&add' . $this->table . '" class="btn btn-primary pull-right" style="margin: 15px;"><i class="process-icon-new"></i> ' . $this->trans('Add new banner', [], 'Modules.PsPromoBanner.Admin') . '</a>';
            $output .= '</div>';
        }

        // 📦 Boxy z banerami pogrupowanymi po hookach
        $output .= '<div class="form-wrapper row">';
        foreach ($this->getUsedHooks() as $hookRow) {
            $hookName = $hookRow['hook_name'];

            $this->_where = 'AND a.`hook_name` = "' . pSQL($hookName) . '" AND a.`id_shop` = ' . (int)$this->context->shop->id;
            $this->_orderBy = 'position';
            $this->_orderWay = 'ASC';
            $this->list_id = $this->table . '_' . md5($hookName);

            $output .= '<div class="col-lg-12"><div class="panel">';
            $output .= '<div class="panel-heading" style="color:#22aac5;">' . $hookName;
            $output .= ' <small style="color:#555;">' . $this->trans('Banner group', [], 'Modules.PsPromoBanner.Admin') . '</small></div>';
            $output .= parent::renderList();
            $output .= '</div></div>';
        }
        $output .= '</div>';

        return $output;
    }


    public function renderList()
    {
        $outputs = '';
        $usedHooks = $this->getUsedHooks();

        foreach ($usedHooks as $hookRow) {
            $hookName = $hookRow['hook_name'];

            $this->_where = 'AND a.`hook_name` = "' . pSQL($hookName) . '" AND a.`id_shop` = ' . (int)$this->context->shop->id;
            $this->_orderBy = 'position';
            $this->_orderWay = 'ASC';

            // zmiana identyfikatora listy, żeby JS działał per-lista
            $this->list_id = $this->table . '_' . md5($hookName);

            $heading = '<h3 style="margin-top:30px;">' . $hookName . '</h3>';
            $outputs .= $heading . parent::renderList();
        }

        return $outputs;
    }


    public function renderForm()
    {
        $image = false;
        if (
            $this->display === 'edit'
            && isset($this->object)
            && $this->object->image
        ) {
            $imagePath = $this->context->link->getBaseLink() .
                'modules/ps_promo_banner/views/img/' . $this->object->image;
            $image = '<img src="' . $imagePath . '" alt="' . htmlspecialchars($this->object->title) . '" style="max-height:120px;margin-bottom:7px">';
        }

        $this->fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Promo banner', [], 'Modules.PsPromoBanner.Admin'),
                ],
                'input' => [
                    [
                        'type'     => 'text',
                        'label'    => $this->trans('Title', [], 'Modules.PsPromoBanner.Admin'),
                        'name'     => 'title',
                        'lang'     => true,
                        'required' => true,
                    ],
                    [
                        'type'  => 'textarea',
                        'label' => $this->trans('Text', [], 'Modules.PsPromoBanner.Admin'),
                        'name'  => 'text',
                        'lang'     => true,
                        'autocomplete' => false,
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->trans('Link', [], 'Modules.PsPromoBanner.Admin'),
                        'name'  => 'url',
                        'lang'     => true,
                        'autocomplete' => false,
                    ],
                    [
                        'type'          => 'file',
                        'label'         => $this->trans('Image', [], 'Modules.PsPromoBanner.Admin'),
                        'name'          => 'image',
                        'display_image' => true,
                        'image'         => $image,
                        'desc'          => $this->trans('Allowed formats: jpg, png, gif, webp. If you don\'t select a new file, the current one will remain.', [], 'Modules.PsPromoBanner.Admin'),
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Display position (hook)', [], 'Modules.PsPromoBanner.Admin'),
                        'name' => 'hook_name',
                        'required' => true,
                        'options' => [
                            'query' => $this->getAvailableHookOptions(),
                            'id' => 'name',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type'  => 'datetime',
                        'label' => $this->trans('From', [], 'Modules.PsPromoBanner.Admin'),
                        'name'  => 'date_from',
                    ],
                    [
                        'type'  => 'datetime',
                        'label' => $this->trans('To', [], 'Modules.PsPromoBanner.Admin'),
                        'name'  => 'date_to',
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->trans('Active', [], 'Modules.PsPromoBanner.Admin'),
                        'name'    => 'active',
                        'is_bool' => true,
                        'values'  => [
                            ['id' => 'active_on',  'value' => 1, 'label' => $this->trans('Yes', [], 'Modules.PsPromoBanner.Admin')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->trans('No', [], 'Modules.PsPromoBanner.Admin')],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Modules.PsPromoBanner.Admin'),
                ],
            ]
        ];

        $helper = new HelperForm();
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        $helper->show_toolbar = false;
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAdd' . $this->table;
        $helper->currentIndex = $this->context->link->getAdminLink($this->controller_name);
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->languages = $this->context->controller->getLanguages(false);
        $helper->id_language = $this->context->language->id;

        $helper->tpl_vars = [
            'fields_value' => $this->getFieldsValue($this->object),
            'languages'    => $helper->languages,
            'id_language'  => $helper->id_language,
        ];

        return $helper->generateForm([$this->fields_form]);
    }

    public function getFieldsValue($obj)
    {
        // np. Form handling:
        $languages = Language::getLanguages(false);
        $fields = [];

        foreach ($languages as $lang) {
            $id_lang = $lang['id_lang'];
            $fields['title'][$id_lang] = $this->getFieldValue($obj, 'title', $id_lang);
            $fields['text'][$id_lang]  = $this->getFieldValue($obj, 'text', $id_lang);
            $fields['url'][$id_lang]   = $this->getFieldValue($obj, 'url', $id_lang);
        }

        $fields['image']     = $this->getFieldValue($obj, 'image');
        $fields['hook_name'] = $this->getFieldValue($obj, 'hook_name');
        $fields['active']    = $this->getFieldValue($obj, 'active');
        $fields['date_from'] = $this->getFieldValue($obj, 'date_from');
        $fields['date_to']   = $this->getFieldValue($obj, 'date_to');

        return $fields;
    }
    protected function getAvailableHookOptions()
    {
        $db = Db::getInstance();
        $prefix = _DB_PREFIX_;

        $sql = "SELECT h.id_hook AS id, h.name AS name
            FROM {$prefix}hook h
            WHERE lower(h.name) LIKE 'display%'
            ORDER BY h.name ASC";

        $hooks = $db->executeS($sql);

        foreach ($hooks as $key => $hook) {
            if (preg_match('/admin/i', $hook['name']) || preg_match('/backoffice/i', $hook['name'])) {
                unset($hooks[$key]);
            }
        }

        return $hooks;
    }



    public function postProcess()
    {
        if (Tools::getIsset('action') && Tools::getValue('action') === 'updatePositions') {
            $this->updateBannerPositions();
        }
        if (
            Tools::isSubmit('submitAdd' . $this->table)
            || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')
        ) {
            $id = (int)Tools::getValue('id_promo_banner');
            $banner = $id ? new PromoBanner($id) : null;

            if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExts)) {
                    $this->errors[] = $this->trans('Allowed formats: JPG, PNG, GIF, WEBP.', [], 'Modules.PsPromoBanner.Admin');
                    return false;
                }
                $fileName = uniqid('banner_') . '.' . $ext;
                $uploadDir = _PS_MODULE_DIR_ . 'ps_promo_banner/views/img/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
                    $this->errors[] = $this->trans('Could not save file on server.', [], 'Modules.PsPromoBanner.Admin');
                    return false;
                }
                if ($banner && $banner->image && file_exists($uploadDir . $banner->image)) {
                    @unlink($uploadDir . $banner->image);
                }
                $_POST['image'] = $fileName;
                $_REQUEST['image'] = $fileName;
            } elseif ($id && $banner) {
                $_POST['image'] = $banner->image;
                $_REQUEST['image'] = $banner->image;
            }
        }
        if (Tools::isSubmit('submitAdd' . $this->table) || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
            $_POST['id_shop'] = $this->context->shop->id;
            $_REQUEST['id_shop'] = $this->context->shop->id;
        }
        return parent::postProcess();
    }

    public function processDelete()
    {
        $id = (int)Tools::getValue($this->identifier);
        if ($id) {
            $banner = new PromoBanner($id);
            $uploadDir = _PS_MODULE_DIR_ . 'ps_promo_banner/views/img/';
            if ($banner->image && file_exists($uploadDir . $banner->image)) {
                @unlink($uploadDir . $banner->image);
            }
        }
        return parent::processDelete();
    }

    public function processBulkDelete()
    {
        $banners = Tools::getValue($this->table . 'Box');
        if (is_array($banners)) {
            $uploadDir = _PS_MODULE_DIR_ . 'ps_promo_banner/views/img/';
            foreach ($banners as $id) {
                $banner = new PromoBanner($id);
                if ($banner->image && file_exists($uploadDir . $banner->image)) {
                    @unlink($uploadDir . $banner->image);
                }
            }
        }
        return parent::processBulkDelete();
    }
    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addjQueryPlugin('tablednd');
        $this->addJS(_PS_JS_DIR_ . 'admin/dnd.js');
    }
    protected function updateBannerPositions()
    {
        foreach ($this->getAvailableHookOptions() as $hook) {
            $hookName = $hook['name'];
            $inputName = $this->table . '_' . md5($hookName);

            $positions = Tools::getValue($inputName);
            if (!is_array($positions)) {
                continue;
            }

            $sql = 'UPDATE `' . _DB_PREFIX_ . $this->table . '` SET `position` = CASE `' . $this->identifier . '`';

            foreach ($positions as $pos => $rowIdentifier) {
                if (preg_match('/tr_\d+_(\d+)_\d+/', $rowIdentifier, $match)) {
                    $id = (int)$match[1];
                    $sql .= ' WHEN ' . $id . ' THEN ' . (int)$pos;
                }
            }

            $sql .= ' ELSE `position` END 
         WHERE `hook_name` = "' . pSQL($hookName) . '" 
         AND `id_shop` = ' . (int)$this->context->shop->id;

            Db::getInstance()->execute($sql);
        }

        if (!$positions || !is_array($positions)) {
            return false;
        }
        $sql = 'UPDATE `' . _DB_PREFIX_ . $this->table . '` SET `position` = CASE `' . $this->identifier . '`';
        foreach ($positions as $position => $rowIdentifier) {
            // przykład: tr_1_33_1 (musisz sprawdzić jak generuje się identyfikator w twojej liście!)
            if (preg_match('/tr_\d+_(\d+)_\d+/', $rowIdentifier, $matches)) {
                $id = (int)$matches[1];
                $sql .= ' WHEN ' . $id . ' THEN ' . (int)$position;
            }
        }
        $sql .= ' ELSE `position` END';
        return Db::getInstance()->execute($sql);
    }
    public function processAdd()
    {
        $return = parent::processAdd();
        $object = $this->object;
        if ($object && $object->id) {
            $idShop = (int)$this->context->shop->id;

            $maxPosition = Db::getInstance()->getValue('SELECT MAX(position) FROM `'._DB_PREFIX_.'promo_banner`');
            $object->position = (int)$maxPosition + 1;
            $object->id_shop = $idShop;

            $object->update();

        }
        return $return;
    }
    protected function getUsedHooks()
    {
        $sql = 'SELECT DISTINCT hook_name FROM '._DB_PREFIX_.'promo_banner 
        WHERE id_shop = ' . (int)$this->context->shop->id . ' 
        ORDER BY hook_name ASC';
        return Db::getInstance()->executeS($sql);
    }



}