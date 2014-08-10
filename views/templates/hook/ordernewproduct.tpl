<!-- Block mymodule -->
<div id="mymodule_block_home" class="block">
    <h4>Welcome!</h4>

    <div class="block_content">
        <p>
            {if isset($config_chiffre) && $config_chiffre}
                config : {$config_chiffre}
            {else}
                World
            {/if}
            {$message}
        </p>
        <ul>
            <li><a href="{$lien}" title="Click this link">Click me!</a></li>
        </ul>
    </div>
</div>
<!-- /Block mymodule -->