<h1 class="page-title">{{ pageTitle }}</h1>
<div class="box box-danger">
    <div class="box-header">
        <h3 class="box-title">{{ boxTitle }}</h3>
        <div class="box-tools pull-right">
            {% button url="cockpit_core_sites_new" type="success" size="sm" icon="plus" hint="Ajouter" %}
        </div>
    </div>
    <div class="box-body">
        <table class="table table-hover table-sm datatables">
            <thead>
                <tr>
                    <th width="10%">#</th>
                    <th>Nom</th>
                    <th>Host</th>
                    <th>Thème</th>
                    <th>Page d'accueil</th>
                    <th>RéducBox</th>
                    <th>Status</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>
            <tbody>
<?php

foreach ($params['sites'] as $site) {
    if ($site->active == 1 && $site->maintenance == 0) {
        $active = '<span class="badge badge-success">Activé</span>';
    } else {
        if ($site->maintenance == 1) {
            $active = '<span class="badge badge-danger">En maintenance</span>';
        } else {
            $active = '<span class="badge badge-danger">Désactivé</span>';
        }
    }

    if ($site->reducbox_opt == 1) {
        $active_reducbox = '<span class="badge badge-success">Activé</span>';
    } else {
        $active_reducbox = '<span class="badge badge-danger">Désactivé</span>';
    }

    // On gère les thème
    if ($site->theme == null) {
        $site->theme = "default";
    }

    echo
        '<tr>'.
            '<td>'.$site->id.'</td>'.
            '<td>'.$site->label.'</td>'.
            '<td>'.$site->host.'</td>'.
            '<td>'.$themeOptions[$site->theme]['label'].'</td>'.
            '<td>'.$site->home_page.'</td>'.
            '<td>'.$active_reducbox.'</td>'.
            '<td>'.$active.'</td>'.
            '<td>';?>
                {% button url="cockpit_core_sites_show_<?php echo $site->id; ?>" type="secondary" size="sm" icon="eye" hint="Modifier" %}
                {% button url="cockpit_core_sites_edit_<?php echo $site->id; ?>" type="info" size="sm" icon="pencil" hint="Modifier" %}
                {% button url="cockpit_core_sites_delete_<?php echo $site->id; ?>" type="danger" size="sm" icon="trash-o" confirmation="Vous confirmer vouloir supprimer ce site ?" hint="Supprimer" %}
<?php
    echo
            '</td>'.
        '</tr>';
}
?>
            </tbody>
        </table>
    </div>
</div>
