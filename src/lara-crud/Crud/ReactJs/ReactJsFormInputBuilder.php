<?php

namespace LaraCrud\Crud\ReactJs;

class ReactJsFormInputBuilder
{
    /**
     * @var array
     */
    protected array $rules;

    public bool $required = false;

    public string $type;

    public int $min;

    public int $max;

    public bool $isArray = false;

    public array $options = [];

    /**
     * ReactJsFormInputBuilder constructor.
     *
     * @param array $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function parse()
    {
        $fileValidators = ['file', 'image', 'mimes', 'mimetypes', 'dimensions'];
        $dateValidators = [
            'after',
            'after_or_equal',
            'date',
            'date_equals',
            'date_format',
            'before',
            'before_or_equal',
        ];
        $numberValidators = [
            'digits',
            'digits_between',
            'integer',
            'numeric',
        ];
        foreach ($this->rules as $rule) {
            $rule = (string) $rule;
            $parts = explode(':', $rule);

            if (in_array($parts[0], $fileValidators)) {
                $this->type = 'file';
            }

            if (in_array($parts[0], $dateValidators)) {
                $this->type = 'date';
            }

            if (in_array($parts[0], $numberValidators)) {
                $this->type = 'number';
            }
            if ('in' === $parts[0]) {
                $options = explode(',', $parts[1]);
                if ($options >= 3) {
                    $this->type = 'select';
                } else {
                    $this->type = 'radio';
                }
                $this->options = $options;
            }
            if ('boolean' === $rule) {
                $this->type = 'checkbox';
            }

            if ('required' === $rule) {
                $this->required = true;
            }
            if ('min' === $parts[0]) {
                $this->min = $parts[1] ?? 0;
            }
            if ('max' === $parts[0]) {
                $this->max = $parts[1] ?? 0;
            }
        }
    }
}
