<?php
namespace App\Controllers\Admin;

use Core\Controller;
use Core\Session;
use App\Models\AssessmentCategory;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\AssessmentScoreRange;
use Core\Auth;

class AssessmentController extends Controller
{
    public function index(): void
    {
        $categories = AssessmentCategory::all('name ASC');
        $assessments = Assessment::raw("SELECT a.*, c.name AS category_name FROM assessments a JOIN assessment_categories c ON c.id = a.category_id ORDER BY a.id DESC");
        $this->view('admin.assessment.categories', ['categories' => $categories, 'assessments' => $assessments]);
    }

    public function storeCategory(): void
    {
        $this->requireCsrf();
        $name = $this->input('name');
        AssessmentCategory::create([
            'name' => $name,
            'slug' => $this->slugify($name),
            'description' => $this->input('description'),
        ]);
        Session::flash('success', 'Kategori assessment berhasil ditambahkan.');
        $this->redirect('/admin/assessments');
    }

    public function storeAssessment(): void
    {
        $this->requireCsrf();
        Assessment::create([
            'category_id' => (int) $this->input('category_id'),
            'title' => $this->input('title'),
            'description' => $this->input('description'),
            'time_limit_minutes' => (int) $this->input('time_limit_minutes', 10),
            'is_active' => 1,
            'created_by' => Auth::id(),
        ]);
        Session::flash('success', 'Assessment berhasil dibuat. Silakan tambahkan pertanyaan.');
        $this->redirect('/admin/assessments');
    }

    public function updateAssessment(array $params): void
    {
        $this->requireCsrf();
        $assessmentId = (int) $params['id'];
        $assessment = Assessment::find($assessmentId);
        if (!$assessment) {
            Session::flash('error', 'Assessment tidak ditemukan.');
            $this->redirect('/admin/assessments');
            return;
        }

        $title = $this->input('title');
        $categoryId = (int) $this->input('category_id');
        if (!$title || !$categoryId) {
            Session::flash('error', 'Judul dan kategori assessment wajib diisi.');
            $this->redirect('/admin/assessments');
            return;
        }

        Assessment::update($assessmentId, [
            'category_id' => $categoryId,
            'title' => $title,
            'time_limit_minutes' => max(1, (int) $this->input('time_limit_minutes', 10)),
            'description' => $this->input('description'),
        ]);
        Session::flash('success', 'Assessment berhasil diperbarui.');
        $this->redirect('/admin/assessments');
    }

    public function deleteAssessment(array $params): void
    {
        $this->requireCsrf();
        $assessmentId = (int) $params['id'];
        if (!Assessment::find($assessmentId)) {
            Session::flash('error', 'Assessment tidak ditemukan.');
            $this->redirect('/admin/assessments');
            return;
        }

        Assessment::delete($assessmentId);
        Session::flash('success', 'Assessment dan data turunannya berhasil dihapus.');
        $this->redirect('/admin/assessments');
    }

    public function questions(array $params): void
    {
        $assessment = Assessment::find((int) $params['id']);
        $questions = Question::forAssessment($assessment['id']);
        foreach ($questions as &$q) { $q['options'] = QuestionOption::forQuestion($q['id']); }
        $ranges = AssessmentScoreRange::forAssessment($assessment['id']);
        $this->view('admin.assessment.questions', ['assessment' => $assessment, 'questions' => $questions, 'ranges' => $ranges]);
    }

    public function storeQuestion(array $params): void
    {
        $this->requireCsrf();
        $assessmentId = (int) $params['id'];
        $questionId = Question::create([
            'assessment_id' => $assessmentId,
            'question_text' => $this->input('question_text'),
            'order_no' => (int) $this->input('order_no', 0),
        ]);
        $optTexts = $_POST['option_text'] ?? [];
        $optScores = $_POST['option_score'] ?? [];
        foreach ($optTexts as $i => $text) {
            if (trim($text) === '') continue;
            QuestionOption::create([
                'question_id' => $questionId,
                'option_text' => $text,
                'score' => (int) ($optScores[$i] ?? 0),
                'order_no' => $i,
            ]);
        }
        Session::flash('success', 'Pertanyaan berhasil ditambahkan.');
        $this->redirect('/admin/assessments/' . $assessmentId . '/questions');
    }

    public function deleteQuestion(array $params): void
    {
        Question::delete((int) $params['qid']);
        $this->redirect('/admin/assessments/' . $params['id'] . '/questions');
    }

    public function storeRange(array $params): void
    {
        $this->requireCsrf();
        $assessmentId = (int) $params['id'];
        AssessmentScoreRange::create([
            'assessment_id' => $assessmentId,
            'min_score' => (int) $this->input('min_score'),
            'max_score' => (int) $this->input('max_score'),
            'category_label' => $this->input('category_label'),
            'interpretation' => $this->input('interpretation'),
            'suggestion' => $this->input('suggestion'),
            'is_high_risk' => (int) $this->input('is_high_risk', 0),
        ]);
        Session::flash('success', 'Rentang skor berhasil ditambahkan.');
        $this->redirect('/admin/assessments/' . $assessmentId . '/questions');
    }

    public function toggleActive(array $params): void
    {
        $assessment = Assessment::find((int) $params['id']);
        Assessment::update($assessment['id'], ['is_active' => $assessment['is_active'] ? 0 : 1]);
        $this->redirect('/admin/assessments');
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-') . '-' . substr(md5(uniqid()), 0, 5);
    }
}
