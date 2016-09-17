# LARAVEL CRUD #

Do you have a well structed database and you want to make a Laravel Application on top of it.
By using this tools you can generate Models which have necessary methods and property, Request class with rules, generate route from controllers method and its parameter and full features form with validation error message and more with a single line of command. So lets start.

### Installation ###
  "require": {
  
     "digitaldream/laracrud": "1.0.*"
        
}

Version 1.x is for laravel 5.2 & 5.1

Then add following line  in console/kernal.php

     protected $commands = [
        \LaraCrud\Console\Request::class,
        \LaraCrud\Console\Model::class,
        \LaraCrud\Console\Controller::class,
        \LaraCrud\Console\Route::class,
        \LaraCrud\Console\View::class,
        \LaraCrud\Console\Mvc::class
    ];
Then you can see new commands by running 'php artisan'

* laracrud:model {tableName} {name?} (create model based on table)
* laracrud:request {tableName} {name?} (create Request Class based on table)
* laracrud:Controller {Model} {name?} (Create Controller Class based on Model)
* laracrud:mvc {table} (run above commands into one place)
* laracrud:route {controller} (create routes based on controller method)
* laracrud:view {table} {page(index|form|details)} {type(table|panel|tabpan)} {name?}


###How to Use###



##Create a Model##

Theare are some good practice for model in Laravel. Use scope to define query, define fillable, dates, casts etc.
ALso define relation, set*Attribute and get*Attribute for doing work before and after model save and fetch.

We are going to create this thing automatically by reading table structure and its relation to others table.

    php artisan laracrud:model users
  
By default Model Name will be based on Table name. But Model name can be specified as second parameter. Like below

    php artisan laracrud:model users MyUser

  
##Create Request##

 An well structured table validate everything before inserting . You can not insert a illegal date in a birth_date column if its data type set to date.So if we have this logic set on table why we should write it on Request again. Lets use this table logic to create a request class in laravel.
 
    php artisan laracrud:request users

Like Model Name we can also specify a custom request name.

    php artisan laracrud:request users RegisterRequest
  


##Create View 

A typical form represent a database table. 
E.g. for a Registration form it has all the input field which is necessary for users table. Most of the time we use 
Bootstrap to generate a form . It has error field highlighting if validation fails. Also display value. This all can be done by
  
    php artisan laracrud:view users form
    php artisan laracrud:view users index //There are three type of layout for index page panel,table and tabpan
    php artisan laracrud:view users details

This will create a complete users crud view. 

##Create Controller##
 
    php artisan laracrud:controller User
    //Or Give a controller name.
    php artisan laracrud:controller User MyUserController
    //Or we can give a sub namespace
    php artisan laracrud:controller User User/UserController
    //It will create a folder User to controllers

This will create a controller which have create, edit, save and delete method with codes .
It also handle your relation syncronization

##Create Route##

Routes are the most vital part of a laravel application.
WE create routes by its public methods and parameter. 
Lets do this work to rotue command.

    php artisan laracrud:route UserController

If you have some routes already redine for <controllerName> then do not worry.
It will create routes for which does not define yet. 
Please use forward slash(/) for sub namespace. For example,

    php artisan laracrud:route Auth/AuthController


##Create everything at once##

If we need all of the command to then just to

    php artisan laracrud:mvc users

It will create Model, Request, Controller, View.
Then you just need to run route command to create routes.

NB: only for mysql database
