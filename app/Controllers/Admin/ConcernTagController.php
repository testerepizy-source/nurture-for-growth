<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\ConcernTag;
use App\Models\ConcernKeyword;

class ConcernTagController extends Controller
{
    public function index(): void
    {
        $tags = ConcernTag::all('name ASC');
        $keywords = ConcernKeyword::all_();
        $this->view('admin.concern_tags.index', compact('tags', 'keywords'));
    }

    public function store(): void
    {
        $this->requireCsrf();
        $name = $this->input('name');
        ConcernTag::create([
            'name' => $name,
            'slug' => $this->slugify($name),
            'description' => $this->input('description'),
            'is_active' => 1,
        ]);
        audit('create', 'concern_tag', 'Menambahkan tag keluhan: ' . $name);
        Session::flash('success', 'Tag keluhan berhasil ditambahkan.');
        $this->redirect('/admin/concern-tags');
    }

    public function delete(array $params): void
    {
        ConcernTag::delete((int) $params['id']);
        audit('delete', 'concern_tag', 'Menghapus tag keluhan ID ' . $params['id']);
        $this->redirect('/admin/concern-tags');
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $tagId = (int) $params['id'];
        $tag = ConcernTag::find($tagId);
        $name = $this->input('name');
        if (!$tag || !$name) {
            Session::flash('error', 'Tag keluhan dan nama tag wajib tersedia.');
            $this->redirect('/admin/concern-tags');
            return;
        }

        ConcernTag::update($tagId, [
            'name' => $name,
            'description' => $this->input('description'),
        ]);
        audit('update', 'concern_tag', 'Memperbarui tag keluhan ID ' . $tagId);
        Session::flash('success', 'Tag keluhan berhasil diperbarui.');
        $this->redirect('/admin/concern-tags');
    }

    public function storeKeyword(): void
    {
        $this->requireCsrf();
        ConcernKeyword::create([
            'tag_id' => (int) $this->input('tag_id'),
            'keyword' => $this->input('keyword'),
        ]);
        Session::flash('success', 'Kata kunci berhasil ditambahkan.');
        $this->redirect('/admin/concern-tags');
    }

    public function deleteKeyword(array $params): void
    {
        ConcernKeyword::delete((int) $params['id']);
        $this->redirect('/admin/concern-tags');
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-') . '-' . substr(md5(uniqid()), 0, 5);
    }
}
