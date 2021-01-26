#LaraCrud Version 5 work plan

It needs 4 years from initial idea to implementation (v4). 
During its development most of the focus is on  working Classes that can generate code. 
For that reason there was a mess in codebase and its hardly readable by other developers to contribute.

Version 5 will be a complete rewrite of the entire codebase with a
guideline to developers who want to contribute
###Repository Pattern
V4 stick with DbReader component which support only `MySQL` database. 
In v5 our plan is to make `LaraCrud` independent from `MySQL` .
`LaraCrud` Should depend on `RepositoryInterface` to get its necessary data.
You can implement your own Repository and register it on `config/laracrud.php` like below

``` 
    'binds'=> [
        \LaraCrud\Contracts\DatabaseContract::class  => \LaraCrud\Repositories\DatabaseRepository::class,
        \LaraCrud\Contracts\TableContract::class     => \LaraCrud\Repositories\TableRepository::class,
        \LaraCrud\Contracts\ColumnContract::class    => \LaraCrud\Repositories\ColumnRepository::class,
```

###MySQL full Text Search /Scott implementation
If you defined a Full Text search index for your table. Then while generating 
your model necessary `FullTextSearch` code will be integrated into your Model. 
When you create Controller/View files based on this Model, Search functionality will be there as well.
So you do not have to write anything to implement your FullTextSearch

###Blade file Builder From UI
Now you can able to manage how your `Post` `index.blade.php` will look like. 
You can select Table/Card template also can able to select which column you want to show on your 
Card\Table and which order. 

