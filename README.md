# Introduction

**If you want to store translations of your models into the database, this package is for you.**

This is a Laravel package for translatable models. Its goal is to remove the complexity in retrieving and storing multilingual model instances. With this package you write less code, as the translations are being fetched/saved when you fetch/save your instance.

The full documentation can be found at [GitHub](https://github.com/mPhpMaster/laravel-translatable).

## Installation

```bash
composer require mphpmaster/laravel-translatable
```

## Quick Example

### **Getting translated attributes**

```php
$book = Book::first();
echo $book->translate('en')->title; // English

App::setLocale('en');
echo $book->title; // English

App::setLocale('fr');
echo $book->title; // French
```

### **Saving translated attributes**

```php
$book = Book::first();
echo $book->translate('en')->title; // English

$book->translate('en')->title = 'English Lang';
$book->save();

$book = Book::first();
echo $book->translate('en')->title; // English Lang
```

### **Filling multiple translations**

```php
$data = [
  'author' => 'name',
  'en' => ['title' => 'English'],
  'fr' => ['title' => 'French'],
];
$book = Book::create($data);

echo $book->translate('fr')->title; // French
```

## Credits

- [hlaCk](https://github.com/mPhpMaster) *author*

## Versions

| Package           | Laravel                       | PHP       |
| :---------------- | :---------------------------- | :-------- |
| **v1.0 - v1.0** | `5.8.* / 6.* / 7.* / 8.*`     | `>=7.2`   |


