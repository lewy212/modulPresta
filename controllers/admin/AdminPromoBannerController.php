<?php

require_once _PS_MODULE_DIR_ . 'ps_promo_banner/classes/PromoBanner.php';

class AdminPromoBannerController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'promo_banner';
        $this->className = 'PromoBanner';
        $this->lang = false;
        $this->allow_export = false;
        $this->identifier = 'id_promo_banner';

        parent::__construct();

        $this->actions = ['edit', 'delete'];

        $this->fields_list = [
            'id_promo_banner' => [
                'title' => $this->trans('ID', [], 'Modules.PsPromoBanner.Admin'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
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
            'legend' => [
                'title' => $this->trans('Promo banner', [], 'Modules.PsPromoBanner.Admin'),
            ],
            'input' => [
                [
                    'type'     => 'text',
                    'label'    => $this->trans('Title', [], 'Modules.PsPromoBanner.Admin'),
                    'name'     => 'title',
                    'required' => true,
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->trans('Text', [], 'Modules.PsPromoBanner.Admin'),
                    'name'  => 'text',
                    'autocomplete' => false,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->trans('Link', [], 'Modules.PsPromoBanner.Admin'),
                    'name'  => 'url',
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
                        'query' => [
                            ['id' => 'displayHome', 'name' => $this->trans('Homepage', [], 'Modules.PsPromoBanner.Admin')],
                            ['id' => 'displayTop', 'name' => $this->trans('Header', [], 'Modules.PsPromoBanner.Admin')],
                            ['id' => 'displayFooter', 'name' => $this->trans('Footer', [], 'Modules.PsPromoBanner.Admin')],
                           ],
                        'id' => 'id',
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
        ];

        return parent::renderForm();
    }

    public function isBannerCurrentlyVisible($id, $row)
    {

    }

    public function postProcess()
    {
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
}
