<?php
// NOTE: needs the YAML extension
$arr = include __DIR__ . '/filecms_config.php';
$yaml = yaml_emit($arr, YAML_UTF8_ENCODING, YAML_LN_BREAK);
echo $yaml;
