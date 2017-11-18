<?php
return [
    'model' => [
        /**
         * Default Model Namespace.
         */
        'namespace' => 'App',

        /**
         * Use Property Definer.
         */
        'propertyDefiner' => true,

        /**
         * Method definer
         */
        'methodDefiner' => true,

        /**
         * Does it generate guarded column. Either guarded or fillable columns should be choose.
         */
        'guarded' => true,
        /**
         * Does it generate fillable columns. Either guarded or fillable columns should be choose.
         */
        'fillable' => false,

        /**
         * Do it generate casts property based on your database column type.
         */
        'casts' => false,

        /**
         * Whether generator create scopes to all columns except protected columns
         */
        'scopes' => true,

        /**
         * Does generator create mutator for date time, string and varchar columns so that data can be converted before save. See getDateFormat options for display format
         */
        'mutators' => true,

        /**
         * Does generator create accessors for date column so it can be render as human readable format.
         */
        'accessors' => true,

        /**
         * Sometimes relation are not defined on database columns. Add missing rules here
         */
        'relations' => [
            //'table.foreign_key'=>'foreign_table' // For example user.role_id=> roles.id
        ],

        /**
         * Not Fillable Columns
         */
        'protectedColumns' => ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token', 'password'],

        /**
         * Get Date Format. Model Date/ Time related column will display date to user in this format
         */
        'getDateFormat' => [
            'time' => 'h:i A',
            'date' => 'm/d/Y',
            'datetime' => 'm/d/Y h:i A',
            'timestamp' => 'm/d/Y h:i A'
        ],

        /**
         * Date will be convert in this format before save.
         */
        'setDateFormat' => [
            'time' => 'H:i:s',
            'date' => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'timestamp' => 'Y-m-d H:i:s'
        ],
    ],
    'view' => [
        /**
         * Path to the main folder. Folder path are relative to base_path
         */
        'path' => 'resources/views/',

        /**
         * Default Layout
         */
        'layout' => 'layouts.app',

        /**
         * Which bootstrap version you like to use in your view code. Available version are 3 and 4
         */
        'bootstrap' => '4',

        /**
         * Protected Columns. There are some column that are internal use only.
         */
        'ignore' => [
            //table.column e.g. users.remember_token
        ],

        /**
         * Whether add breadcrumb or not. [To DO]
         */
        'breadcrumb' => false,

        /**
         * Search box on index page.
         */
        'search' => false,

        'page' => [
            /**
             *  Path to full pages which will viewable by browser.
             *  Relative to resources/views folder. Default to resources/views/pages
             */
            'path' => 'pages',

            'index' => [
                /**
                 * Name of the page. e.g. index.blade.php
                 */
                'name' => 'index',

                /**
                 * Style of the page. available options are table, panel
                 */
                'type' => 'table'
            ],
            'create' => [
                'name' => 'create'
            ],
            'edit' => [
                'name' => 'edit'
            ],
            'show' => [
                'name' => 'show'
            ]
        ]
    ],
    'controller' => [
        /**
         * Controller Parent Namespace for web
         */
        'namespace' => 'App\Http\Controllers',

        /**
         * Controller Parent Namespace for API
         */
        'apiNamespace' => 'App\Http\Controllers\Api',

        /**
         * After every request class name this world will be added. For example, User will be UserController
         */
        'classSuffix' => 'Controller'
    ],
    'request' => [
        /**
         *  Request Parent Namespace for web
         */
        'namespace' => 'App\Http\Requests',

        /**
         *  Request Parent Namespace for API
         */
        'apiNamespace' => 'App\Http\Requests\Api',

        /**
         * After every request class name this world will be added. For example, Users will be UsersRequest
         */
        'classSuffix' => 'Request'
    ],
    'policy' => [
        /**
         * Root namespace
         */
        'namespace' => 'App\Policies',

        /**
         * After every policy class name this world will be added. For example, User will be UserPolicy
         */
        'classSuffix' => 'Policy'
    ],
    'route' => [
        /**
         * Path to web route file
         */
        'web' => 'routes/web.php',

        /**
         * Path to API route file
         */
        'api' => 'routes/api.php',

        /**
         * Sub restful routes
         *
         * All the other option e.g. Controller, Request will be created according to this.
         */
        'subResource' => [
            //'child'=>'parent.child' e.g. photos=>posts.photos
        ]
    ],
    'transformer' => [
        /**
         * Root namespace
         */
        'namespace' => 'App\Transformers',

        /**
         * After every transformer class name this world will be added. For example, Users will be UsersTransformer
         */
        'classSuffix' => 'Transformer'
    ],

    /**
     * Path to Migration
     */
    'migrationPath' => 'database/migrations/',

    /**
     * Pivot tables
     */
    'pivotTables' => [],

    /**
     *
     */
    'image' => [

        /**
         * Images columns. System will automatically add rules to request class and also does uploading work via Controller and Model.
         */
        'columns' => [
            //'table.column'
        ],

        /**
         * Storage folder where images will be saved.
         */
        'saveTo' => 'storage/app/public'
    ],

    /**
     * Name of the INFORMATION SCHEMA that mysql use internally to tracks foreign keys etc
     */
    'informationSchema' => 'INFORMATION_SCHEMA'


];
