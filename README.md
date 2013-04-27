wp-simple-customization
=======================

Usage:
```php
include_once( 'css.php' );

$css = new css();

$css->add(
    'theme_primary',                        // Customize name (must be unique)
    'color',                                // Customize type, (section or whatever you want, only section has a custom use at this point)
    array(
        'label'    => __( 'Main color' ),	// Label for the option
        'object'   => 'html, body',         // The css DOM object to style
        'selector' => 'color',              // The css attribute to style
        'default'  => '#000000',            // The default value
        'type'     => 'color',              // The controller type
        'section'  => 'colors'              // Customize API section
    )
);
```

Customize type can be used to create a section as well, just feed it appropriately
```php
$css->add(
	'section Name',
	'section',
	array(
		'title' => __( 'This is a section' )
	)
);
```

The array takes any argument the WP Customize API would normally accept, the only elements that are required are `object`, `selector` and `section`.

The type will always default to a text field if there's nothing to provide it with, this is a fallback, ideally you'll have SOMETHING here (since it's for styling and css after all)

The type accepts one of the following
 - color
 - upload
 - image
 - background
 - header