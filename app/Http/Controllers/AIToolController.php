<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AIToolController extends Controller
{
    public function index()
    {
        return Inertia::render('Tools/Index');
    }

    public function textGenerator()
    {
        return Inertia::render('Tools/TextGenerator');
    }

    public function imageGenerator()
    {
        return Inertia::render('Tools/ImageGenerator');
    }

    public function codeAssistant()
    {
        return Inertia::render('Tools/CodeAssistant');
    }

    public function chatBot()
    {
        return Inertia::render('Tools/ChatBot');
    }

    public function analytics()
    {
        return Inertia::render('Tools/Analytics');
    }
}
