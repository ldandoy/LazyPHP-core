<h1 class="page-title">{{ pageTitle }}</h1>
<div class="box box-danger">
    <div class="box-header">
        <h3 class="box-title">{{ blockTitle }}</h3>
        <div class="box-tools pull-right">
            {% button url="cockpit_core_sites_edit_<?php echo $site->id; ?>" type="info" size="sm" icon="pencil" %}
            {% button url="cockpit_core_sites_delete_<?php echo $site->id; ?>" type="danger" size="sm" icon="trash-o" confirmation="Vous confirmer vouloir supprimer ce site ?" %}
            <?php if ($this->current_user !== null && $this->current_user->site_id === null) { ?>
                {% button url="cockpit_core_sites_index" type="secondary" size="sm" icon="arrow-left" hint="retour" %}
            <?php } ?>
        </div>
    </div>
    <div class="box-body">
        <p>
            <b>Nom</b>: <?php echo $site->label; ?>
        </p>
        <p>
            <b>Host</b>: <?php echo $site->host; ?>
        </p>
        <p>
            <b>Logo</b>: <img src="<?php echo $site->brand_logo->url; ?>" alt="Logo" />
        </p>
        <p>
            <b>Theme</b>: <?php echo $themeOptions[$site->theme]['label']; ?>
        </p>
        <p>
            <b>Page d'accueil</b>: <?php echo $site->home_page; ?>
        </p>
        <p>
            <b>Description</b>: <?php echo $site->description; ?>
        </p>
        <p>
            <b>Facebook</b>: <?php echo $site->facebook; ?>
        </p>
        <p>
            <b>Twitter</b>: <?php echo $site->twitter; ?>
        </p>
        <p>
            <b>Printerest</b>: <?php echo $site->printerest; ?>
        </p>
        <p>
            <b>Google +</b>: <?php echo $site->googleplus; ?>
        </p>
        <p>
            <b>Actif</b>:
            <?php
                if ($site->active == 1) {
                    echo '<span class="badge badge-success">Activé</span>';
                } else {
                    echo '<span class="badge badge-danger">Désactivé</span>';
                }
            ?>
        </p>
    </div>
</div>
