
# A package to moderate Laravel models
[![Latest Version on Packagist](https://img.shields.io/packagist/v/eightanddouble/laravel-moderator.svg?style=flat-square)](https://packagist.org/packages/eightanddouble/laravel-moderator) [![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/eightanddouble/laravel-moderator/run-tests?label=tests)](https://github.com/eightanddouble/laravel-moderator/actions?query=workflow%3Arun-tests+branch%3Amain) [![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/eightanddouble/laravel-moderator/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/eightanddouble/laravel-moderator/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain) [![Total Downloads](https://img.shields.io/packagist/dt/eightanddouble/laravel-moderator.svg?style=flat-square)](https://packagist.org/packages/eightanddouble/laravel-moderator)

***Note:*** This package is a copy of [Laravel Moderation Package](https://github.com/hootlex/laravel-moderation) by [Alex Kyriakidis](https://github.com/hootlex). I have made this for my personal use as the original package is not maintained anymore. Also, I am a rookie at laravel package development, so don't expect much improvement in the features of the package except for upgrading to support latest versions.

## Installation

You can install the package via composer:

```bash
composer require eightanddouble/laravel-moderator
```
You can publish the config file with:
```bash
php artisan vendor:publish --tag="laravel-moderator-config"
```
This is the contents of the published config file:
```php
return [
	/*
	|--------------------------------------------------------------------------
	| Status column
	|--------------------------------------------------------------------------
	*/
	'status_column' => 'status',
	/*
	|--------------------------------------------------------------------------
	| Moderated At column
	|--------------------------------------------------------------------------
	*/
	'moderated_at_column' => 'moderated_at',
	/*
	|--------------------------------------------------------------------------
	| Moderated By column
	|--------------------------------------------------------------------------
	| Moderated by column is disabled by default.
	| If you want to include the id of the user who moderated a resource set
	| here the name of the column.
	| REMEMBER to migrate the database to add this column.
	*/
	'moderated_by_column' => null,
	/*
	|--------------------------------------------------------------------------
	| Strict Moderation
	|--------------------------------------------------------------------------
	| If Strict Moderation is set to true then the default query will return
	| only approved resources.
	| In other case, all resources except Rejected ones, will returned as well.
	*/
	'strict' => true,
];
```
## Prepare Model

To enable moderation for a model, 
1. use the `EightAndDouble\LaravelModerator\Moderatable` trait on the model 
2. Add the 
	 - `status`,  
	 - `moderated_by` 
	 - `moderated_at` 
	 
columns to your model's table.

```php
use EightAndDouble\LaravelModerator\Moderatable;

class Post extends Model
{
    use Moderatable;
    ...
}
```

Create a migration to add the new columns.

**Example Migration:**

```php
class AddModerationColumnsToPostsTable extends Migration
{
    /**
	* Run the migrations.
	*
	* @return void
	*/
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->smallInteger('status')->default(0);
            $table->dateTime('moderated_at')->nullable();
            //To track who moderated the Model, add 'moderated_by' and set the column name in the config file.
            //$table->integer('moderated_by')->nullable()->unsigned();
        });
    }

	/**
	* Reverse the migrations.
	*
	* @return void
	*/
    public function down()
    {
        Schema::table('posts', function(Blueprint $table)
        {
            $table->dropColumn('status');
            $table->dropColumn('moderated_at');
            //$table->dropColumn('moderated_by');
        });
    }
}
```



**You are ready to go!**

## From Original Readme

## Possible Use Case

1.  User creates a resource (a post, a comment or any Eloquent Model).
    
2.  The resource is pending and invisible in website (ex. `Post::all()` returns only approved posts).
    
3.  Moderator decides if the resource will be approved, rejected or postponed.
    
4.  **Approved**: Resource is now public and queryable.
    
5.  **Rejected**: Resource will be excluded from all queries. Rejected resources will be returned only if you scope a query to include them. (scope: `withRejected`)
    
6.  **Postponed**: Resource will be excluded from all queries until Moderator decides to approve it.
    
7.  You application is clean.


## Usage

> **Note:** In next examples I will use Post model to demonstrate how the query builder works. You can Moderate any Eloquent Model, even User.

### Moderate Models

You can moderate a model Instance:

```php
$post->markApproved();

$post->markRejected();

$post->markPostponed();

$post->markPending();
```
or by referencing it's id
```php
Post::approve($post->id);

Post::reject($post->id);

Post::postpone($post->id);
```
or by making a query.
```php
Post::where('title', 'Horse')->approve();

Post::where('title', 'Horse')->reject();

Post::where('title', 'Horse')->postpone();
```

### Query Models

By default only Approved models will be returned on queries.

##### To query the Approved Posts, run your queries as always.

```php
//it will return all Approved Posts (strict mode)
Post::all();

// when not in strict mode
Post::approved()->get();

//it will return Approved Posts where title is Horse
Post::where('title', 'Horse')->get();
```

##### Query pending or rejected models.

```php
//it will return all Pending Posts
Post::pending()->get();

//it will return all Rejected Posts
Post::rejected()->get();

//it will return all Postponed Posts
Post::postponed()->get();

//it will return Approved and Pending Posts
Post::withPending()->get();

//it will return Approved and Rejected Posts
Post::withRejected()->get();

//it will return Approved and Postponed Posts
Post::withPostponed()->get();
```

##### Query ALL models

```php
//it will return all Posts
Post::withAnyStatus()->get();

//it will return all Posts where title is Horse
Post::withAnyStatus()->where('title', 'Horse')->get();
```
### Model Status
To check the status of a model there are 3 helper methods which return a boolean value.
```php
//check if a model is pending
$post->isPending();

//check if a model is approved
$post->isApproved();

//check if a model is rejected
$post->isRejected();

//check if a model is rejected
$post->isPostponed();
```
## [Strict Moderation](#strict_moderation)

Strict Moderation means that only Approved resource will be queried. To query Pending resources along with Approved you have to disable Strict Moderation. See how you can do this in the [configuration](#configuration).

## [Configuration](#configuration)

### Global Configuration

To configuration Moderation package globally you have to edit `config/moderator.php`. Inside `moderator.php` you can configure the following:

1.  `status_column` represents the default column 'status' in the database.
2.  `moderated_at_column` represents the default column 'moderated_at' in the database.
3.  `moderated_by_column` represents the default column 'moderated_by' in the database.
4.  `strict` represents [_Strict Moderation_](#strict_moderation).

### Model Configuration

Inside your Model you can define some variables to overwrite **Global Settings**.

```php
// To overwrite `status` column define:
const MODERATION_STATUS = 'moderation_status';

// To overwrite `moderated_at` column define:
const MODERATED_AT = 'mod_at';

// To overwrite `moderated_by` column define:
const MODERATED_BY = 'mod_by';

// To enable or disable Strict Moderation:
public static $strictModeration = true;
```
## Testing

All the original tests are retained and updated.
  
```bash
composer test
```
## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing
Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities
Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- Full Credit to [Alex Kyriakidis](https://github.com/hootlex) for the original package
  
- [Praveen K](https://github.com/pravnkay)

- [All Contributors](../../contributors)

## License  

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.