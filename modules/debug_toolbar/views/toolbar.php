<?php defined('SYSPATH') or die('No direct script access.') ?>

<!-- CSS styles (if not added to <head>) -->
<?php if (isset($styles)): ?>
	<?php echo $styles ?>
<?php endif ?>

<!-- Javascript -->
<script type="text/javascript">
<?php echo $scripts ?>
</script>

<div id="kohana-debug-toolbar">

	<!-- Toolbar -->
	<div id="debug-toolbar" class="debug-toolbar-align-<?php echo $align ?>">
	
		<!-- Kohana link -->
		<?php echo html::image(
			Kohana::config('debug_toolbar.icon_path').'/kohana.png',
			array('onclick' => 'debugToolbar.collapse()')
		) ?>
		
		<!-- Kohana icon -->
		<?php if (Kohana::config('debug_toolbar.minimized') === TRUE): ?>
			<ul id="debug-toolbar-menu" class="menu" style="display: none">
		<?php else: ?>
			<ul id="debug-toolbar-menu" class="menu">
		<?php endif ?>
			
			<!-- Kohana version -->
			<li>
				<?php echo html::anchor("http://kohanaphp.com/home", KOHANA_VERSION, array('target' => '_blank')) ?>
			</li>
			
			<!-- Benchmarks -->
			<?php if (Kohana::config('debug_toolbar.panels.benchmarks')): ?>
				<!-- Time -->
				<li id="time" onclick="debugToolbar.show('debug-benchmarks'); return false;">
					<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/time.png', array('alt' => 'time')) ?>
					<?php echo round(($benchmarks['system_benchmark_total_execution']['time'])*1000, 2) ?> ms
				</li>
				<!-- Memory -->
				<li id="memory" onclick="debugToolbar.show('debug-benchmarks'); return false;">
					<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/memory.png', array('alt' => 'memory')) ?>
					<?php echo text::bytes($benchmarks['system_benchmark_total_execution']['memory']) ?>
				</li>
			<?php endif ?>
			
			<!-- Queries -->
			<?php if (Kohana::config('debug_toolbar.panels.database')): ?>
				<li id="toggle-database" onclick="debugToolbar.show('debug-database'); return false;">
					<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/database.png', array('alt' => 'queries')) ?>
					<?php echo isset($queries) ? count($queries) : 0 ?>
				</li>
			<?php endif ?>
			
			<!-- Vars and Config -->
			<?php if (Kohana::config('debug_toolbar.panels.vars_and_config')): ?>
				<li id="toggle-vars" onclick="debugToolbar.show('debug-vars'); return false;">
					<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/config.png', array('alt' => 'vars &amp; config')) ?>
					vars &amp; config
				</li>
			<?php endif ?>
			
			<!-- Logs -->
			<?php if (Kohana::config('debug_toolbar.panels.logs')): ?>
				<li id="toggle-log" onclick="debugToolbar.show('debug-log'); return false;">
					<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/logs.png', array('alt' => 'logs')) ?>
					logs
				</li>
			<?php endif ?>
			
			<!-- Ajax -->
			<?php if (Kohana::config('debug_toolbar.panels.ajax')): ?>
				<li id="toggle-ajax" onclick="debugToolbar.show('debug-ajax'); return false;" style="display: none">
					<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/ajax.png', array('alt' => 'ajax')) ?>
					ajax (<span>0</span>)
				</li>
			<?php endif ?>
			
			<!-- Files -->
			<?php if (Kohana::config('debug_toolbar.panels.files')): ?>
				<li id="toggle-files" onclick="debugToolbar.show('debug-files'); return false;">
					<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/page_copy.png', array('alt' => 'files')) ?>
					files
				</li>
			<?php endif ?>

			
			<!-- Swap sides -->
			<li onclick="debugToolbar.swap(); return false;">
				<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/text_align_left.png', array('alt' => 'align')) ?>
			</li>
			
			<!-- Close -->
			<li class="last" onclick="debugToolbar.close(); return false;">
				<?php echo html::image(Kohana::config('debug_toolbar.icon_path').'/close.png', array('alt' => 'close')) ?>
			</li>
		</ul>
	</div>
	
	<!-- Benchmarks -->
	<?php if (Kohana::config('debug_toolbar.panels.benchmarks')): ?>
		<div id="debug-benchmarks" class="top" style="display: none;">
			<h1>Benchmarks</h1>
			<table cellspacing="0" cellpadding="0">
				<tr>
					<th align="left">benchmark</th>
					<th align="right">time</th>
					<th align="right">memory</th>
				</tr>
				<?php if (count($benchmarks)): ?>
					<?php foreach ((array)$benchmarks as $benchmark): ?>
						<tr class="<?php echo text::alternate('odd','even')?>">
							<td align="left"><?php echo $benchmark['name'] ?></td>
							<td align="right"><?php echo sprintf('%.2f', $benchmark['time'] * 1000) ?> ms</td>
							<td align="right"><?php echo text::bytes($benchmark['memory']) ?></td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr class="<?php echo text::alternate('odd','even') ?>">
						<td colspan="3">no benchmarks to display</td>
					</tr>
				<?php endif ?>
			</table>
		</div>
	<?php endif ?>
	
	<!-- Database -->
	<?php if (Kohana::config('debug_toolbar.panels.database')): ?>
		<div id="debug-database" class="top" style="display: none;">
			<h1>SQL Queries</h1>
			<table cellspacing="0" cellpadding="0">
				<tr align="left">
					<th>#</th>
					<th>query</th>
					<th>time</th>
					<th>rows</th>
				</tr>
				<?php $total_time = $total_rows = 0; ?>
				<?php foreach ((array)$queries as $id => $query): ?>
					<tr class="<?php echo text::alternate('odd','even')?>">
						<td><?php echo $id + 1 ?></td>
						<td><?php echo $query['query']?></td>
						<td><?php echo sprintf('%.3f', $query['time'] * 1000)?> ms</td>
						<td><?php echo $query['rows']?></td>
					</tr>
					<?php 
					$total_time += $query['time'];
					$total_rows += $query['rows'];
					?>
				<?php endforeach; ?>
				<tr align="left">
					<th>&nbsp;</th>
					<th><?php echo count($queries) ?> total</th>
					<th><?php echo sprintf('%.3f', $total_time * 1000) ?> ms</th>
					<th><?php echo $total_rows ?></th>
				</tr>
			</table>
		</div>
	<?php endif ?>
	
	<!-- Vars and Config -->
	<?php if (Kohana::config('debug_toolbar.panels.vars_and_config')): ?>
		<div id="debug-vars" class="top" style="display: none;">
			<h1>Vars &amp; Config</h1>
			<ul class="varmenu">
				<li onclick="debugToolbar.showvar(this, 'vars-post'); return false;">POST</li>
				<li onclick="debugToolbar.showvar(this, 'vars-get'); return false;">GET</li>
				<li onclick="debugToolbar.showvar(this, 'vars-server'); return false;">SERVER</li>
				<li onclick="debugToolbar.showvar(this, 'vars-cookie'); return false;">COOKIE</li>
				<li onclick="debugToolbar.showvar(this, 'vars-session'); return false;">SESSION</li>
				<li onclick="debugToolbar.showvar(this, 'vars-config'); return false;">CONFIG</li>
			</ul>
			<div style="display: none;" id="vars-post">
				<?php echo isset($_POST) ? Kohana::debug($_POST) : Kohana::debug(array()) ?>
			</div>
			<div style="display: none;" id="vars-get">
				<?php echo isset($_GET) ? Kohana::debug($_GET) : Kohana::debug(array()) ?>
			</div>
			<div style="display: none;" id="vars-server">
				<?php echo isset($_SERVER) ? Kohana::debug($_SERVER) : Kohana::debug(array()) ?>
			</div>
			<div style="display: none;" id="vars-cookie">
				<?php echo isset($_COOKIE) ? Kohana::debug($_COOKIE) : Kohana::debug(array()) ?>
			</div>
			<div style="display: none;" id="vars-session">
				<?php echo isset($_SESSION) ? Kohana::debug($_SESSION) : Kohana::debug(array()) ?>
			</div>
			<div style="display: none;" id="vars-config">
				<ul class="configmenu">
					<?php foreach ($configs as $section => $vars): ?>
						<li class="<?php echo text::alternate('odd', 'even') ?>" onclick="debugToolbar.toggle('vars-config-<?php echo $section ?>'); return false;">
							<div><?php echo $section ?></div>
							<div style="display: none;" id="vars-config-<?php echo $section ?>">
								<?php echo Kohana::debug($vars) ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php endif ?>
	
	<!-- Logs and Messages -->
	<?php if (Kohana::config('debug_toolbar.panels.logs')): ?>
		<div id="debug-log" class="top" style="display: none;">
			<h1>Logs</h1>
			<table cellspacing="0" cellpadding="0">
				<tr align="left">
					<th width="1%">#</th>
					<th width="200">time</th>
					<th width="100">level</th>
					<th>message</th>
				</tr>
				<?php foreach ((array)$logs as $id => $log): ?>
					<tr class="<?php echo text::alternate('odd','even')?>">
						<td><?php echo $id + 1 ?></td>
						<td><?php echo $log[0] ?></td>
						<td><?php echo $log[1] ?></td>
						<td><?php echo $log[2] ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	<?php endif ?>
	
	<!-- Ajax Requests -->
	<?php if (Kohana::config('debug_toolbar.panels.ajax')): ?>
		<div id="debug-ajax" class="top" style="display:none;">
			<h1>Ajax</h1>
			<table cellspacing="0" cellpadding="0">
				<tr align="left">
					<th width="1%">#</th>
					<th width="150">source</th>
					<th width="150">status</th>
					<th>request</th>
				</tr>
			</table>
		</div>
	<?php endif ?>
	
	<!-- Included Files -->
	<?php if (Kohana::config('debug_toolbar.panels.files')): ?>
		<div id="debug-files" class="top" style="display: none;">
			<h1>Files</h1>
			<table cellspacing="0" cellpadding="0">
				<tr align="left">
					<th>#</th>
					<th>file</th>
					<th>size</th>
					<th>lines</th>
				</tr>
				<?php $total_size = $total_lines = 0 ?>
				<?php foreach ((array)$files as $id => $file): ?>
					<?php
					$size = filesize($file);
					$lines = count(file($file));
					?>
					<tr class="<?php echo text::alternate('odd','even')?>">
						<td><?php echo $id + 1 ?></td>
						<td><?php echo $file ?></td>
						<td><?php echo $size ?></td>
						<td><?php echo $lines ?></td>
					</tr>
					<?php
					$total_size += $size;
					$total_lines += $lines;
					?>
				<?php endforeach; ?>
				<tr align="left">
					<th colspan="2">total</th>
					<th><?php echo text::bytes($total_size) ?></th>
					<th><?php echo number_format($total_lines) ?></th>
				</tr>
			</table>
		</div>
	<?php endif ?>

</div>