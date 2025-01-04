<?php

namespace App\Exceptions;

use LogicException;

/**
 * Assuming no race conditions, it is impossible to reach this state. This
 * exception reveals a race condition or logic problem.
 */
class ImpossibleStateException extends LogicException {
	public function __construct(string $message = null) {
		$this->message = $message ?? "Impossible state reached";
	}
}
