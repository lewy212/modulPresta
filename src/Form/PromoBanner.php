<?php
namespace Michal\Module\PromoBanner\Form;
use ObjectModel;
use Db;
use Context;
class PromoBanner extends ObjectModel
{
    public $id_promo_banner;

    public $active;

    public $image;

    public $title;

    public $text;

    public $url;
    public $hook_name;
    public $id_shop;

    public $position;

    public $date_from;

    public $date_to;

    public $date_add;

    public $date_upd;

    public static $definition = [
        'table'     => 'promo_banner',
        'primary'   => 'id_promo_banner',
        'multilang' => true,
        'fields'    => [
            'active'    => ['type' => self::TYPE_BOOL,   'validate' => 'isBool'],
            'image'     => ['type' => self::TYPE_STRING, 'validate' => 'isString',    'size' => 255],
            'title'     => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'lang'     => true,   'size' => 255],
            'text'      => ['type' => self::TYPE_HTML,   'validate' => 'isCleanHtml','lang'     => true],
            'url'       => ['type' => self::TYPE_STRING, 'validate' => 'isUrl','lang'     => true,'size' => 255],
            'hook_name' => ['type' => self::TYPE_STRING, 'validate' => 'isHookName',  'size' => 64],
            'position' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
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
        $id_lang = Context::getContext()->language->id;
        $id_shop = Context::getContext()->shop->id;

        $sql = 'SELECT pb.*, pbl.title, pbl.text, pbl.url
        FROM '._DB_PREFIX_.'promo_banner pb
        LEFT JOIN '._DB_PREFIX_.'promo_banner_lang pbl
            ON pb.id_promo_banner = pbl.id_promo_banner AND pbl.id_lang = '.(int)$id_lang.'
        WHERE pb.active = 1
            AND pb.id_shop = '.(int)$id_shop.'
            AND pb.hook_name = "' . pSQL($hook) . '"
            AND pb.date_from <= NOW()
            AND pb.date_to >= NOW()
        ORDER BY pb.date_from DESC';

        return Db::getInstance()->executeS($sql);
    }



}
