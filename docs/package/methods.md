# Methods

## Preconditions

* the `locale` is set to `de`
* the `fallback_locale` is set to `en`
* our main model instance is `$book = Book::first()`
* translations are available for `en`, `de` and `fr`

## Get an instance of the translation model

### translate\(?string $locale = null, bool $withFallback = false\)

**Alias of:** `getTranslation(?string $locale = null, bool $withFallback = null)`

This returns an instance of `BookTranslation` using the default or given locale. It can also use the configured fallback locale if first locale isn't present.

```php
$book->translate(); // returns the german translation model
$book->translate('fr'); // returns the french translation model
$book->translate('it'); // returns null
$book->translate('it', true); // returns the english translation model
```

### translateOrDefault\(?string $locale = null\)

**Alias of:** `getTranslation(?string $locale = null, bool $withFallback = null)`

This returns an instance of `BookTranslation` using the default or given locale and will always use fallback if needed.

```php
$book->translateOrDefault(); // returns the german translation model
$book->translateOrDefault('fr'); // returns the french translation model
$book->translateOrDefault('it'); // returns the english translation model
```

### translateOrNew\(?string $locale = null\)

**Alias of:** `getTranslationOrNew(?string $locale = null)`

This returns an instance of `BookTranslation` using the default or given locale and will create a new instance if needed.

```php
$book->translateOrNew(); // returns the german translation model
$book->translateOrNew('fr'); // returns the french translation model
$book->translateOrNew('it'); // returns the new italian translation model
```

## hasTranslation\(?string $locale = null\)

Check if the book has a translation in default or given locale.

```php
$book->hasTranslation(); // true
$book->hasTranslation('fr'); // true
$book->hasTranslation('it'); // false
```

## translations\(\)

Is the eloquent relation method for the `HasMany` relation to the translation model.

## deleteTranslations\(string\|array $locales = null\)

Deletes all translations for the given locale\(s\).

```php
$book->deleteTranslations(); // delete all translations
$book->deleteTranslations('de'); // delete german translation
$book->deleteTranslations(['de', 'en']); // delete german and english translation
```

## getTranslationsArray\(\)

Returns all the translations as array - the structure is the same as it's accepted by the `fill(array $data)` method.

```php
$book->getTranslationsArray();
// Returns
[
 'en' => ['title' => 'English'],
 'de' => ['title' => 'Dutch'],
 'fr' => ['title' => 'French'],
];
```

## replicateWithTranslations\(array $except = null\)

Creates a clone and clones the translations.

```php
$replicate = $book->replicateWithTranslations();
```

## getDefaultLocale\(\)

Returns the current default locale for the current model or `null` if no default locale is set.

```php
$book->getDefaultLocale(); // null
```

## setDefaultLocale\(?string $locale\)

Sets the default locale for the current model.

```php
$book->setDefaultLocale('fr');
$book->getDefaultLocale(); // 'fr'
```

## Translation Autoloading

If the `toArray()` method is called it's possible to autoload all translations. To control this feature the package comes with a config value `load_translations_when_to_array` and three static methods in the trait:

### static enableAutoloadTranslations\(\)

forces to load all translations

### static disableAutoloadTranslations\(\)

disables autoload and returns parent attributes

### static defaultAutoloadTranslations\(\)

does not change the default behavior logic

