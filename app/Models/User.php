<?php

namespace App\Models;

use App\Enums\UserType;
use App\Exceptions\ImpossibleStateException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
	protected $guarded = [];
	protected $hidden = ["token"];

	protected $casts = [
		"is_admin" => "boolean",
		"is_demo" => "boolean",
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

	public function getTypeAttribute(): UserType {
		if ($this->is_admin) {
			return UserType::Admin;
		} elseif ($this->is_demo) {
			return UserType::Demo;
		}

		return UserType::Basic;
	}

	public function setTypeAttribute(UserType $type): void {
		switch ($type) {
			case UserType::Admin:
				$this->is_admin = true;
				$this->is_demo = false;
				break;
			case UserType::Basic:
				$this->is_admin = false;
				$this->is_demo = false;
				break;
			case UserType::Demo:
				$this->is_admin = false;
				$this->is_demo = true;
				break;
			default:
				throw new ImpossibleStateException();
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
