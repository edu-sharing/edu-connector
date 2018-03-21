
<?php


echo '<a href="edit.php"><h2>Create content<h2></a><br/><br/>';

echo '<h2>Show/edit content</h2>';

$files = scandir('test/');
echo '<ul>';
foreach($files as $file) {
    if($file !== '.' && $file !== '..') {
        $a = str_replace('.h5p', '', $file);
        $b = explode('-', $a);
        echo '<li><a href="show.php?h5p='.$file.'">'.$file.'</a>';
        if(is_numeric(end($b)))
            echo '<a style="background: yellow; padding: 1px 10px" href="edit.php?h5p='.end($b).'">EDIT</a></li>';
    }
}
echo '</ul>';
