{% form_open id="formConfig" action="formConfig" %}
	<h1 class="page-title">
		{{ titlePage }}
		<div class="pull-right">
			{% input_submit name="submit" value="save" formId="formConfig" class="btn-primary btn-sm" icon="floppy-o" label="" %}
		</div>
	</h1>


	<?php foreach ($params['config'] as $key => $value) { ?>
		<div class="box box-warning">
		    <div class="box-header">
		        <h3 class="box-title"><?php echo $key; ?></h3>
		    </div>
		    <div class="box-body">
		    	<?php foreach ($value as $key1 => $value1) { ?>
		    		<div class="form-group row">
		    			<label for="config[<?php echo $key; ?>][<?php echo $key1; ?>]" class="col-sm-2 form-control-label col-form-label-sm"><?php echo $key1; ?></label>
		    			<div class="col-sm-10">
		    				<input type="text" id="config[<?php echo $key; ?>][<?php echo $key1; ?>]" name="config[<?php echo $key; ?>][<?php echo $key1; ?>]" value="<?php echo $value1; ?>" class="form-control form-control-sm" placeholder="<?php echo $key1; ?>" />
		    			</div>
		    		</div>
		    	<?php } ?>
		    </div>
		</div>
	<?php } ?>
	{% input_submit name="submit" value="save" formId="formConfig" class="btn-primary" icon="floppy-o" label=" Enregister les modifications" %}
{% form_close %}
