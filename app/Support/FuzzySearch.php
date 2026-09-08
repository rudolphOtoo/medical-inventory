<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class FuzzySearch
{
    /**
     * Apply multi-token fuzzy wildcard search across specified Eloquent columns.
     *
     * @param  array<int, string>  $columns
     */
    public static function apply(Builder $query, array $columns, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $rawSearch = trim((string) $search);
        // Split by whitespace, limit to 6 tokens for performance
        $tokens = array_slice(preg_split('/\s+/', $rawSearch, -1, PREG_SPLIT_NO_EMPTY) ?: [$rawSearch], 0, 6);

        $query->where(function (Builder $outerQuery) use ($columns, $tokens) {
            foreach ($tokens as $token) {
                // Escape SQL LIKE wildcards
                $escapedToken = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $token);
                $directPattern = "%{$escapedToken}%";

                // Interleaved character fuzzy pattern (e.g. "xray" -> "%x%r%a%y%")
                $chars = mb_str_split($escapedToken);
                $fuzzyPattern = '%'.implode('%', $chars).'%';

                $outerQuery->where(function (Builder $tokenQuery) use ($columns, $directPattern, $fuzzyPattern) {
                    foreach ($columns as $column) {
                        if (str_contains($column, '.')) {
                            [$relation, $relCol] = explode('.', $column, 2);
                            $tokenQuery->orWhereHas($relation, function (Builder $relQuery) use ($relCol, $directPattern, $fuzzyPattern) {
                                $relQuery->where($relCol, 'like', $directPattern)
                                    ->orWhere($relCol, 'like', $fuzzyPattern);
                            });
                        } else {
                            $tokenQuery->orWhere($column, 'like', $directPattern)
                                ->orWhere($column, 'like', $fuzzyPattern);
                        }
                    }
                });
            }
        });

        return $query;
    }
}
