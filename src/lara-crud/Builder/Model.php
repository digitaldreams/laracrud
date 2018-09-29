<?php

namespace LaraCrud\Builder;


use LaraCrud\Helpers\ForeignKey;
use DbReader\Column;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class Model
{
    use Helper;

    /**
     * @var Model
     */
    protected $modelBuilder;

    /**
     * @var Column
     */
    protected $column;

    /**
     * It will be added as comments before the class.
     * It will auto suggest property name and its data type while you are trying to insert values
     *
     * @var array
     */
    public $propertyDefiners = [];

    /**
     * It will be added as comments before the class.
     * It will auto suggest method name and its return type while you are trying to use it
     *
     * @var array
     */
    public $methodDefiners = [];

    /**
     * Will contains all scopeable columns
     * @var array
     */
    public $scopes = [];

    /**
     * Contains all the possible relations
     * @var array
     */
    public $relations = [];

    /**
     * All the casting columns
     * @var array
     */
    public $casts = [];

    /**
     * ALl the contstants
     * @var array
     */
    public $constants = [];

    /**
     * Date time columns
     * @var array
     */
    public $dates = [];

    /**
     * @var array
     */
    public $fillable = [];

    /**
     * @var array
     */
    public $mutators = [];

    /**
     * @var array
     */
    public $accessors = [];

    /**
     * Database Type => PHP data types
     * @var array
     */
    private $converTypes = [
        'varchar' => 'string',
        'boolean' => 'bool',
        'enum' => 'string',
        'int' => 'int',
        'double' => 'double',
        'bigint' => 'int',
        'tinyint' => 'int'
    ];

    /**
     * Make search scope
     * @var array
     */
    public $searchScope = [];

    /**
     * To get data type and assist in casting
     * @var DataType
     */
    public $dataType;

    /**
     * Guarded columns
     * @var array
     */
    public $guarded = [];

    /**
     * ModelBuilder constructor.
     *
     * @param Column $column
     * @internal param Model $modelBuilder
     */
    public function __construct(Column $column)
    {
        $this->column = $column;
        $this->dataType = new DataType($column);
        $this->load();
    }

    /**
     * It will process all the necessary work
     *
     */
    public function build()
    {

    }

    protected function load()
    {
        $this->propertyDefiner();
        $this->fillable();
        $this->methodDefiner();
        $this->constant();
        $this->dates();
        $this->mutators();
        $this->accessors();
        $this->scopes();
        $this->makeSearch();
        $this->casts();
        $this->relations();
        $this->guarded();
    }

    /**
     * It will process all the necessary work
     * @param Model|static $modelBuilder
     */
    public function merge(Model $modelBuilder)
    {
        $this->propertyDefiners = array_merge($this->propertyDefiners, $modelBuilder->propertyDefiners);
        $this->methodDefiners = array_merge($this->methodDefiners, $modelBuilder->methodDefiners);
        $this->constants = array_merge($this->constants, $modelBuilder->constants);
        $this->dates = array_merge($this->dates, $modelBuilder->dates);
        $this->mutators = array_merge($this->mutators, $modelBuilder->mutators);
        $this->accessors = array_merge($this->accessors, $modelBuilder->accessors);
        $this->scopes = array_merge($this->scopes, $modelBuilder->scopes);
        $this->searchScope = array_merge($this->searchScope, $modelBuilder->searchScope);
        $this->casts = array_merge($this->casts, $modelBuilder->casts);
        $this->fillable = array_merge($this->fillable, $modelBuilder->fillable);
        $this->relations = array_merge($this->relations, $modelBuilder->relations);
        $this->guarded = array_merge($this->guarded, $modelBuilder->guarded);
    }

    /**
     * Check if current column isForeign
     * if so then add a row to foreignkeys columns also add builder foreign keys then return
     */
    public function foreign()
    {

    }

    public function guarded()
    {
        if ($this->dataType->guard()) {
            $this->guarded[] = '\'' . $this->column->name() . '\'';
        }
        return $this->guarded;
    }

    /**
     * Check if current column is scopeable if so then make a scope
     */
    public function scopes()
    {
        if (!in_array($this->column->name(), config('laracrud.model.protectedColumns')) && !in_array($this->column->type(), ['text', 'tinytext', 'bigtext'])) {
            $this->scopes[] = (new TemplateManager('model/scope.txt', [
                    'methodName' => ucfirst($this->column->camelCase()),
                    'fielName' => $this->column->name()
                ]))->get() . "\n";
        }
        return $this->scopes;
    }

    /**
     * return a entry to the casts array
     */
    public function casts()
    {
        if (null === $this->dataType->cast()) return $this->casts;
        $this->casts[] = "'" . $this->column->name() . "'=>'" . $this->dataType->cast() . "'";
        return $this->casts;
    }

    /**
     * @return array
     */
    public function constant()
    {
        $cColumns = config('laracrud.model.constantColumns');
        if (!$cColumns && $this->column->type() !== 'enum') {
            return $this->constants;
        }

        $col_name = strtoupper(preg_replace('/[^a-zA-Z0-9]+/', '_', $this->column->name()));
        if (0 === strcmp('enum', $this->column->type())) {
            foreach ($this->column->options() as $value) {
                $name = strtoupper($col_name . '_' . str_replace([" ",
                        "-", "\"", "/"], "_", $value));
                $this->constants[] = 'public const ' . $name . '=' . "'$value'" . ';';
            }
        }
        if ($cColumns) {
            $this->constants[] = 'public const ' . $col_name . ' = \'' . $this->column->name() . '\';';
        }
        return $this->constants;
    }

    /**
     *  return a line to property definer array
     */
    public function propertyDefiner()
    {
        $this->propertyDefiners[] = ' * @property ' . $this->dataType->typeHint() . ' $' . $this->column->name() . ' ' . str_replace("_", " ", $this->column->name());
        return $this->propertyDefiners;
    }

    /**
     *  return a line to method definer array
     */
    public function methodDefiner()
    {
        $this->methodDefiners[] = '@method \Illuminate\Database\Eloquent\Builder ' . lcfirst($this->getModelName($this->column->name())) . '(' . $this->column->type() . ' $' . $this->column->name() . ')' . str_replace("_", " ", $this->column->name());
        return $this->methodDefiners;
    }

    /**
     *  If its applicable to fill then return it otherwise return empty
     */
    public function fillable()
    {
        if (!$this->dataType->guard() && !in_array($this->column->name(), config('laracrud.model.protectedColumns'))) {
            $this->fillable[] = "'" . $this->column->name() . "'";
        }
        return $this->fillable;
    }

    /**
     * Make Relation if its a foreign key.
     */
    public function relations()
    {
        if (!$this->column->isForeign()) {
            return [];
        }
        $fk = new ForeignKey($this->column->foreign);
        $this->relations[] = [
            'name' => ForeignKey::RELATION_BELONGS_TO,
            'foreign_key' => $fk->column(),
            'model' => $this->getModelName($fk->foreignTable()),
            'other_key' => $fk->foreignColumn()
        ];
        return $this->relations;
    }

    /**
     * Date type column processing
     * @return array
     */

    public function dates()
    {
        //Check if it is a data time column. If so then add it to $protected $dates=[]
        if (in_array($this->column->type(), ['time', 'date', 'datetime', 'timestamp'])
            // && !in_array($this->column->name(), config('laracrud.model.protectedColumns'))
        ) {
            $this->dates[] = "'" . $this->column->name() . "'";
        }
        return $this->dates;
    }

    /**
     * Make a line to Table wise search
     * @return array|void
     */
    public function makeSearch()
    {
        //protected column and foreign key will be excepted from making seach scope
        if (in_array($this->column->name(), config('laracrud.model.protectedColumns')) || $this->column->isForeign()) {
            return $this->searchScope;
        }

        if (in_array($this->column->type(), ['varchar', 'text'])) {
            $this->searchScope[] = "\t" . "->orWhere('" . $this->column->name() . "','LIKE','%'.\$q.'%')" . PHP_EOL;
        } elseif (in_array($this->column->type(), ['int', 'bigint'])) {
            $this->searchScope[] = "\t" . "->orWhere('" . $this->column->name() . "',\$q)" . PHP_EOL;
        }
        return $this->searchScope;
    }

    /**
     * making of mutator code
     */
    public function mutators()
    {
        $setTimeFormats = config('laracrud.model.setDateFormat', []);
        if (in_array($this->column->name(), config('laracrud.model.protectedColumns'))) {
            return $this->mutators;
        }
        $label = str_replace(" ", "", ucwords(str_replace("_", " ", $this->column->name())));

        if (in_array($this->column->type(), ['time', 'date', 'datetime', 'timestamp'])) {

            $setDateFormat = isset($setTimeFormats[$this->column->type()]) ? $setTimeFormats[$this->column->type()] : "Y-m-d";

            $tempMan = new TemplateManager('model/setAttributeDate.txt', [
                'format' => $setDateFormat,
                'columnLabel' => $label,
                'column' => $this->column->name()
            ]);
            $this->mutators[] = $tempMan->get();
        } elseif (in_array($this->column->type(), ['varchar', 'text', 'tinytext', 'bigtext'])) {

            $tempMan = new TemplateManager('model/setAttributeText.txt', [
                'columnLabel' => $label,
                'column' => $this->column->name()
            ]);
            $this->mutators[] = $tempMan->get();
        }

        return $this->mutators;
    }

    /**
     * Making of accessors code.
     */
    public function accessors()
    {
        if (in_array($this->column->name(), config('laracrud.model.protectedColumns'))) {
            return $this->accessors;
        }
        if (in_array($this->column->type(), ['time', 'date', 'datetime', 'timestamp'])) {
            $getTimeFormats = config('laracrud.model.getDateFormat', []);
            $tempMan = new TemplateManager('model/getAttributeDate.txt', [
                'format' => isset($getTimeFormats[$this->column->type()]) ? $getTimeFormats[$this->column->type()] : "d M Y",
                'columnLabel' => str_replace(" ", "", ucwords(str_replace("_", " ", $this->column->name()))),
                'column' => $this->column->name()
            ]);
            $this->accessors[] = $tempMan->get();
        }
        return $this->accessors;
    }


}