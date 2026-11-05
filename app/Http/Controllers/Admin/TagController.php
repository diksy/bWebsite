<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SimpanTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;

class TagController extends Controller
{
    public function store(SimpanTagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return back()->with('status', 'Tag dibuat.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('status', 'Tag dihapus.');
    }
}
