<h1 class="page-title">{{ pageTitle }}</h1>
<div class="box box-danger">
    <div class="box-header">
        <h3 class="box-title">{{ boxTitle }}</h3>
        <div class="box-tools pull-right">
            {% button url="cockpit_core_sites_new" type="success" size="sm" icon="plus" hint="Nouveau site" %}
        </div>
    </div>
    <div class="box-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th width="1%">ID</th>
                    <th width="20%">Label</th>
                    <th width="40%">Host</th>
                    <th width="10%">Thème</th>
                    <th width="10%">Status</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>
            <tbody>
<?php

foreach ($params['sites'] as $site) {
    if ($site->active == 1) {
        $active = '<span class="badge badge-success">Activé</span>';
    } else {
        $active = '<span class="badge badge-danger">Désactivé</span>';
    }

    echo
        '<tr>'.
            '<td>'.$site->id.'</td>'.
            '<td>'.$site->label.'</td>'.
            '<td>'.$site->host.'</td>'.
            '<td>'.$site->theme.'</td>'.
            '<td>'.$active.'</td>'.
            '<td>';?>
                {% button url="cockpit_core_sites_show_<?php echo $site->id; ?>" type="secondary" size="sm" icon="eye" hint="" %}
                {% button url="cockpit_core_sites_edit_<?php echo $site->id; ?>" type="info" size="sm" icon="pencil" %}
                {% button url="cockpit_core_sites_delete_<?php echo $site->id; ?>" type="danger" size="sm" icon="trash-o" confirmation="Vous confirmer vouloir supprimer ce site ?" %}
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
