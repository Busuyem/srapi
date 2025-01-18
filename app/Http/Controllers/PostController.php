<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Services\PostService;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Throwable;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index()
    {
        try{
            return response()->json($this->postService->getAllPosts());

        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try{
            return response()->json($this->postService->getPostById($id));
        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store(PostRequest $request)
    {
        try{
            $data = $request->validated();
            $data['user_id'] = auth()->id();

            $createdPost = $this->postService->createPost($data);

            $this->createActivity($createdPost, 'created post');

            return response()->json($createdPost, 201);

        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }

    }

    public function update(PostRequest $request, $id)
    {
        try{
            $post = $this->postService->getPostById($id);

            if ($post->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $updatedPost = $this->postService->updatePost($post, $request->validated());

            $this->createActivity($updatedPost, 'Updated a post');

            return response()->json($updatedPost);
        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }

    }

    public function destroy($id)
    {
        try{
            $post = $this->postService->getPostById($id);

            if ($post->user_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $this->postService->deletePost($post);

            $this->createActivity($post, 'Deleted a post');

            return response()->json(['message' => 'Post successfully deleted']);

        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }

    public function createActivity($model, $action)
    {
        try{
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model' => 'Post',
                'model_id' => $model->id,
            ]);
            
        }catch(Throwable $e){
            return response()->json([
                'message' => $e->getMessage()
            ]);
        }
    }
}

