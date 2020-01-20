# Laravel Code Generator

Do you have a well structed database and you want to make a Laravel Application on top of it.
By using this tools you can generate Models which have necessary methods and property, Request class with rules, generate route from controllers method and its parameter and full features form with validation error message and more with a single line of command. So lets start. [See demo](https://github.com/digitaldreams/laracrud-demo) code and [slides](https://slides.com/tuhinbepari/laracrud/fullscreen#/) 

### Installation ###
```javascript
  "require": { 
     "digitaldream/laracrud": "4.*"
}
```


This version are ready to use in Laravel 5.3 and above. If you are using 5.2  please have a look to config/laracrud.php and adjust folder path.

## Setting

01. Add this line to config/app.php providers array . Not needed if you are using laravel 5.5 or greater
```php
    LaraCrud\LaraCrudServiceProvider::class
``` 
  
02. Then Run
```php
    php artisan vendor:publish --provider="LaraCrud\LaraCrudServiceProvider"
```
    
## Commands
Then you can see new commands by running 'php artisan'

*	`laracrud:model {tableName} {name?} {--on=} {--off=}`: Create model based on table
*	`laracrud:request {Model} {name?} {--resource=} {--controller=} {--api}`: Create Request Class/es based on table
*	`laracrud:Controller {Model} {name?} {--parent=} {--only=} {--api}`: Create Controller Class based on Model
*	`laracrud:mvc {table} {--api}`: Run above commands into one place
*	`laracrud:route {controller} {--api}`: Create routes based on controller method
*	`laracrud:view {Model} {--page=(index|create|edit|show|form|table|panel|modal)} {--type=} {--name=} {--controller=}`
*	`laracrud:migration {table}`: Create a migration file based on Table structure. Its opposite of normal migration file creation in Laravel
* `laracrud:policy {model} {--controller=} {--name=}`
* `laracrud:package {--name=}`
* `laracrud:transformer {model} {name?}`: Create a dingo api transformer for a model
* `laracrud:test {controller} {--api}`: Create test methods for each of the method of a controller

**N.B: --api option will generate api resource. Like Controller, Request, Route, Test. [Dingo API](https://github.com/dingo/api) compatible code will be generated**
 . [See API documentation](https://github.com/digitaldreams/laracrud/wiki/API-Development)

### How to Use



## Create a Model

Theare are some good practice for model in Laravel. Use scope to define query, define fillable, dates, casts etc.
ALso define relation, set*Attribute and get*Attribute for doing work before and after model save and fetch.

We are going to create this thing automatically by reading table structure and its relation to others table.
```php
    php artisan laracrud:model users
```
By default Model Name will be based on Table name. But Model name can be specified as second parameter. Like below
```php
php artisan laracrud:model users MyUser
```  
[Video Tutorial](https://www.youtube.com/watch?v=TDfsdkPHKf4&list=PLcGdsjZbEjRtxROY7mlHcJQcSwxx9L8NB)

## Create Request

 An well structured table validate everything before inserting . You can not insert a illegal date in a birth_date column if its data type set to date.So if we have this logic set on table why we should write it on Request again. Lets use this table logic to create a request class in laravel.
 
    php artisan laracrud:request MyUser
   

 Here **MyUser** is Eloquent Model. From LaraCrud version 4.* this command accept Model Name instead of Table

Like Model Name we can also specify a custom request name.
```php
php artisan laracrud:request User RegisterRequest
```  
Also If you like to create multiple request for your resourceful controller then 
```php
php artisan laracrud:request User –-resource=index,show,create,update,destroy
```

It will create a folder users on app/Http/Requests folder and create these request classes. 
Sometimes you may like to create individual request class for each of your controller method then. 
```php
php artisan laracrud:request User –-controller=UserController
php artisan laracrud:request User --controller=UserController --api //this will generated Request for API usages

```
It will read your controller and create request classes for your Public method 

[video tutorial](https://www.youtube.com/watch?v=MGMP9FB2l5g&index=2&list=PLcGdsjZbEjRtxROY7mlHcJQcSwxx9L8NB)

## Create Controller
```php 
    php artisan laracrud:controller User
    //Or Give a controller name.
    php artisan laracrud:controller User MyUserController
    //Or we can give a sub namespace
    php artisan laracrud:controller User User/UserController
    //It will create a folder User to controllers
    php artisan laracrud:controller Comment --parent=Post // it will create a sub resource CommentController
```
This will create a controller which have create, edit, save and delete method with codes .
It also handle your relation syncronization

[video tutorial](https://youtu.be/MGMP9FB2l5g?t=5m10s)

## Create View 

A typical form represent a database table. 
E.g. for a Registration form it has all the input field which is necessary for users table. Most of the time we use 
Bootstrap to generate a form . It has error field highlighting if validation fails. Also display value. This all can be done by
```php  
 php artisan laracrud:view User --page=form
 php artisan laracrud:view User --page=index --type=panel //There are three type of layout for index page panel,table and tabpan
 php artisan laracrud:view User --controller=UserController // Create all the views which is not created yet for this controller
 
 ```
 Here **User** is Eloquent Model. From LaraCrud version 4.* this command accept Model Name instead of Table

This will create a complete users crud view. 

[video tutorial](https://www.youtube.com/watch?v=RjRFWABwXnA&list=PLcGdsjZbEjRtxROY7mlHcJQcSwxx9L8NB&index=5)

## Create Route

Routes are the most vital part of a laravel application.
WE create routes by its public methods and parameter. 
Lets do this work to rotue command.
```php 
    php artisan laracrud:route UserController
    php artisan laracrud:route UserController --api // generate api routes for this conroller
```
If you have some routes already redine for <controllerName> then do not worry.
It will create routes for which does not define yet. 
Please use forward slash(/) for sub namespace. For example,
```php 
 php artisan laracrud:route Auth/AuthController
```

## Policy
Laravel have default policy generator. It works like same with one extra feature that is create policy method based on controller public methods. 
```php
php artisan laracrud:policy User 
// will create policy class with basic methods
php artisan laracrud:policy User --controller=UserController
// create method based on Controller public methods
```

## Package
Packages gives us opportunity to create/use components into our existing application. That make our code reusable. 
Laravel package has similar structure as a Laravel application has.
```php
php artisan laracrud:package Hello
```
This will create a folder same structure as a Laravel application has into your /packages folder
[See Package documentation](https://github.com/digitaldreams/laracrud/wiki/Package-Development)
[Video tutorial](https://www.youtube.com/watch?v=7-mhRjKQPuY&t=2s)

## Test
We need to test our routes endpoints. To create test class based on a controller do the following
```php
php artisan laracrud:test UserController
// or to make api test just pass --api like below
php artisan laracrud:test UserController --api
```

## Transformer
Transformer are a vital part of Dingo API. To expose a model to api endpoint Transformer play media between api and model.

```php
php artisan laracrud:transformer User
```
[See API documentation](https://github.com/digitaldreams/laracrud/wiki/API-Development)
## Create everything at once

If we need all of the command to then just to
```php 
    php artisan laracrud:mvc users
    php artisan laracrud:mvc users --api // create all the API related resources
```
It will create Model, Request, Controller, View.
Then you just need to run route command to create routes.

## Migration

Somethings we may need to create a migration file from a table. Then this command will be useful. It will generate all the necessary code for your migration files. So your migration file is ready to use.

```php 
php artisan laracrud:migration users
```
## Customize Code Template
Coding Style differ from developer to developer. So you can control how your code will be generated. Code templates are organized by folder in resources/vendor/laracrud/templates . Go there and change the style. After that your code will be generated by reading these files. _Please do not remove or change @@placeHolder@@. This will be replaced by application_.


# NB: only for mysql database

It is recommended to take a look in the generated file before use it.


Like my work? If so [hire me on upwork](https://www.upwork.com/fl/tuhinbepari)
