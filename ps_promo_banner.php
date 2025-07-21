<?php
declare(strict_types=1);
if (!defined('_PS_VERSION_')) {
    exit;
}

use Michal\Module\PromoBanner\Form\PromoBanner;
class Ps_Promo_Banner extends Module
{
    public function __construct()
    {
        $this->name = 'ps_promo_banner';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'MichalLewczuk';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Dedicated promo banners', [], 'Modules.PsPromoBanner.Admin');
        $this->description = $this->trans(
            'Manage promotional banners directly from the admin panel.',
            [],
            'Modules.PsPromoBanner.Admin'
        );
    }

    public function install(): bool
    {
        return parent::install()
            && $this->registerHook('displayHome')
            && $this->registerHook('displayTop')
            && $this->registerHook('displayFooter')
            && $this->installTables();
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            && $this->uninstallTables();
    }

    protected function installTables(): bool
    {
        $queries = [];

        $queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "promo_banner` (
        `id_promo_banner` INT AUTO_INCREMENT PRIMARY KEY,
        `active` TINYINT(1) NOT NULL DEFAULT 0,
        `image` VARCHAR(255),
        `hook_name` VARCHAR(255),
        `date_from` DATETIME,
        `date_to` DATETIME,
        `date_add` DATETIME,
        `date_upd` DATETIME
    ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8mb4;";

        $queries[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "promo_banner_lang` (
        `id_promo_banner` INT UNSIGNED NOT NULL,
        `id_lang` INT UNSIGNED NOT NULL,
        `title` VARCHAR(255),
        `text` TEXT,
        `url` VARCHAR(255),
        PRIMARY KEY (`id_promo_banner`, `id_lang`)
    ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8mb4;";

        foreach ($queries as $sql) {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        }

        return true;
    }

    protected function uninstallTables(): bool
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "promo_banner`";
        return (bool) Db::getInstance()->execute($sql);
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPromoBanner')
        );
    }

//    public function hookDisplayHome($params)
//    {
//        $banners = PromoBanner::getActiveBanners();
//        $this->context->smarty->assign('promo_banners', $banners);
//        return $this->display($this->file, 'views/templates/hook/displayPromoBanner.tpl');
//    }
    public function hookDisplayHome($params)
    {
        return $this->renderBannersForHook('displayHome');
    }

    public function hookDisplayTop($params)
    {
        return $this->renderBannersForHook('displayTop');
    }

    public function hookDisplayFooter($params)
    {
        return $this->renderBannersForHook('displayFooter');
    }
    public function renderBannersForHook($hook)
    {
        $banners = PromoBanner::getBannersByHook($hook);
        $this->context->smarty->assign('promo_banners', $banners);
        return $this->display(__FILE__, 'views/templates/hook/displayPromoBanner.tpl');

    }


}
