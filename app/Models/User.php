<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
	use HasFactory;

	protected $guarded = [];
	protected $hidden = ["token"];

	protected $casts = [
		"is_admin" => "boolean",
		"two_digit_year" => "boolean",
		"show_dow_on_tables" => "boolean",
		"show_dow_on_period_picker" => "boolean",
		"always_show_year" => "boolean",
	];

	/**
	 * The column name that identifies the user in the database for the purpose of authentication and only for that purpose.
	 * @return string
	 */
	public function getAuthIdentifierName(): string {
		return "token";
	}

	public function budgets(): HasMany {
		return $this->hasMany(Budget::class, "owner_id")->withoutGlobalScopes();
	}

	public function funds(): HasMany {
		return $this->hasMany(Fund::class, "owner_id")->withoutGlobalScopes();
	}

	public function transactions(): HasMany {
		return $this->hasMany(Transaction::class, "owner_id")->withoutGlobalScopes();
	}

	public function periods(): HasMany {
		return $this->hasMany(Period::class, "owner_id")->withoutGlobalScopes();
	}

	/**
	 * For display, not logic
	 * @return string
	 */
	public function getTypeAttribute(): string {
		if ($this->is_admin) {
			return "admin";
		} else {
			return "basic";
		}
	}

	/**
	 * Generates a new token for the user.
	 * @return void
	 */
	public function regenerateToken(): void {
		$this->token = substr(base64_encode($this->id . ":" . random_bytes(24)), 0, 32);
	}
}
