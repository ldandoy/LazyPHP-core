<div class="container">
	<div class="row">
		<div class="col-md-12">
			<h1>Liste des urls disponible</h1>
			<table class="table">
				<thead>
				<tr>
					<th>method</th>
					<th>url</th>
					<th>Package</th>
					<th>Prefix</th>
					<th>Controler#Action</th>
				</tr>
				</thead>
				<tbody>
					<?php foreach ($routes as $route) { ?>
						<tr>
							<td>
								<?php echo $route['method']; ?>
							</td>
							<td>
								<?php echo $route['url']; ?>
							</td>
							<td>
								<?php echo $route['package']; ?>
							</td>
							<td>
								<?php echo $route['prefix']; ?>
							</td>
							<td>
								<?php echo $route['controller']."#".$route['action']; ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
