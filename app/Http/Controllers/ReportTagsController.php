<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUpdateReportTagRequest;
use App\Http\Resources\ReportTagResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Spatie\Tags\Tag;

class ReportTagsController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Tag::class, 'tag');
    }

    public function index(): ResourceCollection
    {
        return ReportTagResource::collection(Tag::ordered()->get());
    }

    public function store(CreateUpdateReportTagRequest $request): Response
    {
        $data = $request->validated();

        Tag::findOrCreate($data['name'], 'reports');

        return response('', 204);
    }

    public function update(CreateUpdateReportTagRequest $request, Tag $tag)
    {
        $tag->name = $request->get('name');
        $tag->save();

        return response('', 204);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response('', 204);
    }
}
