{% form_open id="formConfig" action="formConfig" class="form-horizontal" %}
	<h1 class="page-title">
		{{ titlePage }}
		<div class="pull-right">
			{% input_submit name="submit" value="save" formId="formConfig" class="btn-primary btn-xs" icon="floppy-o" label="" %}
		</div>
	</h1>
	

	<?php foreach ($params['config'] as $key => $value) { ?>
		<div class="box box-warning">
		    <div class="box-header">
		        <h3 class="box-title"><?php echo $key; ?></h3>
		    </div>
		    <div class="box-body">
		    	<?php foreach ($value as $key1 => $value1) { ?>
		    		{% input_text name="config[<?php echo $key; ?>][<?php echo $key1; ?>]" value="<?php echo $value1; ?>" label="<?php echo $key1; ?>" %}
		    	<?php } ?>
		    </div>
		</div>
	<?php } ?>
{% form_close %}
