<?php echo $header; ?>
	<div id="content">
		<form enctype="multipart/form-data" method="POST" action="<?php echo $form_action ?>">
			<table class="list">
				<thead>
					<tr>
						<td class="left">XLS файл для импорта</td>
						<td class="left">&nbsp;</td>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td class-"left"><input type="file" name="xls" required></td>
						<td class="center"><input class="button" type="submit" value="Загрузить"></td>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>