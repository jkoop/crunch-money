<?php

namespace App\Enums;

enum UserType: string {
	case Admin = "admin";
	case Basic = "basic";
	case Demo = "demo";
}
