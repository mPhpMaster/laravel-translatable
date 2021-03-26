# Installation

## Install package

Add the package in your `composer.json` by executing the command.

```bash
composer require mphpmaster/laravel-translatable
```

## Configuration

We copy the configuration file to our project.

```bash
php artisan vendor:publish --tag=laravel-translatable 
```

After this you will have to configure the `locales` your app should use.

```php
'locales' => [
    'en',
    'fr',
    'es' => [
        'MX', // mexican spanish
        'CO', // colombian spanish
    ],
],
```

{% hint style="info" %}
There isn't any restriction for the format of the locales. Feel free to use whatever suits you better, like "eng" instead of "en", or "el" instead of "gr". The important is to define your locales and stick to them.
{% endhint %}

That's the only configuration key you **have** to adjust. All the others have a working default value and are described in the configuration file itself.

## Migrations

In this example, we want to translate the model `Book`. We will need an extra table `book_translations`:

{% code title="create\_books\_table.php" %}
```php
Schema::create('books', function(Blueprint $table) {
    $table->increments('id');
    $table->string('author');
    $table->timestamps();
});
```
{% endcode %}

{% code title="create\_book\_translations\_table" %}
```php
Schema::create('book_translations', function(Blueprint $table) {
    $table->increments('id');
    $table->integer('book_id')->unsigned();
    $table->string('locale')->index();
    $table->string('title');
    $table->text('content');

    $table->unique(['book_id', 'locale']);
    $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
});
```
{% endcode %}

## Models

The translatable model `Book` should [use the trait](http://www.sitepoint.com/using-traits-in-php-5-4/) `mPhpMaster\Translatable\Translatable`. The default convention for the translation model is `BookTranslation`. The array `$translatedAttributes` contains the names of the fields being translated in the `BookTranslation` model.

{% code title="Book.php" %}
```php
use mPhpMaster\Translatable\Contracts\Translatable as TranslatableContract;
use mPhpMaster\Translatable\Translatable;

class Book extends Model implements TranslatableContract
{
    use Translatable;
    
    public $translatedAttributes = ['title', 'content'];
    protected $fillable = ['author'];
}
```
{% endcode %}

{% code title="BookTranslation.php" %}
```php
class BookTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'content'];
}
```
{% endcode %}

### Custom foreign key

You may also define a custom foreign key for the package to use, e.g. in case of single table inheritance. So, you have a child class `ChildBook` that inherits from `Book` class, but has the same database table as its parent.

{% code title="ChildBook.php" %}
```php
class ChildBook extends Book 
{
    protected $table = 'books';
}
```
{% endcode %}

You will have to create a Translation Class for it.

{% code title="ChildBookTranslation.php" %}
```php
use Illuminate\Database\Eloquent\Model;

class ChildBookTranslation extends Model 
{
    protected $table = 'book_translations';
    public $timestamps = false;
    protected $fillable = ['title', 'content'];  
}
```
{% endcode %}

This will try to get data from `book_translations` table using foreign key `child_book_id` according to Laravel. So, in this case, you will have to change the property `$translationForeignKey` to your `'book_id'`.

{% code title="ChildBook.php" %}
```php
class ChildBook extends Book 
{
    protected $table = 'books';
    protected $translationForeignKey = 'book_id';
}
```
{% endcode %}

