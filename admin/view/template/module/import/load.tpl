<?php echo $header; ?>
	<div id="content">
		<div id="error"><?php if (isset($error)) { echo $error; } ?></div>
		<h1>Результат импорта:</h1>
		<div>
			<h2>Товаров обновлено: <?php echo count($updated) ?></h2>
			<ul>
				<?php foreach($updated as $item)  { ?>
				<li><?php echo "{$item['sku']}" ?></li>
				<?php } ?>
			</ul>
		</div>
		<div>
			<h2>Товаров не найдено: <?php echo count($missed) ?></h2>
			<ul>
			<?php foreach($missed as $item)  { ?>
				<li><?php echo "{$item['sku']}" ?></li>
				<?php } ?>
			</ul>
		</div>
		<?php #var_dump($xls_content) ?>
	</div>