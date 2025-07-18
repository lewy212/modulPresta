{foreach from=$promo_banners item=banner}
    {if $banner.active}
        <a href="{$banner.url|escape:'html'}" class="promo-banner">
            {if $banner.image}
                <img src="{$link->getMediaLink('/modules/ps_promo_banner/views/img/'|cat:$banner.image)}" alt="{$banner.title|escape:'htmlall'}" />
            {/if}
            <div class="content">
                <h2>{$banner.title|escape:'htmlall'}</h2>
                <div>{$banner.text nofilter}</div>
            </div>
        </a>
    {/if}
{/foreach}
