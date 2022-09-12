<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportTagResource;
use Spatie\Tags\Tag;

class GetReportTagsController extends Controller
{
    public function __invoke()
    {
        return ReportTagResource::collection(Tag::ordered()->withType('reports')->get());
    }
}
