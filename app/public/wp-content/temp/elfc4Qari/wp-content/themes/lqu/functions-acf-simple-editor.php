<?php
// https://www.advancedcustomfields.com/resources/customize-the-wysiwyg-toolbars/
add_filter( 'acf/fields/wysiwyg/toolbars' , 'simple_acf_toolbar'  );
function simple_acf_toolbar($toolbars) {
	$toolbars['Italics Option Only'] = array();
	$toolbars['Italics Option Only'][1] = array('italic');
	return $toolbars;
}
?>
