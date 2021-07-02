<?php
// Ist kein JLayout. Via require einzubinden.
// Hard overrides for old Joomla float classes.
// figureClass Kann man im Beitrag einstellen, falls articleImages.xml im bs3ghsvs-
// Plugin geladen wird, was z.B. float_intro-Feld mit CSS-Klassen versieht.
// Dieses Array hier benötigen wir, falls nur normale Joomla3-Einstellungen
// möglich sind bzw. bei ungespeicherten Beiträgen auf die Globalen Optionen
// zurückfallen.

$imgClassTranslator = [
    'right' => 'ghsvs_img-default',
    'left' => 'ghsvs_img-default',
    'none' => 'ghsvs_img-default',
];
