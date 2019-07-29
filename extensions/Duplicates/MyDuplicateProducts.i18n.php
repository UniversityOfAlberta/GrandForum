<?php
global $config;

$messages = array();
$messages['en'] = array( 
			   'myduplicateproducts' => 'My Duplicate '.Inflect::pluralize($config->getValue('productsTerm')),
			   'MyDuplicateProducts' => 'My Duplicate '.Inflect::pluralize($config->getValue('productsTerm')),
			   'myDuplicateProducts' => 'My Duplicate '.Inflect::pluralize($config->getValue('productsTerm')),
			   );
?>
