<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index(): void
    {
        $faqs = Faq::published();
        $grouped = [];
        foreach ($faqs as $f) { $grouped[$f['category'] ?: 'Umum'][] = $f; }
        $this->view('faq.index', ['grouped' => $grouped], 'public');
    }
}
