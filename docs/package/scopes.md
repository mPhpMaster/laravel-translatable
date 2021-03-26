# Scopes

## translatedIn\(?string $locale = null\)

Returns all books being translated in english

```php
Book::translatedIn('en')->get();
```

## notTranslatedIn\(?string $locale = null\)

Returns all books not being translated in english

```php
Book::notTranslatedIn('en')->get();
```

## translated\(\)

Returns all books not being translated in any locale

```php
Book::translated()->get();
```

## withTranslation\(\)

Eager loads translation relationship only for the default and fallback \(if enabled\) locale

```php
Book::withTranslation()->get();
```

## listTranslations\(string $translationField\)

Returns an array containing pairs of book ids and the translated title attribute

```php
Book::listsTranslations('title')->get()->toArray();
```

```php
[
    ['id' => 1, 'title' => 'English'],
    ['id' => 2, 'title' => 'My second book']
]
```

## where translation

Filters books by checking the translation against the given value

### whereTranslation\(string $translationField, $value, ?string $locale = null\)

```php
Book::whereTranslation('title', 'English')->first();
```

### orWhereTranslation\(string $translationField, $value, ?string $locale = null\)

```php
Book::whereTranslation('title', 'English')
    ->orWhereTranslation('title', 'My second book')
    ->get();
```

### whereTranslationLike\(string $translationField, $value, ?string $locale = null\)

```php
Book::whereTranslationLike('title', '%first%')->first();
```

### orWhereTranslationLike\(string $translationField, $value, ?string $locale = null\)

```php
Book::whereTranslationLike('title', '%first%')
    ->orWhereTranslationLike('title', '%second%')
    ->get();
```

## orderByTranslation\(string $translationField, string $sortMethod = 'asc'\)

Sorts the model by a given translation column value

```php
Book::orderByTranslation('title')->get()
```

