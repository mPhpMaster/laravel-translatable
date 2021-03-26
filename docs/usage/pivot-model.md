# Pivot Model

The package trait could also be used on [pivot models](https://laravel.com/docs/5.8/eloquent-relationships#defining-custom-intermediate-table-models) but you should adjust some things to make everything work.

Because the trait introduces a new relation your base model needs a primary key - we will use an auto-increment `id` column. If you want to use an UUID string column or another key you have to set/adjust more things \(tell the model and trait which is your primary key, adjust migration ...\) but even this is possible.

{% code title="RoleUser.php" %}
```php
use Illuminate\Database\Eloquent\Relations\Pivot;
use mPhpMaster\Translatable\Contracts\Translatable as TranslatableContract;
use mPhpMaster\Translatable\Translatable;

class RoleUser extends Pivot implements TranslatableContract
{
    use Translatable;

    public $incrementing = true;
}
```
{% endcode %}

{% code title="create\_role\_user\_table.php" %}
```php
Schema::create('role_user', function(Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('role_id')->constrained();
    
    $table->unique(['user_id', 'role_id']);
});
```
{% endcode %}



