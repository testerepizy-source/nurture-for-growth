<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index(): void
    {
        $faqs = Faq::all('order_no ASC');
        $this->view('admin.faq.index', ['faqs' => $faqs]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        Faq::create([
            'question' => $this->input('question'),
            'answer' => $this->input('answer'),
            'category' => $this->input('category'),
            'order_no' => (int) $this->input('order_no', 0),
            'is_published' => (int) $this->input('is_published', 1),
        ]);
        Session::flash('success', 'FAQ berhasil ditambahkan.');
        $this->redirect('/admin/faq');
    }

    public function edit(array $params): void
    {
        $faq = Faq::find((int) $params['id']);
        if (!$faq) {
            $this->redirect('/admin/faq');
        }
        $faqs = Faq::all('order_no ASC');
        $this->view('admin.faq.index', ['faqs' => $faqs, 'faq' => $faq]);
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $id = (int) $params['id'];
        $faq = Faq::find($id);
        if (!$faq) {
            $this->redirect('/admin/faq');
        }
        Faq::update($id, [
            'question' => $this->input('question'),
            'answer' => $this->input('answer'),
            'category' => $this->input('category'),
            'order_no' => (int) $this->input('order_no', 0),
            'is_published' => (int) $this->input('is_published', 1),
        ]);
        Session::flash('success', 'FAQ berhasil diperbarui.');
        $this->redirect('/admin/faq');
    }

    public function delete(array $params): void
    {
        Faq::delete((int) $params['id']);
        $this->redirect('/admin/faq');
    }
}
