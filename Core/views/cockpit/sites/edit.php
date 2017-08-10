<h1 class="page-title">{{ pageTitle }}</h1>
<div class="box box-success">
    <div class="box-header">
        <h3 class="box-title">{{ boxTitle }}</h3>
        <div class="box-tools pull-right">
            <?php if ($this->current_administrator !== null && $this->current_administrator->site_id === null) { ?>
                {% button url="cockpit_core_sites_index" type="secondary" size="sm" icon="arrow-left" hint="Retour" %}
            <?php } else { ?>
                {% button url="cockpit_core_sites_show_<?php echo $this->current_administrator->site_id; ?>" type="secondary" size="sm" icon="arrow-left" hint="Retour" %}
            <?php } ?>
        </div>
    </div>
    <div class="box-body">
		{% form_open id="formMenu" action="formAction" %}
		    {% input_text name="label" model="site.label" label="Label" %}
		    {% input_text name="host" model="site.host" label="Host" %}
            {% input_select name="theme" model="site.theme" options="themeOptions" label="Thème" %}
            {% input_textarea name="description" model="site.description" label="Description" rows="10" %}

            {% input_text name="facebook" model="site.facebook" label="Facebook" %}
            {% input_text name="twitter" model="site.twitter" label="Twitter" %}
            {% input_text name="printerest" model="site.printerest" label="Printerest" %}
            {% input_text name="googleplus" model="site.googleplus" label="Google +" %}

            {% input_checkbox name="active" model="site.active" label="Actif" %}
		    {% input_submit name="submit" value="save" formId="formMenu" class="btn-primary" icon="save" label="Enregistrer" %}
		{% form_close %}
	</div>
</div>
