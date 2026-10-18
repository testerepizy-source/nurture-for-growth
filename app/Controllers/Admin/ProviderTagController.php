<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\ConcernTag;
use App\Models\User;

class ProviderTagController extends Controller
{
    public function edit(array $params): void
    {
        $userId = (int) $params['id'];
        $provider = User::find($userId);
        $allTags = ConcernTag::active();
        $assignedIds = array_column(ConcernTag::tagsForProvider($userId), 'id');
        $this->view('admin.provider_tags.edit', compact('provider', 'allTags', 'assignedIds'));
    }

    public function update(array $params): void
    {
        $this->requireCsrf();
        $userId = (int) $params['id'];
        ConcernTag::syncForProvider($userId, $_POST['tags'] ?? []);
        audit('update', 'provider_concern_tags', 'Memperbarui tag spesialisasi psikolog ID ' . $userId);
        Session::flash('success', 'Tag spesialisasi berhasil diperbarui.');
        $this->redirect('/admin/psychologists');
    }
}
