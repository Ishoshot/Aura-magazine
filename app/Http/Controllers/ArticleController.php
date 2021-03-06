<?php

namespace App\Http\Controllers;

use App\Article;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use JD\Cloudder\Facades\Cloudder;

class ArticleController extends Controller
{

    /**
     * Display a listing of the resource for the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(string $category)
    {
        $articles = Article::where("category", "=", $category)->with('user')->latest()->paginate(10);

        return response()->json($articles, 200);
    }

    /**
     * Display a listing of the resource for the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function homeIndex()
    {
        $homeArticles = Article::latest()->get();
        return response()->json($homeArticles);
    }

    /**
     * Display a listing of the resource of a particlar author for the user.
     *
     * @return \Illuminate\Http\Response
     */
    public function authorArticles(User $user)
    {
        $articles = $user->articles()->latest()->paginate(10);
        return response()->json($articles, 200);
    }

    /**
     * Display a listing of the resource for the Admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminIndex()
    {
        if (auth()->user()->isSuperAdmin()) {

            $articles = Article::withTrashed()->latest()->paginate(8);
            return response()->json(['articles' => $articles,
                'user' => auth()->user()], 200);

        } elseif (auth()->user()->isEditor()) {

            $editorArticles = auth()->user()->articles()->withTrashed()->latest()->paginate(8);

            return response()->json(['articles' => $editorArticles,
                'user' => auth()->user()], 200);
        } else {

            return response()->json(['articles' => [],
                'user' => auth()->user()], 200);
        }

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request, User $user)
    {
        $request->validate([
            'title' => ['required', 'string', 'unique:articles'],
            'description' => ['required', 'string', 'min:8'],
            'content' => ['required', 'string', 'min:100'],
            'category' => ['required', 'string'],
            'image' => ['required'],
            'image_orientation' => ['required', 'string'],
        ]);
        $imageName = Str::limit($request->title, 10);
        Cloudder::upload($request['image'], $imageName, [
            'folder' => 'aura/',
        ]);

        $image_url = Cloudder::show(Cloudder::getPublicId(),
            ['format' => 'png']);

        $data = [
            'title' => Str::title($request->title),
            'description' => $request->description,
            'content' => $request->content,
            'slug' => Str::slug($request->title, '-'),
            'category' => $request->category,
            'image' => $image_url,
            'user_name' => $user->name,
            'image_orientation' => $request->image_orientation,
        ];

        $article = $user->articles()->create($data);

        if ($article) {
            return response()->json('Article was created successfully!', 201);
        } else {
            return response()->json(
                'There was a problem while creating article',
                500
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        $recommendations = Article::where("category", "=", $article->category)->inRandomOrder()->limit(5)->get();

        return response()->json([
            "article" => $article,
            "recommendations" => $recommendations,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'description' => ['required', 'string', 'min:8'],
            'content' => ['required', 'string', 'min:100'],
            'category' => ['required', 'string'],
            'image_orientation' => ['required', 'string'],
        ]);

        $use_existing_image = Str::containsAll($request->image, ['res.', 'cloudinary.']);

        if (!$use_existing_image) {

            $imageName = Str::limit($request->title, 10);
            Cloudder::upload($request['image'], $imageName, [
                'folder' => 'aura/',
            ]);

            $image_url = Cloudder::show(Cloudder::getPublicId(),
                ['format' => 'png']);
        } else {
            $image_url = $request->image;
        }

        $data = [
            'title' => Str::title($request->title),
            'description' => $request->description,
            'content' => $request->content,
            'slug' => Str::slug($request->title, '-'),
            'category' => $request->category,
            'image' => $image_url,
            'user_name' => auth()->user()->name,
            'image_orientation' => $request->image_orientation,
        ];

        $updatedArticle = $article->update($data);

        if ($updatedArticle) {
            return response()->json('Article was updated successfully!', 200);
        } else {
            return response()->json(
                'There was a problem while updating article',
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        if ($article->delete()) {
            return response()->json("Article has been removed successfully.", 200);
        }

    }

    /**
     * Restores the specified resource from storage.
     *
     * @param  \App\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function restore(Request $request)
    {
        $article = Article::onlyTrashed()->findOrFail($request->id);
        if ($article->restore()) {
            return response()->json("Article has been restored successfully.", 200);
        }
    }

    public function updateViews(Request $request)
    {
        $article = Article::findOrFail($request->id);
        $newCount = $article->views + 1;
        $article->update([
            'views' => $newCount,
        ]);
    }

}