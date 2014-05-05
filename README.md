# [Meta Box Factory](https://github.com/chapmanu/wp-meta-box-factory)

**Meta Box Factory** is a framework for creating WordPress meta boxes that you can add to any theme or plugin.

It keeps things simple.

![Example 1](http://i.imgur.com/5GQW51X.png)

```php
$mb = new MetaBox(array(
	"title"   => "Mood Ring",
	"screens" => "page,post",
	"fields"  => array(
		"mood" => array(
			"label"   => "What mood is this?",
			"type"    => "select",
			"options" => array(
				"Excited", "Mellow", "Upset", "Comfortable"
			),
			"value"   => "Comfortable"
		)
	)
));
```

---

And helps them stay simple.

![Example 2](http://i.imgur.com/0Ky7g1n.png)

```php
$mb = new MetaBox(array(
	"title"   => "Mood Ring",
	"screens" => "page,post",
	"context" => "side",
	"fields"  => array(
		"summary" => array(
			"label"       => "Summarize this post",
			"type"        => "textarea",
			"rows"        => 4,
			"max"         => 144,
			"placeholder" => "Leave blank for auto-summary"
		),
		"mood" => array(
			"label"   => "What mood is this? (check all that apply)",
			"type"    => "checkbox",
			"options" => array(
				"Excited", "Mellow", "Upset", "Comfortable"
			),
			"value"   => "Comfortable"
		)
	)
));
```

---

And it supports JSON.

```php
$mb = MetaBox::load('mood-ring.json');
```

```json
{
	"title"  : "Mood Ring",
	"screens": "page,post",
	"context": "side",
	"fields" : {
		"summary": {
			"label"      : "Summarize this post",
			"type"       : "textarea",
			"rows"       : 4,
			"max"        : 144,
			"placeholder": "Leave blank for auto-summary"
		},
		"mood": {
			"label"  : "What mood is this? (check all that apply}",
			"type"   : "checkbox",
			"options": ["Excited", "Mellow", "Upset", "Comfortable"],
			"value"  : "Comfortable"
		}
	}
}
```

Enjoy!

---

## Getting started

> The secret to getting ahead is getting started.
> — [Mark Twain](http://en.wikipedia.org/wiki/Mark_Twain)

In your [functions.php](https://codex.wordpress.org/Functions_File_Explained), include this framework.

```php
require_once(TEMPLATEPATH.'/wp-meta-box-factory/meta-box-factory.php');
```

Now, create a new meta box.

```php
$mb = new MetaBox();
```

That’s it! Of course, it won’t be visible anywhere, but that is easily amended.

```php
$mb->add_screen('post');
```

Nice! Now it shows up whenever you add or edit a post. But it says “More” because you didn’t give it a title. So, give it a title.

```php
$mb->set_title('Advanced');
```

You got it! You do the thing and it does the thing. Now, move it to the side.

```php
$mb->set_context('side');
```

Excellent! Now, add a field, and call it “epilogue”.

```php
$mb->add_field('epilogue', array(
	'label' => 'What happened next?'
));
```

You win!

Oh, but next time, write it with way less code.

```php
$mb = new MetaBox(array(
	'screens' => 'post',
	'title'   => 'Advanced',
	'context' => 'side',
	'fields'  => array(
		'epilogue' => array(
			'label' => 'What happened next?'
		)
	)
));
```

---

## How it works

> I don't need to know everything, I just need to know where to find it, when I need it.
> — [Albert Einstein](http://en.wikipedia.org/wiki/Albert_Einstein)

Each new meta box returns a series of chainable methods.

### set_title

Sets the title of the meta box.

```php
$mb->set_title($title);
```

- **title** (*string*): The title.

### add_screen

Sets the screen or screens on which to show the meta box.

```php
$mb->add_screen($screen[, $screen]);
```

And, alternatively:
```php
$mb->remove_screen($screen[, $screen]);
```

- **screen** (*string*): The name of any screen, which may be *post*, *page*, *dashboard*, *link*, *attachment*, or some other custom screen type.

### set_context

Sets the part of the page on which to show the meta box.

```php
$mb->set_context($context);
```

- **context** (*string*): The part of the page, which may be *normal*, *advanced*, or *side*. The default is *advanced*.

### set_priority

Sets the priority within the context where the boxes should show.

```php
$mb->set_priority($priority);
```

- **priority** (*string*): The priority, which may be *high*, *core*, *default*, or *low*. The default is <small>(*wait for it&hellip;*)</small> *default*.

### add_field

Adds a new field or fields to a meta box.

```php
$mb->add_field($name, $field);
```

- **$name** (*string*): the name of the field when saving to or reading from the database.
- **$field** (*array*): the properties of the field.

Or, alternatively:
```php
$mb->add_field($fields);
```

- **$fields**: (*array*), a list of fields when saving to or reading from the database.

## Loading from JSON

```php
$mb = MetaBox::load($path[, $relative_file]);
```

- **$path** (*string*): the path to the JSON file.
- **$relative_file** (*string*): A relative path to the JSON file. Listen, unless you’re saving files relative to the wp-admin directory, you’ll probably need to add `__FILE__` here.

---

## Extending functionality

> Knowing is not enough; we must apply. Willing is not enough; we must do.
> — [Johann Wolfgang von Goethe](http://en.wikipedia.org/wiki/Johann_Wolfgang_von_Goethe)

Need a custom input type?

```php
$mb->add_field('magic', array(
	'type'  => 'some_custom_type'
));
```

Create one.

```php
MetaBox::$create_field->some_custom_type = function ($name, $data) {
	// do stuff and return a string of HTML
};
```

- **$name** (*string*): the name of the field when saving to or reading from the database.
- **$data** (*array*): the properties of the field.

Thus far, **text**, **textarea**, **password**, **select**, **checkbox**, and **color** input types have been defined.
