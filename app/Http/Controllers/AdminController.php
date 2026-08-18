<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $contacts = Contact::with(['category','tags'])
            ->latest()
            ->paginate(7);

            $categories = Category::all();

            $tags = Tag::all();

            return view ('admin.index',compact(
                'contacts',
                'categories',
                'tags'
            ));
    }
}
