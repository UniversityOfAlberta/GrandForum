<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('AnnokiControl')
    ->exclude('AccessControls')
    ->in('extensions/')
;

return new Sami($iterator, array(
    'title'                => 'The Forum',
    'build_dir'            => '/local/data/www-root/html/docs',
    'cache_dir'            => __DIR__.'/cache',
    'default_opened_level' => 2
));

?>
