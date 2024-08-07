<?php

/**
 * @file classes/userGroup/relationships/UserUserGroup.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class \PKP\userGroup\relationships\UserUserGroup
 *
 * @brief UserUserGroup metadata class.
 */

namespace PKP\userGroup\relationships;

use APP\facades\Repo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use PKP\core\Core;

class UserUserGroup extends \Illuminate\Database\Eloquent\Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;
    protected $fillable = ['userGroupId', 'userId', 'dateStart', 'dateEnd', 'masthead'];

    public function user(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => Repo::user()->get($attributes['user_id']),
            set: fn ($value) => $value->getId()
        );
    }

    public function userGroup(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => Repo::userGroup()->get($attributes['user_group_id']),
            set: fn ($value) => $value->getId()
        );
    }

    public function userId(): Attribute
    {
        return Attribute::make(
            get: fn ($user, $attributes) => $attributes['user_id'],
            set: fn ($value) => ['user_id' => $value]
        );
    }

    public function userGroupId(): Attribute
    {
        return Attribute::make(
            get: fn ($userGroup, $attributes) => $attributes['user_group_id'],
            set: fn ($value) => ['user_group_id' => $value]
        );
    }

    public function dateStart(): Attribute
    {
        return Attribute::make(
            get: fn ($userGroup, $attributes) => $attributes['date_start'],
            set: fn ($value) => ['date_start' => $value]
        );
    }

    public function dateEnd(): Attribute
    {
        return Attribute::make(
            get: fn ($userGroup, $attributes) => $attributes['date_end'],
            set: fn ($value) => ['date_end' => $value]
        );
    }

    public function masthead(): Attribute
    {
        return Attribute::make(
            get: fn ($userGroup, $attributes) => $attributes['masthead'],
            set: fn ($value) => ['masthead' => $value]
        );
    }

    public function scopeWithUserId(Builder $query, int $userId): Builder
    {
        return $query->where('user_user_groups.user_id', $userId);
    }

    public function scopeWithUserGroupId(Builder $query, int $userGroupId): Builder
    {
        return $query->where('user_user_groups.user_group_id', $userGroupId);
    }

    public function scopeWithActive(Builder $query): Builder
    {
        $currentDateTime = Core::getCurrentDate();
        return $query->where(
            fn (Builder $query) =>
            $query->where('user_user_groups.date_start', '<=', $currentDateTime)
                ->orWhereNull('user_user_groups.date_start')
        )
            ->where(
                fn (Builder $query) =>
                $query->where('user_user_groups.date_end', '>', $currentDateTime)
                    ->orWhereNull('user_user_groups.date_end')
            );
    }

    public function scopeWithEnded(Builder $query): Builder
    {
        $currentDateTime = Core::getCurrentDate();
        return $query->whereNotNull('user_user_groups.date_end')
            ->where('user_user_groups.date_end', '<=', $currentDateTime);
    }

    public function scopeWithContextId(Builder $query, int $contextId): Builder
    {
        return $query
            ->join('user_groups as ug', 'user_user_groups.user_group_id', '=', 'ug.user_group_id')
            ->where('ug.context_id', $contextId);
    }

    public function scopeWithMasthead(Builder $query): Builder
    {
        return $query->where('user_user_groups.masthead', 1);
    }

    public function scopeSortBy(Builder $query, string $column, ?string $direction = 'asc')
    {
        return $query->orderBy('user_user_groups.' . $column, $direction);
    }
}
