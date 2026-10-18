<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;
use App\Models\SiteReview;

class SiteReviewController extends Controller
{
    public function store(): void
    {
        $this->requireCsrf();
        if (!Auth::check()) {
            Session::flash('error', 'Silakan masuk terlebih dahulu untuk memberi ulasan.');
            $this->redirect('/');
        }

        $user = Auth::user();
        $email = strtolower($user['email'] ?? '');
        if (!str_ends_with($email, '@gmail.com') && !str_ends_with($email, '@googlemail.com')) {
            Session::flash('error', 'Hanya pengguna dengan akun Gmail yang dapat memberi ulasan.');
            $this->redirect('/');
        }

        $rating = max(1, min(5, (int) $this->input('rating', 5)));
        $comment = trim((string) $this->input('comment', ''));

        SiteReview::create([
            'user_id' => (int) $user['id'],
            'rating' => $rating,
            'comment' => $comment,
            'is_published' => 1,
        ]);

        Session::flash('success', 'Terima kasih atas ulasan Anda!');
        $this->redirect('/');
    }
}
