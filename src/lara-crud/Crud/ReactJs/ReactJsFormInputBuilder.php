<?php

namespace LaraCrud\Crud\ReactJs;

class ReactJsFormInputBuilder
{
    public bool $required = false;

    public string $type = 'text';

    public int $min = 0;

    public int $max = 0;

    public bool $isArray = false;

    public array $options = [];

    /**
     * ReactJsFormInputBuilder constructor.
     *
     * @param array $rules
     */
    public function __construct(array $rules = [])
    {
        $this->parse($rules);
    }

    public function parse(array $rules)
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
        foreach ($rules as $rule) {
            try {
                $rule = (string) $rule;
                if ('array' === $rule) {
                    $this->isArray = true;
                    break;
                }
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
                if (in_array($rule, ['accepted', 'boolean'])) {
                    $this->type = 'checkbox';
                }
                //active_url
                if (in_array($rule, ['url', 'email'])) {
                    $this->type = $rule;
                }
                if (in_array($rule, ['alpha', 'alpha_num', 'alpha_dash', 'string'])) {
                    $this->type = 'text';
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
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
