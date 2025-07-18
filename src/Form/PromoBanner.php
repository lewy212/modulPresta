<?php
namespace Michal\Module\PromoBanner\Form;
use ObjectModel;
class PromoBanner extends ObjectModel
{
    public $id_promo_banner;

    public $active;

    public $image;

    public $title;

    public $text;

    public $url;
    public $hook_name;

    public $date_from;

    public $date_to;

    public $date_add;

    public $date_upd;

    public static $definition = [
        'table'     => 'promo_banner',
        'primary'   => 'id_promo_banner',
        'multilang' => false,
        'fields'    => [
            'active'    => ['type' => self::TYPE_BOOL,   'validate' => 'isBool'],
            'image'     => ['type' => self::TYPE_STRING, 'validate' => 'isString',    'size' => 255],
            'title'     => ['type' => self::TYPE_STRING, 'validate' => 'isString',    'size' => 255],
            'text'      => ['type' => self::TYPE_HTML,   'validate' => 'isCleanHtml'],
            'url'       => ['type' => self::TYPE_STRING, 'validate' => 'isUrl',       'size' => 255],
            'hook_name' => ['type' => self::TYPE_STRING, 'validate' => 'isHookName',  'size' => 64],
            'date_from' => ['type' => self::TYPE_DATE,   'validate' => 'isDateFormat'],
            'date_to'   => ['type' => self::TYPE_DATE,   'validate' => 'isDateFormat'],
            'date_add'  => ['type' => self::TYPE_DATE,   'validate' => 'isDateFormat'],
            'date_upd'  => ['type' => self::TYPE_DATE,   'validate' => 'isDateFormat'],
        ],
    ];


    public static function getActiveBanners()
    {
        $now = date('Y-m-d H:i:s');
        $where = 'WHERE active = 1 AND (date_from <= "' . pSQL($now) . '" AND date_to >= "' . pSQL($now) . '")';
        return self::getBanners($where);
    }

    public static function getBanners($where = '')
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promo_banner` ' . ($where ?: '');
        return Db::getInstance()->executeS($sql);
    }
    public static function getBannersByHook($hook)
    {
        $now = date('Y-m-d H:i:s');

        $sql = 'SELECT * FROM `'._DB_PREFIX_.'promo_banner` 
            WHERE active = 1
              AND hook_name = "' . pSQL($hook) . '"
              AND date_from <= "' . pSQL($now) . '"
              AND date_to >= "' . pSQL($now) . '"';

        return Db::getInstance()->executeS($sql);
    }

}
