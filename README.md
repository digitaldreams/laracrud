# LARAVEL CRUD #

CRUD means CREATE, READ, UPDATE AND DELTE are common work in almost every web application. Laravel has also CRUD. We use Model, View, Controller, Request, Route's for CRUD.  A well structured database are the blueprint of a web application. So We can create Model, View, Controller, Request from a database table.

### Installation ###

Install this library into App/Libs folder.

Then add following line to Kernel::commands in console/kernal.php
        \LaraCrud\Console\Request::class,
        \LaraCrud\Console\Model::class,
        \LaraCrud\Console\Controller::class,
        \LaraCrud\Console\Route::class,
        \LaraCrud\Console\View::class,
        \LaraCrud\Console\Mvc::class,
Then you can see new commands by running 'php artisan'

* tb:model {tableName} (create model based on table)
* tb:request {tableName} (create Request Class based on table)
* tb:Controller {Model} (Create Controller Class based on Model)
* tb:mvc {table} (run above commands into one place)
* tb:route {controller} (create routes based on controller method)
* tb:view {table} {page(index|form|details)} {type(table|panel|tabpan)}