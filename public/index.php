<?php

require __DIR__ . '/../core/autoload.php';
require __DIR__ . '/../core/helpers.php';

use Core\Router;
use Core\Session;
use Core\Middleware;
use Core\Localization;
use App\Controllers\AuthController;
use App\Controllers\LocalizationController;
use App\Controllers\DashboardController;
use App\Controllers\AssessmentController;
use App\Controllers\MoodController;
use App\Controllers\JournalController;
use App\Controllers\ArticleController;
use App\Controllers\ProfileController;
use App\Controllers\LandingController;
use App\Controllers\FaqController;
use App\Controllers\ProviderProfileController;
use App\Controllers\ProviderDirectoryController;
use App\Controllers\JobController;
use App\Controllers\ContactController;
use App\Controllers\ScheduleController;
use App\Controllers\ConsultationController;
use App\Controllers\ReviewController;
use App\Controllers\SiteReviewController;
use App\Controllers\MatchingController;
use App\Controllers\PatientController;
use App\Controllers\WellnessController;
use App\Controllers\AiAssistantController;
use App\Controllers\ReportController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\RoleController;
use App\Controllers\Admin\AssessmentController as AdminAssessmentController;
use App\Controllers\Admin\ArticleController as AdminArticleController;
use App\Controllers\Admin\AuditLogController;
use App\Controllers\Admin\ConsultationController as AdminConsultationController;
use App\Controllers\Admin\PatientController as AdminPatientController;
use App\Controllers\Admin\PsychologistController;

use App\Controllers\Admin\FaqController as AdminFaqController;
use App\Controllers\Admin\CrisisController;
use App\Controllers\Admin\ContactMessageController;
use App\Controllers\Admin\TemplateController;
use App\Controllers\Admin\ConcernTagController;
use App\Controllers\Admin\ProviderTagController;
use App\Controllers\Super\SettingController;
use App\Controllers\Super\BackupController;
use App\Controllers\Super\MonitoringController;
use App\Controllers\InvoiceController;
use App\Controllers\CancellationController;
use App\Controllers\PrescriptionController;
use App\Controllers\CalendarController;
use App\Controllers\PaymentController;
use App\Controllers\ProviderApplicationController;
use App\Controllers\PromoController;
use App\Controllers\Admin\ProviderApplicationController as AdminProviderApplicationController;
use App\Controllers\Admin\HeroSlideController;
use App\Controllers\Admin\ServiceVideoController;
use App\Controllers\Admin\JobVacancyController;
use App\Controllers\SeoController;

$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set($config['timezone']);
if ($config['app_debug']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

Session::start();

Localization::initialize();

$router = new Router();

/* ---------------------------------------------------------------- *
 * GUEST / PUBLIC ROUTES
 * ---------------------------------------------------------------- */
$router->get('/', function () { (new LandingController())->index(); });
$router->get('/faq', function () { (new FaqController())->index(); });
$router->get('/contact', function () { (new ContactController())->show(); });
$router->post('/contact', function () { (new ContactController())->submit(); });
$router->get('/education', function () { (new ArticleController())->index(); });
$router->get('/education/{slug}', function ($p) { (new ArticleController())->show($p); });
$router->get('/providers/{id}', function ($p) { (new ProviderProfileController())->show($p); });
$router->get('/klinisi', function () { (new ProviderDirectoryController())->index(); });
$router->get('/karier', function () { (new JobController())->index(); });
$router->get('/karier/{slug}', function ($p) { (new JobController())->show($p); });
$router->get('/sitemap.xml', function () { (new SeoController())->sitemap(); });
$router->get('/robots.txt', function () { (new SeoController())->robots(); });

$router->get('/login', function () { Middleware::guest(); (new AuthController())->showLogin(); });
$router->post('/login', function () { Middleware::guest(); (new AuthController())->login(); });
$router->get('/register', function () { Middleware::guest(); (new AuthController())->showRegister(); });
$router->post('/register', function () { Middleware::guest(); (new AuthController())->register(); });
$router->get('/verify-email', function () { Middleware::guest(); (new AuthController())->showVerifyEmail(); });
$router->post('/verify-email', function () { Middleware::guest(); (new AuthController())->verifyEmail(); });
$router->post('/verify-email/resend', function () { Middleware::guest(); (new AuthController())->resendVerificationOtp(); });
$router->get('/forgot-password', function () { Middleware::guest(); (new AuthController())->showForgot(); });
$router->post('/forgot-password', function () { Middleware::guest(); (new AuthController())->forgot(); });
$router->get('/reset-password', function () { Middleware::guest(); (new AuthController())->showResetPassword(); });
$router->post('/reset-password', function () { Middleware::guest(); (new AuthController())->resetPassword(); });
$router->get('/captcha', function () { (new AuthController())->captcha(); });
$router->get('/logout', function () { Middleware::auth(); (new AuthController())->logout(); });

/* ---------------------------------------------------------------- *
 * LOCALIZATION ROUTES
 * ---------------------------------------------------------------- */
$router->post('/language/set', function () { (new LocalizationController())->setLanguage(); });
$router->get('/language/{language}', function ($p) { (new LocalizationController())->setLanguage($p); });
$router->get('/api/language', function () { (new LocalizationController())->getLanguage(); });

/* ---------------------------------------------------------------- *
 * AUTHENTICATED ROUTES
 * ---------------------------------------------------------------- */
$router->get('/dashboard', function () { Middleware::auth(); Middleware::patientProfileData(); (new DashboardController())->index(); });

// Assessment (Pasien)
$router->get('/assessment', function () { Middleware::permission('assessment.take'); Middleware::patientProfileData(); (new AssessmentController())->index(); });
$router->get('/assessment/history', function () { Middleware::permission('assessment.take'); Middleware::patientProfileData(); (new AssessmentController())->history(); });
$router->get('/assessment/{id}/take', function ($p) { Middleware::permission('assessment.take'); Middleware::patientProfileData(); (new AssessmentController())->take($p); });
$router->post('/assessment/{id}/submit', function ($p) { Middleware::permission('assessment.take'); Middleware::patientProfileData(); (new AssessmentController())->submit($p); });
$router->get('/assessment/result/{id}', function ($p) { Middleware::auth(); Middleware::patientProfileData(); (new AssessmentController())->result($p); });

// Mood (Pasien)
$router->get('/mood', function () { Middleware::permission('mood.manage'); Middleware::patientProfileData(); (new MoodController())->index(); });
$router->post('/mood', function () { Middleware::permission('mood.manage'); Middleware::patientProfileData(); (new MoodController())->store(); });

// Combined Mood & Journal Dashboard (Wellness)
$router->get('/wellness', function () { Middleware::permission('mood.manage'); Middleware::permission('journal.manage'); Middleware::patientProfileData(); (new WellnessController())->index(); });

// Journal (Pasien)
$router->get('/journal', function () { Middleware::permission('journal.manage'); Middleware::patientProfileData(); (new JournalController())->index(); });
$router->get('/journal/create', function () { Middleware::permission('journal.manage'); Middleware::patientProfileData(); (new JournalController())->create(); });
$router->post('/journal', function () { Middleware::permission('journal.manage'); Middleware::patientProfileData(); (new JournalController())->store(); });
$router->get('/journal/{id}/edit', function ($p) { Middleware::permission('journal.manage'); Middleware::patientProfileData(); (new JournalController())->edit($p); });
$router->post('/journal/{id}/update', function ($p) { Middleware::permission('journal.manage'); Middleware::patientProfileData(); (new JournalController())->update($p); });
$router->post('/journal/{id}/delete', function ($p) { Middleware::permission('journal.manage'); Middleware::patientProfileData(); (new JournalController())->delete($p); });
$router->get('/journal/{id}/favorite', function ($p) { Middleware::permission('journal.manage'); Middleware::patientProfileData(); (new JournalController())->toggleFavorite($p); });
$router->get('/journal/provider/{id}', function ($p) { (new JournalController())->viewAsProvider($p); });

// Profile
$router->get('/profile', function () { Middleware::auth(); (new ProfileController())->index(); });
$router->post('/profile', function () { Middleware::auth(); (new ProfileController())->update(); });
$router->post('/profile/family/switch', function () { Middleware::auth(); (new ProfileController())->switchFamilyProfile(); });
$router->post('/profile/family/add', function () { Middleware::auth(); (new ProfileController())->addFamilyProfile(); });
$router->get('/profile/download-data', function () { Middleware::auth(); (new ProfileController())->downloadData(); });
$router->post('/profile/delete-account', function () { Middleware::auth(); (new ProfileController())->deleteAccount(); });

// Jadwal (Psikolog/Psikiater)
$router->get('/schedule', function () { Middleware::permission('schedule.manage'); (new ScheduleController())->index(); });
  $router->post('/schedule', function () { Middleware::permission('schedule.manage'); (new ScheduleController())->store(); });
  $router->get('/schedule/api-slots', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new ScheduleController())->apiSlots(); });
$router->get('/schedule/{id}/delete', function ($p) { Middleware::permission('schedule.manage'); (new ScheduleController())->delete($p); });
$router->get('/schedule/{id}/toggle', function ($p) { Middleware::permission('schedule.manage'); (new ScheduleController())->toggle($p); });
$router->get('/calendar', function () { Middleware::auth(); Middleware::patientProfileData(); (new CalendarController())->index(); });
$router->post('/calendar/event', function () { Middleware::auth(); (new CalendarController())->storeEvent(); });

// Smart Matching Engine (Pasien)
$router->get('/matching', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new MatchingController())->intake(); });
$router->get('/matching/suggest-tags', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new MatchingController())->suggestTags(); });
$router->post('/matching', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new MatchingController())->process(); });
$router->get('/matching/results', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new MatchingController())->results(); });

// Konseling / Konsultasi
$router->get('/consultation', function () { Middleware::auth(); Middleware::patientProfileData(); (new ConsultationController())->index(); });
$router->get('/consultation/create', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new ConsultationController())->create(); });
$router->get('/consultation/consent', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new ConsultationController())->consent(); });
$router->post('/consultation/consent', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new ConsultationController())->consentStore(); });
$router->post('/consultation', function () { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new ConsultationController())->store(); });
$router->get('/consultation/{id}', function ($p) { Middleware::auth(); Middleware::patientProfileData(); (new ConsultationController())->show($p); });
$router->get('/consultation/{id}/reschedule', function ($p) { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new ConsultationController())->rescheduleRequest($p); });
$router->post('/consultation/{id}/reschedule/request', function ($p) { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new ConsultationController())->rescheduleStore($p); });
$router->get('/consultation/{id}/extend', function ($p) { Middleware::permission('consultation.approve'); Middleware::patientProfileData(); (new ConsultationController())->extendRequest($p); });
$router->post('/consultation/{id}/extend/request', function ($p) { Middleware::permission('consultation.approve'); Middleware::patientProfileData(); (new ConsultationController())->extendStore($p); });
$router->post('/consultation/{id}/approve', function ($p) { Middleware::permission('consultation.approve'); (new ConsultationController())->approve($p); });
$router->post('/consultation/{id}/complete', function ($p) { Middleware::permission('consultation.approve'); (new ConsultationController())->complete($p); });
$router->post('/consultation/{id}/update-link', function ($p) { Middleware::permission('consultation.approve'); (new ConsultationController())->updateLink($p); });
$router->post('/consultation/{id}/verify-payment', function ($p) { Middleware::auth(); (new ConsultationController())->verifyPayment($p); });
$router->post('/consultation/reschedule/{id}/approve', function ($p) { Middleware::permission('consultation.approve'); (new ConsultationController())->rescheduleApprove($p); });
$router->post('/consultation/reschedule/{id}/reject', function ($p) { Middleware::permission('consultation.approve'); (new ConsultationController())->rescheduleReject($p); });
$router->post('/consultation/extension/{id}/approve', function ($p) { Middleware::permission('consultation.approve'); (new ConsultationController())->extensionApprove($p); });
$router->post('/consultation/extension/{id}/reject', function ($p) { Middleware::permission('consultation.approve'); (new ConsultationController())->extensionReject($p); });
$router->post('/payment/reschedule/{id}/verify', function ($p) { Middleware::permission('consultation.approve'); (new PaymentController())->verifyReschedulePayment($p); });
$router->post('/payment/extension/{id}/verify', function ($p) { Middleware::permission('consultation.approve'); (new PaymentController())->verifyExtensionPayment($p); });

$router->get('/payment/consultation/{id}', function ($p) { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new PaymentController())->consultationPayment($p); });
$router->post('/consultation/{id}/payment/process', function ($p) { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new PaymentController())->processConsultationPayment($p); });
$router->get('/payment/reschedule/{id}', function ($p) { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new PaymentController())->reschedulePayment($p); });
$router->post('/reschedule/{id}/payment/process', function ($p) { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new PaymentController())->processReschedulePayment($p); });
$router->get('/payment/extension/{id}', function ($p) { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new PaymentController())->extensionPayment($p); });
$router->post('/extension/{id}/payment/process', function ($p) { Middleware::permission('consultation.book'); Middleware::patientProfileData(); (new PaymentController())->processExtensionPayment($p); });
$router->post('/payment/midtrans/notification', function () { (new PaymentController())->midtransNotification(); });
$router->get('/payment/midtrans/finish', function () { Middleware::auth(); (new PaymentController())->midtransFinish(); });

// ---- Invoice ----
$router->get('/invoice/{id}', function ($p) { Middleware::auth(); (new InvoiceController())->show($p); });

// ---- Pembatalan & Refund ----
$router->get('/consultation/{id}/cancel-request', function ($p) { Middleware::auth(); (new CancellationController())->create($p); });
$router->post('/consultation/{id}/cancel-request', function ($p) { Middleware::auth(); (new CancellationController())->store($p); });
$router->get('/admin/cancellations', function () { Middleware::permission('consultation.approve'); (new CancellationController())->adminIndex(); });
$router->post('/admin/cancellations/{id}/approve', function ($p) { Middleware::permission('consultation.approve'); (new CancellationController())->approve($p); });
$router->post('/admin/cancellations/{id}/reject', function ($p) { Middleware::permission('consultation.approve'); (new CancellationController())->reject($p); });
$router->post('/admin/cancellations/{id}/mark-processed', function ($p) { Middleware::permission('consultation.approve'); (new CancellationController())->markProcessed($p); });

// ---- Resep (Psikiater) ----
$router->get('/patients/{id}/prescription', function ($p) { Middleware::permission('medical_note.manage'); (new PrescriptionController())->create($p); });
$router->post('/patients/{id}/prescription', function ($p) { Middleware::permission('medical_note.manage'); (new PrescriptionController())->store($p); });
$router->get('/prescription/{id}', function ($p) { Middleware::auth(); (new PrescriptionController())->show($p); });

// ---- Onboarding Psikolog/Psikiater (publik) ----
$router->get('/join-us', function () { (new ProviderApplicationController())->show(); });
$router->post('/join-us', function () { (new ProviderApplicationController())->store(); });
$router->get('/admin/provider-applications', function () { Middleware::permission('user.manage'); (new AdminProviderApplicationController())->index(); });
$router->get('/admin/provider-applications/{id}', function ($p) { Middleware::permission('user.manage'); (new AdminProviderApplicationController())->show($p); });
$router->post('/admin/provider-applications/{id}/approve', function ($p) { Middleware::permission('user.manage'); (new AdminProviderApplicationController())->approve($p); });
$router->post('/admin/provider-applications/{id}/reject', function ($p) { Middleware::permission('user.manage'); (new AdminProviderApplicationController())->reject($p); });

// ---- Hero Slide (landing page, dikelola Admin) ----
$router->get('/admin/hero-slides', function () { Middleware::permission('article.manage'); (new HeroSlideController())->index(); });
$router->get('/admin/hero-slides/create', function () { Middleware::permission('article.manage'); (new HeroSlideController())->create(); });
$router->post('/admin/hero-slides', function () { Middleware::permission('article.manage'); (new HeroSlideController())->store(); });
$router->get('/admin/hero-slides/{id}/edit', function ($p) { Middleware::permission('article.manage'); (new HeroSlideController())->edit($p); });
$router->post('/admin/hero-slides/{id}/update', function ($p) { Middleware::permission('article.manage'); (new HeroSlideController())->update($p); });
$router->post('/admin/hero-slides/{id}/delete', function ($p) { Middleware::permission('article.manage'); (new HeroSlideController())->delete($p); });
$router->post('/admin/hero-slides/{id}/move-up', function ($p) { Middleware::permission('article.manage'); (new HeroSlideController())->moveUp($p); });
$router->post('/admin/hero-slides/{id}/move-down', function ($p) { Middleware::permission('article.manage'); (new HeroSlideController())->moveDown($p); });

// ---- Video Layanan (landing page, dikelola Admin) ----
$router->get('/admin/service-videos', function () { Middleware::permission('article.manage'); (new ServiceVideoController())->index(); });
$router->get('/admin/service-videos/create', function () { Middleware::permission('article.manage'); (new ServiceVideoController())->create(); });
$router->post('/admin/service-videos', function () { Middleware::permission('article.manage'); (new ServiceVideoController())->store(); });
$router->get('/admin/service-videos/{id}/edit', function ($p) { Middleware::permission('article.manage'); (new ServiceVideoController())->edit($p); });
$router->post('/admin/service-videos/{id}/update', function ($p) { Middleware::permission('article.manage'); (new ServiceVideoController())->update($p); });
$router->post('/admin/service-videos/{id}/delete', function ($p) { Middleware::permission('article.manage'); (new ServiceVideoController())->delete($p); });
$router->post('/admin/service-videos/{id}/toggle', function ($p) { Middleware::permission('article.manage'); (new ServiceVideoController())->toggle($p); });
$router->post('/admin/service-videos/{id}/move-up', function ($p) { Middleware::permission('article.manage'); (new ServiceVideoController())->moveUp($p); });
$router->post('/admin/service-videos/{id}/move-down', function ($p) { Middleware::permission('article.manage'); (new ServiceVideoController())->moveDown($p); });

// ---- Promo Layanan Konseling (Psikolog/Psikiater & Admin) ----
$router->get('/promo', function () { Middleware::permission('promo.manage'); (new PromoController())->index(); });
$router->post('/promo', function () { Middleware::permission('promo.manage'); (new PromoController())->store(); });
$router->get('/promo/create', function () { Middleware::permission('promo.manage'); header('Location: ' . base_url('/promo')); exit; });
$router->get('/promo/{id}/edit', function ($p) { Middleware::permission('promo.manage'); header('Location: ' . base_url('/promo?edit=' . (int) $p['id'])); exit; });
$router->post('/promo/{id}/update', function ($p) { Middleware::permission('promo.manage'); (new PromoController())->update($p['id']); });
$router->get('/promo/{id}/toggle', function ($p) { Middleware::permission('promo.manage'); (new PromoController())->toggle($p['id']); });
$router->get('/promo/{id}/delete', function ($p) { Middleware::permission('promo.manage'); (new PromoController())->delete($p['id']); });

$router->get('/admin/promos', function () { Middleware::permission('promo.manage'); Middleware::role(['admin', 'super_admin']); (new PromoController())->index(); });
$router->post('/admin/promos', function () { Middleware::permission('promo.manage'); Middleware::role(['admin', 'super_admin']); (new PromoController())->store(); });
$router->post('/admin/promos/{id}/update', function ($p) { Middleware::permission('promo.manage'); Middleware::role(['admin', 'super_admin']); (new PromoController())->update($p['id']); });
$router->get('/admin/promos/{id}/toggle', function ($p) { Middleware::permission('promo.manage'); Middleware::role(['admin', 'super_admin']); (new PromoController())->toggle($p['id']); });
$router->get('/admin/promos/{id}/delete', function ($p) { Middleware::permission('promo.manage'); Middleware::role(['admin', 'super_admin']); (new PromoController())->delete($p['id']); });

// ---- Analitik Bisnis ----
$router->get('/admin/analytics', function () { Middleware::permission('report.view'); header('Location: ' . base_url('/dashboard')); exit; });
$router->post('/consultation/{id}/cancel', function ($p) { Middleware::permission('consultation.book'); (new ConsultationController())->cancel($p); });
$router->post('/consultation/{id}/note', function ($p) { Middleware::auth(); (new ConsultationController())->addNote($p); });
$router->post('/consultation/{id}/followup', function ($p) { Middleware::auth(); (new ConsultationController())->addFollowUp($p); });
$router->post('/consultation/{id}/chat', function ($p) { Middleware::auth(); (new ConsultationController())->chatSend($p); });
$router->post('/consultation/{id}/review', function ($p) { Middleware::permission('consultation.book'); (new ReviewController())->store($p); });
// Site reviews (public landing feedback)
$router->post('/reviews', function () { Middleware::auth(); (new SiteReviewController())->store(); });
$router->get('/consultation/{id}/room', function ($p) { Middleware::auth(); (new ConsultationController())->room($p); });
$router->get('/consultation/room/{id}/join', function ($p) { Middleware::auth(); (new ConsultationController())->roomJoin($p); });
$router->post('/consultation/room/{id}/end', function ($p) { Middleware::auth(); (new ConsultationController())->roomEnd($p); });

// ---- Dokumen Konseling (Upload, Download, Delete) ----
$router->post('/consultation/{id}/upload-document', function ($p) { Middleware::permission('consultation.manage_documents'); (new ConsultationController())->uploadDocument($p); });
$router->get('/consultation/{id}/download-document/{doc_id}', function ($p) { Middleware::auth(); (new ConsultationController())->downloadDocument($p); });
$router->post('/consultation/{id}/delete-document/{doc_id}', function ($p) { Middleware::permission('consultation.manage_documents'); (new ConsultationController())->deleteDocument($p); });

// Daftar Pasien (Psikolog/Psikiater)
$router->get('/patients', function () { Middleware::permission('patient.view_assigned'); (new PatientController())->index(); });
$router->get('/patients/{id}', function ($p) { Middleware::permission('patient.view_assigned'); (new PatientController())->show($p); });
$router->post('/patients/{id}/medical-note', function ($p) { Middleware::permission('medical_note.manage'); (new PatientController())->addMedicalNote($p); });
$router->post('/patients/{id}/therapy-history', function ($p) { Middleware::permission('medical_note.manage'); (new PatientController())->addTherapyHistory($p); });
$router->post('/patients/{id}/control-plan', function ($p) { Middleware::permission('medical_note.manage'); (new PatientController())->addControlPlan($p); });

// AI Assistant
$router->get('/ai/summarize-journal/{id}', function ($p) { Middleware::auth(); (new AiAssistantController())->summarizeJournal($p); });
$router->get('/ai/summarize-assessment/{id}', function ($p) { Middleware::auth(); (new AiAssistantController())->summarizeAssessment($p); });
$router->get('/ai/recommend-articles', function () { Middleware::auth(); (new AiAssistantController())->recommendArticles(); });

// Report
$router->get('/report', function () { Middleware::permission('report.view'); (new ReportController())->index(); });
$router->get('/report/export/assessment', function () { Middleware::permission('report.view'); (new ReportController())->exportAssessmentCsv(); });
$router->get('/report/export/consultation', function () { Middleware::permission('report.view'); (new ReportController())->exportConsultationCsv(); });
    $router->get('/report/export/mood', function () { Middleware::permission('report.view'); (new ReportController())->exportMoodCsv(); });
    $router->get('/report/export/{type}/{format}', function (array $p) { Middleware::permission('report.view'); (new ReportController())->export($p['type'], $p['format']); });

// Admin: operasional (daftar konsultasi & rencana tindak lanjut)
$router->get('/admin/consultations', function () { Middleware::permission('report.view'); Middleware::role(['admin', 'super_admin']); (new AdminConsultationController())->index(); });
$router->get('/admin/followups', function () { Middleware::permission('report.view'); Middleware::role(['admin', 'super_admin']); (new AdminConsultationController())->followUps(); });

/* ---------------------------------------------------------------- *
 * ADMIN ROUTES
 * ---------------------------------------------------------------- */
$router->get('/admin/users', function () { Middleware::permission('user.manage'); (new UserController())->index(); });
$router->get('/admin/users/create', function () { Middleware::permission('user.manage'); (new UserController())->create(); });
$router->post('/admin/users', function () { Middleware::permission('user.manage'); (new UserController())->store(); });
$router->get('/admin/users/{id}/edit', function ($p) { Middleware::permission('user.manage'); (new UserController())->edit($p); });
$router->post('/admin/users/{id}/update', function ($p) { Middleware::permission('user.manage'); (new UserController())->update($p); });
$router->post('/admin/users/{id}/delete', function ($p) { Middleware::permission('user.manage'); (new UserController())->delete($p); });

$router->get('/admin/patients', function () { Middleware::permission('patient.manage'); (new AdminPatientController())->index(); });
$router->get('/admin/patients/create', function () { Middleware::permission('patient.manage'); (new AdminPatientController())->create(); });
$router->post('/admin/patients', function () { Middleware::permission('patient.manage'); (new AdminPatientController())->store(); });
$router->get('/admin/patients/{id}/edit', function ($p) { Middleware::permission('patient.manage'); (new AdminPatientController())->edit($p); });
$router->post('/admin/patients/{id}/update', function ($p) { Middleware::permission('patient.manage'); (new AdminPatientController())->update($p); });
$router->post('/admin/patients/{id}/delete', function ($p) { Middleware::permission('patient.manage'); (new AdminPatientController())->delete($p); });

$router->get('/admin/psychologists', function () { Middleware::permission('provider.manage'); (new PsychologistController())->index(); });
$router->get('/admin/psychologists/create', function () { Middleware::permission('provider.manage'); (new PsychologistController())->create(); });
$router->post('/admin/psychologists', function () { Middleware::permission('provider.manage'); (new PsychologistController())->store(); });
$router->get('/admin/psychologists/{id}/edit', function ($p) { Middleware::permission('provider.manage'); (new PsychologistController())->edit($p); });
$router->post('/admin/psychologists/{id}/update', function ($p) { Middleware::permission('provider.manage'); (new PsychologistController())->update($p); });
$router->post('/admin/psychologists/{id}/delete', function ($p) { Middleware::permission('provider.manage'); (new PsychologistController())->delete($p); });

$router->get('/admin/concern-tags', function () { Middleware::permission('provider.manage'); (new ConcernTagController())->index(); });
$router->post('/admin/concern-tags', function () { Middleware::permission('provider.manage'); (new ConcernTagController())->store(); });
$router->get('/admin/concern-tags/{id}/delete', function ($p) { Middleware::permission('provider.manage'); (new ConcernTagController())->delete($p); });
$router->post('/admin/concern-tags/keyword', function () { Middleware::permission('provider.manage'); (new ConcernTagController())->storeKeyword(); });
$router->get('/admin/concern-tags/keyword/{id}/delete', function ($p) { Middleware::permission('provider.manage'); (new ConcernTagController())->deleteKeyword($p); });

$router->get('/admin/provider-tags/{id}/edit', function ($p) { Middleware::permission('provider.manage'); (new ProviderTagController())->edit($p); });
$router->post('/admin/provider-tags/{id}/update', function ($p) { Middleware::permission('provider.manage'); (new ProviderTagController())->update($p); });

$router->get('/admin/roles', function () { Middleware::permission('role.manage'); (new RoleController())->index(); });
$router->get('/admin/roles/{id}/edit', function ($p) { Middleware::permission('role.manage'); (new RoleController())->edit($p); });
$router->post('/admin/roles/{id}/update', function ($p) { Middleware::permission('role.manage'); (new RoleController())->update($p); });

$router->get('/admin/assessments', function () { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->index(); });
$router->post('/admin/assessments/category', function () { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->storeCategory(); });
$router->post('/admin/assessments', function () { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->storeAssessment(); });
$router->post('/admin/assessments/{id}/update', function ($p) { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->updateAssessment($p); });
$router->post('/admin/assessments/{id}/delete', function ($p) { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->deleteAssessment($p); });
$router->post('/admin/concern-tags/{id}/update', function ($p) { Middleware::permission('provider.manage'); (new ConcernTagController())->update($p); });
$router->get('/admin/assessments/{id}/questions', function ($p) { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->questions($p); });
$router->post('/admin/assessments/{id}/questions', function ($p) { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->storeQuestion($p); });
$router->get('/admin/assessments/{id}/questions/{qid}/delete', function ($p) { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->deleteQuestion($p); });
$router->post('/admin/assessments/{id}/ranges', function ($p) { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->storeRange($p); });
$router->get('/admin/assessments/{id}/toggle', function ($p) { Middleware::permission('assessment.manage'); (new AdminAssessmentController())->toggleActive($p); });

$router->get('/admin/articles', function () { Middleware::permission('article.manage'); (new AdminArticleController())->index(); });
$router->get('/admin/articles/create', function () { Middleware::permission('article.manage'); (new AdminArticleController())->create(); });
$router->post('/admin/articles', function () { Middleware::permission('article.manage'); (new AdminArticleController())->store(); });
$router->get('/admin/articles/{id}/edit', function ($p) { Middleware::permission('article.manage'); (new AdminArticleController())->edit($p); });
$router->post('/admin/articles/{id}/update', function ($p) { Middleware::permission('article.manage'); (new AdminArticleController())->update($p); });
$router->post('/admin/articles/{id}/delete', function ($p) { Middleware::permission('article.manage'); (new AdminArticleController())->delete($p); });

// ---- Lowongan Pekerjaan (Admin) ----
$router->get('/admin/jobs', function () { Middleware::permission('job.manage'); (new JobVacancyController())->index(); });
$router->post('/admin/jobs', function () { Middleware::permission('job.manage'); (new JobVacancyController())->store(); });
$router->post('/admin/jobs/{id}/update', function ($p) { Middleware::permission('job.manage'); (new JobVacancyController())->update($p); });
$router->get('/admin/jobs/{id}/toggle', function ($p) { Middleware::permission('job.manage'); (new JobVacancyController())->toggle($p); });
$router->get('/admin/jobs/{id}/delete', function ($p) { Middleware::permission('job.manage'); (new JobVacancyController())->delete($p); });

$router->get('/admin/faq', function () { Middleware::permission('faq.manage'); (new AdminFaqController())->index(); });
$router->post('/admin/faq', function () { Middleware::permission('faq.manage'); (new AdminFaqController())->store(); });
$router->get('/admin/faq/{id}/edit', function ($p) { Middleware::permission('faq.manage'); (new AdminFaqController())->edit($p); });
$router->post('/admin/faq/{id}/update', function ($p) { Middleware::permission('faq.manage'); (new AdminFaqController())->update($p); });
$router->get('/admin/faq/{id}/delete', function ($p) { Middleware::permission('faq.manage'); (new AdminFaqController())->delete($p); });

// ---- Kanal Pembayaran Manual: Transfer Bank & QRIS ----
$router->get('/admin/payment-channels', function () { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->index(); });
$router->get('/admin/payment-channels/create', function () { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->create(); });
$router->post('/admin/payment-channels', function () { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->store(); });
$router->get('/admin/payment-channels/{id}/edit', function ($p) { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->edit($p); });
$router->post('/admin/payment-channels/{id}/update', function ($p) { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->update($p); });
$router->get('/admin/payment-channels/{id}/toggle', function ($p) { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->toggle($p); });
$router->post('/admin/payment-channels/{id}/delete', function ($p) { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->delete($p); });
$router->post('/admin/payment-channels/{id}/move-up', function ($p) { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->moveUp($p); });
$router->post('/admin/payment-channels/{id}/move-down', function ($p) { Middleware::permission('payment.channel.manage'); (new \App\Controllers\Admin\PaymentChannelController())->moveDown($p); });

$router->get('/admin/templates', function () { Middleware::permission('faq.manage'); (new TemplateController())->index(); });
$router->post('/admin/templates/notification/{id}', function ($p) { Middleware::permission('faq.manage'); (new TemplateController())->updateNotification($p); });
$router->post('/admin/templates/email/{id}', function ($p) { Middleware::permission('faq.manage'); (new TemplateController())->updateEmail($p); });

$router->get('/admin/crisis', function () { Middleware::permission('audit.view'); (new CrisisController())->index(); });
$router->get('/admin/contact-messages', function () { Middleware::permission('audit.view'); (new ContactMessageController())->index(); });
$router->get('/admin/contact-messages/{id}/read', function ($p) { Middleware::permission('audit.view'); (new ContactMessageController())->markRead($p); });
$router->get('/admin/audit', function () { Middleware::permission('audit.view'); (new AuditLogController())->index(); });

/* ---------------------------------------------------------------- *
 * SUPER ADMIN ROUTES
 * ---------------------------------------------------------------- */
$router->get('/super/settings', function () { Middleware::permission('integration.manage'); (new SettingController())->index(); });
$router->post('/super/settings/general', function () { Middleware::permission('setting.manage'); (new SettingController())->updateGeneral(); });
$router->post('/super/settings/integration/{provider}', function ($p) { Middleware::permission('integration.manage'); (new SettingController())->updateIntegration($p); });

$router->get('/super/backup', function () { Middleware::permission('backup.manage'); (new BackupController())->index(); });
$router->post('/super/backup/run', function () { Middleware::permission('backup.manage'); (new BackupController())->runDatabaseBackup(); });

$router->get('/super/monitoring', function () { Middleware::role(['super_admin']); (new MonitoringController())->index(); });

/* ---------------------------------------------------------------- */
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
