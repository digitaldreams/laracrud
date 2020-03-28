<?php

namespace LaraCrud\Services;

trait FullTextSearch
{
    /**
     * Replaces spaces with full text search wildcards.
     *
     * @param string $term
     * @param string $start
     * @param string $end
     *
     * @return string
     */
    protected function fullTextWildcards($term, $start = '+', $end = '*')
    {
        // removing symbols used by MySQL
        $reservedSymbols = ['-', '+', '"', "'", '<', '>', '@', '(', ')', '~', '*'];
        $term = str_replace($reservedSymbols, '', $term);

        $words = explode(' ', $term);

        foreach ($words as $key => $word) {
            /*
             * applying + operator (required word) only big words
             * because smaller ones are not indexed by mysql
             */
            if (strlen($word) >= 3) {
                $words[$key] = $start . $word . $end;
            }
        }

        $searchTerm = implode(' ', $words);

        return $searchTerm;
    }

    /**
     * Scope a query that matches a full text search of term.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $term
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $term)
    {
        $columns = implode(',', $this->searchable);

        $query->whereRaw("MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)", $this->fullTextWildcards($term));

        return $query;
    }
}
