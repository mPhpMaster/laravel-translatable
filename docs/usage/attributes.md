# Attributes

The package overrides the `setAttribute()` and `getAttribute()` methods to allow direct access to translated attributes from the main model.

By default the locale returned by `Locales::current()` is used to get/set the attribute from the translation model. But you can also pass a locale after the attribute name like `title:en` this will get/set the `title` attribute from the `en` translation.

```php
app()->setLocale('en');

echo $book->title; // English
echo $book->{'title:de'}; // Dutch
$book->title = 'Dutch';
echo $book->title; // Dutch
```

