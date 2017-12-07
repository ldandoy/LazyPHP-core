<h1 class="page-title">{{ pageTitle }}</h1>
<div class="box box-success">
    <div class="box-header">
        <h3 class="box-title">{{ boxTitle }}</h3>
        <div class="box-tools pull-right">
            <?php if ($this->current_user !== null && $this->current_user->site_id === null) { ?>
                {% button url="cockpit_core_sites_index" type="secondary" size="sm" icon="arrow-left" hint="Retour" %}
            <?php } else { ?>
                {% button url="cockpit_core_sites_show_<?php echo $this->current_user->site_id; ?>" type="secondary" size="sm" icon="arrow-left" hint="Retour" %}
            <?php } ?>
        </div>
    </div>
    <div class="box-body">
        {% form_open id="formSite" action="formAction" %}
<?php if ($this->current_user !== null && $this->current_user->site_id === null): ?>
            {% input_text name="label" model="site.label" label="Label" %}
            {% input_text name="host" model="site.host" label="Host" %}
<?php else: ?>
            {% input_hidden name="label" model="site.label" %}
            {% input_hidden name="host" model="site.host" %}
<?php endif; ?>
            {% input_upload name="brand_logo" model="site.brand_logo" label="Logo" type="image" class="media media-image" %}
            {% input_select name="theme" model="site.theme" options="themeOptions" label="Thème" %}
            {% input_textarea name="description" model="site.description" label="Description" rows="10" %}
            {% input_select name="home_page" model="site.home_page" options="pageOptions" label="Page d'accueil" %}
            {% input_text name="facebook" model="site.facebook" label="Facebook" %}
            {% input_text name="twitter" model="site.twitter" label="Twitter" %}
            {% input_text name="printerest" model="site.printerest" label="Printerest" %}
            {% input_text name="googleplus" model="site.googleplus" label="Google +" %}
<?php if ($this->current_user !== null && $this->current_user->site_id === null): ?>
            {% input_checkbox name="active" model="site.active" label="Actif" %}
            {% input_checkbox name="reducbox_opt" model="site.reducbox_opt" label="Option Reducbox" %}

            {% input_text name="reducbox_partkey" model="site.reducbox_partkey" label="Clé partenaire Reducbox" %}
            {% input_text name="reducbox_secret" model="site.reducbox_secret" label="Secret Reducbox" %}
<?php else: ?>
            {% input_hidden name="active" model="site.active" %}
<?php endif; ?>
            {% input_submit name="submit" value="save" formId="formSite" class="btn-primary" icon="save" label="Enregistrer" %}
        {% form_close %}
    </div>
</div>
