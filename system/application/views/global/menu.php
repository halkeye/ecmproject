<ul> 
    <li class="title">Menu</li> 
    <?php foreach ($menu as $m): ?>
    <li><?php echo anchor($m['url'], $m['title']); ?></li> 
    <?php endforeach; ?>
    </li>
</ul>
