<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

final class SessionController extends Controller {
	public function deleteDownload(string $id): Response {
		$downloads = Session::get("downloads", []);
		$downloads = array_filter($downloads, fn($download) => $download["id"] != $id);
		Session::put("downloads", $downloads);
		return new Response(null, \Symfony\Component\HttpFoundation\Response::HTTP_NO_CONTENT);
	}
}
